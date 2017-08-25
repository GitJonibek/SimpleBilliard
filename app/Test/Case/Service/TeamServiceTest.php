<?php
App::uses('GoalousTestCase', 'Test');
App::import('Service', 'TeamService');

/**
 * @property TeamService $TeamService
 */
class TeamServiceTest extends GoalousTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.term',
        'app.user',
        'app.team',
    );

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->TeamService = ClassRegistry::init('TeamService');
        $this->Team = ClassRegistry::init('Team');
    }

    function test_getServiceUseStatus_success()
    {
        $teamId = $this->createTeam(['service_use_status' => Team::SERVICE_USE_STATUS_FREE_TRIAL]);
        $this->setDefaultTeamIdAndUid(1, $teamId);
        $this->assertEquals($this->TeamService->getServiceUseStatus(), Team::SERVICE_USE_STATUS_FREE_TRIAL);
    }

    function test_getReadOnlyEndDate_success()
    {
        $teamId = $this->createTeam([
            'service_use_status'           => Team::SERVICE_USE_STATUS_READ_ONLY,
            'service_use_state_start_date' => '2017-01-10',
            'service_use_state_end_date'   => '2017-02-09',
        ]);
        $this->setDefaultTeamIdAndUid(1, $teamId);
        $this->TeamService->getStateEndDate();
        $this->assertEquals($this->TeamService->getStateEndDate(), '2017-02-09');
    }

    function test_updateServiceUseStatus_success()
    {
        $teamId = $this->createTeam(['service_use_status' => Team::SERVICE_USE_STATUS_FREE_TRIAL]);
        $this->setDefaultTeamIdAndUid(1, $teamId);

        $res = $this->TeamService->updateServiceUseStatus($teamId, Team::SERVICE_USE_STATUS_PAID, date('Y-m-d'));

        $this->assertTrue($res === true);
        $this->assertEquals($this->TeamService->getServiceUseStatus(), Team::SERVICE_USE_STATUS_PAID);
    }

}
