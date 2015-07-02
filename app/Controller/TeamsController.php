<?php
App::uses('AppController', 'Controller');

/**
 * Teams Controller
 *
 * @property Team $Team
 */
class TeamsController extends AppController
{
    public function beforeFilter()
    {
        parent::beforeFilter();
    }

    public function add()
    {
        $this->layout = LAYOUT_ONE_COLUMN;
        $border_months_options = $this->Team->getBorderMonthsOptions();
        $start_term_month_options = $this->Team->getMonths();
        $this->set(compact('border_months_options', 'start_term_month_options'));

        if (!$this->request->is('post')) {
            return $this->render();
        }

        if (!$this->Team->add($this->request->data, $this->Auth->user('id'))) {
            $this->Pnotify->outError(__d('gl', "チーム作成に失敗しました。"));
            return $this->render();
        }
        $this->_refreshAuth($this->Auth->user('id'));
        $this->Session->write('current_team_id', $this->Team->getLastInsertID());
        $this->Pnotify->outSuccess(__d('gl', "チームを作成しました。"));
        return $this->redirect(['action' => 'invite']);
    }

    public function edit_team()
    {
        $this->request->allowMethod('post');
        $this->Team->id = $this->current_team_id;
        if ($this->Team->save($this->request->data)) {
            $this->Pnotify->outSuccess(__d('gl', "チームの基本設定を更新しました。"));
        }
        else {
            $this->Pnotify->outError(__d('gl', "チームの基本設定の更新に失敗しました。"));
        }
        return $this->redirect($this->referer());
    }

    public function edit_term()
    {
        $this->request->allowMethod('post');
        $this->Team->begin();
        if ($this->Team->saveEditTerm($this->current_team_id, $this->request->data)) {
            $this->Pnotify->outSuccess(__d('gl', "期間設定を更新しました。"));
            $this->Team->commit();
        }
        else {
            $this->Pnotify->outError(__d('gl', "期間設定の更新に失敗しました。"));
            $this->Team->rollback();
        }
        return $this->redirect($this->referer());
    }

    public function settings()
    {
        $this->layout = LAYOUT_SETTING;
        $team_id = $this->Session->read('current_team_id');
        try {
            $this->Team->TeamMember->adminCheck($team_id, $this->Auth->user('id'));
        } catch (RuntimeException $e) {
            $this->Pnotify->outError($e);
            $this->redirect($this->referer());
        }
        $border_months_options = $this->Team->getBorderMonthsOptions();
        $start_term_month_options = $this->Team->getMonths();
        $this->set(compact('border_months_options', 'start_term_month_options'));

        $team = $this->Team->findById($team_id);
        unset($team['Team']['id']);
        $term_start_date = $this->Team->getCurrentTermStartDate();
        $term_end_date = $this->Team->getCurrentTermEndDate();
        $term_end_date = $term_end_date - 1;
        //get evaluation setting
        $eval_enabled = $this->Team->EvaluationSetting->isEnabled();
        $eval_setting = $this->Team->EvaluationSetting->getEvaluationSetting();
        $eval_scores = $this->Team->Evaluation->EvaluateScore->getScore($team_id);
        $goal_categories = $this->Goal->GoalCategory->getCategories($team_id);
        $this->request->data = array_merge($this->request->data, $eval_setting, $eval_scores, $goal_categories, $team);

        $current_term_id = $this->Team->EvaluateTerm->getCurrentTermId();
        $previous_term_id = $this->Team->EvaluateTerm->getPreviousTermId();
        $eval_start_button_enabled = true;
        if (!$this->Team->EvaluateTerm->isAbleToStartEvaluation($current_term_id)) {
            $eval_start_button_enabled = false;
        }
        $this->set(compact('team', 'term_start_date', 'term_end_date', 'eval_enabled', 'eval_start_button_enabled',
                           'eval_scores'));
        $current_statuses = $this->Team->Evaluation->getAllStatusesForTeamSettings($current_term_id);
        $current_progress = $this->_getEvalProgress($current_statuses);
        $previous_statuses = $this->Team->Evaluation->getAllStatusesForTeamSettings($previous_term_id);
        $previous_progress = $this->_getEvalProgress($previous_statuses);

        // Get term info
        $current_eval_is_frozen = $this->Team->EvaluateTerm->checkFrozenEvaluateTerm($current_term_id);
        $current_eval_is_started = $this->Team->EvaluateTerm->isStartedEvaluation($current_term_id);
        $current_term = $this->Team->EvaluateTerm->getCurrentTerm();
        $current_term_start_date = viaIsSet($current_term['start_date']);
        $current_term_end_date = viaIsSet($current_term['end_date']) - 1;
        $previous_eval_is_frozen = $this->Team->EvaluateTerm->checkFrozenEvaluateTerm($previous_term_id);
        $previous_eval_is_started = $this->Team->EvaluateTerm->isStartedEvaluation($previous_term_id);
        $previous_term = $this->Team->EvaluateTerm->getPreviousTerm();
        $previous_term_start_date = viaIsSet($previous_term['start_date']);
        $previous_term_end_date = viaIsSet($previous_term['end_date']) - 1;
        $next_term = $this->Team->EvaluateTerm->getNextTerm();
        $next_term_start_date = viaIsSet($next_term['start_date']);
        $next_term_end_date = viaIsSet($next_term['end_date']) - 1;

        $this->set(compact(
                       'current_statuses',
                       'current_progress',
                       'previous_statuses',
                       'previous_progress',
                       'eval_is_frozen',
                       'current_term_id',
                       'current_eval_is_frozen',
                       'current_eval_is_started',
                       'current_term_start_date',
                       'current_term_end_date',
                       'previous_term_id',
                       'previous_eval_is_frozen',
                       'previous_eval_is_started',
                       'previous_term_start_date',
                       'previous_term_end_date',
                       'next_term_start_date',
                       'next_term_end_date'
                   ));

        return $this->render();
    }

