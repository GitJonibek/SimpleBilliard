<?php App::uses('GoalousTestCase', 'Test');
App::uses('CircleMember', 'Model');

/**
 * CircleMember Test Case
 *
 * @property CircleMember $CircleMember
 */
class CircleMemberTest extends GoalousTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.circle_member',
        'app.circle',
        'app.team',
        'app.badge',
        'app.user', 'app.notify_setting',
        'app.email',
        'app.comment_like',
        'app.comment',
        'app.post',
        'app.post_like',
        'app.post_read',
        'app.comment_mention',
        'app.given_badge',
        'app.post_mention',
        'app.comment_read',

        'app.oauth_token',
        'app.team_member',
        'app.group',
        'app.job_category',
        'app.local_name',
        'app.invite',
        'app.thread',
        'app.message'
    );

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->CircleMember = ClassRegistry::init('CircleMember');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->CircleMember);

        parent::tearDown();
    }

    function _setDefault($uid, $team_id)
    {
        $this->CircleMember->my_uid = $uid;
        $this->CircleMember->current_team_id = $team_id;
        $this->CircleMember->Circle->my_uid = $uid;
        $this->CircleMember->Circle->current_team_id = $team_id;
        $this->CircleMember->Team->TeamMember->my_uid = $uid;
        $this->CircleMember->Team->TeamMember->current_team_id = $team_id;

    }

    function testGetMemberList()
    {
        $uid = 1;
        $team_id = 1;
        $this->_setDefault($uid, $team_id);
        $this->CircleMember->getMemberList(1, true);
        $this->CircleMember->getMemberList(1, true, false);
    }

    function testGetAdminMemberList()
    {
        $uid = 1;
        $team_id = 1;
        $this->_setDefault($uid, $team_id);
        $this->CircleMember->getAdminMemberList(1);
        $this->CircleMember->getAdminMemberList(1, true);
    }

    function testGetMemberListNotWithMe()
    {
        $uid = 1;
        $team_id = 1;
        $this->_setDefault($uid, $team_id);
        $this->CircleMember->getMemberList(1, false);
    }

    public function testGetCircleInitMemberSelect2()
    {
        $uid = 1;
        $team_id = 1;
        $this->_setDefault($uid, $team_id);
        $this->CircleMember->getCircleInitMemberSelect2(1, true);
    }

    public function testIncrementUnreadCount()
    {
        $uid = 1;
        $team_id = 1;
        $this->_setDefault($uid, $team_id);
        $this->CircleMember->incrementUnreadCount([]);
    }

    public function testUpdateModified()
    {
        $this->CircleMember->my_uid = 1;
        $circle_list = [1, 2];
        $this->CircleMember->current_team_id = 1;
        $now = time();
        $this->CircleMember->updateModified($circle_list);
        $res = $this->CircleMember->find('all', ['conditions' => ['CircleMember.circle_id' => $circle_list]]);
        $this->assertGreaterThanOrEqual($now * 2,
                                        $res[0]['CircleMember']['modified'] + $res[1]['CircleMember']['modified']);
    }

    public function testUpdateModifiedIfEmpty()
    {
        $circle_list = [];
        $res = $this->CircleMember->updateModified($circle_list);
        $this->assertFalse($res);
    }

    public function testJoinNewMemberSuccess()
    {
        $circle_id = '18';
        $this->CircleMember->my_uid = 1;
        $this->CircleMember->current_team_id = 1;

        $res = $this->CircleMember->joinNewMember($circle_id);
        $this->assertTrue(!empty($res));
    }

    public function testUnjoinMember()
    {
        $circle_id = '1';
        $this->CircleMember->my_uid = 1;
        $this->CircleMember->current_team_id = 1;

        // 既存のメンバー
        $users = $this->CircleMember->find(
            'list',
            [
                'fields'     => [
                    'CircleMember.user_id',
                    'CircleMember.user_id'
                ],
                'conditions' => [
                    'CircleMember.circle_id' => $circle_id
                ],
            ]
        );
        $this->assertTrue(isset($users[$this->CircleMember->my_uid]));

        // メンバーから外す
        $res = $this->CircleMember->unjoinMember($circle_id);
        $this->assertTrue($res);

        // 自身が消えているか確認
        $users = $this->CircleMember->find(
            'list',
            [
                'fields'     => [
                    'CircleMember.user_id',
                    'CircleMember.user_id'
                ],
                'conditions' => [
                    'CircleMember.circle_id' => $circle_id
                ],
            ]
        );
        $this->assertFalse(isset($users[$this->CircleMember->my_uid]));
    }

    public function testUnjoinMemberWithUserId()
    {
        $this->CircleMember->current_team_id = 1;
        $this->CircleMember->my_uid = 1;
        $circle_id = '1';
        $user_id = '2';

        // 既存のメンバー
        $users = $this->CircleMember->find(
            'list',
            [
                'fields'     => [
                    'CircleMember.user_id',
                    'CircleMember.user_id'
                ],
                'conditions' => [
                    'CircleMember.circle_id' => $circle_id
                ],
            ]
        );
        $this->assertTrue(isset($users[$user_id]));

        // ユーザーID指定でサークルから削除
        $res = $this->CircleMember->unjoinMember($circle_id, $user_id);
        $this->assertTrue($res);

        // 指定ユーザーが消えているか確認
        $users = $this->CircleMember->find(
            'list',
            [
                'fields'     => [
                    'CircleMember.user_id',
                    'CircleMember.user_id'
                ],
                'conditions' => [
                    'CircleMember.circle_id' => $circle_id
                ],
            ]
        );
        $this->assertFalse(isset($users[$user_id]));
        $this->assertTrue(isset($users[$this->CircleMember->my_uid]));
    }

    public function testUnjoinMemberWithInvalidUser()
    {
        $this->CircleMember->current_team_id = 1;
        $this->CircleMember->my_uid = 1;
        $circle_id = '1';
        $user_id = '9999';

        // ユーザーID指定でサークルから削除
        $res = $this->CircleMember->unjoinMember($circle_id, $user_id);
        $this->assertEmpty($res);
    }

    public function testShowHideStats()
    {
        $this->_setDefault(1, 1);
        $result = $this->CircleMember->getShowHideStatus(1, 1);
        $this->assertTrue($result);
    }

    public function testGetMyCircle()
    {
        $this->CircleMember->my_uid = 1;
        $this->CircleMember->current_team_id = 1;

        $result = $this->CircleMember->getMyCircle();
        $this->assertNotEmpty($result);
        $result = $this->CircleMember->getMyCircle();
        $this->assertNotEmpty($result);
        // 先頭はチーム全体サークル
        $this->assertEquals(1, $result[0]['Circle']['team_all_flg']);

        $result = $this->CircleMember->getMyCircle(['circle_created_start' => 500]);
        foreach ($result as $circle) {
            $this->assertGreaterThanOrEqual(500, $circle['Circle']['created']);
        }

        $result = $this->CircleMember->getMyCircle(['circle_created_end' => 500]);
        foreach ($result as $circle) {
            $this->assertLessThan(500, $circle['Circle']['created']);
        }

        $result = $this->CircleMember->getMyCircle(['order' => ['Circle.created desc']]);
        $prev_created = PHP_INT_MAX;
        foreach ($result as $circle) {
            $this->assertLessThanOrEqual($prev_created, $circle['Circle']['created']);
            $prev_created = $circle['Circle']['created'];
        }

    }

    public function testEditAdminStatus()
    {
        $this->CircleMember->current_team_id = 1;
        $circle_id = 1;
        $user_id = 2;

        // 管理者でないことを確認
        $this->assertEmpty($this->CircleMember->isAdmin($user_id, $circle_id));

        // 管理者に変更に変更
        $res = $this->CircleMember->editAdminStatus($circle_id, $user_id, 1);
        $this->assertTrue($res);
        $this->assertEquals(1, $this->CircleMember->getAffectedRows());
        $this->assertNotEmpty($this->CircleMember->isAdmin($user_id, $circle_id));

        // 通常ユーザーに変更に変更
        $res = $this->CircleMember->editAdminStatus($circle_id, $user_id, 0);
        $this->assertTrue($res);
        $this->assertEquals(1, $this->CircleMember->getAffectedRows());
        $this->assertEmpty($this->CircleMember->isAdmin($user_id, $circle_id));
    }

    function testGetActiveMemberCount()
    {
        $this->CircleMember->current_team_id = 9000;
        $this->CircleMember->my_uid = 9001;
        $this->CircleMember->User->TeamMember->current_team_id = 9000;
        $this->CircleMember->User->TeamMember->my_uid = 9001;

        $res = $this->CircleMember->getActiveMemberCount(9000);
        $this->assertEquals(2, $res);
    }

    function testGetActiveMemberCountList()
    {
        $this->CircleMember->current_team_id = 1;
        $this->CircleMember->my_uid = 1;
        $this->CircleMember->Circle->current_team_id = 1;
        $this->CircleMember->Circle->my_uid = 1;
        $this->CircleMember->User->TeamMember->current_team_id = 1;
        $this->CircleMember->User->TeamMember->my_uid = 1;

        $count_list = $this->CircleMember->getActiveMemberCountList(array_keys($this->CircleMember->Circle->getList()));
        foreach ($count_list as $id => $count) {
            $this->assertEquals($this->CircleMember->getActiveMemberCount($id), $count);
        }
    }

    function testGetNonCircleMemberSelect2()
    {
        $this->_setDefault(1, 1);
        $data = [
            'Circle' => [
                'name'       => 'test',
                'public_flg' => true,
                'team_id'    => 1,
            ]
        ];
        $this->CircleMember->Circle->save($data);
        $res = $this->CircleMember->getNonCircleMemberSelect2($this->CircleMember->Circle->getLastInsertID(), 'test');
        $this->assertNotEmpty($res);
    }

    function testUpdateUnreadCount()
    {
        $this->_setDefault(1, 1);
        $res = $this->CircleMember->updateUnreadCount(1);
        $this->assertTrue($res);
    }

    function testJoinCircle()
    {
        $this->_setDefault(1, 1);
        $res = $this->CircleMember->joinCircle([]);
        $this->assertFalse($res);
        $this->CircleMember->Circle->save([
                                              'name' => 'test'
                                          ]);
        $postData = [
            'Circle' => [
                [
                    'circle_id' => 3,
                    'join'      => true
                ],
                [
                    'circle_id' => 2,
                    'join'      => true
                ],
                [
                    'circle_id' => 1,
                    'join'      => false
                ],
                [
                    'circle_id' => $this->CircleMember->Circle->getLastInsertID(),
                    'join'      => true
                ],
            ]
        ];
        $this->assertTrue($this->CircleMember->joinCircle($postData));
    }

    function testCircleStatusToggle()
    {
        $this->_setDefault(1, 1);
        $res = $this->CircleMember->circleStatusToggle(1, 1);
        $this->assertTrue($res);
    }

}
