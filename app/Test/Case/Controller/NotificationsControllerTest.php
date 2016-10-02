<?php App::uses('GoalousControllerTestCase', 'Test');
App::uses('NotificationsController', 'Controller');

/**
 * NotificationsController Test Case
 * @method testAction($url = '', $options = array()) GoalousControllerTestCase::_testAction
 */
class NotificationsControllerTest extends GoalousControllerTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.evaluate_term',
        'app.user',
        'app.team',
        'app.badge',
        'app.comment_like',
        'app.comment',
        'app.post',
        'app.goal',

        'app.goal_category',
        'app.key_result',
        'app.action_result',
        'app.collaborator',
        'app.follower',
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
        'app.member_group',
        'app.invite',
        'app.job_category',
        'app.team_member',
        'app.member_type',
        'app.evaluator',
        'app.evaluation_setting',
        'app.email',
        'app.notify_setting',
        'app.oauth_token',
        'app.local_name',
        'app.approval_history',
        'app.evaluation'
    );

    function testIndex()
    {
        $this->_getNotificationsCommonMock();
        $this->testAction('/notifications/', ['method' => 'GET']);
    }

    function testAjaxGetOldNotifyMoreCaseItemCntIsZero()
    {
        $oldest_score_id = 1;
        $this->_getNotificationsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction("/notifications/ajax_get_old_notify_more/{$oldest_score_id}", ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetOldNotifyMoreCaseItemCntIsMany()
    {
        $oldest_score_id = 1;
        $Notifications = $this->_getNotificationsCommonMock();
        $return_value_map = [
            [
                NOTIFY_PAGE_ITEMS_NUMBER,
                (string)$oldest_score_id,
                [
                    [
                        'User'         => [
                            'id'               => 1,
                            'display_username' => 'test taro',
                            'photo_file_name'  => null,
                        ],
                        'Notification' => [
                            'title'      => 'test taroさんがあなたの投稿にコメントしました。',
                            'url'        => 'http://192.168.50.4/post_permanent/1/from_notification:1',
                            'unread_flg' => false,
                            'created'    => '1429643033',
                            'score'      => 1000,
                            'body'       => 'testA',
                            'type'       => 1,
                        ]
                    ],
                    [
                        'User'         => [
                            'id'               => 2,
                            'display_username' => 'test jiro',
                            'photo_file_name'  => null,
                        ],
                        'Notification' => [
                            'title'      => 'test jiroさんがあなたの投稿にコメントしました。',
                            'url'        => 'http://192.168.50.4/post_permanent/2/from_notification:1',
                            'unread_flg' => false,
                            'created'    => '1429643033',
                            'score'      => 1001,
                            'body'       => 'testB',
                            'type'       => 1,
                        ]
                    ],

                ]
            ]
        ];

        /** @noinspection PhpUndefinedMethodInspection */
        $Notifications->NotifyBiz->expects($this->any())->method('getNotification')
                                 ->will($this->returnValueMap($return_value_map));
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction("/notifications/ajax_get_old_notify_more/{$oldest_score_id}", ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetNewNotifyCount()
    {
        $this->_getNotificationsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction("/notifications/ajax_get_new_notify_count", ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetLatestNotifyItems()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/notifications/ajax_get_latest_notify_items', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetNewMessageNotifyCount()
    {
        $this->_getNotificationsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction("/notifications/ajax_get_new_message_notify_count", ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetLatestMessageNotifyItems()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/notifications/ajax_get_latest_message_notify_items', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function _getNotificationsCommonMock()
    {
        /**
         * @var NotificationsController $Notifications
         */
        $Notifications = $this->generate('Notifications', [
            'components' => [
                'Session',
                'Auth'      => ['user', 'loggedIn'],
                'Security'  => ['_validateCsrf', '_validatePost'],
                'NotifyBiz' => ['getNotification'],
                'GlEmail',
            ]
        ]);
        $value_map = [
            [
                null,
                [
                    'id'         => '1',
                    'last_first' => true,
                    'language'   => 'jpn'
                ]
            ],
            ['id', '1'],
            ['language', 'jpn'],
            ['auto_language_flg', true],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Notifications->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Notifications->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));

        /** @noinspection PhpUndefinedMethodInspection */
        $Notifications->Auth->expects($this->any())->method('loggedIn')
                            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Notifications->Auth->staticExpects($this->any())->method('user')
                            ->will($this->returnValueMap($value_map)
                            );
        /** @noinspection PhpUndefinedMethodInspection */
        $Notifications->Session->expects($this->any())->method('read')
                               ->will($this->returnValueMap([['current_team_id', 1]]));
        $Notifications->Team->EvaluateTerm->my_uid = 1;
        $Notifications->Team->EvaluateTerm->current_team_id = 1;

        return $Notifications;
    }

}

