<?php App::uses('GoalousTestCase', 'Test');
App::uses('SendMailToUser', 'Model');

/**
 * SendMailToUser Test Case
 *
 * @property SendMailToUser $SendMailToUser
 */
class SendMailToUserTest extends GoalousTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.send_mail_to_user',
        'app.send_mail',
        'app.user',
        'app.team',
        'app.badge',
        'app.comment_like',
        'app.comment',
        'app.post',
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
        'app.group',
        'app.team_member',
        'app.job_category',
        'app.invite',
        'app.email',
        'app.notify_setting',
        'app.oauth_token',
        'app.local_name'
    );

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->SendMailToUser = ClassRegistry::init('SendMailToUser');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->SendMailToUser);

        parent::tearDown();
    }

    function testGetToUserList()
    {
        $this->SendMailToUser->getToUserList(1);
    }

}