    function _getEvalProgress($statuses)
    {
        if (!$statuses) {
            return null;
        }
        // 全体progressカウント
        $all_cnt = array_sum(Hash::extract($statuses, "{n}.all_num"));
        $incomplete_cnt = array_sum(Hash::extract($statuses, "{n}.incomplete_num"));
        $complete_cnt = (int)$all_cnt - (int)$incomplete_cnt;
        $progress_percent = 0;
        if ($complete_cnt != 0) {
            $progress_percent = round(((int)$complete_cnt / (int)$all_cnt) * 100, 1);
        }
        return $progress_percent;
    }

    function save_evaluation_setting()
    {
        $this->request->allowMethod(['post', 'put']);
        $this->Team->begin();
        if ($this->Team->EvaluationSetting->save($this->request->data['EvaluationSetting'])) {
            $this->Team->commit();
            $this->Pnotify->outSuccess(__d('gl', "評価設定を保存しました。"));
        }
        else {
            $this->Team->rollback();
            $this->Pnotify->outError(__d('gl', "評価設定が保存できませんでした。"));
        }
        return $this->redirect($this->referer());
    }

    function save_evaluation_scores()
    {
        $this->request->allowMethod(['post', 'put']);
        $this->Team->begin();
        if ($this->Team->Evaluation->EvaluateScore->saveScores($this->request->data['EvaluateScore'],
                                                               $this->Session->read('current_team_id'))
        ) {
            $this->Team->commit();
            $this->Pnotify->outSuccess(__d('gl', "評価スコア設定を保存しました。"));
        }
        else {
            $this->Team->rollback();
            $this->Pnotify->outError(__d('gl', "評価スコア設定が保存できませんでした。"));
        }
        return $this->redirect($this->referer());
    }

    function save_goal_categories()
    {
        $this->request->allowMethod(['post', 'put']);
        $this->Team->begin();
        if ($this->Goal->GoalCategory->saveGoalCategories($this->request->data['GoalCategory'],
                                                          $this->Session->read('current_team_id'))
        ) {
            $this->Team->commit();
            $this->Pnotify->outSuccess(__d('gl', "ゴールカテゴリ設定を保存しました。"));
        }
        else {
            $this->Team->rollback();
            $this->Pnotify->outError(__d('gl', "ゴールカテゴリ設定が保存できませんでした。"));
        }
        return $this->redirect($this->referer());

    }

    function to_inactive_score()
    {
        $id = viaIsSet($this->request->params['named']['team_id']);
        $this->request->allowMethod(['post']);
        $this->Team->Evaluation->EvaluateScore->setToInactive($id);
        $this->Pnotify->outSuccess(__d('gl', "スコア定義を削除しました。"));
        return $this->redirect($this->referer());
    }

