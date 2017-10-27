<?php
App::uses('GoalousTestCase', 'Test');

/**
 * Class CampaignServiceTest
 *
 * @property CampaignService    $CampaignService
 */
class CampaignServiceTest extends GoalousTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.user',
        'app.team',
        'app.team_member',
        'app.campaign_team',
        'app.mst_price_plan_group',
        'app.mst_price_plan',
    );

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->setDefaultTeamIdAndUid();
        $this->CampaignService = ClassRegistry::init('CampaignService');
        $this->Team = $this->Team ?? ClassRegistry::init('Team');
    }

    private function _setupCampaign(int $teamId)
    {
        /** @var CampaignPriceGroup $CampaignPriceGroup */
        $CampaignPriceGroup = ClassRegistry::init('CampaignPriceGroup');
        /** @var CampaignPricePlan $CampaignPricePlan */
        $CampaignPricePlan = ClassRegistry::init('CampaignPricePlan');
        /** @var CampaignTeam $CampaignTeam */
        $CampaignTeam = ClassRegistry::init('CampaignTeam');
        // Create price group
        $pricePlanGroup = [
            'currency' => 1,
        ];
        $CampaignPriceGroup->create();
        $CampaignPriceGroup->save($pricePlanGroup, false);

        // Price plans
        $pricePlans = [
            [
                'group_id' => 1,
                'code' => 'C50',
                'max_members' => 50,
            ],
            [
                'group_id' => 1,
                'code' => 'C200',
                'max_members' => 200,
            ],
            [
                'group_id' => 1,
                'code' => 'C300',
                'max_members' => 300,
            ],
            [
                'group_id' => 1,
                'code' => 'C400',
                'max_members' => 400,
            ],
            [
                'group_id' => 1,
                'code' => 'C500',
                'max_members' => 500,
            ]
        ];
        $CampaignPricePlan->bulkInsert($pricePlans);

        // Create campaign team
        $campaignTeam = [
            'team_id' => $teamId,
            'campaign_type' => 0,
            'price_plan_group_id' => 1,
            'start_date' => '2017-10-27',
        ];

        $CampaignTeam->create();
        $CampaignTeam->save($campaignTeam);
    }

    function test_isCampaignTeam()
    {
        $this->_setupCampaign(1);
        $isCampaign = $this->CampaignService->isCampaignTeam(1);

        $this->assertTrue($isCampaign);
    }

    function test_isCampaignTeam_false()
    {
        $isCampaign = $this->CampaignService->isCampaignTeam(1);
        $this->assertFalse($isCampaign);
    }

    function test_purchased()
    {

    }

    function test_getMaxAllowedUsers()
    {

    }

    function willExceedMaximumCampaignAllowedUser()
    {

    }

    function tearDown()
    {
        parent::tearDown();

        $this->Team->resetCurrentTeam();
    }
}