<?php
App::import('Service', 'AppService');
App::import('Service', 'PaymentService');
App::uses('Email', 'Model');
App::uses('AppController', 'Controller');
App::uses('ComponentCollection', 'Controller');
App::uses('Component', 'Controller');
App::uses('GlEmailComponent', 'Controller/Component');

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
     * @param int   $teamId
     * @param array $emails
     *
     * @return array
     */
    function validateEmails(int $teamId, $emails): array
    {
        /** @var Email $Email */
        $Email = ClassRegistry::init("Email");

        /* Check empty */
        if (empty($emails) || !is_array($emails) || empty(array_filter($emails))) {
            return [__("Input is required")];
        }
        /* Format validation */
        $errors = [];
        $Email->validate = [
            'email' => [
                'maxLength' => ['rule' => ['maxLength', 255]],
                'notBlank'  => ['rule' => 'notBlank',],
                'email'     => ['rule' => ['email'],],
            ],
        ];
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

        $uniqueEmails = array_unique($emails);
        $duplicateEmails = array_diff_key($emails, $uniqueEmails);
        foreach ($duplicateEmails as $i => $duplicateEmail) {
            $errors[] = __("Line %d", $i + 1) . "：" . __("%s is duplicated.", __("Email address"));
        }
        if (!empty($errors)) {
            return $errors;
        }
        $existEmails = $Email->findExistByTeamId($teamId, $emails);
        $errEmails = array_intersect($emails, $existEmails);
        foreach ($errEmails as $i => $mail) {
            $errors[] = __("Line %d",
                    $i + 1) . "：" . __("This email address has already been used. Use another email address.");
        }
        if (!empty($errors)) {
            return $errors;
        }

        $existEmails = $Email->findNotBelongAnyTeamsByEmails($emails);
        $errEmails = array_intersect($emails, $existEmails);
        foreach ($errEmails as $i => $mail) {
            $errors[] = __("Line %d",
                    $i + 1) . "：" . __("This email address has already been used. Use another email address.");
        }
        if (!empty($errEmails)) {
            CakeLog::info(sprintf("[%s] Users with email address does not belong to any team. emails:%s", __METHOD__,
                AppUtil::jsonOneLine($errEmails)));
        }
        return $errors;
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
     * @return array [error:true|false, msg:""]
     */
    function invite(int $teamId, int $fromUserId, array $emails): array
    {
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
        /** @var PaymentService $PaymentService */
        $PaymentService = ClassRegistry::init('PaymentService');

        $res = [
            'error' => false,
            'msg'   => "",
        ];

        try {
            $this->TransactionManager->begin();

            $chargeUserCnt = $PaymentService->calcChargeUserCount($teamId, count($emails));

            /* Insert invitations table */
            if (!$Invite->saveBulk($emails, $teamId, $fromUserId)) {
                throw new Exception(sprintf("Failed to insert invitations. data:%s",
                        AppUtil::varExportOneLine(compact('emails', 'teamId', 'fromUserId')))
                );
            }
            /* Insert users table */
            // Get emails of registered users
            $existEmails = Hash::extract($Email->findExistUsersByEmail($emails), '{n}.email') ?? [];
            $newEmails = array_diff($emails, $existEmails);

            $insertEmails = [];
            foreach ($newEmails as $email) {
                $User->create();
                // There is nothing to specify for saving user table
                if (!$User->save([], false)) {
                    throw new Exception("Failed to insert users.");
                }
                $insertEmails[] = [
                    'user_id' => $User->getLastInsertID(),
                    'email'   => $email
                ];
            }
            /* Insert emails table */
            if (!empty($insertEmails)) {
                if (!$Email->bulkInsert($insertEmails)) {
                    throw new Exception(sprintf("Failed to insert emails. data:%s",
                            AppUtil::varExportOneLine(compact('emails', 'insertEmails', 'teamId')))
                    );
                }
            }

            /* Insert team_members table */
            // Except for already belonged to target team
            $targetUserIds = $User->findNotBelongToTeamByEmail($teamId, $emails);
            if (count($targetUserIds) != count($emails)) {
                throw new Exception(sprintf("Inconsistent users and emails. data:%s",
                        AppUtil::varExportOneLine(compact('emails', 'targetUserIds', 'teamId')))
                );
            }

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
            // TODO.payment: Should we store $addUserCnt to DB?
            $addUserCnt = count($targetUserIds);
            if ($Team->isPaidPlan($teamId) && $chargeUserCnt > 0) {
                // [Important] Transaction commit in this method
                $PaymentService->charge(
                    $teamId,
                    Enum\ChargeHistory\ChargeType::USER_INCREMENT_FEE(),
                    $chargeUserCnt,
                    $fromUserId
                );
            }
            $this->TransactionManager->commit();
        } catch (CreditCardStatusException $e) {
            $this->TransactionManager->rollback();
            CakeLog::error(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            CakeLog::error($e->getTraceAsString());
            $res['error'] = true;
            $res['msg'] = __('Invitation was failed.') . " " . __('There is a problem with your card.');
            return $res;
        } catch (StripeApiException $e) {
            $this->TransactionManager->rollback();
            CakeLog::emergency(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            CakeLog::emergency($e->getTraceAsString());
            $res['error'] = true;
            $res['msg'] = __('Invitation was failed.') . " " . __('Please try again later.');
            return $res;
        } catch (Exception $e) {
            $this->TransactionManager->rollback();
            CakeLog::emergency(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            CakeLog::emergency($e->getTraceAsString());
            $res['error'] = true;
            $res['msg'] = __('Invitation was failed.') . " " . __('System error has occurred.');
            return $res;
        }
        return $res;
    }

    /**
     * @param Exception $e
     */
    private function _errorLogging(Exception $e)
    {
        CakeLog::error(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
        CakeLog::error($e->getTraceAsString());
    }

    function reInvite(array $inviteData, array $emailData, string $email): bool
    {
        /** @var Email $Email */
        $Email = ClassRegistry::init('Email');
        /** @var Team $Team */
        $Team = ClassRegistry::init('Team');
        /** @var Invite $Invite */
        $Invite = ClassRegistry::init('Invite');

        try {
            $this->TransactionManager->begin();
            // create invitation data
            $inviteNew = $Team->Invite->saveInvite(
                $email,
                $inviteData['team_id'],
                $inviteData['from_user_id'],
                !empty($inviteData['message']) ? $inviteData['message'] : null
            );
            if (false === $inviteNew) {
                throw new RuntimeException(sprintf("[%s]%s data:%s", __METHOD__,
                    'DB error, insert new invite failed',
                    AppUtil::varExportOneLine([
                        'invites.id' => $inviteData['id'],
                        'email'      => $email,
                    ])));
            }
            // update emails.email
            $emailData['email'] = $email;
            if (false === $Email->save($emailData)) {
                throw new RuntimeException(sprintf("[%s]%s data:%s", __METHOD__,
                    'DB error, update email failed',
                    AppUtil::varExportOneLine([
                        'invites.id' => $inviteData['id'],
                        'email'      => $email,
                    ])));
            }
            // cancel old invitation
            // this method return false even if delete(update del_flag=1) success...
            $Invite->delete($inviteData['id']);

            $this->TransactionManager->commit();
        } catch (Exception $e) {
            $this->TransactionManager->rollback();
            CakeLog::error(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            CakeLog::error($e->getTraceAsString());
            return false;
        }
        CakeLog::info(sprintf("[%s]%s data:%s", __METHOD__,
            'Re-invite succeed',
            AppUtil::varExportOneLine([
                'old.invites.id' => $inviteData['id'],
                'new.invites.id' => $inviteNew['Invite']['id'],
                'email'          => $email,
            ])));
        return true;
    }

    /**
     * validate email string
     * return array for {Controller}->_getResponseValidationFail()
     *
     * @param string $email
     *
     * @return array
     */
    public function validateEmail(string $email): array
    {
        /** @var Email $Email */
        $Email = ClassRegistry::init("Email");
        $Email->validate = [
            'email' => [
                'maxLength' => ['rule' => ['maxLength', 255]],
                'notBlank'  => ['rule' => 'notBlank',],
                'email'     => ['rule' => ['email'],],
            ],
        ];
        $Email->set(['email' => $email]);
        $Email->validates();
        return $this->validationExtract($Email->validationErrors);
    }
}
