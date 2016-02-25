<?php
App::uses('AppModel', 'Model');
App::uses('Collaborator', 'Model');
App::uses('KeyResult', 'Model');

/**
 * Goal Model
 *
 * @property User         $User
 * @property Team         $Team
 * @property GoalCategory $GoalCategory
 * @property Post         $Post
 * @property KeyResult    $KeyResult
 * @property Collaborator $Collaborator
 * @property Follower     $Follower
 * @property Evaluation   $Evaluation
 * @property Purpose      $Purpose
 * @property ActionResult $ActionResult
 */
class Goal extends AppModel
{
    /**
     * ステータス
     */
    const STATUS_DOING = 0;
    const STATUS_PAUSE = 1;
    const STATUS_COMPLETE = 2;
    static public $STATUS = [self::STATUS_DOING => "", self::STATUS_PAUSE => "", self::STATUS_COMPLETE => ""];

    /**
     * ステータスの名前をセット
     */
    private function _setStatusName()
    {
        self::$STATUS[self::STATUS_DOING] = __d('app', "進行中");
        self::$STATUS[self::STATUS_PAUSE] = __d('app', "中断");
        self::$STATUS[self::STATUS_COMPLETE] = __d('app', "完了");
    }

    /**
     * 重要度の名前をセット
     */
    private function _setPriorityName()
    {
        $this->priority_list[0] = __d('app', "0 (認定対象外)");
        $this->priority_list[1] = __d('app', "1 (とても低い)");
        $this->priority_list[3] = __d('app', "3 (デフォルト)");
        $this->priority_list[5] = __d('app', "5 (とても高い)");
    }

    public $priority_list = [
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
    ];

    /**
     * 検索用オプションをセット
     * ここ以外での各要素の設定は不要です
     * 各タイプの最初の要素がデフォルト表示になります
     */
    public function getSearchOptions()
    {
        $res = [
            'term'     => [
                'present'  => __d('app', "今期"),
                'next'     => __d('app', "来期"),
                'previous' => __d('app', "前期"),
                'before'   => __d('app', "もっと前")],
            'progress' => [
                'all'        => __d('app', "すべて"),
                'complete'   => __d('app', "達成"),
                'incomplete' => __d('app', "未達成")],
            'order'    => [
                'new'      => __d('app', "新着順"),
                'action'   => __d('app', "アクションが多い順"),
                'result'   => __d('app', "出した成果が多い順"),
                'follow'   => __d('app', "フォロワーが多い順"),
                'collabo'  => __d('app', "コラボが多い順"),
                'progress' => __d('app', "進捗率が高い順")]
        ];
        //カテゴリ取得
        $options = [
            'conditions' => [
                'GoalCategory.team_id' => $this->current_team_id,
            ],
            'fields'     => ['id',
                             'name'],
        ];
        $goal_categories = $this->GoalCategory->find('all', $options);
        $res['category'] = ['all' => __d('app', 'すべて')];
        foreach ($goal_categories as $val) {
            $res['category'] += [$val['GoalCategory']['id'] => __d('app', $val['GoalCategory']['name'])];
        }
        return $res;
    }

