<?php
App::uses('AppModel', 'Model');

/**
 * Team Model
 *
 * @property Badge                           $Badge
 * @property Circle                          $Circle
 * @property CommentLike                     $CommentLike
 * @property CommentMention                  $CommentMention
 * @property CommentRead                     $CommentRead
 * @property Comment                         $Comment
 * @property GivenBadge                      $GivenBadge
 * @property Group                           $Group
 * @property Invite                          $Invite
 * @property JobCategory                     $JobCategory
 * @property PostLike                        $PostLike
 * @property PostMention                     $PostMention
 * @property PostRead                        $PostRead
 * @property Post                            $Post
 * @property TeamMember                      $TeamMember
 * @property Thread                          $Thread
 * @property Evaluator                       $Evaluator
 * @property EvaluationSetting               $EvaluationSetting
 * @property Evaluation                      $Evaluation
 * @property EvaluateTerm                    $EvaluateTerm
 * @property TeamVision                      $TeamVision
 * @property GroupVision                     $GroupVision
 */
class Team extends AppModel
{
    /**
     * タイプ
     */
    const TYPE_FREE = 1;
    const TYPE_PRO = 2;
    static public $TYPE = [self::TYPE_FREE => "", self::TYPE_PRO => ""];
    const OPTION_CHANGE_TERM_FROM_CURRENT = 1;
    const OPTION_CHANGE_TERM_FROM_NEXT = 2;
    static public $OPTION_CHANGE_TERM = [
        self::OPTION_CHANGE_TERM_FROM_CURRENT => "",
        self::OPTION_CHANGE_TERM_FROM_NEXT    => ""
    ];

    /**
     * タイプの名前をセット
     */
    private function _setTypeName()
    {
        self::$TYPE[self::TYPE_FREE] = __d('gl', "フリー");
        self::$TYPE[self::TYPE_PRO] = __d('gl', "プロ");
    }

    private function _setTermOptionName()
    {
        self::$OPTION_CHANGE_TERM[self::OPTION_CHANGE_TERM_FROM_CURRENT] = __d('gl', "今期から");
        self::$OPTION_CHANGE_TERM[self::OPTION_CHANGE_TERM_FROM_NEXT] = __d('gl', "来期から");
    }

