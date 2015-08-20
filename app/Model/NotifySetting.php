<?php
App::uses('AppModel', 'Model');

/**
 * NotifySetting Model
 *
 * @property User $User
 */
class NotifySetting extends AppModel
{
    /**
     * 通知設定タイプ
     */
    const TYPE_FEED_POST = 1;
    const TYPE_FEED_COMMENTED_ON_MY_POST = 2;
    const TYPE_FEED_COMMENTED_ON_MY_COMMENTED_POST = 3;
    const TYPE_CIRCLE_USER_JOIN = 4;
    const TYPE_CIRCLE_CHANGED_PRIVACY_SETTING = 5;
    const TYPE_CIRCLE_ADD_USER = 6;
    const TYPE_MY_GOAL_FOLLOW = 7;
    const TYPE_MY_GOAL_COLLABORATE = 8;
    const TYPE_MY_GOAL_CHANGED_BY_LEADER = 9;
    const TYPE_MY_GOAL_TARGET_FOR_EVALUATION = 10;
    const TYPE_MY_GOAL_AS_LEADER_REQUEST_TO_CHANGE = 11;
    const TYPE_MY_GOAL_NOT_TARGET_FOR_EVALUATION = 12;
    const TYPE_MY_MEMBER_CREATE_GOAL = 13;
    const TYPE_MY_MEMBER_COLLABORATE_GOAL = 14;
    const TYPE_MY_MEMBER_CHANGE_GOAL = 15;
    const TYPE_EVALUATION_START = 16;
    const TYPE_EVALUATION_FREEZE = 17;
    const TYPE_EVALUATION_START_CAN_ONESELF = 18;
    const TYPE_EVALUATION_CAN_AS_EVALUATOR = 19;
    const TYPE_EVALUATION_DONE_FINAL = 20;
    const TYPE_FEED_COMMENTED_ON_MY_ACTION = 21;
    const TYPE_FEED_COMMENTED_ON_MY_COMMENTED_ACTION = 22;
    const TYPE_FEED_CAN_SEE_ACTION = 23;
    const TYPE_USER_JOINED_TO_INVITED_TEAM = 24;
    const TYPE_FEED_MESSAGE = 25;

