<?php
App::import('Service', 'AppService');
App::import('Service', 'UserService');
App::uses('Topic', 'Model');
App::uses('Message', 'Model');
App::uses('TopicMember', 'Model');
App::uses('User', 'Model');
App::uses('AttachedFile', 'Model');
App::uses('Topic', 'Model');
App::uses('AppUtil', 'Util');

/**
 * Class MessageService
 */
class MessageService extends AppService
{
    const CHAR_EMOJI_LIKE = "\xF0\x9F\x91\x8D";

    /**
     * Finding messages.
     * This is for fetching data and adding extended fields.
     * Cake key names will be not changed. That is ApiMessageService's job.
     *
     * @param int         $topicId
     * @param             $cursor
     * @param             $limit
     * @param string|null $direction older than cursor or newer
     *
     * @return array
     */
    function findMessages(int $topicId, $cursor, $limit, $direction): array
    {
        /** @var Message $Message */
        $Message = ClassRegistry::init('Message');
        $messages = $Message->findMessages($topicId, $cursor, $limit, $direction);
        // reverse sort messages
        krsort($messages);
        // renumbering keys
        $messages = am($messages);

        $TimeEx = new TimeExHelper(new View());
        /** @var User $User */
        $User = ClassRegistry::init('User');

        // extend message data
        foreach ($messages as &$message) {
            // build message body
            $message = $this->extendBody($message);
            // build display created
            $message['Message']['display_created'] = $TimeEx->datetimeNoYear($message['Message']['created']);
            // user image url
            $message['SenderUser'] = $User->attachImgUrl($message['SenderUser'], 'User', ['medium']);
            // attached file url
            if (Hash::get($message, 'MessageFile')) {
                $message['MessageFile'] = $this->extendAttachedFileUrl($message['MessageFile']);
            }
            // filter only necessary fields
            $message = $this->filterFields($message);
        }

        return $messages;
    }

    /**
     * Getting a message
     *
     * @param int $messageId
     *
     * @return array
     */
    function get(int $messageId): array
    {
        /** @var Message $Message */
        $Message = ClassRegistry::init('Message');
        $message = $Message->get($messageId);

        $TimeEx = new TimeExHelper(new View());
        /** @var User $User */
        $User = ClassRegistry::init('User');

        // build message body
        $message = $this->extendBody($message);
        // build display created
        $message['Message']['display_created'] = $TimeEx->datetimeNoYear($message['Message']['created']);
        // user image url
        $message['SenderUser'] = $User->attachImgUrl($message['SenderUser'], 'User', ['medium']);
        // attached file url
        if (Hash::get($message, 'MessageFile')) {
            $message['MessageFile'] = $this->extendAttachedFileUrl($message['MessageFile']);
        }
        // filter only necessary fields
        $message = $this->filterFields($message);
        return $message;
    }

    /**
     * Extending message body
     * normal case:
     * - only sanitizing
     * other cases:
     * - exchanging body to text message
     *
     * @param array $message
     *
     * @return array
     */
    function extendBody(array $message): array
    {
        /** @var UserService $UserService */
        $UserService = ClassRegistry::init('UserService');

        $type = $message['Message']['type'];
        $body = $message['Message']['body'];
        $targetUids = $message['Message']['target_user_ids'];
        $senderName = $message['SenderUser']['display_first_name'];

        switch ($type) {
            case Message::TYPE_NORMAL:
                $outputBody = h($body);
                break;
            case Message::TYPE_ADD_MEMBER:
                $uids = explode(',', $targetUids);
                $delimiter = Configure::read('Config.language') == "jpn" ? "と" : ", ";
                $addedUserNamesStr = $UserService->getUserNamesAsString($uids, $delimiter);
                $outputBody = __("%s added %s.", $senderName, $addedUserNamesStr);
                break;
            case Message::TYPE_LEAVE:
                $outputBody = __('%s left this topic.', $senderName);
                break;
            case Message::TYPE_SET_TOPIC_NAME:
                if ($body) {
                    $outputBody = __('%s named this topic : %s.', $senderName, $body);
                } else {
                    $outputBody = __('%s removed the topic name.', $senderName);
                }
                break;
            default:
                $outputBody = $body;
        }
        $message['Message']['body'] = $outputBody;
        return $message;
    }

    /**
     * Extending urls those attached file
     * adding the following fields:
     * - download_url
     * - preview_url
     * - thumbnail_url
     *
     * @param array $messageFiles
     *
     * @return array
     */
    function extendAttachedFileUrl(array $messageFiles): array
    {
        $Upload = new UploadHelper(new View());
        foreach ($messageFiles as &$messageFile) {
            if (!Hash::get($messageFile, 'AttachedFile')) {
                continue;
            }

            // bellow fields will be added
            $urls = [
                'download_url'  => null,
                'preview_url'   => null,
                'thumbnail_url' => null,
            ];

            // download url is common.
            $urls['download_url'] = '/posts/attached_file_download/file_id:' . $messageFile['AttachedFile']['id'];

            if ($messageFile['AttachedFile']['file_type'] == AttachedFile::TYPE_FILE_IMG) {
                // In case of image, add thumbnail url and preview url
                $urls['thumbnail_url'] = $Upload->uploadUrl($messageFile, 'AttachedFile.attached',
                    ['style' => 'small']);
                $urls['preview_url'] = $Upload->uploadUrl($messageFile, 'AttachedFile.attached',
                    ['style' => 'original']);
            } elseif ($Upload->isCanPreview($messageFile)) {
                $urls['preview_url'] = $Upload->attachedFileUrl($messageFile);
            }
            $messageFile['AttachedFile'] += $urls;
        }

        return $messageFiles;
    }