    /**
     * Display field
     *
     * @var string
     */
    public $displayField = 'name';

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
                'default_url' => 'no-image-team.jpg',
                'quality'     => 100,
            ]
        ]
    ];
    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        'name'               => ['notEmpty' => ['rule' => ['notEmpty'],],],
        'type'               => ['numeric' => ['rule' => ['numeric'],],],
        'domain_limited_flg' => ['boolean' => ['rule' => ['boolean'],],],
        'start_term_month'   => ['numeric' => ['rule' => ['numeric'],],],
        'border_months'      => ['numeric' => ['rule' => ['numeric'],],],
        'del_flg'            => ['boolean' => ['rule' => ['boolean'],],],
        'photo'              => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'emails'             => [
            'notEmpty'    => ['rule' => ['notEmpty'],],
            'emailsCheck' => [
                'rule' => ['emailsCheck']
            ],
        ],
        'comment'            => [
            'isString' => [
                'rule'       => ['isString',],
                'allowEmpty' => true,
            ],
        ]
    ];

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [];

    /**
     * hasMany associations
     *
     * @var array
     */
    public $hasMany = [
        'Badge',
        'Circle',
        'CommentLike',
        'CommentMention',
        'CommentRead',
        'Comment',
        'GivenBadge',
        'Group',
        'Invite',
        'JobCategory',
        'PostLike',
        'PostMention',
        'PostRead',
        'Post',
        'TeamMember',
        'Thread',
        'Evaluator',
        'Evaluation',
        'EvaluateTerm',
        'EvaluationSetting',
        'EvaluateTerm',
        'TeamVision',
        'GroupVision',
    ];

    public $current_team = [];
    public $current_term_start_date = null;
    public $current_term_end_date = null;

    function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->_setTypeName();
        $this->_setTermOptionName();
    }

    /**
     * @param array  $postData
     * @param string $uid
     *
     * @return array|bool
     */
    function add($postData, $uid)
    {
        $this->set($postData);
        if (!$this->validates()) {
            return false;
        }
        $team_member = [
            'TeamMember' => [
                [
                    'user_id'   => $uid,
                    'admin_flg' => true,
                ]
            ]
        ];
        $postData = array_merge($postData, $team_member);
        $this->saveAll($postData);
        //デフォルトチームを更新
        $user = $this->TeamMember->User->findById($uid);
        if (isset($user['User']) && !$user['User']['default_team_id']) {
            $this->TeamMember->User->id = $uid;
            $this->TeamMember->User->saveField('default_team_id', $this->id);
        }

        // 「チーム全体」サークルを追加
        $circleData = [
            'Circle'       => [
                'team_id'      => $this->id,
                'name'         => __d('gl', 'チーム全体'),
                'description'  => __d('gl', 'チーム全体'),
                'public_flg'   => true,
                'team_all_flg' => true,
            ],
            'CircleMember' => [
                [
                    'team_id'   => $this->id,
                    'user_id'   => $uid,
                    'admin_flg' => true,
                ]
            ]
        ];
        if ($this->Circle->saveAll($circleData)) {
            // サークルメンバー数を更新
            // 新しく追加したチームのサークルなので current_team_id を一時的に変更する
            $tmp = $this->Circle->CircleMember->current_team_id;
            $this->Circle->CircleMember->current_team_id = $this->id;
            $this->Circle->CircleMember->updateCounterCache(['circle_id' => $this->Circle->getLastInsertID()]);
            $this->Circle->CircleMember->current_team_id = $tmp;
        }
        return true;
    }

    function getBorderMonthsOptions()
    {
        $term_options = [
            null => __d('gl', "選択してください"),
            3    => __d('gl', "四半期"),
            6    => __d('gl', "半年"),
            12   => __d('gl', "年")
        ];
        return $term_options;
    }

    function getMonths()
    {
        $months = [
            null => __d('gl', "選択して下さい"),
            1    => __d('gl', "１月"),
            2    => __d('gl', "２月"),
            3    => __d('gl', "３月"),
            4    => __d('gl', "４月"),
            5    => __d('gl', "５月"),
            6    => __d('gl', "６月"),
            7    => __d('gl', "７月"),
            8    => __d('gl', "８月"),
            9    => __d('gl', "９月"),
            10   => __d('gl', "１０月"),
            11   => __d('gl', "１１月"),
            12   => __d('gl', "１２月"),
        ];
        return $months;
    }

    /**
     * @param $data
     *
     * @return null
     */
    function getEmailListFromPost($data)
    {
        if (!isset($data['Team']['emails'])) {
            return null;
        }
        $this->set($data);
        if (!$this->validates()) {
            return null;
        }
        $res = $this->extractEmail($data['Team']['emails']);
        return $res;
    }

    /**
     * @param $emails
     *
     * @return array
     */
    function extractEmail($emails)
    {
        $res = [];
        //一行ずつ処理
        $cr = array("\r\n", "\r"); // 改行コード置換用配列を作成しておく

        $emails = trim($emails); // 文頭文末の空白を削除

        // 改行コードを統一
        //str_replace ("検索文字列", "置換え文字列", "対象文字列");
        $emails = str_replace($cr, "\n", $emails);

        //改行コードで分割（結果は配列に入る）
        $lines_array = explode("\n", $emails);
        //一行ずつ処理
        foreach ($lines_array as $line) {
            //カンマで分割
            $emails = explode(",", $line);
            //メールアドレス毎に処理
            foreach ($emails as $email) {
                //全角スペースを除去
                $email = preg_replace('/　/', ' ', $email);
                //前後スペースを除去
                $email = trim($email);
                //空行はスキップ
                if (empty($email)) {
                    continue;
                }
                if (!in_array($email, $res)) {
                    $res[] = $email;
                }
            }
        }
        return $res;
    }

    function getCurrentTermStartDate()
    {
        if ($this->current_term_start_date) {
            return $this->current_term_start_date;
        }
        $this->setCurrentTermStartEnd();

        return $this->current_term_start_date;
    }

    function getCurrentTermEndDate()
    {
        if ($this->current_term_end_date) {
            return $this->current_term_end_date;
        }
        $this->setCurrentTermStartEnd();

        return $this->current_term_end_date;
    }

    function setCurrentTermStartEnd()
    {
        //既にセットされている場合は処理しない
        if ($this->current_term_start_date && $this->current_term_end_date) {
            return null;
        }
        if (empty($this->current_team)) {
            $this->current_team = $this->findById($this->current_team_id);
            if (empty($this->current_team)) {
                return null;
            }
        }

        $start_term_month = $this->current_team['Team']['start_term_month'];

        $border_months = $this->current_team['Team']['border_months'];

        $now = REQUEST_TIMESTAMP;
        return $this->setCurrentTermStartEndFromParam($start_term_month, $border_months, $now);
    }

    function setCurrentTermStartEndFromParam($start_term_month, $border_months, $target_date = null)
    {
        if (!$target_date) {
            $target_date = REQUEST_TIMESTAMP;
        }
        if ($this->current_term_start_date) {
            $start_date = date("Y-m-1", $this->current_term_start_date + $this->me['timezone'] * 3600);
            $this->current_term_end_date = strtotime($start_date . "+ {$border_months} month") - $this->me['timezone'] * 3600;
            return;
        }
        if ($this->current_term_end_date) {
            $end_date = date("Y-m-1", $this->current_term_end_date + $this->me['timezone'] * 3600);
            $this->current_term_start_date = strtotime($end_date . "- {$border_months} month") - $this->me['timezone'] * 3600;
            return;
        }

        $term = $this->getTermStartEndFromParam($start_term_month, $border_months, $target_date);
        $this->current_term_start_date = $term['start'];
        $this->current_term_end_date = $term['end'];
        return null;
    }

    function getTermStrStartEndFromParam($start_term_month, $border_months, $target_date)
    {
        $res = $this->getTermStartEndFromParam($start_term_month, $border_months, $target_date);
        $res['start'] = date('Y/m/d', $res['start'] + $this->me['timezone'] * 3600);
        $res['end'] = date('Y/m/d', $res['end'] + $this->me['timezone'] * 3600 - 1);
        return $res;
    }

    function getTermStartEndFromParam($start_term_month, $border_months, $target_date)
    {
        $start_date = strtotime(date("Y-{$start_term_month}-1",
                                     $target_date + $this->me['timezone'] * 3600)) - $this->me['timezone'] * 3600;
        $start_date_tmp = date("Y-m-1", $start_date + $this->me['timezone'] * 3600);
        $end_date = strtotime($start_date_tmp . "+ {$border_months} month") - $this->me['timezone'] * 3600;

        //現在が期間内の場合
        if ($start_date <= $target_date && $end_date > $target_date) {
            $term['start'] = $start_date;
            $term['end'] = $end_date;
            return $term;
        }
        //開始日が現在より後の場合
        elseif ($start_date > $target_date) {
            while ($start_date > $target_date) {
                $start_date_tmp = date("Y-m-1", $start_date + $this->me['timezone'] * 3600);
                $start_date = strtotime($start_date_tmp . "- {$border_months} month") - $this->me['timezone'] * 3600;
            }
            $term['start'] = $start_date;
            $start_date_tmp = date("Y-m-1", $term['start'] + $this->me['timezone'] * 3600);
            $term['end'] = strtotime($start_date_tmp . "+ {$border_months} month") - $this->me['timezone'] * 3600;
            return $term;
        }
        //終了日が現在より前の場合
        elseif ($end_date < $target_date) {
            while ($end_date < $target_date) {
                $end_date_tmp = date("Y-m-1", $end_date + $this->me['timezone'] * 3600);
                $end_date = strtotime($end_date_tmp . "+ {$border_months} month") - $this->me['timezone'] * 3600;
            }
            $term['end'] = $end_date;
            $end_date_tmp = date("Y-m-1", $term['end'] + $this->me['timezone'] * 3600);
            $term['start'] = strtotime($end_date_tmp . "- {$border_months} month") - $this->me['timezone'] * 3600;
            return $term;
        }
    }

    function getTermStartEndByDate($target_date)
    {
        if (empty($this->current_team)) {
            $this->current_team = $this->findById($this->current_team_id);
            if (empty($this->current_team)) {
                return null;
            }
        }
        $start_term_month = $this->current_team['Team']['start_term_month'];
        $border_months = $this->current_team['Team']['border_months'];
        return $this->getTermStartEndFromParam($start_term_month, $border_months, $target_date);
    }

    function getBeforeTermStartEnd($count = 1)
    {
        if ($count < 1) {
            return;
        }
        $term['start'] = $this->getCurrentTermStartDate();
        for ($i = 0; $i < $count; $i++) {
            $term = $this->getTermStartEndByDate((strtotime("-1 day", $term['start'])));
        }
        return $term;
    }

    function getAfterTermStartEnd($count = 1)
    {
        if ($count < 1) {
            return;
        }
        $term['end'] = $this->getCurrentTermEndDate();
        for ($i = 0; $i < $count; $i++) {
            $term = $this->getTermStartEndByDate((strtotime("+1 day", $term['end'])));
        }
        return $term;
    }

    /**
     * @param $team_id
     * @param $post_data
     *
     * @return bool
     */
    function saveEditTerm($team_id, $post_data)
    {
        $this->id = $team_id;
        if (!$this->save($post_data)) {
            return false;
        }
        $saved_term = $this->EvaluateTerm->saveChangedTerm(
            $post_data['Team']['change_from'],
            $post_data['Team']['start_term_month'],
            $post_data['Team']['border_months']
        );

        return (bool)$saved_term;
    }
}
