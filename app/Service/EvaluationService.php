<?php
App::import('Service', 'AppService');
App::uses('Evaluation', 'Model');

class EvaluationService extends AppService
{
    /**
     * 認定リストのステータスをフォーマット
     *
     * @param $termId
     * @param $userId
     *
     * @return $res
     */
    function getEvalStatus($termId, $userId)
    {
        /** @var  Evaluation $Evaluation */
        $Evaluation = ClassRegistry::init('Evaluation');

        $evaluations = $Evaluation->getEvaluationListForIndex($termId, $userId);

        $evaluations = Hash::combine($evaluations, '{n}.id', '{n}');
        $flow = [];
        $evaluator_index = 1;
        $status_text = ['your_turn' => false, 'body' => null];
        //update flow
        foreach ($evaluations as $val) {
            $name = Evaluation::$TYPE[$val['evaluate_type']]['index'];
            $otherEvaluator = false;
            if ($val['evaluate_type'] == Evaluation::TYPE_EVALUATOR) {
                $user_name = $val['evaluator_user']['display_username'];
                if ($val['evaluator_user_id'] == $Evaluation->my_uid) {
                    $name = __("You");
                } else {
                    $name = "${evaluator_index}(${user_name})";
                    $otherEvaluator = true;
                }
                $evaluator_index++;
            } //自己評価で被評価者が自分以外の場合は「メンバー」
            elseif ($val['evaluate_type'] == Evaluation::TYPE_ONESELF && $val['evaluatee_user_id'] != $Evaluation->my_uid) {
                $name = __('Members');
            }
            $flow[] = [
                'name'            => $name,
                'status'          => $val['status'],
                'this_turn'       => $val['my_turn_flg'],
                'other_evaluator' => $otherEvaluator
            ];
            //update status_text
            if ($val['my_turn_flg'] === false) {
                continue;
            }
            if ($val['evaluator_user_id'] != $Evaluation->my_uid) {
                $status_text['body'] = __("Waiting for the evaluation by %s.", $name);
                continue;
            }
            //your turn
            $status_text['your_turn'] = true;
            switch ($val['evaluate_type']) {
                case Evaluation::TYPE_ONESELF:
                    $status_text['body'] = __("Please evaluate yourself.");
                    break;
                case Evaluation::TYPE_EVALUATOR:
                    $status_text['body'] = __("Please evaluate.");
                    break;
            }
        }
        if (empty($flow)) {
            return [];
        }

        /** @var  User $User */
        $User = ClassRegistry::init('User');
        $user = $User->getProfileAndEmail($userId);
        $res = array_merge(['flow' => $flow, 'status_text' => $status_text], $user);
        return $res;
    }

    function getEvaluateeEvalStatusAsEvaluator($termId)
    {
        /** @var  Evaluation $Evaluation */
        $Evaluation = ClassRegistry::init('Evaluation');
        /** @var  User $User */
        $User = ClassRegistry::init('User');

        $evaluateeList = $Evaluation->getEvaluateeListEvaluableAsEvaluator($termId);
        $evaluatees = [];
        foreach ($evaluateeList as $uid) {
            $user = $User->getProfileAndEmail($uid);
            $evaluation = $this->getEvalStatus($termId, $uid);
            $evaluatees[] = array_merge($user, $evaluation);
        }
        return $evaluatees;
    }
}
