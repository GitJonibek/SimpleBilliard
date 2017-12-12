<?php
App::uses('ApiController', 'Controller/Api');
App::import('Service', 'KeyResultService');

/**
 * Created by PhpStorm.
 * User: daikihirakata
 * Date: 9/6/16
 * Time: 16:38
 *
 * @property KeyResult $KeyResult
 */
class KeyResultsController extends ApiController
{
    public $uses = [
        'KeyResult'
    ];

    /**
     * KRのバリデーションAPI
     * 成功(Status Code:200)、失敗(Status Code:400)
     *
     * @return CakeResponse
     */
    function post_validate()
    {
        $validation = $this->KeyResult->validateKrPOST($this->request->data);
        if ($validation === true) {
            return $this->_getResponseSuccess();
        }
        // TODO: _validationExtractがService基底クラスに移行されたらここの呼び出し元も変える
        $validationMsg = $this->KeyResult->_validationExtract($validation);
        return $this->_getResponseValidationFail($validationMsg);
    }

    /**
     * 更新
     *
     * @param int $krId
     *
     * @return \Cake\Network\Response|null
     */
    public function put(int $krId)
    {
        /** @var KeyResultService $KeyResultService */
        $KeyResultService = ClassRegistry::init("KeyResultService");
        /** @var GoalMemberService $GoalMemberService */
        $GoalMemberService = ClassRegistry::init("GoalMemberService");

        $requestData = Hash::get($this->request->data, 'KeyResult');
        // バリデーション
        $err = $KeyResultService->validateUpdate($this->my_uid, $krId, $requestData);
        if (!empty($err)) {
            // TODO:失敗全般用のレスポンス作成メソッド追加検討
            return $this->_getResponse(Hash::get($err, 'status_code'), null, null
                , Hash::get($err, 'message'), Hash::get($err, 'validation_errors'));
        }
        // KR更新
        if (!$KeyResultService->update($this->my_uid, $krId, $requestData)) {
            return $this->_getResponseInternalServerError();
        }

        // TKRかつ紐づくゴールが認定対象の場合、コーチへ通知する(ゴール・TKR編集時の通知と同じ)
        $kr = $KeyResultService->get($krId);
        $goalId = Hash::get($kr, 'goal_id');
        if (Hash::get($kr, 'tkr_flg') && $GoalMemberService->isApprovableByGoalId($goalId, $this->my_uid)) {
            $this->_sendNotifyToCoach($goalId, NotifySetting::TYPE_COACHEE_CHANGE_GOAL);
        }

        // メンバーへの通知
        $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_MEMBER_CHANGE_KR, $krId, null);

        return $this->_getResponseSuccess();
    }
}
