<?php
App::uses('Follower', 'Model');

/**
 * Follower Test Case
 *
 * @property Follower $Follower
 */
class FollowerTest extends CakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.follower',
        'app.team',
        'app.badge',
        'app.user',
        'app.email',
        'app.notify_setting',
        'app.comment_like',
        'app.comment',
        'app.post',
        'app.goal',
        'app.goal_category',
        'app.key_result',
        'app.collaborator',
        'app.post_share_user',
        'app.post_share_circle',
        'app.circle',
        'app.circle_member',
        'app.post_like',
        'app.post_read',
        'app.comment_mention',
        'app.given_badge',
        'app.post_mention',
        'app.comment_read',

        'app.oauth_token',
        'app.team_member',
        'app.group',
        'app.member_group',
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
        $this->Follower = ClassRegistry::init('Follower');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Follower);

        parent::tearDown();
    }

    function testAddFollow()
    {
        $this->setDefault();
        $data = [
            'user_id' => 1,
            'team_id' => 1,
            'goal_id' => 100
        ];
        $this->Follower->save($data);
        $this->assertFalse($this->Follower->addFollower(100));
    }

    function testGetFollowerListByGoalId()
    {
        $this->setDefault();
        $expected = [(int)1 => '1'];
        $actual = $this->Follower->getFollowerListByGoalId(1);
        $this->assertEquals($expected, $actual);
    }

    function testGetFollowerByGoalId()
    {
        $this->setDefault();

        // 対象ゴールのフォロワー全員
        $followers = $this->Follower->getFollowerByGoalId(2);
        $this->assertNotEmpty($followers);

        // limit 指定
        $followers2 = $this->Follower->getFollowerByGoalId(2, ['limit' => 1]);
        $this->assertCount(1, $followers2);

        // limit + page 指定
        $followers3 = $this->Follower->getFollowerByGoalId(2, ['limit' => 1, 'page' => 2]);
        $this->assertCount(1, $followers3);
        $this->assertNotEquals($followers2[0]['User']['id'], $followers3[0]['User']['id']);

        // グループ情報付き
        $followers = $this->Follower->getFollowerByGoalId(2);
        $this->assertArrayNotHasKey('Group', $followers[0]);
        $followers = $this->Follower->getFollowerByGoalId(2, ['with_group' => true]);
        $this->assertArrayHasKey('Group', $followers[0]);
    }

    function setDefault()
    {
        $this->Follower->my_uid = 1;
        $this->Follower->current_team_id = 1;
    }

}
