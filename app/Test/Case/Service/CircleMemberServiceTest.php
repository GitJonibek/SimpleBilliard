<?php
App::uses('GoalousTestCase', 'Test');
App::uses('Circle', 'Model');
App::import('Service', 'CircleMemberService');
App::uses('CircleMember', 'Model');

/**
 * Created by PhpStorm.
 * User: StephenRaharja
 * Date: 2018/06/04
 * Time: 15:59
 */

use Goalous\Exception as GlException;

class CircleMemberServiceTest extends GoalousTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.circle',
        'app.user',
        'app.circle_member',
        'app.team',
        'app.team_member',
        'app.post_share_circle'
    ];

    public function test_fetchCircles_success()
    {
        /** @var CircleMemberService $CircleMemberService */
        $CircleMemberService = ClassRegistry::init('CircleMemberService');

        $result1 = $CircleMemberService->getUserCircles(1, 1);

        $this->assertNotEmpty($result1);
    }

    public function test_add_success()
    {
        $newCircleId = 2;
        $newUserId = 2;
        $newTeamId = 1;

        /** @var Circle $Circle */
        $Circle = ClassRegistry::init('Circle');

        /** @var CircleMember $CircleMember */
        $CircleMember = ClassRegistry::init('CircleMember');

        $initialMemberCount = $CircleMember->getMemberCount($newCircleId);

        /** @var CircleMemberService $CircleMemberService */
        $CircleMemberService = ClassRegistry::init('CircleMemberService');

        $result = $CircleMemberService->add($newUserId, $newTeamId, $newCircleId);

        $newMemberCount = $CircleMember->getMemberCount($newCircleId);

        $this->assertNotEmpty($result);
        $this->assertNotEmpty($result['id']);
        $this->assertEquals($newCircleId, $result['circle_id']);
        $this->assertEquals($newUserId, $result['user_id']);
        $this->assertEquals($newTeamId, $result['team_id']);
        $this->assertEquals($initialMemberCount + 1, $newMemberCount);
    }

    /**
     * @expectedException \Goalous\Exception\GoalousConflictException
     */
    public function test_addAlreadyExist_failed()
    {
        $newCircleId = 2;
        $newUserId = 2;
        $newTeamId = 1;

        /** @var CircleMemberService $CircleMemberService */
        $CircleMemberService = ClassRegistry::init('CircleMemberService');

        $result = $CircleMemberService->add($newUserId, $newTeamId, $newCircleId);
        $result = $CircleMemberService->add($newUserId, $newTeamId, $newCircleId);
    }

    /**
     * @expectedException  \Goalous\Exception\GoalousNotFoundException
     */
    public function test_addCircleNotExist_failed()
    {
        $newCircleId = 123123;
        $newUserId = 2;
        $newTeamId = 1;

        /** @var CircleMemberService $CircleMemberService */
        $CircleMemberService = ClassRegistry::init('CircleMemberService');

        $result = $CircleMemberService->add($newUserId, $newTeamId, $newCircleId);
    }

    public function test_delete_success()
    {
        $circleId = 1;
        $userId = 1;
        $teamId = 1;

        /** @var CircleMember $CircleMember */
        $CircleMember = ClassRegistry::init('CircleMember');

        /** @var CircleMemberService $CircleMemberService */
        $CircleMemberService = ClassRegistry::init('CircleMemberService');

        $res = $CircleMemberService->delete($userId, $teamId, $circleId);

        $this->assertTrue($res);

        $condition = [
            'conditions' => [
                'circle_id' => $circleId,
                'user_id'   => $userId
            ]
        ];

        $res = $CircleMember->find('first', $condition);

        $this->assertEmpty($res);
    }

    /**
     * @expectedException \Goalous\Exception\GoalousNotFoundException
     */
    public function test_deleteCircleNotExist_failed()
    {
        $circleId = 101293701;
        $userId = 1;
        $teamId = 1;

        /** @var CircleMemberService $CircleMemberService */
        $CircleMemberService = ClassRegistry::init('CircleMemberService');

        $CircleMemberService->delete($userId, $teamId, $circleId);
    }

    /**
     * @expectedException \Goalous\Exception\GoalousNotFoundException
     */
    public function test_deleteAlreadyDeleted_failed()
    {
        $circleId = 1;
        $userId = 1;
        $teamId = 1;

        /** @var CircleMemberService $CircleMemberService */
        $CircleMemberService = ClassRegistry::init('CircleMemberService');

        $CircleMemberService->delete($userId, $teamId, $circleId);
        $CircleMemberService->delete($userId, $teamId, $circleId);
    }

    public function test_setNotificationFlg_success()
    {
        $circleId = 1;
        $userId = 1;

        /** @var CircleMember $CircleMember */
        $CircleMember = ClassRegistry::init('CircleMember');
        /** @var CircleMemberService $CircleMemberService */
        $CircleMemberService = ClassRegistry::init('CircleMemberService');

        $CircleMemberService->setNotificationSetting($circleId, $userId, false);
        $this->assertFalse($CircleMember->getNotificationFlg($circleId, $userId));
        $CircleMemberService->setNotificationSetting($circleId, $userId, false);
        $this->assertFalse($CircleMember->getNotificationFlg($circleId, $userId));
        $CircleMemberService->setNotificationSetting($circleId, $userId, true);
        $this->assertTrue($CircleMember->getNotificationFlg($circleId, $userId));
        $CircleMemberService->setNotificationSetting($circleId, $userId, false);
        $this->assertFalse($CircleMember->getNotificationFlg($circleId, $userId));

    }

    /**
     * @expectedException \Goalous\Exception\GoalousNotFoundException
     */
    public function test_setNotificationFlgNotFound_failed()
    {
        /** @var CircleMemberService $CircleMemberService */
        $CircleMemberService = ClassRegistry::init('CircleMemberService');

        $CircleMemberService->setNotificationSetting(123293, 1, false);
    }
}