    /**
     * Display field
     *
     * @var string
     */
    public $displayField = 'name';

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        'name'             => [
            'isString'  => [
                'rule'       => ['isString',],
                'allowEmpty' => true,
            ],
            'maxLength' => ['rule' => ['maxLength', 200]],
            'notEmpty'  => [
                'rule' => 'notEmpty',
            ],
        ],
        'value_unit'       => [
            'numeric' => [
                'rule'       => ['numeric',],
                'allowEmpty' => true,
            ],
        ],
        'target_value'     => [
            'numeric' => [
                'rule'       => ['numeric',],
                'allowEmpty' => true,
            ],
        ],
        'start_value'      => [
            'numeric' => [
                'rule'       => ['numeric',],
                'allowEmpty' => true,
            ],
        ],
        'description'      => [
            'isString'  => [
                'rule'       => ['isString',],
                'allowEmpty' => true,
            ],
            'maxLength' => ['rule' => ['maxLength', 2000]],
        ],
        'evaluate_flg'     => [
            'boolean' => [
                'rule' => ['boolean'],
            ],
        ],
        'status'           => [
            'numeric' => [
                'rule' => ['numeric'],
            ],
        ],
        'priority'         => [
            'numeric' => [
                'rule' => ['numeric'],
            ],
        ],
        'del_flg'          => [
            'boolean' => [
                'rule' => ['boolean'],
            ],
        ],
        'photo'            => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'goal_category_id' => [
            'numeric' => [
                'rule' => ['numeric'],
            ]
        ],
        'start_date'       => [
            'numeric' => ['rule' => ['numeric']]
        ],
        'end_date'         => [
            'numeric' => ['rule' => ['numeric']]
        ],
        'start_value'      => [
            'maxLength' => ['rule' => ['maxLength', 15]],
            'numeric'   => ['rule' => ['numeric']]
        ],
        'target_value'     => [
            'maxLength' => ['rule' => ['maxLength', 15]],
            'numeric'   => ['rule' => ['numeric']]
        ],
    ];

    public $post_validate = [
        'start_date' => [
            'isString' => ['rule' => 'isString', 'message' => 'Invalid Submission']
        ],
        'end_date'   => [
            'isString' => ['rule' => 'isString', 'message' => 'Invalid Submission']
        ]

    ];

    public $actsAs = [
        'Upload' => [
            'photo' => [
                'styles'      => [
                    'small'        => '32x32',
                    'medium'       => '48x48',
                    'medium_large' => '96x96',
                    'large'        => '128x128',
                    'x_large'      => '256x256',
                ],
                'path'        => ":webroot/upload/:model/:id/:hash_:style.:extension",
                'default_url' => 'no-image-goal.jpg',
                'quality'     => 100,
            ]
        ]
    ];

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        'User',
        'Team',
        'GoalCategory',
        'Purpose' => [
            "counterCache" => true,
            'counterScope' => ['Purpose.del_flg' => false]
        ],
    ];

    /**
     * hasMany associations
     *
     * @var array
     */
    public $hasMany = [
        'Post'                => [
            'dependent' => true,
        ],
        'KeyResult'           => [
            'dependent' => true,
        ],
        'ActionResult'        => [
            'dependent' => true,
        ],
        'ActionResultCount'   => [
            'className' => 'ActionResult',
        ],
        'IncompleteKeyResult' => [
            'className' => 'KeyResult'
        ],
        'CompleteKeyResult'   => [
            'className' => 'KeyResult'
        ],
        'Collaborator'        => [
            'dependent' => true,
        ],
        'Leader'              => [
            'className' => 'Collaborator',
        ],
        'MyCollabo'           => [
            'className' => 'Collaborator',
        ],
        'Follower'            => [
            'dependent' => true,
        ],
        'MyFollow'            => [
            'className' => 'Follower',
        ],
        'Evaluation'
    ];

    function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->_setStatusName();
        $this->_setPriorityName();
    }

    function add($data)
    {
        if (!isset($data['Goal']) || empty($data['Goal'])) {
            return false;
        }
        $add_new = false;
        if (!isset($data['Goal']['id'])) {
            $add_new = true;
        }
        $data['Goal']['team_id'] = $this->current_team_id;
        $data['Goal']['user_id'] = $this->my_uid;
        //on/offの場合は現在値0,目標値1をセット
        if (isset($data['Goal']['value_unit']) && isset($data['Goal']['start_value'])) {
            if ($data['Goal']['value_unit'] == KeyResult::UNIT_BINARY) {
                $data['Goal']['start_value'] = 0;
                $data['Goal']['target_value'] = 1;
            }
            $data['Goal']['current_value'] = $data['Goal']['start_value'];
        }

        $this->set($data['Goal']);
        $validate_backup = $this->validate;
        $this->validate = array_merge($this->validate, $this->post_validate);
        if (!$this->validates()) {
            return false;
        }
        $this->validate = $validate_backup;

        // 登録するゴールが来期のものか
        $isNextTerm = (isset($data['Goal']['term_type']) && $data['Goal']['term_type'] == 'next');
        $goal_term = null;
        if ($isNextTerm) {
            $goal_term = $this->Team->EvaluateTerm->getNextTermData();
        }
        else {
            $goal_term = $this->Team->EvaluateTerm->getCurrentTermData();
        }

        //時間をunixtimeに変換
        if (!empty($data['Goal']['start_date'])) {
            $data['Goal']['start_date'] = strtotime($data['Goal']['start_date']) - $goal_term['timezone'] * HOUR;
        }
        //期限を+1day-1secする
        if (!empty($data['Goal']['end_date'])) {
            $data['Goal']['end_date'] = strtotime('+1 day -1 sec',
                                                  strtotime($data['Goal']['end_date'])) - $goal_term['timezone'] * HOUR;
        }

        // 評価期間をまたいでいないかチェック
        if (isset($data['Goal']['start_date']) && isset($data['Goal']['end_date'])) {
            if ($data['Goal']['start_date'] < $goal_term['start_date'] || $goal_term['end_date'] < $data['Goal']['end_date']) {
                return false;
            }
        }

        //新規の場合はデフォルトKRを追加
        if ($add_new) {
            //コラボレータをタイプ　リーダーで保存
            $data['Collaborator'][0]['user_id'] = $this->my_uid;
            $data['Collaborator'][0]['team_id'] = $this->current_team_id;
            $data['Collaborator'][0]['type'] = Collaborator::TYPE_OWNER;
        }
        $this->create();
        $res = $this->saveAll($data);
        Cache::delete($this->getCacheKey(CACHE_KEY_MY_GOAL_AREA, true), 'user_data');
        if ($add_new) {
            Cache::delete($this->getCacheKey(CACHE_KEY_CHANNEL_COLLABO_GOALS, true), 'user_data');
            //ゴール投稿
            $this->Post->addGoalPost(Post::TYPE_CREATE_GOAL, $this->getLastInsertID());
        }
        return $res;
    }

    /**
     * オーナー権限チェック
     *
     * @param $id
     *
     * @return bool
     * @throws RuntimeException
     */
    function isPermittedAdmin($id)
    {
        $this->id = $id;
        if (!$this->exists()) {
            throw new RuntimeException(__d('app', "このゴールは存在しません。"));
        }
        if (!$this->isOwner($this->my_uid, $id)) {
            throw new RuntimeException(__d('app', "このゴールの編集の権限がありません。"));
        }
        return true;
    }

    function isNotExistsEvaluation($goal_id)
    {
        $options = [
            'conditions' => [
                'goal_id' => $goal_id
            ]
        ];
        $res = $this->Evaluation->find('first', $options);
        if (!empty($res)) {
            throw new RuntimeException(__d('app', "このゴールは評価中のため、変更できません。"));
        }
        return true;
    }

    function getAddData($id)
    {
        $start_date = $this->Team->EvaluateTerm->getCurrentTermData()['start_date'];
        $end_date = $this->Team->EvaluateTerm->getCurrentTermData()['end_date'];
        $options = [
            'conditions' => [
                'Goal.id' => $id,
            ],
            'contain'    => [
                'KeyResult'    => [
                    'conditions' => [
                        'KeyResult.end_date >=' => $start_date,
                        'KeyResult.end_date <=' => $end_date,
                        'KeyResult.team_id'     => $this->current_team_id,
                    ]
                ],
                'Purpose',
                'Collaborator' => [
                    'conditions' => [
                        'Collaborator.user_id' => $this->my_uid
                    ]
                ],
            ]
        ];
        $res = $this->find('first', $options);
        //基準の数値を変換
        $res['Goal']['start_value'] = (double)$res['Goal']['start_value'];
        $res['Goal']['current_value'] = (double)$res['Goal']['current_value'];
        $res['Goal']['target_value'] = (double)$res['Goal']['target_value'];

        //KRの数値を変換
        if (!empty($res['KeyResult'])) {
            foreach ($res['KeyResult'] as $k => $k_val) {
                $res['KeyResult'][$k]['start_value'] = (double)$k_val['start_value'];
                $res['KeyResult'][$k]['current_value'] = (double)$k_val['current_value'];
                $res['KeyResult'][$k]['target_value'] = (double)$k_val['target_value'];
            }
        }
        //期間表示名をセット
        $res['Goal']['term_text'] = $this->Team->EvaluateTerm->getTermText($res['Goal']['start_date'],
                                                                           $res['Goal']['end_date']);
        return $res;
    }

    /**
     * user_idからgoal_idを取得する
     *
     * @param $user_id
     * @param $team_id
     *
     * @return array|null
     */
    function getGoalIdFromUserId($user_id, $team_id)
    {
        $options = [
            'fields'     => ['id'],
            'conditions' => [
                'Goal.user_id'     => $user_id,
                'Goal.team_id'     => $team_id,
                'Goal.end_date >=' => $this->Team->EvaluateTerm->getCurrentTermData()['start_date'],
                'Goal.end_date <=' => $this->Team->EvaluateTerm->getCurrentTermData()['end_date'],
                'Goal.del_flg'     => 0,
            ],
        ];
        return $this->find('list', $options);
    }

    /**
     * 自分が作成したゴール取得
     *
     * @param null   $limit
     * @param int    $page
     * @param string $type
     * @param null   $user_id
     * @param int    $start_date
     * @param int    $end_date
     * @param null   $kr_limit
     *
     * @return array
     */
    function getMyGoals($limit = null, $page = 1, $type = "all", $user_id = null, $start_date = null, $end_date = null, $kr_limit = null)
    {
        $user_id = !$user_id ? $this->my_uid : $user_id;
        $start_date = !$start_date ? $this->Team->EvaluateTerm->getCurrentTermData()['start_date'] : $start_date;
        $end_date = !$end_date ? $this->Team->EvaluateTerm->getCurrentTermData()['end_date'] : $end_date;
        $options = [
            'conditions' => [
                'Goal.user_id'     => $user_id,
                'Goal.team_id'     => $this->current_team_id,
                'Goal.end_date >=' => $start_date,
                'Goal.end_date <=' => $end_date,
            ],
            'contain'    => [
                'MyCollabo'           => [
                    'conditions' => [
                        'MyCollabo.user_id' => $this->my_uid
                    ]
                ],
                'KeyResult'           => [
                    //KeyResultは期限が今期内
                    'conditions' => [
                        'KeyResult.end_date >=' => $start_date,
                        'KeyResult.end_date <=' => $end_date,
                    ],
                    'order'      => [
                        'KeyResult.progress ASC',
                        'KeyResult.start_date ASC',
                        'KeyResult.end_date ASC',
                        'KeyResult.priority DESC',
                    ],
                ],
                'IncompleteKeyResult' => [
                    'conditions' => [
                        'IncompleteKeyResult.completed'   => null,
                        'IncompleteKeyResult.end_date >=' => $start_date,
                        'IncompleteKeyResult.end_date <=' => $end_date,
                    ],
                    'fields'     => [
                        'IncompleteKeyResult.id'
                    ]
                ],
                'CompleteKeyResult'   => [
                    'conditions' => [
                        'NOT'                           => [
                            'CompleteKeyResult.completed' => null,
                        ],
                        'CompleteKeyResult.end_date >=' => $start_date,
                        'CompleteKeyResult.end_date <=' => $end_date,
                    ],
                    'fields'     => [
                        'CompleteKeyResult.id'
                    ]
                ],
                'Purpose',
                'Evaluation'          => [
                    'conditions' => [
                        'Evaluation.evaluatee_user_id' => $user_id,
                    ],
                    'fields'     => ['Evaluation.id'],
                    'limit'      => 1,
                ]
            ],
            'limit'      => $limit,
            'page'       => $page
        ];
        if ($kr_limit) {
            $options['contain']['KeyResult']['limit'] = $kr_limit;
        }
        if ($type == "count") {
            unset($options['contain']);
            return $this->find($type, $options);
        }
        $res = $this->find('all', $options);
        //進捗を計算
        foreach ($res as $key => $goal) {
            $res[$key]['Goal']['progress'] = $this->getProgress($goal);
            foreach ($goal['MyCollabo'] as $cb_info) {
                if ($goal['Goal']['id'] === $cb_info['goal_id']) {
                    $res[$key]['Goal']['owner_approval_flag'] = $cb_info['valued_flg'];
                }
            }
        }

        /**
         * ソート
         * ソートは優先順位が低いものから処理する
         */
        //・第４優先ソート【進捗更新日】
        //　進捗更新日が近→遠。
        //　つまり、「進捗更新日」をデータ登録すること。
        //　目的作成や基準作成時は、0%としての更新があったとする。
        $res = $this->sortModified($res);

        //・第３優先ソート【期限】
        //　期限が近→遠
        $res = $this->sortEndDate($res);

        //・第２優先ソート【重要度】
        //　重要度が高→低
        $res = $this->sortPriority($res);

        //目的一覧を取得
        if ($page == 1 && !empty($purposes = $this->Purpose->getPurposesNoGoal())) {
            foreach ($purposes as $key => $val) {
                $purposes[$key]['Goal'] = [];
            }
            /** @noinspection PhpParamsInspection */
            $res = array_merge($purposes, $res);
        }

        return $res;
    }

    /**
     * 自分が作成した前期の未評価ゴール取得
     *
     * @param null   $limit
     * @param int    $page
     * @param string $type
     * @param null   $kr_limit
     *
     * @return array
     */
    function getMyPreviousGoals($limit = null, $page = 1, $type = "all", $kr_limit = null)
    {
        $term = $this->Team->EvaluateTerm->getPreviousTermData();
        $start_date = $term['start_date'];
        $end_date = $term['end_date'];

        //自分がリーダーの未評価前期ゴールリストを取得
        $options = [
            'conditions' => [
                'Goal.user_id'     => $this->my_uid,
                'Goal.team_id'     => $this->current_team_id,
                'Goal.end_date >=' => $start_date,
                'Goal.end_date <=' => $end_date,
            ],
            'fields'     => [
                'Goal.id',
                'Evaluation.status',
            ],
            'joins'      => [
                [
                    'type'       => 'left',
                    'table'      => 'evaluations',
                    'alias'      => 'Evaluation',
                    'conditions' => [
                        'Evaluation.goal_id = Goal.id',
                        'Evaluation.del_flg' => 0,
                    ],
                ],
            ],
            'group'      => [
                'Goal.id'
            ],
        ];
        $res = $this->find('all', $options);
        $goal_ids = [];
        foreach ($res as $record) {
            if (viaIsSet($record['Evaluation']['status']) != 2) {
                $goal_ids[] = $record['Goal']['id'];
            }
        }

        //自分がコラボってるの未評価前期ゴールリストを取得
        $options = [
            'conditions' => [
                'Goal.id'          => $this->Collaborator->getCollaboGoalList($this->my_uid, false),
                'Goal.end_date >=' => $start_date,
                'Goal.end_date <=' => $end_date,
            ],
            'fields'     => [
                'Goal.id',
                'Evaluation.status',
            ],
            'joins'      => [
                [
                    'type'       => 'left',
                    'table'      => 'evaluations',
                    'alias'      => 'Evaluation',
                    'conditions' => [
                        'Evaluation.goal_id = Goal.id',
                        'Evaluation.del_flg' => 0,
                    ],
                ],
            ],
            'group'      => [
                'Goal.id'
            ],
        ];
        $res = $this->find('all', $options);
        foreach ($res as $record) {
            if (viaIsSet($record['Evaluation']['status']) != 2) {
                $goal_ids[] = $record['Goal']['id'];
            }
        }

        //ゴール付加情報を取得
        $options = [
            'conditions' => [
                'Goal.id' => $goal_ids,
            ],
            'contain'    => [
                'MyCollabo'           => [
                    'conditions' => [
                        'MyCollabo.user_id' => $this->my_uid
                    ]
                ],
                'KeyResult'           => [
                    'order' => [
                        'KeyResult.progress ASC',
                        'KeyResult.start_date ASC',
                        'KeyResult.end_date ASC',
                        'KeyResult.priority DESC',
                    ],
                ],
                'IncompleteKeyResult' => [
                    'conditions' => [
                        'IncompleteKeyResult.completed'   => null,
                        'IncompleteKeyResult.end_date >=' => $start_date,
                        'IncompleteKeyResult.end_date <=' => $end_date,
                    ],
                    'fields'     => [
                        'IncompleteKeyResult.id'
                    ]
                ],
                'CompleteKeyResult'   => [
                    'conditions' => [
                        'NOT'                           => [
                            'CompleteKeyResult.completed' => null,
                        ],
                        'CompleteKeyResult.end_date >=' => $start_date,
                        'CompleteKeyResult.end_date <=' => $end_date,
                    ],
                    'fields'     => [
                        'CompleteKeyResult.id'
                    ]
                ],
                'Purpose',
                'Evaluation'          => [
                    'conditions' => [
                        'Evaluation.evaluatee_user_id' => $this->my_uid,
                    ],
                    'fields'     => ['Evaluation.id'],
                    'limit'      => 1,
                ]
            ],
            'limit'      => $limit,
            'page'       => $page
        ];
        if ($type == "count") {
            unset($options['contain']);
            return $this->find('count', $options);
        }
        if ($kr_limit) {
            $options['contain']['KeyResult']['limit'] = $kr_limit;
        }

        $res = $this->find('all', $options);
        //進捗を計算
        foreach ($res as $key => $goal) {
            $res[$key]['Goal']['progress'] = $this->getProgress($goal);
        }

        return $res;
    }

    /**
     * 期限が近→遠　で並べ替え
     *
     * @param     $goals
     * @param int $direction
     *
     * @return bool
     */
    function sortEndDate($goals, $direction = SORT_ASC)
    {
        $end_date_list = array();
        foreach ($goals as $key => $goal) {
            if (isset($goal['Goal']['end_date'])) {
                $end_date_list[$key] = $goal['Goal']['end_date'];
            }
            else {
                //基準なしは下に
                $end_date_list[$key] = 99999999999999999;
            }
        }
        array_multisort($end_date_list, $direction, SORT_NUMERIC, $goals);
        return $goals;
    }

    /**
     * 進捗更新日で並べ替え 近→遠
     *
     * @param     $goals
     * @param int $direction
     *
     * @return bool
     */
    function sortModified($goals, $direction = SORT_DESC)
    {
        $modify_list = array();
        foreach ($goals as $key => $goal) {
            $modify_list[$key] = $goal['Goal']['modified'];
        }
        array_multisort($modify_list, $direction, SORT_NUMERIC, $goals);
        return $goals;
    }

    /**
     * 重要度が高→低 で並べ替え
     *
     * @param     $goals
     * @param int $direction
     *
     * @return bool
     */
    function sortPriority($goals, $direction = SORT_DESC)
    {
        $priority_list = array();
        foreach ($goals as $key => $goal) {
            if (isset($goal['MyCollabo'][0]['priority'])) {
                $priority_list[$key] = $goal['MyCollabo'][0]['priority'];
            }
        }
        if (!empty($priority_list)) {
            array_multisort($priority_list, $direction, SORT_NUMERIC, $goals);
        }
        return $goals;
    }

    function getMyCreateGoalsList($uid)
    {
        return $this->find(
            'list',
            [
                'conditions' => [
                    'Goal.user_id' => $uid
                ],
                'fields'     => [
                    'id'
                ]
            ]
        );
    }

    /**
     * 自分がこらぼったゴール取得
     *
     * @param null   $limit
     * @param int    $page
     * @param string $type
     * @param null   $user_id
     * @param int    $start_date
     * @param int    $end_date
     * @param null   $kr_limit
     *
     * @return array
     */
    function getMyCollaboGoals($limit = null, $page = 1, $type = "all", $user_id = null, $start_date = null, $end_date = null, $kr_limit = null)
    {
        $user_id = !$user_id ? $this->my_uid : $user_id;
        $start_date = !$start_date ? $this->Team->EvaluateTerm->getCurrentTermData()['start_date'] : $start_date;
        $end_date = !$end_date ? $this->Team->EvaluateTerm->getCurrentTermData()['end_date'] : $end_date;

        $goal_ids = $this->Collaborator->getCollaboGoalList($user_id);
        if ($type == "count") {
            return $this->getByGoalId($goal_ids, $limit, $page, $type, $start_date, $end_date);
        }
        $res = $this->getByGoalId($goal_ids, $limit, $page, $type, $start_date, $end_date, $kr_limit);
        $res = $this->sortModified($res);
        $res = $this->sortEndDate($res);
        $res = $this->sortPriority($res);

        return $res;
    }

    function setIsCurrentTerm($goals)
    {
        $start_date = $this->Team->EvaluateTerm->getCurrentTermData()['start_date'];
        $end_date = $this->Team->EvaluateTerm->getCurrentTermData()['end_date'];

        foreach ($goals as $k => $goal) {
            $goals[$k]['Goal']['is_current_term'] = false;
            if ($target_end_date = viaIsSet($goal['Goal']['end_date'])) {
                if ($target_end_date >= $start_date && $target_end_date <= $end_date) {
                    $goals[$k]['Goal']['is_current_term'] = true;
                }
            }
        }
        return $goals;
    }

    function getGoalsWithAction($user_id, $action_limit = MY_PAGE_ACTION_NUMBER, $start_date = null, $end_date = null)
    {
        //対象が自分だった場合プラスボタンを出力する関係上、アクション件数を-1にする
        if ($user_id == $this->my_uid) {
            $action_limit--;
        }
        $goal_ids = $this->Collaborator->getCollaboGoalList($user_id, true);
        $start_date = !$start_date ? $this->Team->EvaluateTerm->getCurrentTermData()['start_date'] : $start_date;
        $end_date = !$end_date ? $this->Team->EvaluateTerm->getCurrentTermData()['end_date'] : $end_date;

        $options = [
            'conditions' => [
                'Goal.id'          => $goal_ids,
                'Goal.end_date >=' => $start_date,
                'Goal.end_date <=' => $end_date,
            ],
            'fields'     => ['Goal.id', 'Goal.user_id', 'Goal.name', 'Goal.photo_file_name', 'Goal.end_date'],
            'contain'    => [
                'Purpose'           => [
                    'fields' => ['Purpose.name']
                ],
                'ActionResult'      => [
                    'fields'           => [
                        'ActionResult.id',
                        'ActionResult.name',
                        'ActionResult.photo1_file_name',
                        'ActionResult.photo2_file_name',
                        'ActionResult.photo3_file_name',
                        'ActionResult.photo4_file_name',
                        'ActionResult.photo5_file_name',
                    ],
                    'limit'            => $action_limit,
                    'conditions'       => ['ActionResult.user_id' => $user_id],
                    'order'            => ['ActionResult.created desc'],
                    'Post'             => [
                        'fields' => ['Post.id']
                    ],
                    'ActionResultFile' => [
                        'conditions' => [
                            'ActionResultFile.index_num' => 0
                        ],
                        'AttachedFile'
                    ],
                ],
                'KeyResult'         => [
                    'fields'     => ['KeyResult.id', 'KeyResult.progress', 'KeyResult.priority'],
                    'conditions' => [
                        'KeyResult.end_date >=' => $start_date,
                        'KeyResult.end_date <=' => $end_date,
                    ]
                ],
                'ActionResultCount' => [
                    'fields'     => ['ActionResultCount.id'],
                    'conditions' => ['ActionResultCount.user_id' => $user_id]
                ],
                'MyCollabo'         => [
                    'fields'     => ['MyCollabo.id', 'MyCollabo.type'],
                    'conditions' => ['MyCollabo.user_id' => $this->my_uid]
                ],
                'MyFollow'          => [
                    'fields'     => ['MyFollow.id'],
                    'conditions' => ['MyFollow.user_id' => $this->my_uid]
                ],
            ]
        ];
        $goals = $this->find('all', $options);
        $goals = Hash::combine($goals, '{n}.Goal.id', '{n}');
        //再度ゴールIDを抽出(フォロー中、コーチング、コラボのゴールは期間の抽出ができない為、期間をキーにして再度抽出する必要がある)
        $goal_ids = Hash::extract($goals, '{n}.Goal.id');
        // getByGoalIdでは自分のゴールのみ取得するので、フォロー中のゴールのCollaborator情報はEmptyになる。
        // そのためsetFollowGoalApprovalFlagメソッドにてCollaborator情報を取得し、認定ステータスを設定する
        $approval_statuses = $this->Collaborator->getOwnersStatus($goal_ids);
        //のちにゴールデータとマージしやすいように配列のキーをgoal_idに差し替える
        $approval_statuses = Hash::combine($approval_statuses, '{n}.Collaborator.goal_id', '{n}');
        //認定ステータスのデータをマージ
        $goals = Hash::merge($goals, $approval_statuses);
        //進捗を計算
        foreach ($goals as $key => $goal) {
            $goals[$key]['Goal']['progress'] = $this->getProgress($goal);
        }

        $res = $this->setFollowGoalApprovalFlag($goals);

        return $res;
    }

    function getMyFollowedGoals($limit = null, $page = 1, $type = 'all', $user_id = null, $start_date = null, $end_date = null)
    {
        $user_id = !$user_id ? $this->my_uid : $user_id;
        $start_date = !$start_date ? $this->Team->EvaluateTerm->getCurrentTermData()['start_date'] : $start_date;
        $end_date = !$end_date ? $this->Team->EvaluateTerm->getCurrentTermData()['end_date'] : $end_date;
        $follow_goal_ids = $this->Follower->getFollowList($user_id);
        $coaching_goal_ids = $this->Team->TeamMember->getCoachingGoalList($user_id);
        $collabo_goal_ids = $this->Collaborator->getCollaboGoalList($user_id, true);
        //フォローしているゴールとコーチングしているゴールをマージして、そこからコラボしているゴールを除外したものが
        //フォロー中ゴールとなる
        $goal_ids = $follow_goal_ids + $coaching_goal_ids;
        //exclude collabo goal
        foreach ($collabo_goal_ids as $k => $v) {
            unset($goal_ids[$k]);
        }
        if ($type == "count") {
            return $this->getByGoalId($goal_ids, $limit, $page, $type, $start_date, $end_date);
        }
        $goals = $this->getByGoalId($goal_ids, $limit, $page, $type, $start_date, $end_date);
        //のちにコラボデータとマージしやすいように配列のキーをgoal_idに差し替える
        $goals = Hash::combine($goals, '{n}.Goal.id', '{n}');
        //再度ゴールIDを抽出(フォロー中、コーチング、コラボのゴールは期間の抽出ができない為、期間をキーにして再度抽出する必要がある)
        $goal_ids = Hash::extract($goals, '{n}.Goal.id');
        // getByGoalIdでは自分のゴールのみ取得するので、フォロー中のゴールのCollaborator情報はEmptyになる。
        // そのためsetFollowGoalApprovalFlagメソッドにてCollaborator情報を取得し、認定ステータスを設定する
        $approval_statuses = $this->Collaborator->getOwnersStatus($goal_ids);
        //のちにゴールデータとマージしやすいように配列のキーをgoal_idに差し替える
        $approval_statuses = Hash::combine($approval_statuses, '{n}.Collaborator.goal_id', '{n}');
        //認定ステータスのデータをマージ
        $goals = Hash::merge($goals, $approval_statuses);
        $res = $this->setFollowGoalApprovalFlag($goals);
        $res = $this->sortModified($res);
        $res = $this->sortEndDate($res);

        return $res;
    }

    function setFollowGoalApprovalFlag($goals)
    {
        foreach ($goals as $key => $goal) {
            if (isset($goal['Collaborator']['valued_flg'])) {
                $goals[$key]['Goal']['owner_approval_flag'] = $goal['Collaborator']['valued_flg'];
            }
        }
        return $goals;
    }

    function getGoalAndKr($goal_ids, $user_id)
    {
        $options = [
            'conditions' => [
                'Goal.id'      => $goal_ids,
                'Goal.team_id' => $this->current_team_id,
            ],
            'contain'    => [
                'KeyResult'    => [
                    'fields' => [
                        'KeyResult.id',
                        'KeyResult.progress',
                        'KeyResult.priority',
                        'KeyResult.completed',
                    ],
                ],
                'Collaborator' => [
                    'conditions' => [
                        'Collaborator.user_id' => $user_id
                    ]
                ],
            ]
        ];
        $res = $this->find('all', $options);
        //calc progress
        foreach ($res as $key => $goal) {
            $res[$key]['Goal']['progress'] = $this->getProgress($goal);
        }
        return $res;
    }

    /**
     * @param        $goal_ids
     * @param null   $limit
     * @param int    $page
     * @param string $type
     * @param int    $start_date
     * @param int    $end_date
     * @param null   $kr_limit
     *
     * @return array|null
     */
    function getByGoalId($goal_ids, $limit = null, $page = 1, $type = "all", $start_date = null, $end_date = null, $kr_limit = null)
    {
        $start_date = !$start_date ? $this->Team->EvaluateTerm->getCurrentTermData()['start_date'] : $start_date;
        $end_date = !$end_date ? $this->Team->EvaluateTerm->getCurrentTermData()['end_date'] : $end_date;
        $options = [
            'conditions' => [
                'Goal.id'          => $goal_ids,
                'Goal.team_id'     => $this->current_team_id,
                'Goal.end_date >=' => $start_date,
                'Goal.end_date <=' => $end_date,
            ],
            'page'       => $page,
            'limit'      => $limit,
            'contain'    => [
                'Purpose',
                'KeyResult'           => [
                    //KeyResultは期限が今期内
                    'conditions' => [
                        'KeyResult.end_date >=' => $start_date,
                        'KeyResult.end_date <=' => $end_date,
                    ],
                    'fields'     => [
                        'KeyResult.id',
                        'KeyResult.name',
                        'KeyResult.end_date',
                        'KeyResult.action_result_count',
                        'KeyResult.progress',
                        'KeyResult.priority',
                        'KeyResult.completed',
                    ],
                    'order'      => [
                        'KeyResult.progress ASC',
                        'KeyResult.start_date ASC',
                        'KeyResult.end_date ASC',
                        'KeyResult.priority DESC',
                    ],
                ],
                'IncompleteKeyResult' => [
                    'conditions' => [
                        'IncompleteKeyResult.completed'   => null,
                        'IncompleteKeyResult.end_date >=' => $start_date,
                        'IncompleteKeyResult.end_date <=' => $end_date,
                    ],
                    'fields'     => [
                        'IncompleteKeyResult.id'
                    ]
                ],
                'CompleteKeyResult'   => [
                    'conditions' => [
                        'NOT'                           => [
                            'CompleteKeyResult.completed' => null,
                        ],
                        'CompleteKeyResult.end_date >=' => $start_date,
                        'CompleteKeyResult.end_date <=' => $end_date,
                    ],
                    'fields'     => [
                        'CompleteKeyResult.id'
                    ]
                ],
                'MyCollabo'           => [
                    'conditions' => [
                        'MyCollabo.user_id' => $this->my_uid
                    ]
                ],
            ]
        ];

        if ($type == "count") {
            unset($options['contain']);
            return $this->find($type, $options);
        }
        if ($kr_limit) {
            $options['contain']['KeyResult']['limit'] = $kr_limit;
        }

        $res = $this->find('all', $options);
        //進捗を計算
        foreach ($res as $key => $goal) {
            $res[$key]['Goal']['progress'] = $this->getProgress($goal);
            foreach ($goal['MyCollabo'] as $cb_info) {
                if ($goal['Goal']['id'] === $cb_info['goal_id']) {
                    $res[$key]['Goal']['owner_approval_flag'] = $cb_info['valued_flg'];
                }
            }
        }
        return $res;
    }

    /**
     * ゴール単独取得
     *
     * @param $id
     *
     * @return array
     */
    function getGoal($id)
    {
        $options = [
            'conditions' => [
                'Goal.id'      => $id,
                'Goal.team_id' => $this->current_team_id,
            ],
            'contain'    => [
                'Purpose',
                'GoalCategory',
                'Leader'       => [
                    'conditions' => ['Leader.type' => Collaborator::TYPE_OWNER],
                    'fields'     => ['Leader.id', 'Leader.user_id'],
                    'User'       => [
                        'fields' => $this->User->profileFields,
                    ]
                ],
                'Collaborator' => [
                    'conditions' => ['Collaborator.type' => Collaborator::TYPE_COLLABORATOR],
                    'fields'     => ['Collaborator.id', 'Collaborator.user_id'],
                    'User'       => [
                        'fields' => $this->User->profileFields,
                    ]
                ],
                'Follower'     => [
                    'fields' => ['Follower.id', 'Follower.user_id'],
                    'User'   => [
                        'fields' => $this->User->profileFields,
                    ]
                ],
                'MyCollabo'    => [
                    'conditions' => [
                        'MyCollabo.type'    => Collaborator::TYPE_COLLABORATOR,
                        'MyCollabo.user_id' => $this->my_uid,
                    ],
                    'fields'     => [
                        'MyCollabo.id',
                        'MyCollabo.role',
                        'MyCollabo.description',
                    ],
                ],
                'MyFollow'     => [
                    'conditions' => [
                        'MyFollow.user_id' => $this->my_uid,
                    ],
                    'fields'     => [
                        'MyFollow.id',
                    ],
                ],
                'KeyResult'    => [
                    'fields' => [
                        'KeyResult.id',
                        'KeyResult.name',
                        'KeyResult.progress',
                        'KeyResult.priority',
                        'KeyResult.completed',
                    ],
                    'order'  => ['KeyResult.completed' => 'asc'],
                ],
                'User'         => [
                    'fields' => $this->User->profileFields,
                ]
            ]
        ];
        $res = $this->find('first', $options);
        $res['Goal']['progress'] = $this->getProgress($res);

        return $res;
    }

    function getGoalMinimum($id)
    {
        $options = [
            'conditions' => [
                'Goal.id'      => $id,
                'Goal.team_id' => $this->current_team_id,
            ],
        ];
        $res = $this->find('first', $options);
        if (!empty($res)) {
            $res['Goal']['progress'] = $this->getProgress($res);
            //不要な少数を除去
            $res['Goal']['start_value'] = (double)$res['Goal']['start_value'];
            $res['Goal']['current_value'] = (double)$res['Goal']['current_value'];
            $res['Goal']['target_value'] = (double)$res['Goal']['target_value'];
        }

        return $res;
    }

    /**
     * $goal_id のゴール情報 + ユーザー情報を取得
     *
     * @param $goal_id
     *
     * @return array|null
     */
    function getGoalsWithUser($goal_id)
    {
        $options = [
            'conditions' => [
                'Goal.id'      => $goal_id,
                'Goal.team_id' => $this->current_team_id,
            ],
            'contain'    => ['User'],
        ];
        return $this->find('all', $options);
    }

    /**
     * 全てのゴール取得
     *
     * @param int   $limit
     * @param array $search_option
     * @param null  $params
     * @param bool  $is_complete
     *
     * @return array
     */
    function getAllGoals($limit = 20, $search_option = null, $params = null, $is_complete = false)
    {
        $start_date = $this->Team->EvaluateTerm->getCurrentTermData()['start_date'];
        $end_date = $this->Team->EvaluateTerm->getCurrentTermData()['end_date'];
        $page = 1;
        if (isset($params['named']['page']) || !empty($params['named']['page'])) {
            $page = $params['named']['page'];
            unset($params['named']['page']);
        }
        $options = [
            'conditions' => [
                'Goal.team_id'     => $this->current_team_id,
                'Goal.end_date >=' => $start_date,
                'Goal.end_date <=' => $end_date,
            ],
            'fields'     => ['Goal.user_id', 'Goal.name', 'Goal.photo_file_name', 'Goal.completed',],
            'order'      => ['Goal.created desc'],
            'limit'      => $limit,
            'page'       => $page,
            'contain'    => [
                'Purpose',
                'Leader'       => [
                    'conditions' => ['Leader.type' => Collaborator::TYPE_OWNER],
                    'User'       => [
                        'fields' => $this->User->profileFields,
                    ]
                ],
                'Collaborator' => [
                    'conditions' => ['Collaborator.type' => Collaborator::TYPE_COLLABORATOR],
                    'User'       => [
                        'fields' => $this->User->profileFields,
                    ]
                ],
                'MyCollabo'    => [
                    'conditions' => [
                        'MyCollabo.type'    => Collaborator::TYPE_COLLABORATOR,
                        'MyCollabo.user_id' => $this->my_uid,
                    ],
                    'fields'     => [
                        'MyCollabo.id',
                        'MyCollabo.role',
                        'MyCollabo.description',
                    ],
                ],
                'MyFollow'     => [
                    'conditions' => [
                        'MyFollow.user_id' => $this->my_uid,
                    ],
                    'fields'     => [
                        'MyFollow.id',
                    ],
                ],
                'Follower'     => [
                    'fields' => [
                        'Follower.id',
                    ],
                ],
                'KeyResult'    => [
                    'fields' => [
                        'KeyResult.id',
                        'KeyResult.progress',
                        'KeyResult.priority',
                        'KeyResult.completed',
                    ],
                ],
                'User'         => [
                    'fields'     => $this->User->profileFields,
                    'TeamMember' => [
                        'fields'     => [
                            'coach_user_id',
                        ],
                        'conditions' => [
                            'coach_user_id' => $this->my_uid,
                        ]
                    ],
                ],
                'ActionResult' => [
                    'fields' => [
                        'id'
                    ]
                ]
            ]
        ];
        if ($is_complete == true) {
            $options['contain']['KeyResult']['conditions']['NOT']['completed'] = null;
        }
        $options = $this->setFilter($options, $search_option);
        $res = $this->find('all', $options);
        //進捗を計算
        foreach ($res as $key => $goal) {
            $res[$key]['Goal']['progress'] = $this->getProgress($goal);
        }
        return $res;
    }

    function countGoalRes($search_option)
    {
        $start_date = $this->Team->EvaluateTerm->getCurrentTermData()['start_date'];
        $end_date = $this->Team->EvaluateTerm->getCurrentTermData()['end_date'];
        $options = [
            'conditions' => [
                'Goal.team_id'     => $this->current_team_id,
                'Goal.end_date >=' => $start_date,
                'Goal.end_date <=' => $end_date,
            ],
            'fields'     => ['Goal.user_id'],
        ];
        $options = $this->setFilter($options, $search_option);
        $res_count = $this->find('count', $options);
        return $res_count ? $res_count : 0;
    }

    function setFilter($options, $search_option)
    {
        //期間指定
        switch (viaIsSet($search_option['term'][0])) {
            case 'previous':
                $previous_term = $this->Team->EvaluateTerm->getPreviousTermData();
                if (!empty($previous_term)) {
                    $options['conditions']['Goal.end_date >='] = $previous_term['start_date'];
                    $options['conditions']['Goal.end_date <='] = $previous_term['end_date'];
                }
                else {
                    $current_term_start = $this->Team->EvaluateTerm->getCurrentTermData()['start_date'];
                    $options['conditions']['Goal.end_date <'] = $current_term_start;
                }
                break;
            case 'next':
                $next_term = $this->Team->EvaluateTerm->getNextTermData();
                if (!empty($next_term)) {
                    $options['conditions']['Goal.end_date >='] = $next_term['start_date'];
                    $options['conditions']['Goal.end_date <='] = $next_term['end_date'];
                }
                else {
                    $current_term_end = $this->Team->EvaluateTerm->getNextTermData()['end_date'];
                    $options['conditions']['Goal.end_date >'] = $current_term_end;
                }
                break;
            case 'before' :
                $previous_term = $this->Team->EvaluateTerm->getPreviousTermData();
                if (!empty($previous_term)) {
                    $options['conditions']['Goal.end_date <='] = $previous_term['start_date'];
                }
                else {
                    $current_term_start = $this->Team->EvaluateTerm->getCurrentTermData()['start_date'];
                    $options['conditions']['Goal.end_date <'] = $current_term_start;
                }
                unset($options['conditions']['Goal.end_date >=']);
                break;
        }
        //カテゴリ指定
        if (viaIsSet($search_option['category'][0]) && $search_option['category'][0] != 'all') {
            $options['conditions']['Goal.goal_category_id'] = $search_option['category'][0];
        }
        //進捗指定
        switch (viaIsSet($search_option['progress'][0])) {
            case 'complete' :
                $options['conditions']['NOT']['Goal.completed'] = null;
                break;
            case 'incomplete' :
                $options['conditions']['Goal.completed'] = null;
                break;
        }
        //ソート指定
        switch (viaIsSet($search_option['order'][0])) {
            case 'action' :
                $options['order'] = ['Goal.action_result_count desc'];
                break;
            case 'result' :
                $options['order'] = ['count_key_result desc'];
                $options['fields'][] = 'count(KeyResult.id) as count_key_result';
                $options['joins'] = [
                    [
                        'type'       => 'left',
                        'table'      => 'key_results',
                        'alias'      => 'KeyResult',
                        'conditions' => [
                            'KeyResult.goal_id = Goal.id',
                            'KeyResult.del_flg' => 0,
                            'NOT'               => ['KeyResult.completed' => null],
                        ],
                    ],
                ];
                $options['group'] = ['Goal.id'];
                break;
            case 'follow' :
                $options['order'] = ['count_follow desc'];
                $options['fields'][] = 'count(Follower.id) as count_follow';
                $options['joins'] = [
                    [
                        'type'       => 'left',
                        'table'      => 'followers',
                        'alias'      => 'Follower',
                        'conditions' => [
                            'Follower.goal_id = Goal.id',
                            'Follower.del_flg' => 0,
                        ],
                    ],
                ];
                $options['group'] = ['Goal.id'];
                break;
            case 'collabo' :
                $options['order'] = ['count_collaborator desc'];
                $options['fields'][] = 'count(Collaborator.id) as count_collaborator';
                $options['joins'] = [
                    [
                        'type'       => 'left',
                        'table'      => 'collaborators',
                        'alias'      => 'Collaborator',
                        'conditions' => [
                            'Collaborator.goal_id = Goal.id',
                            'Collaborator.del_flg' => 0,
                        ],
                    ],
                ];
                $options['group'] = ['Goal.id'];
                break;
            case 'progress' :
                $options['order'] = ['cal_progress desc'];
                $options['fields'][] = '(SUM(KeyResult.priority * KeyResult.progress)/(SUM(KeyResult.priority * 100)))*100 as cal_progress';
                $options['joins'] = [
                    [
                        'type'       => 'left',
                        'table'      => 'key_results',
                        'alias'      => 'KeyResult',
                        'conditions' => [
                            'KeyResult.goal_id = Goal.id',
                            'KeyResult.del_flg' => 0,
                        ],
                    ],
                ];
                $options['group'] = ['Goal.id'];
                break;
        }
        return $options;
    }

    function getProgress($goal)
    {
        $res = 0;
        if (empty($goal['KeyResult'])) {
            return $res;
        }

        $target_progress_total = 0;
        $current_progress_total = 0;
        foreach ($goal['KeyResult'] as $key_result) {
            $target_progress_total += $key_result['priority'] * 100;
            $current_progress_total += $key_result['priority'] * $key_result['progress'];
        }
        if ($target_progress_total != 0) {
            $res = round($current_progress_total / $target_progress_total, 2) * 100;
        }
        return $res;
    }

    function getAllUserGoalProgress($goal_ids, $user_id)
    {
        $res = 0;
        $goals = $this->getGoalAndKr($goal_ids, $user_id);
        if (empty($goals)) {
            return $res;
        }

        $target_progress_total = 0;
        $current_progress_total = 0;
        foreach ($goals as $goal) {
            if (!viaIsSet($goal['Collaborator'][0]['priority'])) {
                continue;
            }
            $target_progress_total += $goal['Collaborator'][0]['priority'] * 100;
            $current_progress_total += $goal['Collaborator'][0]['priority'] * $goal['Goal']['progress'];
        }
        if ($target_progress_total != 0) {
            $res = round($current_progress_total / $target_progress_total, 2) * 100;
        }
        return $res;

    }

    function complete($goal_id)
    {
        $goal = $this->findById($goal_id);
        if (empty($goal)) {
            throw new RuntimeException(__d('app', "ゴールが存在しません。"));
        }
        $this->id = $goal_id;
        $this->saveField('current_value', $goal['Goal']['target_value']);
        $this->saveField('progress', 100);
        $this->saveField('completed', REQUEST_TIMESTAMP);
        return true;
    }

    function incomplete($goal_id)
    {
        $goal = $this->findById($goal_id);
        if (empty($goal)) {
            throw new RuntimeException(__d('app', "ゴールが存在しません。"));
        }
        $goal['Goal']['completed'] = null;
        unset($goal['Goal']['modified']);
        $this->save($goal);
        return true;
    }

    function getCollaboModalItem($id)
    {
        $options = [
            'conditions' => [
                'Goal.id'      => $id,
                'Goal.team_id' => $this->current_team_id,
            ],
            'contain'    => [
                'MyCollabo' => [
                    'conditions' => [
                        'MyCollabo.type'    => Collaborator::TYPE_COLLABORATOR,
                        'MyCollabo.user_id' => $this->my_uid,
                    ],
                    'fields'     => [
                        'MyCollabo.id',
                        'MyCollabo.role',
                        'MyCollabo.description',
                        'MyCollabo.priority',
                    ],
                ],
            ],
        ];
        $res = $this->find('first', $options);
        return $res;
    }

    function getAllUserGoal($start_date = null, $end_date = null)
    {

        if (!$start_date) {
            $start_date = $this->Team->EvaluateTerm->getCurrentTermData()['start_date'];
        }
        if (!$end_date) {
            $end_date = $this->Team->EvaluateTerm->getCurrentTermData()['end_date'];
        }
        $team_member_list = $this->Team->TeamMember->getAllMemberUserIdList();

        $options = [
            'conditions' => [
                'User.id' => $team_member_list
            ],
            'fields'     => $this->User->profileFields,
            'contain'    => [
                'LocalName'    => [
                    'conditions' => ['LocalName.language' => $this->me['language']],
                ],
                'Collaborator' => [
                    'conditions' => [
                        'Collaborator.team_id' => $this->current_team_id,
                    ],
                    'Goal'       => [
                        'conditions' => [
                            'Goal.end_date >=' => $start_date,
                            'Goal.end_date <=' => $end_date
                        ],
                        'Purpose',
                        'GoalCategory',
                    ]
                ],
                'TeamMember'   => [
                    'fields'     => [
                        'member_no',
                        'evaluation_enable_flg'
                    ],
                    'conditions' => [
                        'TeamMember.team_id' => $this->current_team_id
                    ],
                    'order'      => ['TeamMember.member_no DESC']
                ]
            ]
        ];
        $res = $this->Collaborator->User->find('all', $options);
        return $res;
    }

    function filterThisTermIds($gids)
    {
        $start_date = $this->Team->EvaluateTerm->getCurrentTermData()['start_date'];
        $end_date = $this->Team->EvaluateTerm->getCurrentTermData()['end_date'];
        $options = [
            'conditions' => [
                'id'          => $gids,
                'end_date >=' => $start_date,
                'end_date <=' => $end_date,
            ],
            'fields'     => ['id']
        ];
        $res = $this->find('list', $options);
        return $res;
    }

    function isPresentTermGoal($goal_id)
    {
        $options = [
            'fields'     => ['start_date', 'end_date'],
            'conditions' => ['id' => $goal_id],
        ];
        $res = $this->find('first', $options);

        $end_date = $res['Goal']['end_date'];

        $is_present_term_flag = false;
        if (intval($end_date) >= $this->Team->EvaluateTerm->getCurrentTermData()['start_date']
            && intval($end_date) <= $this->Team->EvaluateTerm->getCurrentTermData()['end_date']
        ) {
            $is_present_term_flag = true;
        }

        return $is_present_term_flag;
    }

    function getAllMyGoalNameList($start, $end)
    {
        $goal_ids = $this->Collaborator->getCollaboGoalList($this->my_uid, true);
        $options = [
            'conditions' => [
                'id'          => $goal_ids,
                'end_date >=' => $start,
                'end_date <=' => $end,
            ],
        ];
        $res = $this->find('list', $options);
        return $res;
    }

    function getGoalNameListByGoalIds($goal_ids, $with_all_opt = false, $separate_term = false)
    {
        $options = [
            'conditions' => ['id' => $goal_ids],
            'fields'     => ['id', 'name'],
            'order'      => ['created desc'],
        ];
        if (!$separate_term) {
            $res = $this->find('list', $options);
            if ($with_all_opt) {
                return [null => __d('app', 'すべて')] + $res;
            }
            return $res;
        }
        $start_date = $this->Team->EvaluateTerm->getCurrentTermData()['start_date'];
        $current_term_opt = $options;
        $current_term_opt['conditions']['end_date >='] = $start_date;
        $current_goals = $this->find('list', $current_term_opt);
        $before_term_opt = $options;
        $before_term_opt['conditions']['end_date <='] = $start_date;
        $before_goals = $this->find('list', $before_term_opt);
        $res = [];
        $res += $with_all_opt ? [null => __d('app', 'すべて')] : null;
        $res += ['disable_value1' => '----------------------------------------------------------------------------------------'];
        $res += $current_goals;
        $res += ['disable_value2' => '----------------------------------------------------------------------------------------'];
        $res += $before_goals;
        return $res;
    }

    /**
     * ゴール名が $keyword にマッチするゴールを返す
     *
     * @param string $keyword
     * @param int    $limit
     *
     * @return array
     */
    public function getGoalsByKeyword($keyword, $limit = 10)
    {
        $keyword = trim($keyword);
        $options = [
            'conditions' => [
                'Goal.name LIKE' => $keyword . '%',
                'Goal.team_id'   => $this->current_team_id,
            ],
            'limit'      => $limit,
        ];
        return $this->find('all', $options);
    }

    /**
     * ゴール名が $keyword にマッチするゴールを select2 用のデータ形式にして返す
     *
     * @param string $keyword
     * @param int    $limit
     * @param null   $start_date
     * @param null   $end_date
     *
     * @return array
     */
    public function getGoalsSelect2($keyword, $limit = 10, $start_date = null, $end_date = null)
    {
        $goals = $this->getGoalsByKeyword($keyword, $limit, $start_date, $end_date);

        App::uses('UploadHelper', 'View/Helper');
        $Upload = new UploadHelper(new View());
        $res = [];
        foreach ($goals as $val) {
            $data = [];
            $data['id'] = 'goal_' . $val['Goal']['id'];
            $data['text'] = $val['Goal']['name'];
            $data['image'] = $Upload->uploadUrl($val, 'Goal.photo', ['style' => 'small']);
            $res[] = $data;
        }
        return ['results' => $res];
    }

    /**
     * ゴールが属している評価期間のデータを返す
     *
     * @param $goal_id
     *
     * @return bool
     */
    public function getGoalTermData($goal_id)
    {
        $goal = $this->findById($goal_id);
        if (!$goal) {
            return false;
        }
        return ClassRegistry::init('EvaluateTerm')->getTermDataByDatetime($goal['Goal']['end_date']);
    }

    public function getRelatedGoals($user_id = null)
    {
        if (!$user_id) {
            $user_id = $this->my_uid;
        }
        $g_list = [];
        $g_list = array_merge($g_list, $this->Follower->getFollowList($user_id));
        $g_list = array_merge($g_list, $this->Collaborator->getCollaboGoalList($user_id, true));
        $g_list = array_merge($g_list, $this->User->TeamMember->getCoachingGoalList($user_id));
        return $g_list;
    }
}
