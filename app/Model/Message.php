<?php
App::uses('AppModel', 'Model');

/**
 * Message Model
 *
 * @property Topic       $Topic
 * @property User        $SenderUser
 * @property MessageFile $MessageFile
 */
class Message extends AppModel
{
    const TYPE_NORMAL = 1;
    const TYPE_ADD_MEMBER = 2;
    const TYPE_LEAVE = 3;
    const TYPE_SET_TOPIC_NAME = 4;

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        'topic_id'       => [
            'numeric'       => ['rule' => ['numeric'],],
            'notBlank'      => [
                'required' => 'create',
                'rule'     => 'notBlank',
            ],
            'isTopicMember' => ['rule' => ['customValidateIsTopicMember'],],
        ],
        'sender_user_id' => [
            'numeric'       => ['rule' => ['numeric'],],
            'notBlank'      => [
                'required' => 'create',
                'rule'     => 'notBlank',
            ],
            'isTopicMember' => ['rule' => ['customValidateSenderIsBelongTheTopic']]
        ],
        'body'           => [
            'maxLength'                  => ['rule' => ['maxLength', 5000]],
            'isString'                   => ['rule' => 'isString', 'message' => 'Invalid Submission'],
            'bodyOrAttachedFileRequired' => ['rule' => ['customValidateBody']],
        ],
        'type'           => [
            'numeric' => ['rule' => ['numeric'],],
        ],
        'del_flg'        => [
            'boolean' => ['rule' => ['boolean'],],
        ],
    ];

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        'Topic',
        'SenderUser' => [
            'className'  => 'User',
            'foreignKey' => 'sender_user_id',
        ],
    ];

    /**
     * hasMany associations
     *
     * @var array
     */
    public $hasMany = [
        'MessageFile',
    ];

    /**
     * Find messages
     *
     * @param int      $topicId
     * @param int|null $cursor
     * @param int      $limit
     *
     * @return array
     */
    function findMessages(int $topicId, $cursor, int $limit): array
    {
        $options = [
            'conditions' => [
                'Message.topic_id' => $topicId,
            ],
            'fields'     => [
                'id',
                'body',
                'type',
                'target_user_ids',
                'created'
            ],
            'order'      => [
                'Message.id' => 'DESC'
            ],
            'contain'    => [
                'SenderUser'  => [
                    'fields' => $this->SenderUser->profileFields
                ],
                'MessageFile' => [
                    'fields'       => [],
                    'order'        => ['MessageFile.index_num asc'],
                    'AttachedFile' => [
                        'id',
                        'attached_file_name',
                        'file_type',
                        'file_ext'
                    ]
                ]
            ],
            'limit'      => $limit,
        ];

        if ($cursor) {
            $options['conditions']['Message.id <'] = $cursor;
        }

        $res = $this->find('all', $options);
        return $res;
    }

    /**
     * Find One message
     *
     * @param int $id
     *
     * @return array
     */
    function get(int $id): array
    {
        $options = [
            'conditions' => [
                'Message.id' => $id,
            ],
            'fields'     => [
                'id',
                'body',
                'type',
                'target_user_ids',
                'created'
            ],
            'contain'    => [
                'SenderUser'  => [
                    'fields' => $this->SenderUser->profileFields
                ],
                'MessageFile' => [
                    'fields'       => [],
                    'order'        => ['MessageFile.index_num asc'],
                    'AttachedFile' => [
                        'id',
                        'attached_file_name',
                        'file_type',
                        'file_ext'
                    ]
                ]
            ],
        ];

        $res = $this->find('first', $options);
        return $res;
    }

    /**
     * Add a new message.
     *
     * @param array $data
     * @param int   $userId
     *
     * @return mixed
     */
    function saveNormal(array $data, int $userId)
    {
        $data = am($data, [
            'type'           => self::TYPE_NORMAL,
            'sender_user_id' => $userId,
            'team_id'        => $this->current_team_id,
        ]);
        $ret = $this->save($data);
        return $ret;
    }

    /**
     * Is topic member?
     *
     * @param array $val
     *
     * @return bool
     */
    function customValidateIsTopicMember(array $val): bool
    {
        $topicId = array_shift($val);

        /** @var TopicMember $TopicMember */
        $TopicMember = ClassRegistry::init('TopicMember');
        if ($TopicMember->isMember($topicId, $this->my_uid)) {
            return true;
        }
        return false;
    }

    /**
     * sender user id belongs to the topic?
     *
     * @param array $val
     *
     * @return bool
     */
    function customValidateSenderIsBelongTheTopic(array $val): bool
    {
        $senderUid = array_shift($val);

        $topicId = Hash::get($this->data, 'Message.topic_id');

        /** @var TopicMember $TopicMember */
        $TopicMember = ClassRegistry::init('TopicMember');
        if ($TopicMember->isMember($topicId, $senderUid)) {
            return true;
        }
        return false;
    }

    /**
     * Is body valid?
     * - body on normal message is required when no files.
     *
     * @param array $val
     *
     * @return bool
     */
    function customValidateBody(array $val): bool
    {
        $body = array_shift($val);

        if (!empty($body)) {
            return true;
        }
        $type = Hash::get($this->data, 'Message.type');
        if ($type === null) {
            // default type
            $type = self::TYPE_NORMAL;
        }

        if ($type != self::TYPE_NORMAL) {
            return true;
        }

        $fileIds = Hash::get($this->data, 'Message.file_ids');
        if (!empty($fileIds)) {
            // remove empty values.
            $fileIds = array_filter($fileIds, "strlen");
        }

        if (!empty($fileIds)) {
            return true;
        }
        return false;
    }

}
