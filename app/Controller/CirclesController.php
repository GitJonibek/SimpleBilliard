<?php
App::uses('AppController', 'Controller');

/**
 * Circles Controller
 *
 * @property Circle $Circle
 */
class CirclesController extends AppController
{
    /**
     * beforeFilter callback
     *
     * @return void
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
    }

    /**
     * add method
     *
     * @return void
     */
    public function add()
    {
        $this->request->allowMethod('post');
        $this->Circle->create();

        if ($this->Circle->add($this->request->data)) {
            if (!empty($this->Circle->add_new_member_list)) {
                $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_CIRCLE_ADD_USER, $this->Circle->id,
                                                 null, $this->Circle->add_new_member_list);
            }
            $this->Pnotify->outSuccess(__d('gl', "サークルを作成しました。"));
        }
        else {
            $this->Pnotify->outError(__d('gl', "サークルの作成に失敗しました。"));
        }
        /** @noinspection PhpInconsistentReturnPointsInspection */
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return $this->redirect($this->referer());
    }

    public function ajax_get_edit_modal()
    {
        $circle_id = $this->request->params['named']['circle_id'];
        $this->_ajaxPreProcess();
        $this->request->data = $this->Circle->getEditData($circle_id);
        //htmlレンダリング結果
        $response = $this->render('modal_edit_circle');
        $html = $response->__toString();

        return $this->_ajaxGetResponse($html);
    }

    function ajax_select2_init_circle_members($circle_id)
    {
        $this->_ajaxPreProcess();
        $res = $this->Circle->CircleMember->getCircleInitMemberSelect2($circle_id);
        return $this->_ajaxGetResponse($res);
    }

    public function edit()
    {
        $this->Circle->id = $this->request->params['named']['circle_id'];
        try {
            if (!$this->Circle->exists()) {
                throw new RuntimeException(__d('gl', "このサークルは存在しません。"));
            }
            if (!$this->Circle->CircleMember->isAdmin($this->Auth->user('id'), $this->Circle->id)) {
                throw new RuntimeException(__d('gl', "サークルの変更ができるのはサークル管理者のみです。"));
            }
        } catch (RuntimeException $e) {
            $this->Pnotify->outError($e->getMessage());
            $this->redirect($this->referer());
            return;
        }
        $this->request->allowMethod('put');
        $before_circle = $this->Circle->read();
        //プライバシー設定が変更されているか判定
        $is_privacy_changed = false;
        if (isset($before_circle['Circle']['public_flg']) &&
            isset($this->request->data['Circle']['public_flg']) &&
            $before_circle['Circle']['public_flg'] != $this->request->data['Circle']['public_flg']
        ) {
            $is_privacy_changed = true;
        }
        // team_all_flg は変更不可
        $this->request->data['Circle']['team_all_flg'] = $before_circle['Circle']['team_all_flg'];

        if ($this->Circle->edit($this->request->data)) {
            if (!empty($this->Circle->add_new_member_list)) {
                $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_CIRCLE_ADD_USER, $this->Circle->id,
                                                 null, $this->Circle->add_new_member_list);
            }
            if ($is_privacy_changed) {
                $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_CIRCLE_CHANGED_PRIVACY_SETTING,
                                                 $this->Circle->id);
            }
            $this->Pnotify->outSuccess(__d('gl', "サークル設定を保存しました。"));
        }
        else {
            $this->Pnotify->outError(__d('gl', "サークル設定の保存に失敗しました。"));
        }
        $this->redirect($this->referer());
    }

    public function delete()
    {
        $this->Circle->id = $this->request->params['named']['circle_id'];
        try {
            if (!$this->Circle->exists()) {
                throw new RuntimeException(__d('gl', "このサークルは存在しません。"));
            }
            if (!$this->Circle->CircleMember->isAdmin($this->Auth->user('id'), $this->Circle->id)) {
                throw new RuntimeException(__d('gl', "サークルの削除ができるのはサークル管理者のみです。"));
            }
            $teamAllCircle = $this->Circle->getTeamAllCircle();
            if (isset($teamAllCircle["Circle"]["id"]) &&
                $teamAllCircle["Circle"]["id"] == $this->Circle->id
            ) {
                throw new RuntimeException(__d('gl', "チーム全体サークルは削除できません。"));
            }
        } catch (RuntimeException $e) {
            $this->Pnotify->outError($e->getMessage());
            $this->redirect($this->referer());
        }
        $this->request->allowMethod('post');
        $this->Circle->delete();
        $this->Pnotify->outSuccess(__d('gl', "サークルを削除しました。"));
        $this->redirect($this->referer());
    }

    public function ajax_get_public_circles_modal()
    {
        $this->_ajaxPreProcess();
        $joined_circles = array_merge(
            $this->Circle->getPublicCircles('joined', strtotime("-1 week"), null, 'Circle.created desc'),
            $this->Circle->getPublicCircles('joined', null, strtotime("-1 week"), 'Circle.modified desc')
        );
        $non_joined_circles = array_merge(
            $this->Circle->getPublicCircles('non-joined', strtotime("-1 week"), null, 'Circle.created desc'),
            $this->Circle->getPublicCircles('non-joined', null, strtotime("-1 week"), 'Circle.modified desc')
        );
        // チーム全体サークルを先頭に移動する
        foreach ($joined_circles as $k => $circle) {
            if ($circle['Circle']['team_all_flg']) {
                $team_all_circle = array_splice($joined_circles, $k, 1);
                array_unshift($joined_circles, $team_all_circle[0]);
                break;
            }
        }
        $this->set(compact('joined_circles', 'non_joined_circles'));
        //エレメントの出力を変数に格納する
        //htmlレンダリング結果
        $response = $this->render('modal_public_circles');
        $html = $response->__toString();

        return $this->_ajaxGetResponse($html);
    }

    public function join()
    {
        $this->request->allowMethod('post');
        if ($this->Circle->CircleMember->joinCircle($this->request->data)) {
            if (!empty($this->Circle->CircleMember->new_joined_circle_list)) {
                foreach ($this->Circle->CircleMember->new_joined_circle_list as $circle_id) {
                    $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_CIRCLE_USER_JOIN, $circle_id);
                }
            }
            $this->Pnotify->outSuccess(__d('gl', "公開サークルの参加設定を保存しました。"));
        }
        else {
            $this->Pnotify->outSuccess(__d('gl', "公開サークルの参加設定の保存に失敗しました。"));
        }
        $this->redirect($this->referer());
    }

    public function ajax_get_circle_members()
    {
        $circle_id = $this->request->params['named']['circle_id'];
        $this->_ajaxPreProcess();
        $circle_members = $this->Circle->CircleMember->getMembers($circle_id, true, 'CircleMember.modified', 'desc');
        $this->set(compact('circle_members'));

        //エレメントの出力を変数に格納する
        //htmlレンダリング結果
        $response = $this->render('modal_circle_members');
        $html = $response->__toString();

        return $this->_ajaxGetResponse($html);
    }

}