    /**
     * Filtering fields
     * - Message
     * - User
     * - AttachedFile
     *
     * @param array $message
     *
     * @return array
     */
    function filterFields(array $message): array
    {
        $messageFilter = [
            'id',
            'body',
            'type',
            'created',
            'display_created',
        ];

        $senderUserFilter = [
            'id',
            'display_username',
            'medium_img_url'
        ];

        $attachedFileFilter = [
            'id',
            'attached_file_name',
            'file_type',
            'file_ext',
            'file_size',
            'download_url',
            'preview_url',
            'thumbnail_url'
        ];
        $message['Message'] = AppUtil::filterWhiteList($message['Message'], $messageFilter);
        $message['SenderUser'] = AppUtil::filterWhiteList($message['SenderUser'], $senderUserFilter);
        foreach ($message['MessageFile'] as &$file) {
            $file['AttachedFile'] = AppUtil::filterWhiteList($file['AttachedFile'], $attachedFileFilter);
        }
        return $message;
    }

    /**
     * Validate a posted message
     *
     * @param array $data
     *
     * @return array|true
     */
    function validatePostMessage(array $data, $ignoreFields = [])
    {
        /** @var Message $Message */
        $Message = ClassRegistry::init('Message');
        $backupValidate = $Message->validate;
        // ignore validate fields
        foreach($ignoreFields as $fieldName) {
            unset($Message->validate[$fieldName]);
        }
        $Message->set($data);
        $isValid = $Message->validates();
        $Message->validate = $backupValidate;
        if ($isValid) {
            return true;
        }
        return $this->validationExtract($Message->validationErrors);
    }

    /**
     * Saving a new message.
     * - updating latest message on the topic.
     * - return message id if success. otherwise, return false.
     *
     * @param array $data
     * @param int   $userId
     *
     * @return int|false
     */
    function add(array $data, int $userId)
    {
        $topicId = $data['topic_id'];
        /** @var Message $Message */
        $Message = ClassRegistry::init('Message');
        /** @var Topic $Topic */
        $Topic = ClassRegistry::init('Topic');
        /** @var AttachedFile $AttachedFile */
        $AttachedFile = ClassRegistry::init('AttachedFile');
        /** @var TopicMember $TopicMember */
        $TopicMember = ClassRegistry::init('TopicMember');

        $Message->begin();

        try {
            // saving message
            $message = $Message->saveNormal($data, $userId);
            if ($message === false) {
                $errorMsg = sprintf("Failed to add a message. userId:%s, topicId:%s, data:%s, validationErrors:%s",
                    $userId,
                    $topicId,
                    var_export($data, true),
                    var_export($Message->validationErrors, true)
                );
                throw new Exception($errorMsg);
            }
            $messageId = $Message->getLastInsertID();

            // saving attached files
            if (Hash::get($data, 'file_ids')) {
                $attachedFiles = $AttachedFile->saveRelatedFiles($messageId, AttachedFile::TYPE_MODEL_MESSAGE,
                    $data['file_ids']);
                if ($attachedFiles === false) {
                    $errorMsg = sprintf("Failed to save attached files on message. data:%s, validationErrors:%s",
                        var_export($data, true),
                        var_export($AttachedFile->validationErrors, true)
                    );
                    throw new Exception($errorMsg);
                }

                // updating attached file count
                $Message->id = $messageId;
                $Message->saveField('attached_file_count', count($data['file_ids']));
            }

            // updating latest message on the topic
            $updateTopic = $Topic->updateLatestMessage($topicId, $messageId);
            if ($updateTopic === false) {
                $errorMsg = sprintf("Failed to update latest message on the topic. topicId:%s, messageId:%s, validationErrors:%s",
                    $topicId,
                    $messageId,
                    var_export($Topic->validationErrors, true)
                );
                throw new Exception($errorMsg);
            }

            // updating last message sent
            $updateLastSent = $TopicMember->updateLastMessageSentDate($topicId, $userId);
            if ($updateLastSent === false) {
                $errorMsg = sprintf("Failed to update last message sent. topicId:%s, userId:%s, validationErrors:%s",
                    $topicId,
                    $userId,
                    var_export($Topic->validationErrors, true)
                );
                throw new Exception($errorMsg);
            }
        } catch (Exception $e) {
            $this->log($e->getMessage());
            $Message->rollback();
            return false;
        }

        $Message->commit();
        return $messageId;
    }

    /**
     * Saving a like message.
     * - updating latest message on the topic.
     * - return message id if success. otherwise, return false.
     *
     * @param int $topicId
     * @param int $userId
     *
     * @return false|int
     */
    function addLike(int $topicId, int $userId)
    {
        $data = [
            'topic_id' => $topicId,
            'body'     => self::CHAR_EMOJI_LIKE,
        ];
        $ret = $this->add($data, $userId);
        return $ret;
    }

    /**
     * pushing new message event to topic member.
     *
     * @param int         $topicId
     * @param null|string $socketId for exclude sender to publish
     */
    function execPushMessageEvent(int $topicId, $socketId = null)
    {
        $cmd = " push_message";
        $cmd .= " -t " . $topicId;
        if ($socketId) {
            $cmd .= " -s " . $socketId;
        }
        $cmdEnd = " > /dev/null &";
        $allCmd = AppUtil::baseCmdOfBgJob() . $cmd . $cmdEnd;
        exec($allCmd);
    }

}
