<?php
App::uses('ApiController', 'Controller/Api');
App::uses('Topic', 'Model');
App::uses('TopicMember', 'Model');
App::import('Service', 'MessageService');
App::import('Service/Api', 'ApiMessageService');

/**
 * Class MessagesController
 */
class MessagesController extends ApiController
{
    /**
     * Send a message
     * url: POST /api/v1/messages
     *
     * @data integer $topic_id required
     * @data string $body optional
     * @data array $file_ids optional
     * @return CakeResponse
     * @link https://confluence.goalous.com/display/GOAL/%5BPOST%5D+Send+message
     */
    function post()
    {
        /** @var MessageService $MessageService */
        $MessageService = ClassRegistry::init('MessageService');
        /** @var ApiMessageService $ApiMessageService */
        $ApiMessageService = ClassRegistry::init('ApiMessageService');

        $userId = $this->Auth->user('id');

        // filter fields
        $postedData = AppUtil::filterWhiteList($this->request->data, ['topic_id', 'body', 'file_ids']);

        $topicId = $postedData['topic_id'];

        // checking 403 or 404
        $errResponse = $this->_validateCreateForbiddenOrNotFound($topicId, $userId);
        if ($errResponse !== true) {
            return $errResponse;
        }

        // validation
        $validationResult = $MessageService->validatePostMessage($postedData);
        if ($validationResult !== true) {
            return $this->_getResponseValidationFail($validationResult);
        }
        // saving datas
        $messageId = $MessageService->add($postedData, $userId);
        if ($messageId === false) {
            return $this->_getResponseBadFail(null);
        }

        $topicId = $postedData['topic_id'];

        // tracking by mixpanel
        $this->Mixpanel->trackMessage($topicId);
        $this->NotifyBiz->execSendNotify(NotifySetting::TYPE_MESSAGE, $messageId);
        //TODO pusherのsocket_idをフォームで渡してもらう必要がある。これはapiからのつなぎこみ時に行う。
        $socketId = "test";
        $this->_execPushMessageEvent($topicId, $socketId);
        // find the message as response data
        $newMessage = $ApiMessageService->get($messageId);
        return $this->_getResponseSuccess($newMessage);
    }

    /**
     * Send a Like message
     * url: POST /api/v1/messages/like
     *
     * @data integer $topic_id required
     * @return CakeResponse
     * @link https://confluence.goalous.com/display/GOAL/%5BPOST%5D+Send+like+message
     *       TODO: This is mock! We have to implement it!
     */
    function post_like()
    {
        $topicId = $this->request->data('topic_id');

        /** @var TopicMember $TopicMember */
        $TopicMember = ClassRegistry::init('TopicMember');
        if (!$TopicMember->isMember($topicId, $this->Auth->user('id'))) {
            return $this->_getResponseBadFail(__("You cannot access the topic"));
        }

        //thumbs up character is bellow
        $body = "\xF0\x9F\x91\x8D";

        $dataMock = ['message_id' => 1234];
        return $this->_getResponseSuccessSimple($dataMock);
    }

    /**
     * validation for creating a message
     * - if not found, it will return 404 response
     * - if not have permission, it will return 403 response
     *
     * @param $topicId
     * @param $userId
     *
     * @return CakeResponse|true
     */
    private function _validateCreateForbiddenOrNotFound($topicId, $userId)
    {
        /** @var Topic $Topic */
        $Topic = ClassRegistry::init("Topic");
        /** @var TopicMember $TopicMember */
        $TopicMember = ClassRegistry::init("TopicMember");

        // topic is exists?
        if (!$Topic->exists($topicId)) {
            return $this->_getResponseNotFound();
        }
        // is topic member?
        $isMember = $TopicMember->isMember($topicId, $userId);
        if (!$isMember) {
            return $this->_getResponseForbidden();
        }
        return true;
    }

    /**
     * pushing new message event to topic member.
     *
     * @param int    $topicId
     * @param string $socketId
     */
    private function _execPushMessageEvent(int $topicId, string $socketId)
    {
        $cmd = " push_message";
        $cmd .= " -t " . $topicId;
        $cmd .= " -s " . $socketId;
        $cmdEnd = " > /dev/null &";
        $allCmd = AppUtil::baseCmdOfBgJob() . $cmd . $cmdEnd;
        exec($allCmd);
    }

}
