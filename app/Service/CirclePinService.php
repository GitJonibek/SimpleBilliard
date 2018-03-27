<?php
App::import('Service', 'AppService');
App::import('Service', 'UserService');
App::uses('Circle', 'Model');
App::uses('CirclePin', 'Model');
App::uses('CircleMember', 'Model');
App::uses('CirclesController', 'Controller');
App::uses('AppUtil', 'Util');

/**
 * Class CirclePinService
 */
class CirclePinService extends AppService
{
    /**
     * Circle Pins Order Validation
     *
     * @param  string $pinOrder
     *
     * @return true|CakeResponse
     */
    function validateApprovalPinOrder($pinOrder)
    {
        /** @var CirclePin $CirclePin */
        $CirclePin = ClassRegistry::init("CirclePin");

        $validation = $CirclePin->validates();

        if($validation !== true){
            $validation['circle_orders'] = $this->_validationExtract($CirclePin->validationErrors);
        }

        if(isset($validation)){
            return $validation;
        }

        return true;
    }

    /**
     * 自分が所属しているサークルのソート順をDBへセットする
     * @return bool
     */
    public function setCircleOrders(int $userId, int $teamId, string $orders): bool
    {
        try{
            ClassRegistry::init('CirclePin')->insertUpdate($userId, $teamId, $orders);
        } catch (RuntimeException $e) {
            $this->log(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            $this->log($e->getTraceAsString());
            return false;
        }

        return true;
    }
    /**
     * 自分が所属しているサークルをソートして返す
     * @return array
     */
    public function getMyCircleSortedList(int $userId, int $teamId): array
    {
        $CirclePin = ClassRegistry::init('CirclePin');
        $Upload = new UploadHelper(new View());

        $pinOrders = [];
        $circles = $CirclePin->getJoinedCircleData($userId, $teamId);
        $pinOrderInformation = $CirclePin->getPinData($userId, $teamId);

        if($pinOrderInformation !== ''){
            $pinOrders = explode(',', $pinOrderInformation);
        }

        foreach ($circles as &$circle) {
            $circle['Data'] = array_merge($circle['Circle'],$circle['CircleMember']);
            $circle['Data']['image'] = $Upload->uploadUrl($circle, 'Circle.photo', ['style' => 'small']);
            $circle = $circle['Data'];
            $circle['order'] = null;
            unset($circle['Circle']);
            unset($circle['CircleMember']);
            unset($circle['CirclePin']);
            unset($circle['Data']);
        }

        
        $counter = 0;
        $circleIds = array_column($circles, 'id');
        foreach ($pinOrders as $key => $circleId) {
            $arrayKey = array_search($circleId, $circleIds);
            if($arrayKey !== false){
                $circles[$arrayKey]['order'] = $counter;
                $counter++;
            }
        }

        $defaultCircle = [];
        $defaultCircleKey = array_search(true, array_column($circles, 'team_all_flg'));
        if($defaultCircleKey !== false){
            $defaultCircle = $circles[$defaultCircleKey];
            unset($circles[$defaultCircleKey]);
        }

        // $circles = Hash::sort($circles, '{n}.modified', 'desc', 'numeric');
        $unsortedCircles = array_filter($circles, function($value, $key){
           return !isset($value['order']);
        }, ARRAY_FILTER_USE_BOTH);
        $sortedCircles = array_filter($circles, function($value, $key){
           return isset($value['order']);
        }, ARRAY_FILTER_USE_BOTH);
        $sortedCircles = Hash::sort($sortedCircles, '{n}.order', 'asc', 'numeric');
        $orderedCircles = array_merge($sortedCircles, $unsortedCircles);

        //TODO:For Debugging SQL Queries
        //debug(ClassRegistry::init('CirclePin')->getDataSource()->getLog(false, false));
        $returnArray['regular_circle'] = $orderedCircles;
        $returnArray['default_circle'] = $defaultCircle;
        return $returnArray;
    }
}
