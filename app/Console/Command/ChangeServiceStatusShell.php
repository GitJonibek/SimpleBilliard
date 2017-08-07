<?php
App::uses('AppUtil', 'Util');
App::import('Service', 'TeamService');

/**
 * Batch for changing service status of team.
 * # Description
 * ## Usage
 * - Console/cake change_service_status
 * - Console/cake change_service_status -t [target date] -c [current timestamp]
 * ## changing status in the following order:
 * - Free trial -> Read-only -> Cannot use Service -> Deleted
 * ## How to decide expire date
 * - fetching teams.service_use_state_start_date + Team::DAYS_SERVICE_USE_STATUS[status_name]
 * ## UTC or local date?
 * - UTC only
 *
 * @property TeamService $TeamService
 * @property Team        $Team
 * @property GlRedis     $GlRedis
 */
class ChangeServiceStatusShell extends AppShell
{
    public $TeamService;

    public $uses = [
        'Team',
        'GlRedis',
    ];

    public function startup()
    {
        parent::startup();
        $this->TeamService = ClassRegistry::init('TeamService');
    }

    /**
     * @return ConsoleOptionParser
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        $parser = parent::getOptionParser();
        $options = [
            'targetExpireDate' => [
                'short'   => 't',
                'help'    => 'This is target expire date. It automatically will be yesterday UTC as default',
                'default' => AppUtil::dateYesterday(date('Y-m-d')),
            ],
        ];
        $parser->addOptions($options);
        return $parser;
    }

    function main()
    {
        $targetExpireDate = $this->param('targetExpireDate');
        $this->_changeFreeTrialToReadonly($targetExpireDate);
        $this->_changeReadonlyToCannotUseService($targetExpireDate);
        $this->_deleteCannotUseServiceExpired($targetExpireDate);
    }

    function _changeFreeTrialToReadonly(string $targetExpireDate)
    {

    }

    function _changeReadonlyToCannotUseService(string $targetExpireDate)
    {
        $this->TeamService->changeStatusAllTeamFromReadonlyToCannotUseService($targetExpireDate);
    }

    function _deleteCannotUseServiceExpired(string $targetExpireDate)
    {
        $this->TeamService->deleteTeamCannotUseServiceExpired($targetExpireDate);
    }

}