    function ajax_get_confirm_inactive_score_modal()
    {
        $id = viaIsSet($this->request->params['named']['team_id']);
        $this->_ajaxPreProcess();
        $this->set(compact('id'));
        $response = $this->render('Team/confirm_to_inactive_score_modal');
        $html = $response->__toString();
        return $this->_ajaxGetResponse($html);
    }

    function ajax_get_score_elm()
    {
        $this->_ajaxPreProcess();
        if (viaIsSet($this->request->params['named']['index'])) {
            $this->set(['index' => $this->request->params['named']['index']]);
        }
        $response = $this->render('Team/eval_score_form_elm');
        $html = $response->__toString();
        return $this->_ajaxGetResponse($html);
    }

    function ajax_get_confirm_inactive_goal_category_modal()
    {
        $id = viaIsSet($this->request->params['named']['team_id']);
        $this->_ajaxPreProcess();
        $this->set(compact('id'));
        $response = $this->render('Team/confirm_to_inactive_goal_category_modal');
        $html = $response->__toString();
        return $this->_ajaxGetResponse($html);
    }

    function ajax_get_goal_category_elm()
    {
        $this->_ajaxPreProcess();
        if (viaIsSet($this->request->params['named']['index'])) {
            $this->set(['index' => $this->request->params['named']['index']]);
        }
        $response = $this->render('Team/goal_category_form_elm');
        $html = $response->__toString();
        return $this->_ajaxGetResponse($html);
    }

    function to_inactive_goal_category()
    {
        $id = viaIsSet($this->request->params['named']['team_id']);
        $this->request->allowMethod(['post']);
        $this->Goal->GoalCategory->setToInactive($id);
        $this->Pnotify->outSuccess(__d('gl', "ゴールカテゴリを削除しました。"));
        return $this->redirect($this->referer());
    }

