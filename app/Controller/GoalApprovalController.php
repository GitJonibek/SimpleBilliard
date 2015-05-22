<?php
App::uses('AppController', 'Controller');

/**
 * GoalApproval Controller
 *
 * @property PaginatorComponent $Paginator
 * @property SessionComponent   $Session
 * @property TeamMember         $TeamMember
 * @property Collaborator       $Collaborator
 * @property ApprovalHistory    $ApprovalHistory
 */
class GoalApprovalController extends AppController
{

    /*
     * 使用モデル
     */
    public $uses = [
        'Collaborator',
        'TeamMember',
        'ApprovalHistory',
    ];

    /*
     * 処理待ち && 自分のゴールの場合
     */
    const WAIT_MY_GOAL_MSG = 0;

    /*
     * 処理待ち && メンバーのゴール && valued_flag=3 の場合
     */
    const MODIFY_MEMBER_GOAL_MSG = 1;

    /*
     * 処理済み && メンバーのゴール && valued_flag=1 の場合
     */
    const APPROVAL_MEMBER_GOAL_MSG = 2;

    /*
     * 処理済み && メンバーのゴール && valued_flag=2 の場合
     */
    const NOT_APPROVAL_MEMBER_GOAL_MSG = 3;

    /*
     * 処理済み用のメッセージリスト
     */
    public $approval_msg_list = [];

    /*
     * コーチ判定フラグ
     * true: コーチがいる false: コーチがいない
     */
    public $coach_flag = false;

    /*
     * コーチID
     */
    public $coach_id = '';

    /*
     * メンバー判定フラグ
     * true: メンバーがいる false: メンバーがいない
     */
    public $member_flag = false;

    /*
     * メンバーIDリスト
     */
    public $member_ids = [];

    /*
     * ログインしているユーザータイプ
     * 1: コーチのみ存在
     * 2: コーチとメンバーが存在
     * 3: メンバーのみ存在
     */
    public $user_type = 0;

    /*
     * ログインユーザーのuser_id
     */
    public $user_id = null;

    /*
     * ログインユーザーのteam_id
     */
    public $team_id = null;

    /*
     * 評価ステータス
     */
    public $goal_status = [
        'unapproved' => 0,
        'approval'   => 1,
        'hold'       => 2,
        'modify'     => 3,
    ];

    /*
     * 検索対象のゴールID
     */
    public $goal_user_ids = [];

    /*
     * 承認前ページの「全ゴール - 自分のゴール」件数
     */
    public $done_cnt = 0;

    /*
     * ログインユーザーの評価対象フラグ
     */
    public $my_evaluation_flg = false;

    public function __construct(CakeRequest $request = null, CakeResponse $response = null)
    {
        parent::__construct($request, $response);
        $this->_setMsg();
    }

    private function _setMsg()
    {
        $this->approval_msg_list = [
            self::WAIT_MY_GOAL_MSG             => __d('gl', "認定待ち"),
            self::MODIFY_MEMBER_GOAL_MSG       => __d('gl', "修正待ち"),
            self::APPROVAL_MEMBER_GOAL_MSG     => __d('gl', "評価対象"),
            self::NOT_APPROVAL_MEMBER_GOAL_MSG => __d('gl', "評価対象外"),
        ];
    }

    /*
     * オーバーライド
     */
    public function beforeFilter()
    {

        parent::beforeFilter();

        $this->user_id = $this->Auth->user('id');
        $this->team_id = $this->Session->read('current_team_id');

        $this->_setCoachFlag($this->user_id, $this->team_id);
        $this->_setMemberFlag($this->user_id, $this->team_id);

        // コーチ認定機能が使えるユーザーはトップページ
        $this->user_type = $this->_getUserType();
        if ($this->user_type === 0) {
        }

        $this->my_evaluation_flg = $this->TeamMember->getEvaluationEnableFlg($this->user_id, $this->team_id);
        $this->goal_user_ids = $this->_getCollaboratorUserId();

        $this->done_cnt = $this->Collaborator->countCollaboGoal(
            $this->team_id, $this->user_id, $this->goal_user_ids,
            [$this->goal_status['approval'], $this->goal_status['hold']]
        );

        $this->layout = LAYOUT_ONE_COLUMN;
    }

    /*
     * 処理待ちページ
     */
    public function index()
    {
        if ($this->request->is('post')) {
            $this->_saveApprovalData();
            return $this->redirect($this->referer());
        }

        $goal_info = $this->_getGoalInfo([$this->goal_status['unapproved'], $this->goal_status['modify']]);

        foreach ($goal_info as $key => $val) {
            $goal_info[$key]['my_goal'] = false;

            if ($this->user_id === $val['User']['id']) {
                $goal_info[$key]['my_goal'] = true;
                $goal_info[$key]['status'] = $this->approval_msg_list[self::WAIT_MY_GOAL_MSG];
                if ($this->my_evaluation_flg === false) {
                    unset($goal_info[$key]);
                }
            }

            if ($val['Collaborator']['valued_flg'] === '3') {
                $goal_info[$key]['status'] = $this->approval_msg_list[self::MODIFY_MEMBER_GOAL_MSG];
            }

        }

        $done_cnt = $this->done_cnt;
        $kr = new KeyResult();
        $value_unit_list = $kr::$UNIT;

        $this->set(compact('value_unit_list', 'goal_info', 'done_cnt'));
    }

