<?php
App::uses('AppController', 'Controller');

/**
 * Goals Controller
 *
 * @property Goal $Goal
 */
class GoalsController extends AppController
{

    public function beforeFilter()
    {
        parent::beforeFilter();
    }

    /**
     * ゴール一覧画面
     */
    public function index()
    {
        $this->_setMyCircle();
        $goals = $this->Goal->getGoals(3);
        $my_goals = $this->Goal->getGoals();
        $this->set(compact('goals', 'my_goals'));
    }

    /**
     * ゴール作成
     * URLパラメータでmodeを付ける
     * mode なしは目標を決める,2はゴールを定める,3は他の情報を追加
     */
    public function add($id = null)
    {
        $this->layout = LAYOUT_ONE_COLUMN;
        //編集権限を確認。もし権限がある場合はデータをセット
        if ($id) {
            $this->request->data['Goal']['id'] = $id;
            try {
                $this->Goal->isPermitted($id);

            } catch (RuntimeException $e) {
                $this->Pnotify->outError($e->getMessage());
                $this->redirect($this->referer());
            }
        }

        if (($this->request->is('post') || $this->request->is('put')) && !empty($this->request->data)) {
            if ($this->Goal->add($this->request->data)) {
                if (isset($this->request->params['named']['mode'])) {
                    switch ($this->request->params['named']['mode']) {
                        case 2:
                            $this->Pnotify->outSuccess(__d('gl', "ゴールを保存しました。"));
                            //「ゴールを定める」に進む
                            $this->redirect([$id, 'mode' => 3, '#' => 'AddGoalFormOtherWrap']);
                            break;
                        case 3:
                            //完了
                            $this->Pnotify->outSuccess(__d('gl', "ゴールの作成が完了しました。"));
                            //TODO 一旦、トップにリダイレクト
                            $this->redirect("/");
                            break;
                    }
                }
                else {
                    $this->Pnotify->outSuccess(__d('gl', "ゴールを目的を保存しました。"));
                    //「ゴールを定める」に進む
                    $this->redirect([$this->Goal->id, 'mode' => 2, '#' => 'AddGoalFormKeyResultWrap']);
                }
            }
            else {
                $this->Pnotify->outError(__d('gl', "ゴールの保存に失敗しました。"));
                $this->redirect($this->referer());
            }
        }
        else {
            //新規作成時以外はデータをセット
            if ($id) {
                $this->request->data = $this->Goal->getAddData($id);
            }

        }
        $goal_category_list = $this->Goal->GoalCategory->getCategoryList();
        $priority_list = $this->Goal->priority_list;
        $kr_priority_list = $this->Goal->KeyResult->priority_list;
        $kr_value_unit_list = KeyResult::$UNIT;
        if (isset($this->request->data['KeyResult'][0]) && !empty($this->request->data['KeyResult'][0])) {
            $kr_start_date_format = date('Y/m/d',
                                         $this->request->data['KeyResult'][0]['start_date'] + ($this->Auth->user('timezone') * 60 * 60));
            $kr_end_date_format = date('Y/m/d',
                                       $this->request->data['KeyResult'][0]['end_date'] + ($this->Auth->user('timezone') * 60 * 60));
        }
        else {
            $kr_start_date_format = date('Y/m/d', time() + ($this->Auth->user('timezone') * 60 * 60));
            //TODO 将来的には期間をまたぐ当日+6ヶ月を期限にするが、現状期間末日にする
            //$kr_end_date_format = date('Y/m/d', $this->getEndMonthLocalDateTime());
            $kr_end_date_format = date('Y/m/d', strtotime("- 1 day", $this->Goal->Team->getTermEndDate()));
        }
        $this->set(compact('goal_category_list', 'priority_list', 'kr_priority_list', 'kr_value_unit_list',
                           'kr_start_date_format', 'kr_end_date_format'));
    }

    /**
     * delete method
     *
     * @param string $id
     *
     * @return void
     */
    public function delete($id)
    {
        try {
            $this->Goal->isPermitted($id);
        } catch (RuntimeException $e) {
            $this->Pnotify->outError($e->getMessage());
            $this->redirect($this->referer());
        }
        $this->request->allowMethod('post', 'delete');
        $this->Goal->id = $id;
        $this->Goal->delete();
        $this->Pnotify->outSuccess(__d('gl', "ゴールを削除しました。"));
        /** @noinspection PhpInconsistentReturnPointsInspection */
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return $this->redirect($this->referer());
    }
}
