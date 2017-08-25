<?php
App::import('Service', 'AppService');
App::uses('Email', 'Model');

use Goalous\Model\Enum as Enum;

/**
 * Class InvitationService
 */
class InvitationService extends AppService
{
    const MAX_INVITATION_CNT = 100;

    /**
     * Validate emails
     *
     * @param array $emails
     *
     * @return null
     */
    function validateEmails($emails)
    {
        /** @var Email $Email */
        $Email = ClassRegistry::init("Email");

        /* Check empty */
        if (empty($emails) || !is_array($emails) || empty(array_filter($emails))) {
            return [__("Input is required")];
        }
        /* Format validation */
        $errors = [];
        foreach ($emails as $i => $email) {
            if (empty($email)) {
                continue;
            }
            $Email->set(['email' => $email]);
            if (!$Email->validates(['fieldList' => ['email']])) {
                $errors[] = __("Line %d", $i + 1) . "：" . Hash::get($Email->validationErrors, 'email.0');
            }
        }
        if (!empty($errors)) {
            return $errors;
        }

        /* Check invitations limit  */
        if (count($emails) > self::MAX_INVITATION_CNT) {
            return [__("%s invitations are the limits in one time.", self::MAX_INVITATION_CNT)];
        }

        $duplicateEmails = array_diff_key($emails, array_unique($emails));
        foreach ($duplicateEmails as $i => $duplicateEmail) {
            $errors[] = __("Line %d", $i + 1) . "：" . __("%s is duplicated.", __("Email address"));
        }

        return $errors;
    }

    /**
     * Calc charge user count
     *
     * @param int $teamId
     * @param int $addUserCnt
     *
     * @return int
     */
    function calcChargeUserCount(int $teamId, int $addUserCnt): int
    {
        /** @var ChargeHistory $ChargeHistory */
        $ChargeHistory = ClassRegistry::init("ChargeHistory");
        /** @var TeamMember $TeamMember */
        $TeamMember = ClassRegistry::init("TeamMember");

        $maxChargedUserCnt = $ChargeHistory->getLatestMaxChargeUsers($teamId);
        $currentChargeTargetUserCnt = $TeamMember->countChargeTargetUsers();
        // Regard adding users as charge users as it is
        //  if current users does not over max charged users
        if ($currentChargeTargetUserCnt - $maxChargedUserCnt >= 0) {
            return $addUserCnt;
        }
        $chargeUserCnt = $currentChargeTargetUserCnt + $addUserCnt - $maxChargedUserCnt;
        return $chargeUserCnt;
    }

    /**
     * Invite users bulk
     * - Update DB
     *  - invitations
     *  - emails
     *  - users
     *  - team_members
     * - Charge if paid plan
     *
     * @param int   $teamId
     * @param int   $fromUserId
     * @param array $emails
     *
     * @return bool
     */
    function invite(int $teamId, int $fromUserId, array $emails): bool
    {
        /** @var ChargeHistory $ChargeHistory */
        $ChargeHistory = ClassRegistry::init("ChargeHistory");
        /** @var Invite $Invite */
        $Invite = ClassRegistry::init("Invite");
        /** @var Email $Email */
        $Email = ClassRegistry::init("Email");
        /** @var User $User */
        $User = ClassRegistry::init("User");
        /** @var TeamMember $TeamMember */
        $TeamMember = ClassRegistry::init("TeamMember");
        /** @var Team $Team */
        $Team = ClassRegistry::init("Team");
        /** @var PaymentSetting $PaymentSetting */
        $PaymentSetting = ClassRegistry::init("PaymentSetting");
        /** @var PaymentService $PaymentService */
        $PaymentService = ClassRegistry::init('PaymentService');

        try {
            $this->TransactionManager->begin();
            /* Delete old invitations if already invited past */
            if (!$Invite->softDeleteAll(['email' => $emails])) {
                throw new Exception(sprintf("Failed to reset old invitattions. data:%s",
                        AppUtil::varExportOneLine(compact('emails', 'teamId')))
                );
            }
            /* Insert invitations table */
            if (!$Invite->saveBulk($emails, $teamId, $fromUserId)) {
                throw new Exception(sprintf("Failed to insert invitations. data:%s",
                        AppUtil::varExportOneLine(compact('emails', 'teamId', 'fromUserId')))
                );
            }
            /* Insert users table */
            // Get emails of registered users
            $existEmails = Hash::get($Email->findExistUsersByEmail($emails), '{n}.email') ?? [];
            $newEmails = array_diff($emails, $existEmails);

            $insertEmails = [];
            foreach ($newEmails as $email) {
                $User->create();
                if (!$User->save(['team_id' => $teamId], false)) {
                    throw new Exception(sprintf("Failed to insert users. data:%s",
                            AppUtil::varExportOneLine(compact('emails', 'newEmails', 'teamId', 'fromUserId')))
                    );
                }
                $insertEmails[] = [
                    'user_id' => $User->getLastInsertID(),
                    'email'   => $email
                ];
            }
            /* Insert emails table */
            if (!$Email->bulkInsert($insertEmails)) {
                throw new Exception(sprintf("Failed to insert emails. data:%s",
                        AppUtil::varExportOneLine(compact('emails', 'insertEmails', 'teamId')))
                );
            }

            /* Insert team_members table */
            // Except for already belonged to target team
            $targetUserIds = $User->findNotBelongToTeamByEmail($emails);
            $insertTeamMembers = [];
            foreach ($targetUserIds as $userId) {
                $insertTeamMembers[] = [
                    'user_id' => $userId,
                    'team_id' => $teamId,
                    'status'  => TeamMember::USER_STATUS_INVITED
                ];
            }
            if (!$TeamMember->bulkInsert($insertTeamMembers)) {
                throw new Exception(sprintf("Failed to insert team members. data:%s",
                        AppUtil::varExportOneLine(compact('insertTeamMembers', 'emails')))
                );
            }

            /* Charge if paid plan */
            if ($Team->isPaidPlan($teamId)) {
                $chargeUserCnt = count($targetUserIds);
                // [Important] Transaction commit in this method
                $PaymentService->charge(
                    $teamId, Enum\ChargeHistory\ChargeType::USER_INCREMENT_FEE(),
                    $chargeUserCnt
                );
            }
            $this->TransactionManager->commit();
        } catch (Exception $e) {
            $this->TransactionManager->rollback();
            $this->log(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            $this->log($e->getTraceAsString());
            return false;
        }
        return true;
    }
}