    /*
     * 処理済みページ
     */
    public function done()
    {
        if ($this->request->is('post')) {
            $this->_saveApprovalData();
            return $this->redirect($this->referer());
        }

        $goal_info = $this->_getGoalInfo([$this->goal_status['approval'], $this->goal_status['hold']]);

        foreach ($goal_info as $key => $val) {
            $goal_info[$key]['my_goal'] = false;
            $goal_info[$key]['is_present_term'] = $this->Goal->isPresentTermGoal($val['Goal']['id']);

            if ($this->user_id === $val['User']['id']) {
                $goal_info[$key]['my_goal'] = true;
                if ($this->my_evaluation_flg === false) {
                    unset($goal_info[$key]);
                }
            }

            if ($val['Collaborator']['valued_flg'] === '1') {
                $goal_info[$key]['status'] = $this->approval_msg_list[self::APPROVAL_MEMBER_GOAL_MSG];

            }
            else {
                if ($val['Collaborator']['valued_flg'] === '2') {
                    $goal_info[$key]['status'] = $this->approval_msg_list[self::NOT_APPROVAL_MEMBER_GOAL_MSG];
                }
            }
        }

        $done_cnt = $this->done_cnt;
        $kr = new KeyResult();
        $value_unit_list = $kr::$UNIT;

        $this->set(compact('value_unit_list', 'goal_info', 'done_cnt'));
    }

    function _saveApprovalData()
    {
        $data = viaIsSet($this->request->data['GoalApproval']);
        if (empty($data)) {
            return;
        }
        $this->_changeStatus($data);

        $cb_id = viaIsSet($data['collaborator_id']);
        if (!$cb_id) {
            return;
        }
        $collaborator = $this->Collaborator->findById($cb_id);
        $goal_id = viaIsSet($collaborator['Collaborator']['goal_id']);
        if (!$goal_id) {
            return;
        }
        $this->_trackToMixpanel($goal_id);
        $this->_notifyToCollaborator($cb_id);

    }

    /*
     * 認定状態変更コントロール
     */
    public function _changeStatus($data)
    {
        if (isset($this->request->data['comment_btn']) === true) {
            $this->_comment($data);
        }
        elseif (isset($this->request->data['wait_btn']) === true) {
            $this->_wait($data);
        }
        elseif (isset($this->request->data['approval_btn']) === true) {
            $this->_approval($data);
        }
        elseif (isset($this->request->data['modify_btn']) === true) {
            $this->_modify($data);
        }
    }

    /*
     * 承認する
     */
    public function _approval($data)
    {
        $cb_id = isset($data['collaborator_id']) === true ? $data['collaborator_id'] : '';
        if (empty($cb_id) === false) {
            $this->Collaborator->changeApprovalStatus(intval($cb_id), $this->goal_status['approval']);
            $this->_comment($data);
        }
    }

    /*
     * 承認しない
     */
    public function _wait($data)
    {
        $cb_id = isset($data['collaborator_id']) === true ? $data['collaborator_id'] : '';
        if (empty($cb_id) === false) {
            $this->Collaborator->changeApprovalStatus(intval($cb_id), $this->goal_status['hold']);
            $this->_comment($data);
        }
    }

    /*
     * 修正依頼をする
     */
    public function _modify($data)
    {
        $cb_id = isset($data['collaborator_id']) === true ? $data['collaborator_id'] : '';
        if (empty($cb_id) === false) {
            $this->Collaborator->changeApprovalStatus(intval($cb_id), $this->goal_status['modify']);
            $this->_comment($data);
        }
    }

    /*
     *  コメントする
     */
    public function _comment($data)
    {
        $cb_id = isset($data['collaborator_id']) === true ? $data['collaborator_id'] : '';
        $comment = isset($data['comment']) === true ? $data['comment'] : '';

        // 現状はコメントがある時、履歴を追加している。
        // 今後はコメントなくてもアクションステータスを格納する必要あり。
        if (empty($cb_id) === false && empty($comment) === false) {
            // Todo: 第３パラメータに「1」がハードコーディングされているが、履歴表示の実装の時、定数化する
            $this->ApprovalHistory->add($cb_id, $this->user_id, 1, $comment);
        }
    }

