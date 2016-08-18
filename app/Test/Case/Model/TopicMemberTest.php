<?php
App::uses('TopicMember', 'Model');
App::uses('GoalousTestCase', 'Test');

/**
 * TopicMember Test Case
 *
 * @property TopicMember $TopicMember
 */
class TopicMemberTest extends GoalousTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.topic_member',
        'app.topic',
        'app.user',
        'app.team',
        'app.badge',
        'app.circle',
        'app.circle_member',
        'app.post_share_circle',
        'app.post',
        'app.goal',
        'app.purpose',
        'app.goal_category',
        'app.key_result',
        'app.action_result',
        'app.action_result_file',
        'app.attached_file',
        'app.comment_file',
        'app.comment',
        'app.comment_like',
        'app.comment_read',
        'app.post_file',
        'app.collaborator',
        'app.approval_history',
        'app.follower',
        'app.evaluation',
        'app.evaluate_term',
        'app.evaluator',
        'app.evaluate_score',
        'app.post_share_user',
        'app.post_like',
        'app.post_read',
        'app.comment_mention',
        'app.given_badge',
        'app.post_mention',
        'app.group',
        'app.member_group',
        'app.group_vision',
        'app.invite',
        'app.job_category',
        'app.team_member',
        'app.member_type',
        'app.evaluation_setting',
        'app.team_vision',
        'app.team_insight',
        'app.group_insight',
        'app.circle_insight',
        'app.access_user',
        'app.email',
        'app.notify_setting',
        'app.oauth_token',
        'app.local_name',
        'app.recovery_code',
        'app.device'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->TopicMember = ClassRegistry::init('TopicMember');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->TopicMember);

        parent::tearDown();
    }

    public function testDummy()
    {

    }

}