    function ajax_get_term_start_end($start_term_month, $border_months)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->getTermStrStartEndFromParam($start_term_month, $border_months, REQUEST_TIMESTAMP);
        return $this->_ajaxGetResponse($res);
    }

    function ajax_get_term_start_end_by_edit($start_term_month, $border_months, $option)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->EvaluateTerm->getChangeCurrentNextTerm($option, $start_term_month, $border_months);
        if ($res['current']['start_date']) {
            $res['current']['start_date'] = date('Y/m/d',
                                                 $res['current']['start_date'] + $this->Team->me['timezone'] * 3600);
            $res['current']['end_date'] = date('Y/m/d',
                                               $res['current']['end_date'] + $this->Team->me['timezone'] * 3600);
        }
        if ($res['next']['start_date']) {
            $res['next']['start_date'] = date('Y/m/d', $res['next']['start_date'] + $this->Team->me['timezone'] * 3600);
            $res['next']['end_date'] = date('Y/m/d', $res['next']['end_date'] + $this->Team->me['timezone'] * 3600);
        }
        return $this->_ajaxGetResponse($res);
    }

    function start_evaluation()
    {
        $this->request->allowMethod('post');
        try {
            if (!$this->Team->EvaluationSetting->isEnabled()) {
                throw new RuntimeException(__d('gl', "評価設定が有効ではありません。"));
            }
        } catch (RuntimeException $e) {
            $this->Pnotify->outError($e);
            return $this->redirect($this->referer());
        }
        //start evaluation process
        $this->Team->Evaluation->begin();
        if (!$this->Team->Evaluation->startEvaluation()) {
            $this->Team->Evaluation->rollback();
            $this->Pnotify->outError(__d('gl', "評価を開始できませんでした。"));
            return $this->redirect($this->referer());
        }
        $this->Team->Evaluation->commit();
        $this->Pnotify->outSuccess(__d('gl', "評価を開始しました。"));
        $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_EVALUATION_START,
                                         $this->Team->EvaluateTerm->getCurrentTermId());
        return $this->redirect($this->referer());
    }

    public function invite()
    {
        $from_setting = false;
        if (strstr($this->referer(), "/settings")) {
            $from_setting = true;
        }
        $this->set(compact('from_setting'));

        $team_id = $this->Session->read('current_team_id');
        $this->Team->TeamMember->adminCheck($team_id, $this->Auth->user('id'));
        $team = $this->Team->findById($team_id);
        $this->set(compact('team'));

        if (!$this->request->is('post')) {
            $this->layout = LAYOUT_ONE_COLUMN;
            return $this->render();
        }

        $data = $this->request->data;
        //convert mail-address to array
        $email_list = $this->Team->getEmailListFromPost($data);

        //not exists correct email address.
        if (!$email_list) {
            $this->Pnotify->outError(__d('gl', "メールアドレスが正しくありません。"));
            return $this->redirect($this->referer());
        }

        $alreadyBelongTeamEmails = [];
        $sentEmails = [];
        //generate token and send mail one by one.
        foreach ($email_list as $email) {
            //don't process in case of exists in team.
            if ($this->User->Email->isBelongTeamByEmail($email, $team_id)) {
                $alreadyBelongTeamEmails[] = $email;
                continue;
            }
            //save invite mail data
            $invite = $this->Team->Invite->saveInvite(
                $email,
                $team_id,
                $this->Auth->user('id'),
                !empty($data['Team']['comment']) ? $data['Team']['comment'] : null
            );
            //send invite mail
            $team_name = $this->Team->TeamMember->myTeams[$this->Session->read('current_team_id')];
            $this->GlEmail->sendMailInvite($invite, $team_name);
            $sentEmails[] = $email;
        }

        $already_joined_usr_msg = null;
        if (!empty($alreadyBelongTeamEmails)) {
            $already_joined_usr_msg .= __d('gl', "%s人は既にチームに参加しているユーザの為、メール送信をキャンセルしました。",
                                           count($alreadyBelongTeamEmails));
        }

        if (empty($sentEmails)) {
            $this->Pnotify->outError($already_joined_usr_msg);
            return $this->redirect($this->referer());
        }

        $msg = __d('gl', "%s人に招待メールを送信しました。", count($sentEmails)) . "\n" . $already_joined_usr_msg;
        $this->Pnotify->outSuccess($msg);

        if (!$from_setting) {
            return $this->redirect('/');
        }

        return $this->redirect($this->referer());
    }

    function download_add_members_csv_format()
    {
        $team_id = $this->Session->read('current_team_id');
        $this->Team->TeamMember->adminCheck($team_id, $this->Auth->user('id'));

        $this->layout = false;
        $filename = 'add_member_csv_format';
        //heading
        $th = $this->Team->TeamMember->_getCsvHeading(true);
        $td = [];
        $this->set(compact('filename', 'th', 'td'));
    }

    function ajax_upload_update_members_csv()
    {
        $this->request->allowMethod('post');
        $result = [
            'error' => false,
            'css'   => 'alert-success',
            'title' => __d('gl', "正常に更新が完了しました。"),
            'msg'   => '',
        ];
        $this->_ajaxPreProcess('post');
        $csv = $this->Csv->convertCsvToArray($this->request->data['Team']['csv_file']['tmp_name']);
        $this->Team->TeamMember->begin();
        $save_res = $this->Team->TeamMember->updateMembersFromCsv($csv);
        if ($save_res['error']) {
            $this->Team->TeamMember->rollback();
            $result['error'] = true;
            $result['css'] = 'alert-danger';
            $result['msg'] = $save_res['error_msg'];
            if ($save_res['error_line_no'] == 0) {
                $result['title'] = __d('gl', "更新データに誤りがあります。");
            }
            else {
                $result['title'] = __d('gl', "%s行目でエラーがあります(行番号は見出し含む)。", $save_res['error_line_no']);
            }
        }
        else {
            $this->Team->TeamMember->commit();
            $result['msg'] = __d('gl', "%s人のメンバーを更新しました。", $save_res['success_count']);
        }
        return $this->_ajaxGetResponse($result);
    }

    function ajax_upload_new_members_csv()
    {
        $this->request->allowMethod('post');
        $result = [
            'error' => false,
            'css'   => 'alert-success',
            'title' => __d('gl', "正常に登録が完了しました。"),
            'msg'   => '',
        ];
        $this->_ajaxPreProcess('post');
        $csv = $this->Csv->convertCsvToArray($this->request->data['Team']['csv_file']['tmp_name']);
        $this->Team->TeamMember->begin();
        $save_res = $this->Team->TeamMember->saveNewMembersFromCsv($csv);
        if ($save_res['error']) {
            $this->Team->TeamMember->rollback();
            $result['error'] = true;
            $result['css'] = 'alert-danger';
            $result['msg'] = $save_res['error_msg'];
            if ($save_res['error_line_no'] == 0) {
                $result['title'] = __d('gl', "エラーがあります。");
            }
            else {
                $result['title'] = __d('gl', "%s行目でエラーがあります(行番号は見出し含む)。", $save_res['error_line_no']);
            }
        }
        else {
            $this->Team->TeamMember->commit();
            $team = $this->Team->findById($this->Session->read('current_team_id'));
            //send invite mail
            foreach ($this->Team->TeamMember->csv_datas as $data) {
                //save invite mail data
                $invite = $this->Team->Invite->saveInvite(
                    $data['Email']['email'],
                    $this->Team->current_team_id,
                    $this->Auth->user('id'),
                    null
                );
                //send invite mail
                $this->GlEmail->sendMailInvite($invite, $team['Team']['name']);
            }

            $result['msg'] = __d('gl', "%s人のメンバーを追加しました。", $save_res['success_count']);
        }
        return $this->_ajaxGetResponse($result);
    }

    function download_team_members_csv()
    {
        $team_id = $this->Session->read('current_team_id');
        $this->Team->TeamMember->adminCheck($team_id, $this->Auth->user('id'));
        $this->layout = false;
        $filename = 'team_members_' . date('YmdHis');

        //見出し
        $th = $this->Team->TeamMember->_getCsvHeading(false);
        $td = $this->Team->TeamMember->getAllMembersCsvData();

        $this->set(compact('filename', 'th', 'td'));
    }

    function ajax_upload_final_evaluations_csv()
    {
        $evaluate_term_id = viaIsSet($this->request->params['named']['evaluate_term_id']);
        $this->request->allowMethod('post');
        $result = [
            'error' => false,
            'css'   => 'alert-success',
            'title' => __d('gl', "正常に最終評価が完了しました。"),
            'msg'   => '',
        ];
        $this->_ajaxPreProcess('post');
        $csv = $this->Csv->convertCsvToArray($this->request->data['Team']['csv_file']['tmp_name']);
        $this->Team->TeamMember->begin();
        $save_res = $this->Team->TeamMember->updateFinalEvaluationFromCsv($csv, $evaluate_term_id);
        if ($save_res['error']) {
            $this->Team->TeamMember->rollback();
            $result['error'] = true;
            $result['css'] = 'alert-danger';
            $result['msg'] = $save_res['error_msg'];
            if ($save_res['error_line_no'] == 0) {
                $result['title'] = __d('gl', "更新データに誤りがあります。");
            }
            else {
                $result['title'] = __d('gl', "%s行目でエラーがあります(行番号は見出し含む)。", $save_res['error_line_no']);
            }
        }
        else {
            $this->Team->TeamMember->commit();
            $result['msg'] = __d('gl', "%s人の最終評価を更新しました。", $save_res['success_count']);
            $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_EVALUATION_DONE_FINAL,
                                             $this->Team->EvaluateTerm->getCurrentTermId());
        }
        return $this->_ajaxGetResponse($result);
    }

    function download_final_evaluations_csv()
    {
        $evaluate_term_id = viaIsSet($this->request->params['named']['evaluate_term_id']);
        $team_id = $this->Session->read('current_team_id');
        $this->Team->TeamMember->adminCheck($team_id, $this->Auth->user('id'));
        $this->layout = false;
        $filename = 'final_evaluations_' . date('YmdHis');

        //見出し
        $th = $this->Team->TeamMember->_getCsvHeadingEvaluation();
        $td = $this->Team->TeamMember->getAllEvaluationsCsvData($evaluate_term_id, $team_id);

        $this->set(compact('filename', 'th', 'td'));
    }

    public function ajax_switch_team()
    {
        $team_id = viaIsSet($this->request->params['named']['team_id']);
        $this->layout = 'ajax';
        Configure::write('debug', 0);
        $redirect_url = Router::url("/", true);
        $this->set(compact("redirect_url"));
        if (!$team_id || !$this->request->is('ajax')) {
            $this->Pnotify->outError(__d('gl', "不正なアクセスです"));
            return $this->render();
        }
        //チーム所属チェック
        $my_teams = $this->Team->TeamMember->getActiveTeamList($this->Auth->user('id'));
        if (!array_key_exists($team_id, $my_teams)) {
            $this->Pnotify->outError(__d('gl', "このチームには所属していません"));
            return $this->render();
        }
        $this->_switchTeam($team_id, $this->Auth->user('id'));
        $this->Pnotify->outSuccess(__d('gl', "チームを「%s」に切り換えました。", $my_teams[$team_id]));
        return $this->render();
    }

    function change_freeze_status()
    {
        $termId = $this->request->params['named']['evaluate_term_id'];
        $this->request->allowMethod('post');
        try {
            $res = $this->Team->EvaluateTerm->changeFreezeStatus($termId);
        } catch (RuntimeException $e) {
            $this->Pnotify->outError($e);
            return $this->redirect($this->referer());
        }
        if ($res['EvaluateTerm']['evaluate_status'] == EvaluateTerm::STATUS_EVAL_FROZEN) {
            $this->Pnotify->outSuccess(__d('gl', "評価を凍結しました。"));
            $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_EVALUATION_FREEZE,
                                             $this->Team->EvaluateTerm->getCurrentTermId());
        }
        else {
            $this->Pnotify->outSuccess(__d('gl', "評価の凍結を解除しました。"));
        }
        return $this->redirect($this->referer());
    }

    function main()
    {
        $this->layout = LAYOUT_ONE_COLUMN;
        $current_global_menu = "team";
        // グルーブの絞り込みが選択された場合
        $this->set(compact('current_global_menu'));
        return $this->render();
    }

    /**
     * グループビジョン一覧取得
     * @param     $team_id
     * @param int $active_flg
     * @return CakeResponse
     */
    function ajax_get_group_vision($team_id, $active_flg = 1)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->GroupVision->getGroupVision($team_id, $active_flg);
        $group_vision_list = $this->Team->GroupVision->convertData($team_id, $res);
        return $this->_ajaxGetResponse($group_vision_list);
    }

    /**
     * グループビジョンアーカイブ設定
     * @param     $group_vision_id
     * @param int $active_flg
     * @return CakeResponse
     */
    function ajax_set_group_vision_archive($group_vision_id, $active_flg = 1)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->GroupVision->setGroupVisionActiveFlag($group_vision_id, $active_flg);
        return $this->_ajaxGetResponse($res);
    }

    /**
     * 所属のグループ情報を取得
     * @param $team_id
     * @param $user_id
     * @return CakeResponse
     */
    function ajax_get_login_user_group_id ($team_id, $user_id)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->Group->MemberGroup->getMyGroupList($team_id, $user_id);
        return $this->_ajaxGetResponse($res);
    }

    /**
     * グループビジョンの削除
     * @param $group_vision_id
     * @return CakeResponse
     */
    function ajax_delete_group_vision($group_vision_id)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->GroupVision->deleteGroupVision($group_vision_id);
        return $this->_ajaxGetResponse($res);
    }

    /**
     * チームビジョンの詳細を取得
     * @param $team_vision_id
     * @param $active_flg
     * @return CakeResponse
     */
    function ajax_get_team_vision_detail($team_vision_id, $active_flg)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->TeamVision->getTeamVisionDetail($team_vision_id, $active_flg);
        $team_vision_detail = $this->Team->TeamVision->convertData($res);
        return $this->_ajaxGetResponse($team_vision_detail);
    }

    /**
     * グループビジョンの詳細を取得
     * @param $group_vision_id
     * @param $active_flg
     * @return CakeResponse
     */
    function ajax_get_group_vision_detail($group_vision_id, $active_flg)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->GroupVision->getGroupVisionDetail($group_vision_id, $active_flg);
        $team_id = $this->Session->read('current_team_id');
        $group_vision_detail = $this->Team->GroupVision->convertData($team_id, $res);
        return $this->_ajaxGetResponse($group_vision_detail);
    }

    function ajax_team_admin_user_check()
    {
        $this->_ajaxPreProcess();
        $is_admin_user = true;
        $team_id = $this->Session->read('current_team_id');
        $user_id = $this->Auth->user('id');
        try {
            $this->Team->TeamMember->adminCheck($team_id, $user_id);
        } catch (RuntimeException $e) {
            $is_admin_user = false;
        }
        return $this->_ajaxGetResponse(['is_admin_user' => $is_admin_user]);
    }

    function ajax_delete_team_vision($team_vision_id)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->TeamVision->deleteTeamVision($team_vision_id);
        return $this->_ajaxGetResponse($res);
    }

    function ajax_get_team_vision($team_id, $active_flg = 1)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->TeamVision->getTeamVision($team_id, $active_flg);
        $team_vision_list = $this->Team->TeamVision->convertData($res);
        return $this->_ajaxGetResponse($team_vision_list);
    }

    function ajax_set_team_vision_archive($team_archive_id, $active_flg = 1)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->TeamVision->setTeamVisionActiveFlag($team_archive_id, $active_flg);
        return $this->_ajaxGetResponse($res);
    }

    function ajax_get_team_member_init()
    {
        $this->_ajaxPreProcess();
        // ログインユーザーは管理者なのか current_team_idのadmin_flgがtrueを検索
        $current_team_id = $this->Session->read('current_team_id');
        $login_user_id = $this->Auth->user('id');
        $login_user_admin_flg = $this->Team->TeamMember->getLoginUserAdminFlag($current_team_id, $login_user_id);
        $admin_user_cnt = $this->Team->TeamMember->getAdminUserCount($current_team_id);

        $res = [
            'current_team_id'      => $current_team_id,
            'admin_user_cnt'       => $admin_user_cnt,
            'login_user_id'        => $login_user_id,
            'login_user_admin_flg' => $login_user_admin_flg,
            'login_user_language'  => $this->Session->read('Auth.User.language'),
        ];
        return $this->_ajaxGetResponse($res);
    }

    function ajax_get_team_member()
    {
        $this->_ajaxPreProcess();
        $team_id = $this->Session->read('current_team_id');
        $user_info = $this->Team->TeamMember->selectMemberInfo($team_id);
        $res = [
            'user_info' => $user_info,
        ];
        return $this->_ajaxGetResponse($res);
    }

    function ajax_get_group_member($group_id = '')
    {
        $this->_ajaxPreProcess();
        $team_id = $this->Session->read('current_team_id');
        $user_info = $this->Team->TeamMember->selectGroupMemberInfo($team_id, $group_id);
        $res = [
            'user_info' => $user_info,
        ];
        return $this->_ajaxGetResponse($res);
    }

    function ajax_get_current_team_group_list()
    {
        $this->_ajaxPreProcess();
        $team_id = $this->Session->read('current_team_id');
        // グループ名を取得
        $group_info = $this->Team->Group->getByAllName($team_id);
        return $this->_ajaxGetResponse($group_info);
    }

    function ajax_get_current_team_admin_list()
    {
        $this->_ajaxPreProcess();
        $team_id = $this->Session->read('current_team_id');
        $user_info = $this->Team->TeamMember->selectAdminMemberInfo($team_id);
        $res = [
            'user_info' => $user_info,
        ];
        return $this->_ajaxGetResponse($res);
    }

    function ajax_get_current_not_2fa_step_user_list()
    {
        $this->_ajaxPreProcess();
        $team_id = $this->Session->read('current_team_id');
        $user_info = $this->Team->TeamMember->select2faStepMemberInfo($team_id);
        $res = [
            'user_info' => $user_info,
        ];
        return $this->_ajaxGetResponse($res);
    }

    function ajax_set_current_team_active_flag($member_id, $active_flg)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->TeamMember->setActiveFlag($member_id, $active_flg);
        return $this->_ajaxGetResponse($res);
    }

    function ajax_set_current_team_admin_user_flag($member_id, $active_flg)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->TeamMember->setAdminUserFlag($member_id, $active_flg);
        return $this->_ajaxGetResponse($res);
    }

    function ajax_set_current_team_evaluation_flag($member_id, $evaluation_flg)
    {
        $this->_ajaxPreProcess();
        $res = $this->Team->TeamMember->setEvaluationFlag($member_id, $evaluation_flg);
        return $this->_ajaxGetResponse($res);
    }

    function ajax_get_invite_member_list()
    {
        $this->_ajaxPreProcess();
        $team_id = $this->Session->read('current_team_id');
        $invite_member_list = $this->Team->Invite->getInviteUserList($team_id);
        $res = [
            'user_info' => $invite_member_list,
        ];
        return $this->_ajaxGetResponse($res);
    }

    function add_team_vision()
    {
        $this->layout = LAYOUT_ONE_COLUMN;
        try {
            $this->Team->TeamMember->adminCheck();
        } catch (RuntimeException $e) {
            $this->Pnotify->outError($e->getMessage());
            return $this->redirect($this->referer());
        }

        if ($this->request->is('get')) {
            return $this->render();
        }
        if (!viaIsSet($this->request->data['TeamVision'])) {
            $this->Pnotify->outError(__d('gl', "チームビジョンの保存に失敗しました。"));
            return $this->redirect($this->referer());
        }

        if ($this->Team->TeamVision->saveTeamVision($this->request->data)) {
            $this->Pnotify->outSuccess(__d('gl', "チームビジョンを追加しました。"));
            //TODO 遷移先はビジョン一覧ページ。未実装の為、仮でホームに遷移させている。
            return $this->redirect("/");
        }
        return $this->render();
    }

    function edit_team_vision()
    {
        $this->layout = LAYOUT_ONE_COLUMN;
        try {
            $this->Team->TeamMember->adminCheck();
        } catch (RuntimeException $e) {
            $this->Pnotify->outError($e->getMessage());
            return $this->redirect($this->referer());
        }

        if (!$team_vision_id = viaIsSet($this->request->params['named']['team_vision_id'])) {
            $this->Pnotify->outError(__d('gl', "不正な画面遷移です。"));
            return $this->redirect($this->referer());
        }
        if (!$this->Team->TeamVision->exists($team_vision_id)) {
            $this->Pnotify->outError(__d('gl', "ページが存在しません。"));
            return $this->redirect($this->referer());
        }

        if ($this->request->is('get')) {
            $this->request->data = $this->Team->TeamVision->findById($team_vision_id);
            return $this->render();
        }
        if (!viaIsSet($this->request->data['TeamVision'])) {
            $this->Pnotify->outError(__d('gl', "チームビジョンの保存に失敗しました。"));
            return $this->redirect($this->referer());
        }

        if ($this->Team->TeamVision->saveTeamVision($this->request->data, false)) {
            $this->Pnotify->outSuccess(__d('gl', "チームビジョンを更新しました。"));
            //TODO 遷移先はビジョン一覧ページ。未実装の為、仮でホームに遷移させている。
            return $this->redirect("/");
        }
        return $this->render();
    }

    function add_group_vision()
    {
        $this->layout = LAYOUT_ONE_COLUMN;
        $group_list = $this->Team->Group->MemberGroup->getMyGroupList();
        if (empty($group_list)) {
            $this->Pnotify->outError(__d('gl', "グループに所属していない為、グループビジョンは作成できません。"));
            return $this->redirect($this->referer());
        }
        $this->set(compact('group_list'));

        if ($this->request->is('get')) {
            return $this->render();
        }
        if (!viaIsSet($this->request->data['GroupVision'])) {
            $this->Pnotify->outError(__d('gl', "グループビジョンの保存に失敗しました。"));
            return $this->redirect($this->referer());
        }

        if ($this->Team->GroupVision->saveGroupVision($this->request->data)) {
            $this->Pnotify->outSuccess(__d('gl', "グループビジョンを追加しました。"));
            //TODO 遷移先はビジョン一覧ページ。未実装の為、仮でホームに遷移させている。
            return $this->redirect("/");
        }
        return $this->render();
    }

    function edit_group_vision()
    {
        $this->layout = LAYOUT_ONE_COLUMN;

        if (!$group_vision_id = viaIsSet($this->request->params['named']['group_vision_id'])) {
            $this->Pnotify->outError(__d('gl', "不正な画面遷移です。"));
            return $this->redirect($this->referer());
        }
        if (!$this->Team->GroupVision->exists($group_vision_id)) {
            $this->Pnotify->outError(__d('gl', "ページが存在しません。"));
            return $this->redirect($this->referer());
        }
        $group_list = $this->Team->Group->MemberGroup->getMyGroupList();
        $this->set(compact('group_list'));

        if ($this->request->is('get')) {
            $this->request->data = $this->Team->GroupVision->findById($group_vision_id);
            return $this->render();
        }
        if (!viaIsSet($this->request->data['GroupVision'])) {
            $this->Pnotify->outError(__d('gl', "グループビジョンの保存に失敗しました。"));
            return $this->redirect($this->referer());
        }

        if ($this->Team->GroupVision->saveGroupVision($this->request->data, false)) {
            $this->Pnotify->outSuccess(__d('gl', "グループビジョンを更新しました。"));
            //TODO 遷移先はビジョン一覧ページ。未実装の為、仮でホームに遷移させている。
            return $this->redirect("/");
        }
        return $this->render();
    }
}
