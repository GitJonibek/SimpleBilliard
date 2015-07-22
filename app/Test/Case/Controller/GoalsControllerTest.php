<?php
App::uses('GoalsController', 'Controller');

/**
 * GoalsController Test Case
 * @method testAction($url = '', $options = array()) ControllerTestCase::_testAction

 */
class GoalsControllerTest extends ControllerTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.evaluate_term',
        'app.action_result',
        'app.evaluation_setting',
        'app.evaluation',
        'app.purpose',
        'app.goal',
        'app.follower',
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
        'app.member_group',
        'app.team_member',
        'app.job_category',
        'app.invite',

        'app.thread',
        'app.message',
        'app.email',
        'app.notify_setting',
        'app.oauth_token',
        'app.local_name',
        'app.goal_category',
        'app.key_result',
        'app.collaborator',
        'app.approval_history',
    );

    public $goal_id = null;
    public $kr_id = null;
    public $collabo_id = null;
    public $purpose_id = null;

    function testIndex()
    {
        $Goals = $this->_getGoalsCommonMock();
        $goal_data = [
            'user_id' => 1,
            'team_id' => 1,
            'name'    => 'test'
        ];
        $Goals->Goal->save($goal_data);
        $key_result_data = [
            'user_id'     => 1,
            'team_id'     => 1,
            'goal_id'     => $Goals->Goal->getLastInsertID(),
            'name'        => 'test',
            'special_flg' => true,
        ];
        $Goals->Goal->KeyResult->save($key_result_data);
        $goal_data = [
            'user_id' => 1,
            'team_id' => 1,
            'name'    => 'test'
        ];
        $Goals->Goal->create();
        $Goals->Goal->save($goal_data);

        $this->testAction('/goals/index', ['method' => 'GET']);
    }

    function testIndexWithSearch()
    {
        $Goals = $this->_getGoalsCommonMock();
        $goal_data = [
            'user_id' => 1,
            'team_id' => 1,
            'name'    => 'test'
        ];
        $Goals->Goal->save($goal_data);
        $key_result_data = [
            'user_id'     => 1,
            'team_id'     => 1,
            'goal_id'     => $Goals->Goal->getLastInsertID(),
            'name'        => 'test',
            'special_flg' => true,
        ];
        $Goals->Goal->KeyResult->save($key_result_data);
        $goal_data = [
            'user_id' => 1,
            'team_id' => 1,
            'name'    => 'test'
        ];
        $Goals->Goal->create();
        $Goals->Goal->save($goal_data);

        $this->testAction('/goals/index/term:previous/page:1', ['method' => 'GET']);
    }

    function testAjaxGetGoalDetailModal()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_goal_description_modal/goal_id:' . $this->goal_id, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetMoreIndexItems()
    {
        $this->_getGoalsCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $res = $this->testAction('/goals/ajax_get_more_index_items/page:2', ['method' => 'GET']);
        $data = json_decode($res, true);
        $this->assertArrayHasKey('html', $data);
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('page_item_num', $data);
        $this->assertArrayHasKey('start', $data);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetKRList()
    {
        $this->_getGoalsCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_kr_list/goal_id:1', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetFollowers()
    {
        $this->_getGoalsCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $res = $this->testAction('/goals/ajax_get_followers/goal_id:1/page:1', ['method' => 'GET']);
        $data = json_decode($res, true);
        $this->assertArrayHasKey('html', $data);
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('page_item_num', $data);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetMembers()
    {
        $this->_getGoalsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $res = $this->testAction('/goals/ajax_get_members/goal_id:1/page:1', ['method' => 'GET']);
        $data = json_decode($res, true);
        $this->assertArrayHasKey('html', $data);
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('page_item_num', $data);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAdd()
    {
        $Goals = $this->_getGoalsCommonMock();
        $Goals->Goal->GoalCategory->deleteAll(['team_id' => 1]);
        $this->testAction('/goals/add', ['method' => 'GET']);
    }

    function testAddWithId()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);

        //存在するゴールで自分が作成したもの
        $this->testAction('/goals/add/goal_id:' . $this->goal_id, ['method' => 'GET']);
    }

    function testAddWithPurposeIdSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);

        $this->testAction('/goals/add/purpose_id:' . $this->purpose_id, ['method' => 'GET']);
    }

    function testAddWithPurposeIdFail()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);

        $this->testAction('/goals/add/purpose_id:' . 999999, ['method' => 'GET']);
    }

    function testAddWithIdNotOwn()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $Goals->Goal->id = $this->goal_id;
        $Goals->Goal->saveField('user_id', 2);
        //存在するゴールで他人が作成したもの
        $this->testAction('/goals/add/goal_id:' . $this->goal_id, ['method' => 'GET']);
    }

    function testAddWithIdNotExists()
    {
        $this->_getGoalsCommonMock();
        //存在しないゴール
        $this->testAction('/goals/add/goal_id:' . 9999999999, ['method' => 'GET']);
    }

    function testAddPostPurpose()
    {
        $Goal = $this->_getGoalsCommonMock();
        $this->_setDefault($Goal);
        $data = [
            'Purpose' => [
                'name' => 'test',
            ],
        ];
        $this->testAction('/goals/add', ['method' => 'POST', 'data' => $data]);
    }

    function testAddPostPurposeFail()
    {
        $Goal = $this->_getGoalsCommonMock();
        $this->_setDefault($Goal);
        $data = [
            'Purpose' => [
            ],
        ];
        $this->testAction('/goals/add', ['method' => 'POST', 'data' => $data]);
    }

    function testAddPostMode2()
    {
        $Goal = $this->_getGoalsCommonMock();
        $this->_setDefault($Goal);
        $data = [
            'Goal' => [
                'purpose_id'       => $this->purpose_id,
                'goal_category_id' => 1,
                'name'             => 'test',
                'value_unit'       => 0,
                'target_value'     => 100,
                'start_value'      => 0,
                'start_date'       => date('yyyy/mm/dd', $this->start_date),
                'end_date'         => date('yyyy/mm/dd', $this->end_date),
            ]
        ];
        $this->testAction("/goals/add/mode:2/purpose_id:{$this->purpose_id}", ['method' => 'POST', 'data' => $data]);
    }

    function testAddPostMode2Edit()
    {
        $Goal = $this->_getGoalsCommonMock();
        $this->_setDefault($Goal);
        $data = [
            'Goal' => [
                'goal_category_id' => 1,
                'name'             => 'test',
                'value_unit'       => 0,
                'target_value'     => 100,
                'start_value'      => 0,
                'start_date'       => date('yyyy/mm/dd', $this->start_date),
                'end_date'         => date('yyyy/mm/dd', $this->end_date),
            ]
        ];
        $this->testAction("/goals/add/goal_id:{$this->goal_id}/mode:2/purpose_id:{$this->purpose_id}",
                          ['method' => 'POST', 'data' => $data]);
    }

    function testAddPostMode3()
    {
        $Goal = $this->_getGoalsCommonMock();
        $this->_setDefault($Goal);
        $Goal->Goal->Collaborator->id = $this->collabo_id;
        $Goal->Goal->Collaborator->saveField('valued_flg', Collaborator::STATUS_MODIFY);
        $data = [
            'Goal'         => [
                'description' => 'test',
                'priority'    => 0,
            ],
            'Collaborator' => [
                [
                    'id'       => $this->collabo_id,
                    'priority' => 3
                ]
            ]
        ];
        $this->testAction("/goals/add/goal_id:{$this->goal_id}/mode:3", ['method' => 'POST', 'data' => $data]);
    }

    function testAddPostMode3GoApprovalPage()
    {
        $Goal = $this->_getGoalsCommonMock();
        $this->_setDefault($Goal);
        $data = [
            'Goal'         => [
                'description' => 'test',
            ],
            'Collaborator' => [
                [
                    'id'       => $this->collabo_id,
                    'priority' => 3
                ]
            ]
        ];

        $team_member = [
            'user_id'       => 1,
            'team_id'       => 1,
            'coach_user_id' => 999,
        ];
        $Goal->User->TeamMember->save($team_member);

        $this->testAction("/goals/add/goal_id:{$this->goal_id}/mode:3", ['method' => 'POST', 'data' => $data]);
    }

    function testAddPostEmpty()
    {
        $this->_getGoalsCommonMock();
        $data = ['Goal' => []];
        $this->testAction('/goals/add/mode:2', ['method' => 'POST', 'data' => $data]);
    }

    function testGetEndMonthLocalDateTime()
    {
        $Goals = $this->_getGoalsCommonMock();
        $Goals->getEndMonthLocalDateTime('test');
        $Goals->getEndMonthLocalDateTime(6, 'test');
        $Goals->getEndMonthLocalDateTime();
    }

    /**
     * testDelete method
     *
     * @return void
     */
    public function testDeleteFail()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('goals/delete/goal_id:0', ['method' => 'POST']);
    }

    public function testDeleteNotOwn()
    {
        /**
         * @var UsersController $Goals
         */
        $Goals = $this->_getGoalsCommonMock();

        $user_id = 10;
        $team_id = 1;

        $goal_data = [
            'Goal' => [
                'user_id'    => $user_id,
                'team_id'    => $team_id,
                'name'       => 'test',
                'start_date' => strtotime('2014/07/07'),
                'end_date'   => strtotime('2014/10/07'),
            ],
        ];
        $goal = $Goals->Goal->save($goal_data);
        $this->testAction('goals/delete/goal_id:' . $goal['Goal']['id'], ['method' => 'POST']);
    }

    public function testDeleteSuccess()
    {
        /**
         * @var UsersController $Goals
         */
        $Goals = $this->_getGoalsCommonMock();

        $this->_setDefault($Goals);

        $this->testAction('goals/delete/goal_id:' . $this->goal_id, ['method' => 'POST']);
    }

    /**
     * testDeletePurpose method
     *
     * @return void
     */
    public function testDeletePurposeFail()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('goals/delete_purpose/purpose_id:0', ['method' => 'POST']);
    }

    public function testDeletePurposeNotOwn()
    {
        /**
         * @var UsersController $Goals
         */
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $Goals->Goal->Purpose->id = $this->purpose_id;
        $Goals->Goal->Purpose->saveField('user_id', 99999);
        $this->testAction('goals/delete_purpose/purpose_id:' . $this->purpose_id, ['method' => 'POST']);
    }

    public function testDeletePurposeSuccess()
    {
        /**
         * @var UsersController $Goals
         */
        $Goals = $this->_getGoalsCommonMock();

        $this->_setDefault($Goals);
        $this->testAction('goals/delete_purpose/purpose_id:' . $this->purpose_id, ['method' => 'POST']);
    }

    function testEditCollaboSuccess()
    {
        $this->_getGoalsCommonMock();
        $data = [
            'Collaborator' => [
                'role'        => 'test',
                'description' => 'test',
                'goal_id'     => 1,
            ]
        ];
        $this->testAction('/goals/edit_collabo', ['method' => 'POST', 'data' => $data]);
    }

    function testEditCollaboPriority0Success()
    {
        $this->_getGoalsCommonMock();
        $data = [
            'Collaborator' => [
                'role'        => 'test',
                'description' => 'test',
                'goal_id'     => 1,
                'priority'    => 0,
            ]
        ];
        $this->testAction('/goals/edit_collabo', ['method' => 'POST', 'data' => $data]);
    }

    function testEditCollaboCollaboIdSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
            'Collaborator' => [
                'role'        => 'test2',
                'description' => 'test2',
                'goal_id'     => 1,
            ]
        ];
        $this->testAction('/goals/edit_collabo/collaborator_id:' . $this->collabo_id,
                          ['method' => 'POST', 'data' => $data]);
    }

    function testEditCollaboFail()
    {
        $this->_getGoalsCommonMock();
        $data = [];
        $this->testAction('/goals/edit_collabo', ['method' => 'POST', 'data' => $data]);
    }

    function testDeleteCollaboSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $this->testAction('/goals/delete_collabo/collaborator_id:' . $this->collabo_id, ['method' => 'POST']);
    }

    function testDeleteCollaboFailNotExists()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/delete_collabo/collaborator_id:' . 99999, ['method' => 'POST']);
    }

    function testDeleteCollaboFailNotOwn()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $Goals->Goal->Collaborator->id = $this->collabo_id;
        $Goals->Goal->Collaborator->saveField('user_id', 99999999);
        $this->testAction('/goals/delete_collabo/collaborator_id:' . $this->collabo_id, ['method' => 'POST']);
    }

    function testAddCompletedActionSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
            'ActionResult' => [
                'name'          => 'test',
                'key_result_id' => 0,
                'note'          => 'test',
                'socket_id'     => 'hogehage'
            ]
        ];
        $this->testAction('/goals/add_completed_action/goal_id:1', ['method' => 'POST', 'data' => $data]);
    }

    function testAddCompletedActionSuccessFromCommon()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
            'ActionResult' => [
                'name'      => 'test',
                'goal_id'   => 1,
                'note'      => 'test',
                'socket_id' => 'hogehage'
            ]
        ];
        $this->testAction('/goals/add_completed_action/', ['method' => 'POST', 'data' => $data]);
    }

    function testAddCompletedActionFailNoGoal()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
            'ActionResult' => [
                'name'      => 'test',
                'note'      => 'test',
                'socket_id' => 'hogehage'
            ]
        ];
        $this->testAction('/goals/add_completed_action/', ['method' => 'POST', 'data' => $data]);
    }

    function testAddCompletedActionFailNoData()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [];
        $this->testAction('/goals/add_completed_action/goal_id:1', ['method' => 'POST', 'data' => $data]);
    }

    function testAddCompletedActionSuccessNoKR()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
            'ActionResult' => [
                'name' => 'test',
                'note' => 'test'
            ]
        ];
        $this->testAction('/goals/add_completed_action/goal_id:1', ['method' => 'POST', 'data' => $data]);
    }

    function testAddCompletedActionFailNotCollabo()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
            'ActionResult' => [
                'name'          => 'test',
                'key_result_id' => 0,
                'note'          => 'test'
            ]
        ];
        $this->testAction('/goals/add_completed_action/goal_id:99999999', ['method' => 'POST', 'data' => $data]);
    }

    function testAddCompletedActionFailEmptyAction()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
        ];
        $this->testAction('/goals/add_completed_action/goal_id:1', ['method' => 'POST', 'data' => $data]);
    }

    function testAddFollowSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        /** @noinspection PhpUndefinedFieldInspection */
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_toggle_follow/goal_id:' . $this->goal_id, ['method' => 'POST']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAddFollowFailExist()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
            'team_id' => 1,
            'user_id' => 1,
            'goal_id' => $this->goal_id,
        ];
        $Goals->Goal->Follower->save($data);
        /** @noinspection PhpUndefinedFieldInspection */
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_toggle_follow/goal_id:' . $this->goal_id, ['method' => 'POST']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAddFollowFailNotExistKeyResult()
    {
        $this->_getGoalsCommonMock();
        /** @noinspection PhpUndefinedFieldInspection */
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_toggle_follow/goal_id:' . 999999999999999999, ['method' => 'POST']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testDeleteFollowSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $data = [
            'name'    => 'test',
            'team_id' => 1,
            'user_id' => 1,
            'goal_id' => 1,
        ];
        $Goals->Goal->KeyResult->save($data);
        $key_result_user_id = $Goals->Goal->KeyResult->getLastInsertID();
        /** @noinspection PhpUndefinedFieldInspection */
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_toggle_follow/goal_id:' . $key_result_user_id, ['method' => 'POST']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testDeleteFollowFailNotExistKeyResult()
    {
        $this->_getGoalsCommonMock();
        /** @noinspection PhpUndefinedFieldInspection */
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_toggle_follow/goal_id:' . 999999999999999999, ['method' => 'POST']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetCollaboChangeModal()
    {
        $this->_getGoalsCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_collabo_change_modal/goal_id:' . 1, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testEditActionFailNoArId()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
            'ActionResult' => [
                'name'          => 'test',
                'key_result_id' => 0,
                'note'          => 'test',
            ]
        ];
        $this->testAction('/goals/edit_action/action_result_id:99999999999999', ['method' => 'PUT', 'data' => $data]);
    }

    function testEditActionFailEmpty()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
        ];
        $this->testAction('/goals/edit_action/action_result_id:1', ['method' => 'PUT', 'data' => $data]);
    }

    function testEditActionSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
            'ActionResult' => [
                'id'     => 1,
                'name'   => 'test',
                'photo1' => null,
            ],
            'photo_delete' => [
                1 => 1
            ]
        ];
        $this->testAction('/goals/edit_action/action_result_id:1', ['method' => 'PUT', 'data' => $data]);
    }

    function testDeleteActionFailNotExists()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $this->testAction('/goals/delete_action/action_result_id:9999999', ['method' => 'POST']);
    }

    function testDeleteActionFailNotCollabo()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
            'ActionResult' => [
                'name'    => 'test',
                'team_id' => 1,
                'user_id' => 1,
                'goal_id' => 99,
            ],
        ];
        $Goals->Goal->ActionResult->save($data);
        $ar_id = $Goals->Goal->ActionResult->getLastInsertID();
        $this->testAction('/goals/delete_action/action_result_id:' . $ar_id, ['method' => 'POST']);
    }

    function testDeleteActionSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $this->testAction('/goals/delete_action/action_result_id:1', ['method' => 'POST']);
    }

    function testAddKeyResultFail()
    {
        $this->_getGoalsCommonMock();
        $data = ['KeyResult' => []];
        $this->testAction('/goals/add_key_result/goal_id:999999999', ['method' => 'POST', 'data' => $data]);
    }

    function testAddKeyResultSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = ['KeyResult' =>
                     [
                         'name'        => 'test',
                         'value_unit'  => 0,
                         'start_value' => 1
                     ]
        ];
        $this->testAction("/goals/add_key_result/goal_id:{$this->goal_id}/key_result_id:{$this->kr_id}",
                          ['method' => 'POST', 'data' => $data]);
    }

    function testAddKeyResultFailPermit()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = ['KeyResult' =>
                     [
                         'name'        => 'test',
                         'value_unit'  => 0,
                         'start_value' => 1
                     ]
        ];
        $this->testAction("/goals/add_key_result/goal_id:{$this->goal_id}/key_result_id:99999",
                          ['method' => 'POST', 'data' => $data]);
    }

    function testAjaxGetEditActionModalSuccess()
    {
        $this->_getGoalsCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_edit_action_modal/action_result_id:' . 1, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetEditActionModalFail()
    {
        $this->_getGoalsCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_edit_action_modal/action_result_id:' . 9999999, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetAddActionModalSuccess()
    {
        $this->_getGoalsCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_add_action_modal/goal_id:' . 1, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetAddActionModalFail()
    {
        $this->_getGoalsCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_add_action_modal/goal_id:' . 99999, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }


    function testAjaxGetAddKeyResultModalSuccess()
    {
        $this->_getGoalsCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_add_key_result_modal/goal_id:' . 1, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetAddKeyResultModalFail()
    {
        $this->_getGoalsCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_add_key_result_modal/goal_id:' . 99999, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetKeyResults()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $kr = [
            'goal_id' => $this->goal_id,
            'user_id' => 1,
            'team_id' => 1,
        ];
        $Goals->Goal->KeyResult->save($kr);
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_key_results/goal_id:' . $this->goal_id, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetKeyResultsWithParams()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_key_results/page:2/view:key_results/goal_id:1', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testEditKeyResultFailEmpty()
    {
        $Goals = $this->_getGoalsCommonMock();

        $data = [];
        $this->_setDefault($Goals);
        $this->testAction('/goals/edit_key_result/key_result_id:' . $this->kr_id, ['method' => 'PUT', 'data' => $data]);
    }

    function testEditKeyResultFailNotCollabo()
    {
        $Goals = $this->_getGoalsCommonMock();

        $this->_setDefault($Goals);
        $this->testAction('/goals/edit_key_result/key_result_id:' . 99999, ['method' => 'PUT', 'data' => []]);
    }

    function testEditKeyResultSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $data = [
            'KeyResult' => [
                'id'         => $this->kr_id,
                'name'       => 'test',
                'value_unit' => 2,
                'start_date' => '2015/1/15',
                'end_date'   => '2015/1/20',
                'goal_id'    => $this->goal_id,
            ]
        ];
        $this->testAction('/goals/edit_key_result/key_result_id:' . $this->kr_id, ['method' => 'PUT', 'data' => $data]);
    }

    function testDeleteKeyResultSuccess()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/delete_key_result/key_result_id:' . 1, ['method' => 'POST']);
    }

    function testDeleteKeyResultFail()
    {
        $Goals = $this->_getGoalsCommonMock();
        $Goals->Goal->Collaborator->my_uid = 999;
        $this->testAction('/goals/delete_key_result/key_result_id:' . 1, ['method' => 'POST']);
    }

    function testAjaxGetEditKeyResultModalSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_edit_key_result_modal/key_result_id:' . $this->kr_id, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetEditKeyResultModalFail1()
    {
        $this->_getGoalsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_edit_key_result_modal/key_result_id:' . 9999999, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetEditKeyResultModalFail2()
    {
        $this->_getGoalsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_edit_key_result_modal/key_result_id:' . 1, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetNewActionFormSuccess()
    {
        $this->_getGoalsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_new_action_form/goal_id:1/ar_count:9', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testCompleteKrSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $this->testAction('/goals/complete_kr/key_result_id:' . $this->kr_id, ['method' => 'POST']);
    }

    function testCompleteKrSuccessWithGoal()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $this->testAction('/goals/complete_kr/key_result_id:' . $this->kr_id . "/1", ['method' => 'POST']);
    }

    function testIncompleteKrSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $this->testAction('/goals/incomplete_kr/key_result_id:' . $this->kr_id, ['method' => 'POST']);
    }

    function testCompleteKrFail()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/complete_kr/key_result_id:9999999', ['method' => 'POST']);
    }

    function testIncompleteKrFail()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/incomplete_kr/key_result_id:9999999999', ['method' => 'POST']);
    }

    function testAjaxGetLastKrConfirmFail()
    {
        $this->_getGoalsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_last_kr_confirm/key_result_id:' . 999999, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetLastKrConfirmSuccess()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_last_kr_confirm/key_result_id:' . $this->kr_id, ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testDownloadAllGoalCsvUnapproved()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $Goals->Goal->Collaborator->id = $this->collabo_id;
        $Goals->Goal->Collaborator->saveField('valued_flg', Collaborator::STATUS_UNAPPROVED);
        $this->testAction('/goals/download_all_goal_csv/', ['method' => 'POST']);
    }

    function testDownloadAllGoalCsvApproval()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $Goals->Goal->Collaborator->id = $this->collabo_id;
        $Goals->Goal->Collaborator->saveField('valued_flg', Collaborator::STATUS_APPROVAL);
        $this->testAction('/goals/download_all_goal_csv/', ['method' => 'POST']);
    }

    function testDownloadAllGoalCsvHold()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $Goals->Goal->Collaborator->id = $this->collabo_id;
        $Goals->Goal->Collaborator->saveField('valued_flg', Collaborator::STATUS_HOLD);
        $this->testAction('/goals/download_all_goal_csv/', ['method' => 'POST']);
    }

    function testDownloadAllGoalCsvModify()
    {
        $Goals = $this->_getGoalsCommonMock();
        $this->_setDefault($Goals);
        $Goals->Goal->Collaborator->id = $this->collabo_id;
        $Goals->Goal->Collaborator->saveField('valued_flg', Collaborator::STATUS_MODIFY);
        $this->testAction('/goals/download_all_goal_csv/', ['method' => 'POST']);
    }

    function testGetTeamIdFromRequest()
    {
        $Goals = $this->_getGoalsCommonMock();

        $request_params = [];
        $Goals->_getTeamIdFromRequest($request_params);

        $request_params = [
            'controller' => 'pages',
            'action'     => 'home',
            'named'      => [
                'circle_id' => 1,
                'post_id'   => 1,
            ]
        ];
        $Goals->_getTeamIdFromRequest($request_params);

        $request_params = [
            'controller' => 'posts',
            'action'     => 'feed',
            'named'      => [
                'circle_id' => 1,
                'post_id'   => 1,
                'team_id'   => 1,
            ]
        ];
        $Goals->_getTeamIdFromRequest($request_params);

        $request_params = [
            'controller' => 'posts',
            'action'     => 'feed',
            'named'      => [
                'circle_id' => 1,
            ]
        ];
        $Goals->_getTeamIdFromRequest($request_params);
        $request_params = [
            'controller' => 'posts',
            'action'     => 'feed',
            'named'      => [
                'post_id' => 1,
            ]
        ];
        $Goals->_getTeamIdFromRequest($request_params);

        $request_params = [
            'controller' => 'posts',
            'action'     => 'feed',
            'named'      => [
                'team_id' => 1,
            ]
        ];
        $Goals->_getTeamIdFromRequest($request_params);

        $request_params = [
            'controller' => 'users',
            'action'     => 'add',
            'named'      => [
                'user_id' => 1,
            ]
        ];
        $Goals->_getTeamIdFromRequest($request_params);
    }

    function testIsIsaoUser()
    {
        $Goals = $this->_getGoalsCommonMock();
        $user = [
            'PrimaryEmail' => [
                'email' => 'test@isao.co.jp'
            ]
        ];
        $Goals->_isIsaoUser($user, 999);
        $user = [
            'PrimaryEmail' => [
                'email' => 'test@aaa.com'
            ]
        ];
        $Goals->_isIsaoUser($user, 999);
    }

    function testForceSSL()
    {
        $Goals = $this->_getGoalsCommonMock();
        $Goals->forceSSL();
    }

    function testSwitchTeamBeforeCheckFalse()
    {
        $Goals = $this->_getGoalsCommonMock();
        $Goals->request->params['controller'] = 'teams';
        $res = $Goals->_switchTeamBeforeCheck();
        $this->assertFalse($res);
    }

    function testSwitchTeamBeforeCheckNotBelongTeam()
    {
        $Goals = $this->_getGoalsCommonMock();
        //所属していないチームのゴールをあらかじめ保存
        $goal = [
            'name'       => 'test',
            'purpose_id' => 1,
            'user_id'    => 1,
            'team_id'    => 999,
        ];
        $Goals->Goal->save($goal);

        $Goals->request->params = [
            'controller' => 'goals',
            'action'     => 'add',
            'named'      => [
                'goal_id' => $Goals->Goal->getLastInsertID(),
            ]
        ];
        $Goals->_switchTeamBeforeCheck();
    }

    function testSwitchTeamBeforeCheckBelongTeam()
    {
        $Goals = $this->_getGoalsCommonMock();
        //所属していないチームのゴールをあらかじめ保存
        $goal = [
            'name'       => 'test',
            'purpose_id' => 1,
            'user_id'    => 1,
            'team_id'    => 2,
        ];
        $Goals->Goal->save($goal);

        $team_member = [
            'user_id' => 1,
            'team_id' => 2,
        ];
        $Goals->User->TeamMember->save($team_member);

        $Goals->request->params = [
            'controller' => 'goals',
            'action'     => 'add',
            'named'      => [
                'goal_id' => $Goals->Goal->getLastInsertID(),
            ]
        ];
        $Goals->_switchTeamBeforeCheck();
    }

    function testAjaxGetMyGoalsTypeLeader()
    {
        $this->_getGoalsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_my_goals/page:1/type:leader', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetMyGoalsTypeCollabo()
    {
        $this->_getGoalsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_my_goals/page:1/type:collabo', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetMyGoalsTypeFollow()
    {
        $this->_getGoalsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_my_goals/page:1/type:follow', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetMyGoalsTypeMyPrev()
    {
        $this->_getGoalsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_my_goals/page:1/type:my_prev', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetMyGoalsNotExistPage()
    {
        $this->_getGoalsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_my_goals/type:follow', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetMyGoalsNotExistType()
    {
        $this->_getGoalsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_my_goals/page:1', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetMyGoalsNotAllowedType()
    {
        $this->_getGoalsCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/goals/ajax_get_my_goals/type:hogehage', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testViewFollowers()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_followers/goal_id:1');
    }

    function testViewFollowersNoParams()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_followers/');
    }

    function testViewFollowersInvalidParam()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_followers/goal_id:999');
    }

    function testViewMembers()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_members/goal_id:1');
    }

    function testViewMembersNoParams()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_members/');
    }

    function testViewMembersInvalidParam()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_members/goal_id:999');
    }

    function testViewActionsList()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_members/goal_id:1/page_type:list');
    }

    function testViewActionsImage()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_members/goal_id:1/page_type:image');
    }

    function testViewActionsNoParams()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_members/');
    }

    function testViewActionsInvalidParam()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_members/goal_id:999');
    }

    function testViewKrs()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_krs/goal_id:1');
    }

    function testViewKrsNoParams()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_krs/');
    }

    function testViewKrsInvalidParam()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_krs/goal_id:999');
    }

    function testViewInfo()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_info/goal_id:1');
    }

    function testViewInfoNoParams()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_info/');
    }

    function testViewInfoInvalidParam()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_info/goal_id:999');
    }

    function testViewInfoAsCollaborator()
    {
        $this->_getGoalsCommonMock();
        $this->testAction('/goals/view_info/goal_id:7');
    }

    var $current_date;
    var $start_date;
    var $end_date;

    /**
     * @param $Goals
     */
    function _setDefault($Goals)
    {
        $purpose = [
            'user_id' => 1,
            'team_id' => 1,
            'name'    => 'test',
        ];
        $Goals->Goal->Purpose->create();
        $Goals->Goal->Purpose->save($purpose);
        $this->purpose_id = $Goals->Goal->Purpose->getLastInsertID();
        $goal = [
            'user_id'    => 1,
            'team_id'    => 1,
            'purpose_id' => $this->purpose_id,
            'name'       => 'test',
            'start_date' => $this->start_date,
            'end_date'   => $this->end_date,
        ];
        $Goals->Goal->create();
        $Goals->Goal->save($goal);
        $this->goal_id = $Goals->Goal->getLastInsertID();
        $kr = [
            'user_id'    => 1,
            'team_id'    => 1,
            'goal_id'    => $this->goal_id,
            'name'       => 'test',
            'start_date' => $this->start_date,
            'end_date'   => $this->end_date,
        ];
        $Goals->Goal->KeyResult->create();
        $Goals->Goal->KeyResult->save($kr);
        $this->kr_id = $Goals->Goal->KeyResult->getLastInsertID();
        $collaborator = [
            'user_id' => 1,
            'team_id' => 1,
            'goal_id' => $this->goal_id,
            'type'    => 1,
        ];
        $Goals->Goal->Collaborator->create();
        $Goals->Goal->Collaborator->save($collaborator);
        $this->collabo_id = $Goals->Goal->Collaborator->getLastInsertID();
        return;
    }

    function _getGoalsCommonMock()
    {
        /**
         * @var GoalsController $Goals
         */
        $Goals = $this->generate('Goals', [
            'components' => [
                'Session',
                'Auth'     => ['user', 'loggedIn'],
                'Security' => ['_validateCsrf', '_validatePost'],
                'Ogp',
                'NotifyBiz',
                'GlEmail',
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
        $Goals->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Goals->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));

        /** @noinspection PhpUndefinedMethodInspection */
        $Goals->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Goals->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        /** @noinspection PhpUndefinedMethodInspection */
        $Goals->Session->expects($this->any())->method('read')
                       ->will($this->returnValueMap([['current_team_id', 1]]));
        $Goals->Goal->Team->my_uid = 1;
        $Goals->Goal->Team->current_team_id = 1;
        $Goals->Goal->Team->current_team = [
            'Team' => [
                'start_term_month' => 4,
                'border_months'    => 6
            ]
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Goals->Session->expects($this->any())->method('read')
                       ->will($this->returnValueMap([['current_team_id', 1]]));

        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->Team->TeamMember->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->Team->TeamMember->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->ActionResult->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->ActionResult->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->GoalCategory->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->GoalCategory->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->KeyResult->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->KeyResult->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->Collaborator->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->Collaborator->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->Follower->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Goals->Goal->Follower->current_team_id = '1';
        $Goals->Goal->Post->my_uid = '1';
        $Goals->Goal->Post->current_team_id = '1';
        $Goals->Team->EvaluateTerm->my_uid = 1;
        $Goals->Team->EvaluateTerm->current_team_id = 1;

        $this->current_date = strtotime('2015/7/1');
        $this->start_date = strtotime('2015/7/1');
        $this->end_date = strtotime('2015/10/1');

        $Goals->Goal->Team->current_term_start_date = strtotime('2015/1/1');
        $Goals->Goal->Team->current_term_end_date = strtotime('2015/12/1');

        return $Goals;
    }
}
