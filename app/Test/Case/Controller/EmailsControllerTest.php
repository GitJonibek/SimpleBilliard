<?php App::uses('GoalousControllerTestCase', 'Test');
App::uses('EmailsController', 'Controller');

/**
 * EmailsController Test Case
 * @method testAction($url = '', $options = array()) GoalousControllerTestCase::_testAction
 */
class EmailsControllerTest extends GoalousControllerTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.local_name',
        'app.cake_session',
        'app.email',
        'app.evaluation_setting',
        'app.user', 'app.notify_setting',
        'app.team',
        'app.badge',
        'app.comment_like',
        'app.comment',
        'app.post',
        'app.comment_mention',
        'app.given_badge',
        'app.post_like',
        'app.post_mention',
        'app.post_read',
        'app.comment_read',
        'app.group',
        'app.team_member',
        'app.job_category',
        'app.invite',

        'app.thread',
        'app.message',
        'app.follower',
        'app.collaborator',
        'app.goal',
        'app.goal_category',
        'app.circle_member',
        'app.oauth_token'
    );

    /**
     * testDelete method
     *
     * @return void
     */
    public function testDeleteFail()
    {
        /**
         * @var EmailsController $Emails
         */
        $Emails = $this->generate('Emails', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
            ]
        ]);
        $value_map = [
            ['id', "10"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Emails->Auth->expects($this->any())->method('loggedIn')
                     ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Emails->Auth->staticExpects($this->any())->method('user')
                     ->will($this->returnValueMap($value_map));
        try {
            $this->testAction('emails/delete', ['method' => 'POST']);
        } catch (NotFoundException $e) {
        }
        $Emails->Team->EvaluateTerm->my_uid = 1;
        $Emails->Team->EvaluateTerm->current_team_id = 1;

        $this->assertTrue(isset($e), "[異常]メアド削除");
    }

    public function testDeleteNotOwn()
    {
        /**
         * @var EmailsController $Emails
         */
        $Emails = $this->generate('Emails', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
            ]
        ]);
        $value_map = [
            ['id', "10"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Emails->Auth->expects($this->any())->method('loggedIn')
                     ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Emails->Auth->staticExpects($this->any())->method('user')
                     ->will($this->returnValueMap($value_map));
        $Emails->Team->EvaluateTerm->my_uid = 1;
        $Emails->Team->EvaluateTerm->current_team_id = 1;
        $email_id = "10";
        try {
            $this->testAction('emails/delete/' . $email_id, ['method' => 'POST']);
        } catch (NotFoundException $e) {
        }
        $this->assertTrue(isset($e), "[異常]所有していないメアド削除");
    }

    public function testDeleteSuccess()
    {
        /**
         * @var EmailsController $Emails
         */
        $Emails = $this->generate('Emails', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
            ]
        ]);
        $value_map = [
            ['id', "10"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Emails->Auth->expects($this->any())->method('loggedIn')
                     ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Emails->Auth->staticExpects($this->any())->method('user')
                     ->will($this->returnValueMap($value_map));
        $Emails->Team->EvaluateTerm->my_uid = 1;
        $Emails->Team->EvaluateTerm->current_team_id = 1;
        $email_id = '9';
        try {
            $this->testAction('emails/delete/' . $email_id, ['method' => 'POST']);
        } catch (NotFoundException $e) {
        }
        $this->assertFalse(isset($e), "[正常]メアド削除");
    }

}
