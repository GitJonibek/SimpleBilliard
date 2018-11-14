<?php
App::import('Service', 'AppService');
App::uses('TeamMember', 'Model');
App::uses('User', 'Model');

use Goalous\Model\Enum as Enum;

/**
 * Class TeamMemberService
 */
class TeamMemberService extends AppService
{
    /**
     * Activate team member
     *
     * @param int $teamId
     * @param int $teamMemberId
     * @param int $opeUserId
     *
     * @return array [error:true|false, msg:""]
     */
    public function activateWithPayment(int $teamId, int $teamMemberId, int $opeUserId): array
    {
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

            // Team member activate
            if (!$TeamMember->activate($teamMemberId)) {
                throw new Exception(sprintf("Failed to activate team member. data:%s",
                    AppUtil::varExportOneLine(compact('teamId', 'teamMemberId'))));
            }

            // Charge if paid plan
            if ($Team->isPaidPlan($teamId)) {
                $PaymentService->charge(
                    $teamId,
                    Enum\ChargeHistory\ChargeType::USER_ACTIVATION_FEE(),
                    1,
                    $opeUserId
                );
            }
        } catch (CreditCardStatusException $e) {
            $this->TransactionManager->rollback();
            CakeLog::error(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            CakeLog::error($e->getTraceAsString());
            $res['error'] = true;
            $res['msg'] = __("Failed to activate team member.") . " " . __('There is a problem with your card.');
            return $res;
        } catch (StripeApiException $e) {
            $this->TransactionManager->rollback();
            CakeLog::emergency(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            CakeLog::emergency($e->getTraceAsString());
            $res['error'] = true;
            $res['msg'] = __("Failed to activate team member.") . " " . __('Please try again later.');
            return $res;
        } catch (Exception $e) {
            $this->TransactionManager->rollback();
            CakeLog::emergency(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            CakeLog::emergency($e->getTraceAsString());
            $res['error'] = true;
            $res['msg'] = __("Failed to activate team member.") . " " . __('System error has occurred.');
            return $res;
        }

        $this->TransactionManager->commit();
        return $res;
    }

    /**
     * Validate activate
     * - Check team plan
     * - Check being team member
     * - Check can activate status
     *
     * @param int $teamMemberId
     *
     * @return bool
     */
    public function validateActivation(int $teamId, int $teamMemberId): bool
    {
        /** @var Team $Team */
        $Team = ClassRegistry::init("Team");
        /** @var TeamMember $TeamMember */
        $TeamMember = ClassRegistry::init("TeamMember");

        // Check team plan
        if (!$Team->isFreeTrial($teamId) && !$Team->isPaidPlan($teamId)) {
            return false;
        }

        // Check is team member
        if (!$TeamMember->isTeamMember($teamId, $teamMemberId)) {
            return false;
        }

        // Check inactive
        if (!$TeamMember->isInactive($teamMemberId)) {
            return false;
        }

        return true;
    }

    /**
     * Inactivate a team member by the team member id
     *
     * @param int $teamMemberId
     *
     * @return bool
     * @throws Exception
     */
    public function inactivate(int $teamMemberId): bool
    {
        /** @var TeamMember $TeamMember */
        $TeamMember = ClassRegistry::init('TeamMember');
        /** @var User $User */
        $User = ClassRegistry::init('User');

        try {
            $this->TransactionManager->begin();

            $res = $TeamMember->inactivate($teamMemberId);

            if (!$res){
                throw new RuntimeException();
            }

            $teamMember = $TeamMember->getById($teamMemberId);
            $user = $User->getById($teamMember['user_id']);

            //If inactivated team ID is the same as user's default one, update user's default team
            if ($user['default_team_id'] == $teamMember['team_id']) {
                $newTeamId = $TeamMember->getLatestLoggedInActiveTeamId($teamMember['user_id']) ?: null;
                $User->updateDefaultTeam($newTeamId, true, $teamMember['user_id']);
            }

            $this->TransactionManager->commit();

            return true;
        } catch (Exception $exception) {
            $this->TransactionManager->rollback();
            throw $exception;
        }
    }
}
