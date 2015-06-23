<?php
App::uses('CirclesController', 'Controller');

/**
 * CirclesController Test Case
 * @method testAction($url = '', $options = array()) ControllerTestCase::_testAction
 */
class CirclesControllerTest extends ControllerTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.purpose',
        'app.action_result',
        'app.cake_session',
        'app.evaluation',
        'app.circle',
        'app.team',
        'app.evaluation_setting',
        'app.evaluate_term',
        'app.badge',
        'app.user',
        'app.notify_setting',
        'app.email',
        'app.comment_like',
        'app.send_mail',
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
        'app.message',
        'app.circle_member',
        'app.post_share_user',
        'app.post_share_circle',
        'app.follower',
        'app.collaborator',
        'app.goal',
        'app.goal_category'
    );

    function testAddSuccess()
    {
        $this->_getCirclesCommonMock();
        $data = [
            'Circle' => [
                'name'    => 'test',
                'members' => '2,12',
            ],
        ];
        $this->testAction('/circles/add',
                          ['method' => 'POST', 'data' => $data, 'return' => 'contents']);

    }

    function testAddFail()
    {
        $this->_getCirclesCommonMock();
        $data = [];
        $this->testAction('/circles/add',
                          ['method' => 'POST', 'data' => $data, 'return' => 'contents']);

    }

    function testAjaxGetEditModal()
    {
        $this->_getCirclesCommonMock();

        /** @noinspection PhpUndefinedFieldInspection */
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/circles/ajax_get_edit_modal/circle_id:1', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxSelect2InitCircleMembers()
    {
        $this->_getCirclesCommonMock();

        /** @noinspection PhpUndefinedFieldInspection */
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/circles/ajax_select2_init_circle_members/1', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testEditSuccess()
    {
        $this->_getCirclesCommonMock();
        $data = [
            'Circle' => [
                'id'      => 1,
                'name'    => 'xxx',
                'members' => 'user_12,user_13'
            ],
        ];
        $this->testAction('/circles/edit/circle_id:1', ['method' => 'PUT', 'data' => $data, 'return' => 'contents']);
    }

    function testEditSuccessChangePrivacy()
    {
        $this->_getCirclesCommonMock();
        $data = [
            'Circle' => [
                'id'         => 1,
                'name'       => 'xxx',
                'members'    => 'user_12,user_13',
                'public_flg' => false,
            ],
        ];
        $this->testAction('/circles/edit/circle_id:1', ['method' => 'PUT', 'data' => $data, 'return' => 'contents']);
    }

    function testEditTeamAll()
    {
        $this->_getCirclesCommonMock();
        $data = [
            'Circle' => [
                'id'         => 3,
                'name'       => 'xxx',
                'description' => 'xxxx yyyy',
            ],
        ];
        $this->testAction('/circles/edit/circle_id:3', ['method' => 'PUT', 'data' => $data, 'return' => 'contents']);
    }

    function testEditFail()
    {
        $this->_getCirclesCommonMock();
        $data = ['Circle' => ['id' => 1, 'name' => null]];
        $this->testAction('/circles/edit/circle_id:1', ['method' => 'PUT', 'data' => $data, 'return' => 'contents']);
    }

    function testEditNotExists()
    {
        $this->_getCirclesCommonMock();
        $data = [];
        $this->testAction('/circles/edit/circle_id:99999',
                          ['method' => 'PUT', 'data' => $data, 'return' => 'contents']);
    }

    function testEditNotAdmin()
    {
        $this->_getCirclesCommonMock();
        $data = [];
        $this->testAction('/circles/edit/circle_id:2', ['method' => 'PUT', 'data' => $data, 'return' => 'contents']);
    }

    function testDeleteSuccess()
    {
        $this->_getCirclesCommonMock();
        $this->testAction('/circles/delete/circle_id:1', ['method' => 'POST', 'return' => 'contents']);
    }

    function testDeleteNotExists()
    {
        $this->_getCirclesCommonMock();
        $this->testAction('/circles/delete/circle_id:99999', ['method' => 'POST', 'return' => 'contents']);
    }

    function testDeleteNotAdmin()
    {
        $this->_getCirclesCommonMock();
        $this->testAction('/circles/delete/circle_id:2', ['method' => 'POST', 'return' => 'contents']);
    }

    function testDeleteFailTeamAll()
    {
        $this->_getCirclesCommonMock();
        $this->testAction('/circles/delete/circle_id:3', ['method' => 'POST', 'return' => 'contents']);
    }

    function testAjaxGetPublicCirclesModal()
    {
        $this->_getCirclesCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/circles/ajax_get_public_circles_modal/', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetCircleMembers()
    {
        $this->_getCirclesCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $this->testAction('/circles/ajax_get_circle_members/circle_id:1', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testJoinSuccess()
    {
        $this->_getCirclesCommonMock();
        $data = [
            'Circle' => [
                [
                    'join'      => true,
                    'circle_id' => "1",
                ],
                [
                    'join'      => false,
                    'circle_id' => "2",
                ],
                [
                    'join'      => true,
                    'circle_id' => "3",
                ],
            ],
        ];
        $this->testAction('/circles/join',
                          ['method' => 'POST', 'data' => $data, 'return' => 'contents']);

    }

    function testJoinFail()
    {
        $this->_getCirclesCommonMock();
        $data = [];
        $this->testAction('/circles/join',
                          ['method' => 'POST', 'data' => $data, 'return' => 'contents']);

    }

    function _getCirclesCommonMock()
    {
        /**
         * @var CirclesController $Circles
         */
        $Circles = $this->generate('Circles', [
            'components' => [
                'Session',
                'Auth'     => ['user', 'loggedIn'],
                'Security' => ['_validateCsrf', '_validatePost'],
                'Ogp',
            ]
        ]);
        $value_map = [
            [null, [
                'id'         => '1',
                'last_first' => true,
                'language'   => 'jpn'
            ]],
            ['id', '1'],
            ['language', 'jpn'],
            ['auto_language_flg', true],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Circles->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Circles->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));

        /** @noinspection PhpUndefinedMethodInspection */
        $Circles->Auth->expects($this->any())->method('loggedIn')
                      ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Circles->Auth->staticExpects($this->any())->method('user')
                      ->will($this->returnValueMap($value_map)
                      );
        /** @noinspection PhpUndefinedFieldInspection */
        $Circles->Circle->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Circles->Circle->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Circles->Circle->CircleMember->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Circles->Circle->CircleMember->current_team_id = '1';
        $Circles->Team->EvaluateTerm->my_uid = 1;
        $Circles->Team->EvaluateTerm->current_team_id = 1;
        $Circles->Circle->Team->TeamMember->my_uid = '1';
        $Circles->Circle->Team->TeamMember->current_team_id = '1';

        return $Circles;
    }

}
