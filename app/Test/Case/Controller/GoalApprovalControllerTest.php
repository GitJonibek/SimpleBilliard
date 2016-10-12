<?php App::uses('GoalousControllerTestCase', 'Test');
App::uses('GoalApprovalController', 'Controller');

/**
 * GoalApprovalController Test Case
 * @method testAction($url = '', $options = array()) GoalousControllerTestCase::_testAction
 */
class GoalApprovalControllerTest extends GoalousControllerTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
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
        'app.evaluate_term',
        'app.email',
        'app.notify_setting',
        'app.oauth_token',
        'app.local_name',
        'app.approval_history',
        'app.evaluation'
    );

    function testIndex()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();

        $team_id = 1;
        $params = [
            'first_name' => 'test',
            'last_name'  => 'test'
        ];
        $GoalApproval->Collaborator->User->save($params);
        $user_id = $GoalApproval->Collaborator->User->getLastInsertID();

        $params = [
            'user_id'          => $user_id,
            'team_id'          => $team_id,
            'name'             => 'test',
            'goal_category_id' => 1,
            'end_date'         => '1427813999',
            'photo_file_name'  => 'aa.png'
        ];
        $GoalApproval->Collaborator->Goal->save($params);
        $goal_id = $GoalApproval->Collaborator->Goal->getLastInsertID();

        $approval_status = 0;
        $params = [
            'user_id'         => $user_id,
            'team_id'         => $team_id,
            'goal_id'         => $goal_id,
            'approval_status' => $approval_status,
            'type'            => 0,
            'priority'        => 1,
        ];
        $GoalApproval->Collaborator->save($params);

        $GoalApproval->user_id = $user_id;
        $GoalApproval->request->data = [
            'GoalApproval' => '',
            'modify_btn'   => '',
        ];

        $this->testAction('/goal_approval/index', ['method' => 'GET']);
    }

    function testIndexNoEvaluable()
    {
        $GoalApprovals = $this->_getGoalApprovalCommonMock();
        $GoalApprovals->TeamMember->id = 1;
        $GoalApprovals->TeamMember->saveField('evaluation_enable_flg', false);
        $GoalApprovals->Collaborator->id = 1;
        $GoalApprovals->Collaborator->saveField('approval_status', Collaborator::APPROVAL_STATUS_WITHDRAWN);
        $this->testAction('/goal_approval/index', ['method' => 'GET',]);
    }

    function testIndexPost()
    {
        $this->_getGoalApprovalCommonMock();
        $data = [
            'GoalApproval' => [
                'collaborator_id' => 1,
                'comment'         => 'test'
            ],
            'comment_btn'  => null
        ];
        $this->testAction('/goal_approval/index', ['method' => 'POST', 'data' => $data]);
    }

    function testDoneNoEvaluable()
    {
        $GoalApprovals = $this->_getGoalApprovalCommonMock();
        $GoalApprovals->TeamMember->id = 1;
        $GoalApprovals->TeamMember->saveField('evaluation_enable_flg', false);
        $GoalApprovals->Collaborator->id = 1;
        $GoalApprovals->Collaborator->saveField('approval_status', Collaborator::APPROVAL_STATUS_DONE);
        $this->testAction('/goal_approval/done', ['method' => 'GET',]);
    }

    function testDone()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();

        $team_id = 1;
        $user_id = 1;

        $params = [
            'user_id'          => $user_id,
            'team_id'          => $team_id,
            'name'             => 'test',
            'goal_category_id' => 1,
            'end_date'         => '1427813999',
            'photo_file_name'  => 'aa.png'
        ];
        $GoalApproval->Collaborator->Goal->save($params);
        $goal_id = $GoalApproval->Collaborator->Goal->getLastInsertID();

        $approval_status = 1;
        $params = [
            'user_id'         => $user_id,
            'team_id'         => $team_id,
            'goal_id'         => $goal_id,
            'approval_status' => $approval_status,
            'type'            => 0,
            'priority'        => 1,
        ];
        $GoalApproval->Collaborator->save($params);

        $GoalApproval->user_id = $user_id;
        $GoalApproval->request->data = [
            'GoalApproval' => '',
        ];
        $this->testAction('/goal_approval/done', ['method' => 'GET']);
    }

    function testDonePost()
    {
        $this->_getGoalApprovalCommonMock();
        $data = [
            'GoalApproval' => [
                'collaborator_id' => 1,
                'comment'         => 'test'
            ],
            'comment_btn'  => null
        ];
        $this->testAction('/goal_approval/done', ['method' => 'POST', 'data' => $data]);
    }

    function testSaveApprovalData()
    {
        $GoalApprovals = $this->_getGoalApprovalCommonMock();
        $GoalApprovals->_saveApprovalData();

        $GoalApprovals->request->data['GoalApproval'] = [
            'comment' => 'test'
        ];
        $GoalApprovals->_saveApprovalData();

        $GoalApprovals->request->data['GoalApproval'] = [
            'comment'         => 'test',
            'collaborator_id' => 99999
        ];
        $GoalApprovals->_saveApprovalData();
    }

    function testApproval()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $params = [
            'user_id'         => 999,
            'team_id'         => 888,
            'goal_id'         => 777,
            'approval_status' => 0,
        ];
        $GoalApproval->current_team_id = 1;
        $GoalApproval->Collaborator->save($params);

        $id = $GoalApproval->Collaborator->getLastInsertID();
        $data = ['collaborator_id' => $id];
        $GoalApproval->_approval($data);
        $GoalApproval->Collaborator->current_team_id = 888;
        $res = $GoalApproval->Collaborator->find('first', ['conditions' => ['id' => $id]]);
        $approval_status = $res['Collaborator']['approval_status'];
        $this->assertEquals($approval_status, '1');
    }

    function testWait()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $params = [
            'user_id'         => 999,
            'team_id'         => 888,
            'goal_id'         => 777,
            'approval_status' => 0,
        ];
        $GoalApproval->Collaborator->current_team_id = 888;
        $GoalApproval->Collaborator->save($params);

        $id = $GoalApproval->Collaborator->getLastInsertID();
        $data = ['collaborator_id' => $id];
        $GoalApproval->_wait($data);

        $res = $GoalApproval->Collaborator->find('first', ['conditions' => ['id' => $id]]);
        $approval_status = $res['Collaborator']['approval_status'];
        $this->assertEquals($approval_status, '2');
    }

    function testModify()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->Collaborator->current_team_id = 888;
        $params = [
            'user_id'         => 999,
            'team_id'         => 888,
            'goal_id'         => 777,
            'approval_status' => 0,
        ];
        $GoalApproval->Collaborator->save($params);

        $id = $GoalApproval->Collaborator->getLastInsertID();
        $data = ['collaborator_id' => $id];
        $GoalApproval->_modify($data);

        $res = $GoalApproval->Collaborator->find('first', ['conditions' => ['id' => $id]]);
        $approval_status = $res['Collaborator']['approval_status'];
        $this->assertEquals($approval_status, '3');
    }

    function testComment()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->user_id = 999;
        $data = ['collaborator_id' => 888, 'comment' => 'test'];

        $GoalApproval->_comment($data);

        $res = $GoalApproval->ApprovalHistory->find('first', ['conditions' => ['collaborator_id' => 888]]);
        $comment = $res['ApprovalHistory']['comment'];
        $this->assertEquals($comment, 'test');
    }

    function testChangeStatusTypeComment()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->request->data = ['comment_btn' => ''];
        $GoalApproval->user_id = 999;
        $data = ['collaborator_id' => 888, 'comment' => 'test'];
        $GoalApproval->_changeStatus($data);

        $res = $GoalApproval->ApprovalHistory->find('first', ['conditions' => ['collaborator_id' => 888]]);
        $comment = $res['ApprovalHistory']['comment'];
        $this->assertEquals($comment, 'test');
    }

    function testChangeStatusTypeWait()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->Collaborator->current_team_id = 888;
        $params = [
            'user_id'         => 999,
            'team_id'         => 888,
            'goal_id'         => 777,
            'approval_status' => 0,
        ];
        $GoalApproval->Collaborator->save($params);
        $id = $GoalApproval->Collaborator->getLastInsertID();

        $GoalApproval->request->data = ['wait_btn' => ''];
        $data = ['collaborator_id' => $id];
        $GoalApproval->_changeStatus($data);

        $res = $GoalApproval->Collaborator->find('first', ['conditions' => ['id' => $id]]);
        $approval_status = $res['Collaborator']['approval_status'];
        $this->assertEquals($approval_status, '2');
    }

    function testChangeStatusTypeApproval()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->Collaborator->current_team_id = 888;
        $params = [
            'user_id'         => 999,
            'team_id'         => 888,
            'goal_id'         => 777,
            'approval_status' => 0,
        ];
        $GoalApproval->Collaborator->save($params);
        $id = $GoalApproval->Collaborator->getLastInsertID();

        $GoalApproval->request->data = ['approval_btn' => ''];
        $data = ['collaborator_id' => $id];
        $GoalApproval->_changeStatus($data);

        $res = $GoalApproval->Collaborator->find('first', ['conditions' => ['id' => $id]]);
        $approval_status = $res['Collaborator']['approval_status'];
        $this->assertEquals($approval_status, '1');
    }

    function testChangeStatusTypeModify()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->Collaborator->current_team_id = 888;
        $params = [
            'user_id'         => 999,
            'team_id'         => 888,
            'goal_id'         => 777,
            'approval_status' => 0,
        ];
        $GoalApproval->Collaborator->save($params);
        $id = $GoalApproval->Collaborator->getLastInsertID();

        $GoalApproval->request->data = ['modify_btn' => ''];
        $data = ['collaborator_id' => $id];
        $GoalApproval->_changeStatus($data);

        $res = $GoalApproval->Collaborator->find('first', ['conditions' => ['id' => $id]]);
        $approval_status = $res['Collaborator']['approval_status'];
        $this->assertEquals($approval_status, Collaborator::APPROVAL_STATUS_WITHDRAWN);
    }

    function testTrackToMixpanel()
    {
        $GoalApprovals = $this->_getGoalApprovalCommonMock();

        unset($GoalApprovals->request->data);
        $GoalApprovals->request->data = ['comment_btn' => ''];
        $GoalApprovals->_trackToMixpanel(1);
        unset($GoalApprovals->request->data);
        $GoalApprovals->request->data = ['wait_btn' => ''];
        $GoalApprovals->_trackToMixpanel(1);
        unset($GoalApprovals->request->data);
        $GoalApprovals->request->data = ['approval_btn' => ''];
        $GoalApprovals->_trackToMixpanel(1);
        unset($GoalApprovals->request->data);
        $GoalApprovals->request->data = ['modify_btn' => ''];
        $GoalApprovals->_trackToMixpanel(1);
    }

    function testNotifyToCollaborator()
    {
        $GoalApprovals = $this->_getGoalApprovalCommonMock();
        $GoalApprovals->_notifyToCollaborator(99999999);
        unset($GoalApprovals->request->data);
        $GoalApprovals->request->data = ['comment_btn' => ''];
        $GoalApprovals->_notifyToCollaborator(1);
        unset($GoalApprovals->request->data);
        $GoalApprovals->request->data = ['wait_btn' => ''];
        $GoalApprovals->_notifyToCollaborator(1);
        unset($GoalApprovals->request->data);
        $GoalApprovals->request->data = ['approval_btn' => ''];
        $GoalApprovals->_notifyToCollaborator(1);
        unset($GoalApprovals->request->data);
        $GoalApprovals->request->data = ['modify_btn' => ''];
        $GoalApprovals->_notifyToCollaborator(1);
    }

    function testSetCoachFlagTrue()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->Collaborator->current_team_id = 888;
        $params = [
            'user_id'       => 999,
            'team_id'       => 888,
            'coach_user_id' => 777,
        ];
        $GoalApproval->TeamMember->save($params);
        $GoalApproval->_setCoachFlag(999);
        $this->assertTrue($GoalApproval->coach_flag);
    }

    function testSetMemberFlag()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->Collaborator->current_team_id = 888;
        $GoalApproval->TeamMember->current_team_id = 888;
        $params = [
            'user_id'       => 999,
            'team_id'       => 888,
            'coach_user_id' => 777,
        ];
        $GoalApproval->TeamMember->save($params);
        $GoalApproval->_setMemberFlag(777);
        $this->assertTrue($GoalApproval->member_flag);
    }

    function testGetUserTypeReturn1()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->coach_flag = true;
        $GoalApproval->member_flag = false;
        $type = $GoalApproval->_getUserType();
        $this->assertEquals(1, $type);
    }

    function testGetUserTypeReturn2()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->coach_flag = true;
        $GoalApproval->member_flag = true;
        $type = $GoalApproval->_getUserType();
        $this->assertEquals(2, $type);
    }

    function testGetUserTypeReturn3()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->coach_flag = false;
        $GoalApproval->member_flag = true;
        $type = $GoalApproval->_getUserType();
        $this->assertEquals(3, $type);
    }

    function testGetUserTypeReturn0()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->coach_flag = false;
        $GoalApproval->member_flag = false;
        $type = $GoalApproval->_getUserType();
        $this->assertEquals(0, $type);
    }

    function testGetCollaboratorUserIdType1()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->user_type = 1;
        $GoalApproval->user_id = 999;
        $GoalApproval->member_ids = [888, 777];
        $goal_user_id = $GoalApproval->_getCollaboratorUserId();
        $this->assertContains($GoalApproval->user_id, $goal_user_id);
    }

    function testGetCollaboratorUserIdType2()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->user_type = 2;

        $user_id = 999;
        $member_id = [888, 777];
        $ids = array_merge([$user_id], $member_id);

        $GoalApproval->user_id = $user_id;
        $GoalApproval->member_ids = $member_id;
        $goal_user_id = $GoalApproval->_getCollaboratorUserId();
        $this->assertEquals($ids, $goal_user_id);

    }

    function testGetCollaboratorUserIdType3()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->user_type = 3;

        $user_id = 999;
        $member_id = [888, 777];

        $GoalApproval->user_id = $user_id;
        $GoalApproval->member_ids = $member_id;

        $goal_user_id = $GoalApproval->_getCollaboratorUserId();
        $this->assertEquals($member_id, $goal_user_id);

    }

    function testGetGoalInfoType1()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->user_type = 1;
        $GoalApproval->team_id = 1;
        $GoalApproval->user_id = 999;
        $approval_status = 0;
        $GoalApproval->Collaborator->current_team_id = $GoalApproval->team_id;

        $params = [
            'user_id'         => $GoalApproval->user_id,
            'team_id'         => $GoalApproval->team_id,
            'goal_id'         => 888,
            'approval_status' => $approval_status,
            'type'            => 0,
            'priority'        => 1,
        ];
        $GoalApproval->Collaborator->save($params);

        $res = $GoalApproval->_getGoalInfo($approval_status);
        $this->assertCount(1, $res);
    }

    function testGetGoalInfoType2()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->user_type = 2;
        $GoalApproval->team_id = 1;
        $GoalApproval->user_id = 999;
        $approval_status = 0;
        $GoalApproval->Collaborator->current_team_id = $GoalApproval->team_id;

        $params = [
            'user_id'         => $GoalApproval->user_id,
            'team_id'         => $GoalApproval->team_id,
            'goal_id'         => 999,
            'approval_status' => $approval_status,
            'type'            => 0,
            'priority'        => 1,
        ];
        $GoalApproval->Collaborator->save($params);

        $GoalApproval->Collaborator->create();
        $member_id = 888;
        $GoalApproval->member_ids = [$member_id];
        $params = [
            'user_id'         => $member_id,
            'team_id'         => $GoalApproval->team_id,
            'goal_id'         => 888,
            'approval_status' => $approval_status,
            'type'            => 0,
            'priority'        => 1,
        ];
        $GoalApproval->Collaborator->save($params);

        $res = $GoalApproval->_getGoalInfo($approval_status);
        $this->assertCount(2, $res);
    }

    function testGetGoalInfoType3()
    {
        $GoalApproval = $this->_getGoalApprovalCommonMock();
        $GoalApproval->user_type = 3;
        $GoalApproval->team_id = 1;
        $GoalApproval->Collaborator->current_team_id = $GoalApproval->team_id;
        $member_id = 888;
        $GoalApproval->member_ids = [$member_id];
        $approval_status = 0;

        $params = [
            'user_id'         => $member_id,
            'team_id'         => $GoalApproval->team_id,
            'goal_id'         => 888,
            'approval_status' => $approval_status,
            'type'            => 0,
            'priority'        => 1,
        ];
        $GoalApproval->Collaborator->save($params);

        $res = $GoalApproval->_getGoalInfo($approval_status);
        $this->assertCount(1, $res);
    }

    function _getGoalApprovalCommonMock()
    {
        /**
         * @var GoalApprovalController $GoalApproval
         */
        $GoalApproval = $this->generate('GoalApproval', [
            'components' => [
                'Session',
                'Auth'     => ['user', 'loggedIn'],
                'Security' => ['_validateCsrf', '_validatePost'],
                'Ogp',
                'NotifyBiz',
                'GlEmail',
            ],
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
        $GoalApproval->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $GoalApproval->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));

        /** @noinspection PhpUndefinedMethodInspection */
        $GoalApproval->Auth->expects($this->any())->method('loggedIn')
                           ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $GoalApproval->Auth->staticExpects($this->any())->method('user')
                           ->will($this->returnValueMap($value_map)
                           );
        /** @noinspection PhpUndefinedMethodInspection */
        $GoalApproval->Session->expects($this->any())->method('read')
                              ->will($this->returnValueMap([['current_team_id', 1]]));

        $GoalApproval->Goal->Team->my_uid = 1;
        $GoalApproval->Goal->Team->current_team_id = 1;
        $GoalApproval->ApprovalHistory->my_uid = 1;
        $GoalApproval->ApprovalHistory->current_team_id = 1;
        $GoalApproval->Goal->Team->current_team = [
            'Team' => [
                'start_term_month' => 4,
                'border_months'    => 6
            ]
        ];
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->Team->TeamMember->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->Team->TeamMember->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->ActionResult->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->ActionResult->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->GoalCategory->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->GoalCategory->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->KeyResult->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->KeyResult->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->Collaborator->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->Collaborator->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->Follower->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $GoalApproval->Goal->Follower->current_team_id = '1';
        $GoalApproval->Goal->Post->my_uid = '1';
        $GoalApproval->Goal->Post->current_team_id = '1';
        $GoalApproval->Team->EvaluateTerm->my_uid = 1;
        $GoalApproval->Team->EvaluateTerm->current_team_id = 1;

        $this->current_date = strtotime('2015/7/1');
        $this->start_date = strtotime('2015/7/1');
        $this->end_date = strtotime('2015/10/1');

        return $GoalApproval;
    }
}
