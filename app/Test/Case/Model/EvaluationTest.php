<?php
App::uses('Evaluation', 'Model');

/**
 * Evaluation Test Case
 *
 * @property Evaluation $Evaluation
 */
class EvaluationTest extends CakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.evaluation',
        'app.team',
        'app.badge',
        'app.user',
        'app.email',
        'app.notify_setting',
        'app.comment_like',
        'app.comment',
        'app.post',
        'app.goal',
        'app.purpose',
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
        'app.notification',
        'app.notify_to_user',
        'app.notify_from_user',
        'app.oauth_token',
        'app.team_member',
        'app.job_category',
        'app.member_type',
        'app.local_name',
        'app.member_group',
        'app.group',
        'app.evaluator',
        'app.invite',
        'app.thread',
        'app.message',
        'app.evaluate_term',
        'app.evaluate_score',
        'app.evaluation_setting'
    );
    private $current_date;
    private $start_date;
    private $end_date;
    private $notAllowEmptyArray;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Evaluation = ClassRegistry::init('Evaluation');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Evaluation);

        parent::tearDown();
    }

    function testAddDrafts()
    {
        $this->_setDefault();

        $draftData = [
            [
                'Evaluation' => [
                    'id'                => 1,
                    'comment'           => 'あいうえお',
                    'evaluate_score_id' => 1,
                ],
            ],
            [
                'Evaluation' => [
                    'id'                => 2,
                    'comment'           => 'かきくけこ',
                    'evaluate_score_id' => 1,
                ],
            ],
            [
                'Evaluation' => [
                    'id'                => 3,
                    'comment'           => 'さしすせそ',
                    'evaluate_score_id' => 1,
                ],
            ],
            [
                'Evaluation' => [
                    'id'                => 4,
                    'comment'           => 'たちつてと',
                    'evaluate_score_id' => 1,
                ],
            ],
        ];
        $res = $this->Evaluation->add($draftData, "draft");
        $this->assertNotEmpty($res, "[正常]下書き保存");
        $res = $this->Evaluation->find('all',
                                       [
                                           'conditions' => [
                                               'evaluatee_user_id' => 1,
                                               'evaluate_term_id'  => 1,
                                               'status'            => 1
                                           ]
                                       ]
        );
        $this->assertEquals(count($res), count($draftData));
    }

    function testCheckAvailViewEvaluateListNoTeamMember()
    {
        $this->Evaluation->Team->TeamMember->deleteAll(['TeamMember.team_id' => 1]);
        try {
            $this->Evaluation->checkAvailViewEvaluationList();
        } catch (RuntimeException $e) {
        }
        $this->assertTrue(isset($e));
    }

    function testCheckAvailViewEvaluateListNotEnabled()
    {
        $this->Evaluation->Team->TeamMember->deleteAll(['TeamMember.team_id' => 1]);
        $data = [
            'team_id'               => 1,
            'user_id'               => 1,
            'evaluation_enable_flg' => 0,
        ];
        $this->Evaluation->Team->TeamMember->save($data);
        $this->Evaluation->Team->TeamMember->current_team_id = 1;
        $this->Evaluation->Team->TeamMember->my_uid = 1;
        try {
            $this->Evaluation->checkAvailViewEvaluationList();
        } catch (RuntimeException $e) {
        }
        $this->assertTrue(isset($e));
    }

    function testCheckAvailParameterInEvalFormParameterIsNull()
    {
        $this->_setDefault();
        $termId = null;
        $evaluateeId = null;
        try {
            $this->Evaluation->checkAvailParameterInEvalForm($termId, $evaluateeId);
        } catch (RuntimeException $e) {
        }
        $this->assertTrue(isset($e));
    }

    function testCheckAvailParameterInEvalFormTermIdIsUncorrect()
    {
        $this->_setDefault();
        $termId = 1;
        $evaluateeId = 1000;
        try {
            $this->Evaluation->checkAvailParameterInEvalForm($termId, $evaluateeId);
        } catch (RuntimeException $e) {
        }
        $this->assertTrue(isset($e));
    }

    function testCheckAvailParameterInEvalFormEvaluateeIdIsUncorrect()
    {
        $this->_setDefault();
        $termId = 1000;
        $evaluateeId = 1;
        try {
            $this->Evaluation->checkAvailParameterInEvalForm($termId, $evaluateeId);
        } catch (RuntimeException $e) {
        }
        $this->assertTrue(isset($e));
    }

    function testCheckAvailParameterInEvalFormStatusCannotGet()
    {
        $this->_setDefault();
        $this->Evaluation->Team->EvaluateTerm->saveTerm();
        $termId = $this->Evaluation->Team->EvaluateTerm->getLastInsertID();
        $this->Evaluation->deleteAll(['Evaluation.id >' => 0]);
        $evaluateeId = 1;
        try {
            $this->Evaluation->checkAvailParameterInEvalForm($termId, $evaluateeId);
        } catch (RuntimeException $e) {
        }
        $this->assertTrue(isset($e));
    }

    function testAddRegisters()
    {
        $this->_setDefault();

        $registerData = [
            [
                'Evaluation' => [
                    'id'                => 1,
                    'comment'           => 'あいうえお',
                    'evaluate_score_id' => 1,
                ],
            ],
            [
                'Evaluation' => [
                    'id'                => 2,
                    'comment'           => 'かきくけこ',
                    'evaluate_score_id' => 1,
                ],
            ],
            [
                'Evaluation' => [
                    'id'                => 3,
                    'comment'           => 'さしすせそ',
                    'evaluate_score_id' => 1,
                ],
            ],
            [
                'Evaluation' => [
                    'id'                => 4,
                    'comment'           => 'たちつてと',
                    'evaluate_score_id' => 1,
                ],
            ],
        ];
        $res = $this->Evaluation->add($registerData, "register");
        $this->assertNotEmpty($res, "[正常]評価登録");
        $res = $this->Evaluation->find(
            'all',
            [
                'conditions' => [
                    'evaluatee_user_id' => 1,
                    'evaluate_term_id'  => 1,
                    'status'            => 2
                ]
            ]
        );
        $this->assertEquals(count($res), count($registerData));
    }

    function testAddRegistersValidationError()
    {
        $this->_setDefault();

        $registerData = [
            [
                'Evaluation' => [
                    'id'                => 1,
                    'comment'           => null,
                    'evaluate_score_id' => null,
                ],
            ],
            [
                'Evaluation' => [
                    'id'                => 2,
                    'comment'           => null,
                    'evaluate_score_id' => null,
                ],
            ],
            [
                'Evaluation' => [
                    'id'                => 3,
                    'comment'           => null,
                    'evaluate_score_id' => null,
                ],
            ],
            [
                'Evaluation' => [
                    'id'                => 4,
                    'comment'           => null,
                    'evaluate_score_id' => null,
                ],
            ],
        ];
        $this->setExpectedException('RuntimeException');
        $this->Evaluation->add($registerData, "register");

    }

    function testCheckAvailViewEvaluateListTrue()
    {
        $this->Evaluation->Team->TeamMember->deleteAll(['TeamMember.team_id' => 1]);
        $data = [
            'team_id'               => 1,
            'user_id'               => 1,
            'evaluation_enable_flg' => 1,
        ];
        $this->Evaluation->Team->TeamMember->save($data);
        $this->Evaluation->Team->TeamMember->current_team_id = 1;
        $this->Evaluation->Team->TeamMember->my_uid = 1;
        $res = $this->Evaluation->checkAvailViewEvaluationList();
        $this->assertTrue($res);
    }

    function testGetMyEvaluations()
    {
        $this->Evaluation->deleteAll(['Evaluation.team_id' => 1]);
        $data = [
            'team_id'           => 1,
            'evaluatee_user_id' => 1,
            'evaluator_user_id' => 2,
            'status'            => 0
        ];
        $this->Evaluation->save($data);
        $this->Evaluation->current_team_id = 1;
        $this->Evaluation->my_uid = 1;
        $actual = $this->Evaluation->getMyEvaluation();
        $expect = [
            (int)0 => [
                'Evaluation' => [
                    'id'                => '2',
                    'team_id'           => '1',
                    'evaluatee_user_id' => '1',
                    'evaluator_user_id' => '2',
                    'evaluate_term_id'  => null,
                    'evaluate_type'     => '0',
                    'goal_id'           => null,
                    'comment'           => null,
                    'evaluate_score_id' => null,
                    'index_num'         => '0',
                    'status'            => '0',
                    'del_flg'           => false,
                    'deleted'           => null,
                    'created'           => '1426206160',
                    'modified'          => '1426206160'
                ]
            ]
        ];
        unset($actual[0]['Evaluation']['created']);
        unset($actual[0]['Evaluation']['modified']);
        $this->assertEquals(count($expect), count($actual));
    }

    function testGetEditableEvaluations()
    {
        $this->_setDefault();
        $this->Evaluation->deleteAll(['evaluate_term_id' => 1]);
        $evaluateTermId = 1;
        $evaluateeId = 1;

        $records = [
            [
                'Evaluation' => [
                    'evaluatee_user_id' => 1,
                    'evaluator_user_id' => 1,
                    'evaluate_term_id'  => 1,
                    'comment'           => null,
                    'evaluate_score_id' => null,
                    'index_num'         => 0,
                    'status'            => 0
                ],
            ],
            [
                'Evaluation' => [
                    'evaluatee_user_id' => 1,
                    'evaluator_user_id' => 1,
                    'evaluate_term_id'  => 1,
                    'goal_id'           => 1,
                    'comment'           => null,
                    'evaluate_score_id' => null,
                    'index_num'         => 1,
                    'status'            => 0
                ],

            ],
            [
                'Evaluation' => [
                    'evaluatee_user_id' => 1,
                    'evaluator_user_id' => 1,
                    'evaluate_term_id'  => 1,
                    'comment'           => null,
                    'goal_id'           => 2,
                    'evaluate_score_id' => null,
                    'index_num'         => 2,
                    'status'            => 0
                ],
            ],
            [
                'Evaluation' => [
                    'evaluatee_user_id' => 1,
                    'evaluator_user_id' => 1,
                    'evaluate_term_id'  => 1,
                    'goal_id'           => 3,
                    'comment'           => null,
                    'evaluate_score_id' => null,
                    'index_num'         => 3,
                    'status'            => 0
                ],
            ],
            [
                'Evaluation' => [
                    'evaluatee_user_id' => 1,
                    'evaluator_user_id' => 1,
                    'evaluate_term_id'  => 1,
                    'goal_id'           => 4,
                    'comment'           => null,
                    'evaluate_score_id' => null,
                    'index_num'         => 4,
                    'status'            => 0
                ],
            ],
            [
                'Evaluation' => [
                    'evaluatee_user_id' => 1,
                    'evaluator_user_id' => 1,
                    'evaluate_term_id'  => 1,
                    'goal_id'           => 5,
                    'comment'           => null,
                    'evaluate_score_id' => null,
                    'index_num'         => 5,
                    'status'            => 0
                ],
            ],
        ];
        $this->Evaluation->saveAll($records);
        $res = $this->Evaluation->getEditableEvaluations($evaluateTermId, $evaluateeId);
        $this->assertNotEmpty($res, "[正常]評価登録");
        $this->assertEquals(count($res), count($records));
    }

    /**
     * setAllowEmptyToComment method
     *
     * @return void
     */
    function testSetAllowEmptyToCommentCaseOfEmpty()
    {
        $this->_setDefault();
        $this->Evaluation->validate['comment'] = [];
        $this->Evaluation->setAllowEmptyToComment();
        $this->assertEquals($this->Evaluation->validate['comment'], []);

    }

    function testSetAllowEmptyToCommentCaseOfNotEmpty()
    {
        $this->_setDefault();
        $this->Evaluation->validate['comment'] = $this->notAllowEmptyArray;
        $this->Evaluation->setAllowEmptyToComment();
        $this->assertEquals($this->Evaluation->validate['comment'], []);
    }

    /**
     * setNotAllowEmptyToComment method
     *
     * @return void
     */
    public function testSetNotAllowEmptyToCommentCaseOfEmpty()
    {
        $this->_setDefault();
        $this->Evaluation->validate['comment'] = [];
        $this->Evaluation->setNotAllowEmptyToComment();
        $this->assertEquals($this->Evaluation->validate['comment'], $this->notAllowEmptyArray);

    }

    public function testSetNotAllowEmptyToCommentCaseOfNotEmpty()
    {
        $this->_setDefault();
        $this->Evaluation->validate['comment'] = $this->notAllowEmptyArray;
        $this->Evaluation->setNotAllowEmptyToComment();
        $this->assertEquals($this->Evaluation->validate['comment'], $this->notAllowEmptyArray);
    }

    /**
     * setAllowEmptyToEvaluateScoreId method
     *
     * @return void
     */
    public function testSetAllowEmptyToEvaluateScoreIdCaseOfEmpty()
    {
        $this->_setDefault();
        $this->Evaluation->validate['evaluate_score_id'] = [];
        $this->Evaluation->setAllowEmptyToEvaluateScoreId();
        $this->assertEquals($this->Evaluation->validate['evaluate_score_id'], []);
    }

    public function testSetAllowEmptyToEvaluateScoreIdCaseOfNotEmpty()
    {
        $this->_setDefault();
        $this->Evaluation->validate['evaluate_score_id'] = $this->notAllowEmptyArray;
        $this->Evaluation->setAllowEmptyToEvaluateScoreId();
        $this->assertEquals($this->Evaluation->validate['evaluate_score_id'], []);
    }

    /**
     * setNotAllowEmptyToComment method
     *
     * @return void
     */
    public function testSetNotAllowEmptyToEvaluateScoreIdCaseOfEmpty()
    {
        $this->_setDefault();
        $this->Evaluation->validate['evaluate_score_id'] = [];
        $this->Evaluation->setNotAllowEmptyToEvaluateScoreId();
        $this->assertEquals($this->Evaluation->validate['evaluate_score_id'], $this->notAllowEmptyArray);
    }

    public function testSetNotAllowEmptyToEvaluateScoreIdCaseOfNotEmpty()
    {
        $this->_setDefault();
        $this->Evaluation->validate['evaluate_score_id'] = $this->notAllowEmptyArray;
        $this->Evaluation->setNotAllowEmptyToEvaluateScoreId();
        $this->assertEquals($this->Evaluation->validate['evaluate_score_id'], $this->notAllowEmptyArray);
    }

    function testStartEvaluationNotEnabled()
    {
        $this->Evaluation->current_team_id = 1;
        $this->Evaluation->my_uid = 1;
        $res = $this->Evaluation->startEvaluation();
        $this->assertFalse($res);
    }

    function testStartEvaluationAllEnabled()
    {
        $this->_setDefault();
        $res = $this->Evaluation->startEvaluation();
        $this->assertTrue($res);
    }

    function testGetMyEvalStatus()
    {
        $this->_setDefault();
        $eval_term = [
            'team_id'    => 1,
            'start_date' => 1,
            'end_date'   => 1,
        ];
        $this->Evaluation->Team->EvaluateTerm->save($eval_term);
        $term_id = $this->Evaluation->Team->EvaluateTerm->getLastInsertID();
        $eval = [
            [
                'team_id'           => 1,
                'evaluatee_user_id' => 1,
                'evaluator_user_id' => 1,
                'evaluate_term_id'  => $term_id,
                'evaluate_type'     => 0,
                'index_num'         => 0,
                'my_turn_flg'       => true,
            ],
            [
                'team_id'           => 1,
                'evaluatee_user_id' => 1,
                'evaluator_user_id' => 2,
                'evaluate_term_id'  => $term_id,
                'evaluate_type'     => Evaluation::TYPE_EVALUATOR,
                'index_num'         => 1,
            ],
        ];
        $this->Evaluation->saveAll($eval);
        $expected = [
            (int)0 => [
                'name'      => 'あなた',
                'status'    => '0',
                'this_turn' => true
            ],
            (int)1 => [
                'name'      => '評価者1',
                'status'    => '0',
                'this_turn' => false
            ]
        ];
        $actual = $this->Evaluation->getEvalStatus($term_id, 1);
        $this->assertEquals($expected, $actual['flow']);
    }

    function testGetAddRecordsOfEvaluatee()
    {
        $this->_setDefault();
        $evaluators_save_data = [
            [
                'evaluatee_user_id' => 1,
                'evaluator_user_id' => 2,
                'team_id'           => 1,
                'index_num'         => 0,
            ],
            [
                'evaluatee_user_id' => 1,
                'evaluator_user_id' => 3,
                'team_id'           => 1,
                'index_num'         => 1,
            ],
        ];
        $this->Evaluation->Team->Evaluator->saveAll($evaluators_save_data);
        $evaluators = $this->Evaluation->Team->Evaluator->getEvaluatorsCombined();

        $this->Evaluation->Team->current_term_start_date = 0;
        $this->Evaluation->Team->current_term_end_date = 9999999;

        $collabo = $this->Evaluation->Goal->Collaborator->find('all');
        foreach ($collabo as $k => $v) {
            $collabo[$k]['Collaborator']['valued_flg'] = Collaborator::STATUS_APPROVAL;
        }
        $this->Evaluation->Goal->Collaborator->saveAll($collabo);
        $this->Evaluation->Goal->id = 1;
        $this->Evaluation->Goal->saveField('start_date', 1);
        $this->Evaluation->Goal->saveField('end_date', 1);

        $res = $this->Evaluation->getAddRecordsOfGoalEvaluation(1, 1, $evaluators, 0);
        $this->assertCount(5, $res);
    }

    function _setDefault()
    {
        $this->Evaluation->current_team_id = 1;
        $this->Evaluation->my_uid = 1;
        $this->Evaluation->Team->TeamMember->current_team_id = 1;
        $this->Evaluation->Team->TeamMember->my_uid = 1;
        $this->Evaluation->Team->EvaluateTerm->current_team_id = 1;
        $this->Evaluation->Team->EvaluateTerm->my_uid = 1;
        $this->Evaluation->Team->Evaluator->current_team_id = 1;
        $this->Evaluation->Team->Evaluator->my_uid = 1;
        $this->Evaluation->Team->EvaluationSetting->current_team_id = 1;
        $this->Evaluation->Team->EvaluationSetting->my_uid = 1;
        $this->Evaluation->Goal->Collaborator->current_team_id = 1;
        $this->Evaluation->Goal->Collaborator->my_uid = 1;
        $this->current_date = strtotime('2015/7/1');
        $this->start_date = strtotime('2015/7/1');
        $this->end_date = strtotime('2015/10/1');
        $this->notAllowEmptyArray = [
            'notEmpty' => [
                'rule' => 'notEmpty'
            ]
        ];
    }

}