    function _trackToMixpanel($goal_id)
    {

        $approval_type = $approval_member_type = null;
        if (isset($this->request->data['comment_btn']) === true) {
            $approval_type = MixpanelComponent::PROP_APPROVAL_STATUS_APPROVAL_COMMENT_GOAL;
            $approval_member_type = MixpanelComponent::PROP_APPROVAL_MEMBER_MEMBER;
        }
        elseif (isset($this->request->data['wait_btn']) === true) {
            $approval_type = MixpanelComponent::PROP_APPROVAL_STATUS_APPROVAL_INEVALUABLE;
            $approval_member_type = MixpanelComponent::PROP_APPROVAL_MEMBER_COACH;
        }
        elseif (isset($this->request->data['approval_btn']) === true) {
            $approval_type = MixpanelComponent::PROP_APPROVAL_STATUS_APPROVAL_EVALUABLE;
            $approval_member_type = MixpanelComponent::PROP_APPROVAL_MEMBER_COACH;
        }
        elseif (isset($this->request->data['modify_btn']) === true) {
            $approval_type = MixpanelComponent::PROP_APPROVAL_STATUS_APPROVAL_REVISION_REQUESTS;
            $approval_member_type = MixpanelComponent::PROP_APPROVAL_MEMBER_COACH;
        }

        $this->Mixpanel->trackApproval(
            $approval_type,
            $approval_member_type,
            $goal_id
        );
    }

    /*
     * リストに表示するゴールのUserIDを取得
     */
    public function _getCollaboratorUserId()
    {
        $goal_user_ids = [];
        if ($this->user_type === 1) {
            $goal_user_ids = [$this->user_id];
        }
        elseif ($this->user_type === 2) {
            $goal_user_ids = array_merge([$this->user_id], $this->member_ids);
        }
        elseif ($this->user_type === 3) {
            $goal_user_ids = $this->member_ids;
        }
        return $goal_user_ids;
    }

    /*
     * リストに表示するゴールのUserIDを取得
     */
    public function _getGoalInfo($goal_status)
    {
        $goal_info = [];
        if ($this->user_type === 1) {
            $goal_info = $this->Collaborator->getCollaboGoalDetail(
                $this->team_id, [$this->user_id], $goal_status);

        }
        elseif ($this->user_type === 2) {
            $member_goal_info = $this->Collaborator->getCollaboGoalDetail(
                $this->team_id, $this->member_ids, $goal_status, false);

            $my_goal_info = $this->Collaborator->getCollaboGoalDetail(
                $this->team_id, [$this->user_id], $goal_status);

            $goal_info = array_merge($member_goal_info, $my_goal_info);

        }
        elseif ($this->user_type === 3) {
            $goal_info = $this->Collaborator->getCollaboGoalDetail(
                $this->team_id, $this->member_ids, $goal_status, false);
        }

        return $goal_info;
    }

    /*
     * ログインしているユーザーはコーチが存在するのか
     */
    public function _setCoachFlag($user_id, $team_id)
    {
        $coach_id = $this->TeamMember->selectCoachUserIdFromTeamMembersTB($user_id, $team_id);
        if (isset($coach_id['TeamMember']['coach_user_id']) === true
            && is_null($coach_id['TeamMember']['coach_user_id']) === false
        ) {
            $this->coach_id = $coach_id['TeamMember']['coach_user_id'];
            $this->coach_flag = true;
        }
    }

    /*
     * ログインしているユーザーは管理するメンバー存在するのか
     */
    public function _setMemberFlag($user_id, $team_id)
    {
        $member_ids = $this->TeamMember->selectUserIdFromTeamMembersTB($user_id, $team_id);
        if (empty($member_ids) === false) {
            $this->member_ids = $member_ids;
            $this->member_flag = true;
        }
    }

    /*
     * コーチ認定機能を使えるユーザーか判定
     * 1: コーチがいる、メンバーいない
     * 2: コーチいる、メンバーがいる
     * 3: コーチがいない、メンバーがいる
     */
    public function _getUserType()
    {

        if ($this->coach_flag === true && $this->member_flag === false) {
            return 1;
        }

        if ($this->coach_flag === true && $this->member_flag === true) {
            return 2;
        }

        if ($this->coach_flag === false && $this->member_flag === true) {
            return 3;
        }

        return 0;
    }

    /**
     * send notify to collaborator
     *
     * @param $collabo_id
     */
    function _notifyToCollaborator($collabo_id)
    {
        $collaborator = $this->Collaborator->findById($collabo_id);
        if (!viaIsSet($collaborator['Collaborator'])) {
            return;
        }
        $notify_type = null;
        if (isset($this->request->data['comment_btn']) === true) {
            //TODO コーチ宛に通知出さなくていいのかな？ by Daiki
        }
        elseif (isset($this->request->data['wait_btn']) === true) {
            $notify_type = NotifySetting::TYPE_MY_GOAL_NOT_TARGET_FOR_EVALUATION;
        }
        elseif (isset($this->request->data['approval_btn']) === true) {
            $notify_type = NotifySetting::TYPE_MY_GOAL_TARGET_FOR_EVALUATION;
        }
        elseif (isset($this->request->data['modify_btn']) === true) {
            $notify_type = NotifySetting::TYPE_MY_GOAL_AS_LEADER_REQUEST_TO_CHANGE;
        }

        //Notify
        $this->NotifyBiz->execSendNotify($notify_type,
                                         $collaborator['Collaborator']['goal_id'],
                                         null,
                                         $collaborator['Collaborator']['user_id']
        );
    }

}
