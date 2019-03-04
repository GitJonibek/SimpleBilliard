<?php
App::uses('AppUtil', 'Util');
App::import('Service', 'TeamService');

/**
 * Batch for changing service status of team.
 * # Description
 * ## Usage
 * - Console/cake Payment.change_service_status
 * - Console/cake Payment.change_service_status -t [target date]
 * ## changing status in the following order:
 * - Free trial -> Read-only -> Cannot use Service -> Deleted
 * ## UTC or local date?
 * - UTC only
 *
 * @property TeamService $TeamService
 */
class ChangeServiceStatusShell extends AppShell
{
    protected $enableOutputLogStartStop = true;

    public $TeamService;

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
                'default' => null,
            ],
        ];
        $parser->addOptions($options);
        return $parser;
    }

    function main()
    {
        $targetExpireDate = $this->param('targetExpireDate') ?? GoalousDateTime::now()->subDay(2)->format('Y-m-d');
        $this->logInfo(sprintf('target expire date: %s', $targetExpireDate));
        // updating status from Free-trial to Read-only
        $this->TeamService->changeStatusAllTeamExpired(
            $targetExpireDate,
            Team::SERVICE_USE_STATUS_FREE_TRIAL,
            Team::SERVICE_USE_STATUS_READ_ONLY
        );
        // updating status from Read-only to Cannot-use-service
        $this->TeamService->changeStatusAllTeamExpired(
            $targetExpireDate,
            Team::SERVICE_USE_STATUS_READ_ONLY,
            Team::SERVICE_USE_STATUS_CANNOT_USE
        );
        $this->TeamService->deleteTeamCannotUseServiceExpired($targetExpireDate);
        // updating status from Paid to Read-only
        $this->TeamService->changePaidTeamToReadOnly($targetExpireDate);
    }
}
