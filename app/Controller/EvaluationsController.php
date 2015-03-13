<?php
App::uses('AppController', 'Controller');

/**
 * Evaluations Controller
 *
 * @property Evaluation $Evaluation
 */
class EvaluationsController extends AppController
{

    function index()
    {
        $this->layout = LAYOUT_ONE_COLUMN;

        try {
            $this->Evaluation->checkAvailViewEvaluationList();
            if (!$this->Team->EvaluationSetting->isEnabled()) {
                throw new RuntimeException(__d('gl', "チームの評価設定が有効になっておりません。チーム管理者にお問い合わせください。"));
            }
        } catch (RuntimeException $e) {
            $this->Pnotify->outError($e->getMessage());
            return $this->redirect($this->referer());
        }

        //get evaluation setting.
        $is_self_on = $this->Team->EvaluationSetting->isEnabledSelf();
        $is_evaluator_on = $this->Team->EvaluationSetting->isEnabledEvaluator();
        $is_final_on = $this->Team->EvaluationSetting->isEnabledFinal();
        $my_evaluations = $this->Evaluation->getMyEvaluation();
        $this->set(compact('is_self_on', 'is_evaluator_on', 'is_final_on', 'my_evaluations'));
    }

    function view($evaluateTermId=null, $evaluateeId=null)
    {
        if(!$evaluateTermId || !$evaluateeId) {
            $this->Pnotify->outError(__d('gl', "パラメータが不正です。"));
            return $this->redirect($this->referer());
        }

        $this->layout = LAYOUT_ONE_COLUMN;
        $teamId = $this->Session->read('current_team_id');
        $scoreList = $this->Evaluation->EvaluateScore->getScoreList($teamId);
        $evaluationList = $this->Evaluation->getNotEnteredEvaluations($evaluateTermId, $evaluateeId);
        $this->set(compact('scoreList', 'evaluationList'));
    }

    function add()
    {
        // case of saving draft
        if(isset($this->request->data['is_draft'])) {
            $saveType = "draft";
            unset($this->request->data['is_draft']);
            $successMsg = __d('gl', "下書きを保存しました。");
            $errorMsg   = __d('gl', "下書きの保存に失敗しました。");

        // case of registering
        } else {
            $saveType = "register";
            unset($this->request->data['is_register']);
            $successMsg = __d('gl', "自己評価を登録しました。");
            $errorMsg   = __d('gl', "自己評価の登録に失敗しました。");
        }

        $saveEvaluation = $this->Evaluation->add($this->request->data, $saveType);
        if ($saveEvaluation) {
            $this->Pnotify->outSuccess($successMsg);
        } else {
            $this->Pnotify->outError($errorMsg);
        }
        $this->redirect('index');

    }

}