    static public $TYPE = [
        self::TYPE_FEED_POST                             => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'feed_post',
            'icon_class'      => 'fa-comment-o',
        ],
        self::TYPE_FEED_COMMENTED_ON_MY_POST             => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'feed_commented_on_my_post',
            'icon_class'      => 'fa-comment-o',
        ],
        self::TYPE_FEED_COMMENTED_ON_MY_COMMENTED_POST   => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'feed_commented_on_my_commented_post',
            'icon_class'      => 'fa-comment-o',
        ],
        self::TYPE_CIRCLE_USER_JOIN                      => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'circle_user_join',
            'icon_class'      => 'fa-circle-o',
        ],
        self::TYPE_CIRCLE_CHANGED_PRIVACY_SETTING        => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'circle_changed_privacy_setting',
            'icon_class'      => 'fa-circle-o',
        ],
        self::TYPE_CIRCLE_ADD_USER                       => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'circle_add_user',
            'icon_class'      => 'fa-circle-o',
        ],
        self::TYPE_MY_GOAL_FOLLOW                        => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'my_goal_follow',
            'icon_class'      => 'fa-flag',
        ],
        self::TYPE_MY_GOAL_COLLABORATE                   => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'my_goal_collaborate',
            'icon_class'      => 'fa-flag',
        ],
        self::TYPE_MY_GOAL_CHANGED_BY_LEADER             => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'my_goal_changed_by_leader',
            'icon_class'      => 'fa-flag',
        ],
        self::TYPE_MY_GOAL_TARGET_FOR_EVALUATION         => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'my_goal_target_for_evaluation',
            'icon_class'      => 'fa-flag',
        ],
        self::TYPE_MY_GOAL_AS_LEADER_REQUEST_TO_CHANGE   => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'my_goal_as_leader_request_to_change',
            'icon_class'      => 'fa-flag',
        ],
        self::TYPE_MY_GOAL_NOT_TARGET_FOR_EVALUATION     => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'my_goal_not_target_for_evaluation',
            'icon_class'      => 'fa-flag',
        ],
        self::TYPE_MY_MEMBER_CREATE_GOAL                 => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'my_member_create_goal',
            'icon_class'      => 'fa-flag',
        ],
        self::TYPE_MY_MEMBER_COLLABORATE_GOAL            => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'my_member_collaborate_goal',
            'icon_class'      => 'fa-flag',
        ],
        self::TYPE_MY_MEMBER_CHANGE_GOAL                 => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'my_member_change_goal',
            'icon_class'      => 'fa-flag',
        ],
        self::TYPE_EVALUATION_START                      => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'start_evaluation',
            'icon_class'      => 'fa-paw',
        ],
        self::TYPE_EVALUATION_FREEZE                     => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'fleeze_evaluation',
            'icon_class'      => 'fa-paw',
        ],
        self::TYPE_EVALUATION_START_CAN_ONESELF          => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'start_can_oneself_evaluation',
            'icon_class'      => 'fa-paw',
        ],
        self::TYPE_EVALUATION_CAN_AS_EVALUATOR           => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'start_can_evaluate_as_evaluator',
            'icon_class'      => 'fa-paw',
        ],
        self::TYPE_EVALUATION_DONE_FINAL                 => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'final_evaluation_is_done',
            'icon_class'      => 'fa-paw',
        ],
        self::TYPE_FEED_COMMENTED_ON_MY_ACTION           => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'feed_commented_on_my_action',
            'icon_class'      => 'fa-check-circle',
        ],
        self::TYPE_FEED_COMMENTED_ON_MY_COMMENTED_ACTION => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'feed_commented_on_my_commented_action',
            'icon_class'      => 'fa-check-circle',
        ],
        self::TYPE_FEED_CAN_SEE_ACTION                   => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'feed_action',
            'icon_class'      => 'fa-check-circle',
        ],
        self::TYPE_USER_JOINED_TO_INVITED_TEAM           => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'user_joined_to_invited_team',
            'icon_class'      => 'fa-users',
        ],
        self::TYPE_FEED_MESSAGE                          => [
            'mail_template'   => "notify_basic",
            'field_real_name' => null,
            'field_prefix'    => 'feed_message',
            'icon_class'      => 'fa-paper-plane-o',
        ],
    ];

    public function _setFieldRealName()
    {
        self::$TYPE[self::TYPE_FEED_POST]['field_real_name']
            = __d('gl', "自分が閲覧可能な投稿があったとき");
        self::$TYPE[self::TYPE_FEED_COMMENTED_ON_MY_POST]['field_real_name']
            = __d('gl', "自分の投稿に「コメント」されたとき");
        self::$TYPE[self::TYPE_FEED_COMMENTED_ON_MY_COMMENTED_POST]['field_real_name']
            = __d('gl', "自分のコメントした投稿に「コメント」されたとき");
        self::$TYPE[self::TYPE_CIRCLE_USER_JOIN]['field_real_name']
            = __d('gl', "自分が管理者の公開サークルに誰かが参加したとき");
        self::$TYPE[self::TYPE_CIRCLE_CHANGED_PRIVACY_SETTING]['field_real_name']
            = __d('gl', "自分が所属するサークルのプライバシー設定が変更になったとき");
        self::$TYPE[self::TYPE_CIRCLE_ADD_USER]['field_real_name']
            = __d('gl', "自分が新たにサークルメンバーに追加させたとき");
        self::$TYPE[self::TYPE_MY_GOAL_FOLLOW]['field_real_name']
            = __d('gl', "自分がオーナーのゴールがフォローされたとき");
        self::$TYPE[self::TYPE_MY_GOAL_COLLABORATE]['field_real_name']
            = __d('gl', "自分がオーナーのゴールがコラボレートされたとき");
        self::$TYPE[self::TYPE_MY_GOAL_CHANGED_BY_LEADER]['field_real_name']
            = __d('gl', "自分がオーナーの内容がリーダーによって変更されたとき");
        self::$TYPE[self::TYPE_MY_GOAL_TARGET_FOR_EVALUATION]['field_real_name']
            = __d('gl', "自分がオーナーのゴールが評価対象となったとき");
        self::$TYPE[self::TYPE_MY_GOAL_AS_LEADER_REQUEST_TO_CHANGE]['field_real_name']
            = __d('gl', "自分がリーダーのゴールが修正依頼を受けたとき");
        self::$TYPE[self::TYPE_MY_GOAL_NOT_TARGET_FOR_EVALUATION]['field_real_name']
            = __d('gl', "自分がオーナーのゴールが評価対象外となったとき");
        self::$TYPE[self::TYPE_MY_MEMBER_CREATE_GOAL]['field_real_name']
            = __d('gl', "自分(コーチとして)のメンバーがゴールを作成したとき");
        self::$TYPE[self::TYPE_MY_MEMBER_COLLABORATE_GOAL]['field_real_name']
            = __d('gl', "自分(コーチとして)のメンバーがゴールのコラボレーターとなったとき");
        self::$TYPE[self::TYPE_MY_MEMBER_CHANGE_GOAL]['field_real_name']
            = __d('gl', "ゴールの修正依頼を受けた自分(コーチとして)のメンバーがゴール内容を修正したとき");
        self::$TYPE[self::TYPE_EVALUATION_START]['field_real_name']
            = __d('gl', "自分が所属するチームが評価開始となったとき");
        self::$TYPE[self::TYPE_EVALUATION_FREEZE]['field_real_name']
            = __d('gl', "自分が所属するチームが評価凍結となったとき");
        self::$TYPE[self::TYPE_EVALUATION_START_CAN_ONESELF]['field_real_name']
            = __d('gl', "自分が自己評価できる状態になったとき");
        self::$TYPE[self::TYPE_EVALUATION_CAN_AS_EVALUATOR]['field_real_name']
            = __d('gl', "評価者としての自分が評価できる状態になったとき");
        self::$TYPE[self::TYPE_EVALUATION_DONE_FINAL]['field_real_name']
            = __d('gl', "自分の所属するチームの最終者が最終評価データをUploadしたとき");
        self::$TYPE[self::TYPE_FEED_COMMENTED_ON_MY_ACTION]['field_real_name']
            = __d('gl', "自分のアクションに「コメント」されたとき");
        self::$TYPE[self::TYPE_FEED_COMMENTED_ON_MY_COMMENTED_ACTION]['field_real_name']
            = __d('gl', "自分のコメントしたアクションに「コメント」されたとき");
        self::$TYPE[self::TYPE_FEED_CAN_SEE_ACTION]['field_real_name']
            = __d('gl', "自分が閲覧可能なアクションがあったとき");
        self::$TYPE[self::TYPE_USER_JOINED_TO_INVITED_TEAM]['field_real_name']
            = __d('gl', "自分の所属するチームへ招待したユーザーがチームに参加したとき");
        self::$TYPE[self::TYPE_FEED_MESSAGE]['field_real_name']
            = __d('gl', "自分が閲覧可能なメッセージがあったとき");
    }

    function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->_setFieldRealName();
    }

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        'feed_post_app_flg'                             => [
            'boolean' => ['rule' => ['boolean'], 'allowEmpty' => true,],
        ],
        'feed_post_email_flg'                           => [
            'boolean' => ['rule' => ['boolean'], 'allowEmpty' => true,],
        ],
        'feed_commented_on_my_post_app_flg'             => [
            'boolean' => ['rule' => ['boolean'], 'allowEmpty' => true,],
        ],
        'feed_commented_on_my_post_email_flg'           => [
            'boolean' => ['rule' => ['boolean'], 'allowEmpty' => true,],
        ],
        'feed_commented_on_my_commented_post_app_flg'   => [
            'boolean' => ['rule' => ['boolean'], 'allowEmpty' => true,],
        ],
        'feed_commented_on_my_commented_post_email_flg' => [
            'boolean' => ['rule' => ['boolean'], 'allowEmpty' => true,],
        ],
        'circle_user_join_app_flg'                      => [
            'boolean' => ['rule' => ['boolean'], 'allowEmpty' => true,],
        ],
        'circle_user_join_email_flg'                    => [
            'boolean' => ['rule' => ['boolean'], 'allowEmpty' => true,],
        ],
        'circle_changed_privacy_setting_app_flg'        => [
            'boolean' => ['rule' => ['boolean'], 'allowEmpty' => true,],
        ],
        'circle_changed_privacy_setting_email_flg'      => [
            'boolean' => ['rule' => ['boolean'], 'allowEmpty' => true,],
        ],
        'circle_add_user_app_flg'                       => [
            'boolean' => ['rule' => ['boolean'], 'allowEmpty' => true,],
        ],
        'circle_add_user_email_flg'                     => [
            'boolean' => ['rule' => ['boolean'], 'allowEmpty' => true,],
        ],
        'del_flg'                                       => [
            'boolean' => ['rule' => ['boolean'],],
        ],
    ];

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        'User'
    ];

    /**
     * 指定タイプのアプリ、メールの通知設定を返却
     * ユーザ指定は単数、複数の両方対応
     * 返却値は[uid=>['app'=>true,'email'=>true],,,,]
     *
     * @param $user_ids
     * @param $type
     *
     * @return array
     */
    function getAppEmailNotifySetting($user_ids, $type)
    {
        if (!is_array($user_ids)) {
            $user_ids = [$user_ids];
        }
        $default_data = [
            'app'   => true,
            'email' => false,
        ];
        $options = array(
            'conditions' => array(
                'user_id' => $user_ids,
                'NOT'     => ['user_id' => $this->my_uid]
            )
        );
        $result = $this->find('all', $options);
        $res_data = [];
        $field_prefix = self::$TYPE[$type]['field_prefix'];
        if (!empty($result)) {
            foreach ($result as $val) {
                $res_data[$val['NotifySetting']['user_id']] = $default_data;
                if (!$val['NotifySetting'][$field_prefix . '_app_flg']) {
                    //アプリがoff
                    $res_data[$val['NotifySetting']['user_id']]['app'] = false;
                }
                if ($val['NotifySetting'][$field_prefix . '_email_flg']) {
                    //メールがon
                    $res_data[$val['NotifySetting']['user_id']]['email'] = true;
                }
                //引数のユーザリストから除去
                if (($array_key = array_search($val['NotifySetting']['user_id'], $user_ids)) !== false) {
                    unset($user_ids[$array_key]);
                }
            }
        }
        //設定なしユーザはデフォルトを適用
        if (!empty($user_ids)) {
            foreach ($user_ids as $uid) {
                $res_data[$uid] = $default_data;
            }
        }
        return $res_data;
    }

    /**
     * Notification のタイトルを返す
     *
     * 戻り値はHTML入りの文字列になるので注意
     *
     * @param       $type
     * @param       $from_user_names
     * @param       $count_num
     * @param       $item_name
     * @param array $options
     *
     * @return null|string
     */
    function getTitle($type, $from_user_names, $count_num, $item_name, $options = [])
    {
        if ($item_name && !is_array($item_name)) {
            $item_name = json_decode($item_name, true);
        }
        $title = null;
        $user_text = null;
        //カウント数はユーザ名リストを引いた数
        if ($from_user_names) {
            $count_num -= count($from_user_names);
            if (!is_array($from_user_names)) {
                $from_user_names = [$from_user_names];
            }
            foreach ($from_user_names as $key => $name) {
                if ($key !== 0) {
                    $user_text .= __d('gl', "、");
                }
                $user_text .= $name;
            }
        }
        $title = null;
        switch ($type) {
            case self::TYPE_FEED_POST:
                $targets = [];
                // 自分が個人として共有されている場合
                if (isset($options['is_shared_user']) && $options['is_shared_user']) {
                    $share_user_count = count($options['share_user_list']);
                    // 自分以外に個人として他の人に共有されている場合
                    if ($share_user_count >= 2) {
                        $user_name = __d('gl', $options['other_share_user']['User']['display_username'])
                            . __d('gl', '他%d人', $share_user_count - 1);
                    }
                    // 自分だけの場合
                    else {
                        $user_name = __d('gl', 'あなた');
                    }
                    $targets[] = $user_name;

                    // サークルに共有されている場合
                    if (isset($options['share_circle_list']) && $options['share_circle_list']) {
                        $circle_name = $options['share_circle']['Circle']['name'];
                        $circle_count = count($options['share_circle_list']);
                        if ($circle_count >= 2) {
                            $circle_name .= __d('gl', '他%dサークル', $circle_count - 1);
                        }
                        $targets[] = $circle_name;
                    }
                }
                // サークルメンバーとして共有されている場合
                else if (isset($options['is_shared_circle_member']) && $options['is_shared_circle_member']) {
                    // 自分以外に個人として他の人に共有されている場合
                    if (isset($options['share_user_list']) && $options['share_user_list']) {
                        $user_name = $options['other_share_user']['User']['display_username'];
                        $share_user_count = count($options['share_user_list']);
                        if ($share_user_count >= 2) {
                            $user_name .= __d('gl', '他%d人', $share_user_count - 1);
                        }
                        $targets[] = $user_name;
                    }

                    $circle_name = $options['share_circle']['Circle']['name'];
                    $circle_count = count($options['share_circle_list']);
                    if ($circle_count >= 2) {
                        $circle_name .= __d('gl', '他%dサークル', $circle_count - 1);
                    }
                    $targets[] = $circle_name;
                }
                $target_str = "";
                if ($targets) {
                    $target_str = implode(__d('gl', "、"), $targets);
                }
                $title = __d('gl', '<span class="font_bold">%1$s%2$s</span>が<span class="font_bold">%3$s</span>に投稿しました。',
                             h($user_text),
                             ($count_num > 0) ? h(__d('gl', "と他%s人", $count_num)) : null,
                             h($target_str));
                break;
            case self::TYPE_FEED_COMMENTED_ON_MY_POST:
                $title = __d('gl', '<span class="font_bold">%1$s%2$s</span>があなたの投稿にコメントしました。',
                             h($user_text),
                             ($count_num > 0) ? h(__d('gl', "と他%s人", $count_num)) : null);
                break;
            case self::TYPE_FEED_COMMENTED_ON_MY_COMMENTED_POST:
                $title = __d('gl', '<span class="font_bold">%1$s%2$s</span>も<span class="font_bold">%3$s</span>の投稿にコメントしました。',
                             h($user_text),
                             ($count_num > 0) ? h(__d('gl', "と他%s人", $count_num)) : null,
                             isset($options['post_user_name']) ? h($options['post_user_name']) : "");
                break;
            case self::TYPE_CIRCLE_USER_JOIN:
                $title = __d('gl', '<span class="font_bold">%1$s%2$s</span>がサークルに参加しました。', $user_text,
                             ($count_num > 0) ? __d('gl', "と他%s人", $count_num) : null);
                break;
            case self::TYPE_CIRCLE_CHANGED_PRIVACY_SETTING:
                $title = __d('gl', '<span class="font_bold">%1$s</span>がサークルのプライバシー設定を「<span class="font_bold">%2$s</span>」に変更しました。', h($user_text), h($item_name[1]));
                break;
            case self::TYPE_CIRCLE_ADD_USER:
                $title = __d('gl', '<span class="font_bold">%1$s</span>がサークルにあなたを追加しました。', h($user_text));
                break;
            case self::TYPE_MY_GOAL_FOLLOW:
                $title = __d('gl', '<span class="font_bold">%1$s</span>があなたのゴールをフォローしました。', h($user_text));
                break;
            case self::TYPE_MY_GOAL_COLLABORATE:
                $title = __d('gl', '<span class="font_bold">%1$s</span>があなたのゴールにコラボりました。', h($user_text));
                break;
            case self::TYPE_MY_GOAL_CHANGED_BY_LEADER:
                $title = __d('gl', '<span class="font_bold">%1$s</span>があなたのゴールの内容を変更しました。', h($user_text));
                break;
            case self::TYPE_MY_GOAL_TARGET_FOR_EVALUATION:
                $title = __d('gl', '<span class="font_bold">%1$s</span>があなたのゴールを評価対象としました。', h($user_text));
                break;
            case self::TYPE_MY_GOAL_AS_LEADER_REQUEST_TO_CHANGE:
                $title = __d('gl', '<span class="font_bold">%1$s</span>があなたのゴールに修正依頼をしました。', h($user_text));
                break;
            case self::TYPE_MY_GOAL_NOT_TARGET_FOR_EVALUATION:
                $title = __d('gl', '<span class="font_bold">%1$s</span>があなたのゴールを評価対象外としました。', h($user_text));
                break;
            case self::TYPE_MY_MEMBER_CREATE_GOAL:
                $title = __d('gl', '<span class="font_bold">%1$s</span>が新しいゴールを作成しました。', h($user_text));
                break;
            case self::TYPE_MY_MEMBER_COLLABORATE_GOAL:
                $title = __d('gl', '<span class="font_bold">%1$s</span>がゴールにコラボりました。', h($user_text));
                break;
            case self::TYPE_MY_MEMBER_CHANGE_GOAL:
                $title = __d('gl', '<span class="font_bold">%1$s</span>がゴール内容を修正しました。', h($user_text));
                break;
            case self::TYPE_EVALUATION_START:
                $title = __d('gl', '評価期間に入りました。');
                break;
            case self::TYPE_EVALUATION_FREEZE:
                $title = __d('gl', '評価が凍結されました。');
                break;
            case self::TYPE_EVALUATION_START_CAN_ONESELF:
                $title = __d('gl', '自己評価を実施してください。');
                break;
            case self::TYPE_EVALUATION_CAN_AS_EVALUATOR:
                $title = __d('gl', '被評価者の評価を実施してください。');
                break;
            case self::TYPE_EVALUATION_DONE_FINAL:
                $title = __d('gl', '最終者が評価を実施しました。');
                break;
            case self::TYPE_FEED_COMMENTED_ON_MY_ACTION:
                $title = __d('gl', '<span class="font_bold">%1$s</span>があなたのアクションにコメントしました。', h($user_text));
                break;
            case self::TYPE_FEED_COMMENTED_ON_MY_COMMENTED_ACTION:
                $title = __d('gl', '<span class="font_bold">%1$s</span>も<span class="font_bold">%2$s</span>のアクションにコメントしました。',
                             h($user_text),
                             isset($options['post_user_name']) ? h($options['post_user_name']) : "");
                break;
            case self::TYPE_FEED_CAN_SEE_ACTION:
                $title = __d('gl', '<span class="font_bold">%1$s</span>がアクションしました。', $user_text);
                break;
            case self::TYPE_USER_JOINED_TO_INVITED_TEAM:
                $title = __d('gl', '<span class="font_bold">%1$s</span>がチームに参加しました。', $user_text);
                break;
            case self::TYPE_FEED_MESSAGE:
                $title = __d('gl', '<span class="font_bold">%1$s%2$s</span>', $user_text,
                             ($count_num > 0) ? __d('gl', " +%s", $count_num) : null);
                break;
        }
        return $title;
    }

}
