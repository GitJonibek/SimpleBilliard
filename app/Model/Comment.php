<?php
App::uses('AppModel', 'Model');

/**
 * Comment Model
 *
 * @property Post        $Post
 * @property User        $User
 * @property Team        $Team
 * @property CommentLike $CommentLike
 * @property CommentRead $CommentRead
 */
class Comment extends AppModel
{
    public $actsAs = [
        'Upload' => [
            'photo1'     => [
                'styles'  => [
                    'small' => '420l',
                    'large' => '2048l',
                ],
                'path'    => ":webroot/upload/:model/:id/:hash_:style.:extension",
                'quality' => 100,
            ],
            'photo2'     => [
                'styles'  => [
                    'small' => '420l',
                    'large' => '2048l',
                ],
                'path'    => ":webroot/upload/:model/:id/:hash_:style.:extension",
                'quality' => 100,
            ],
            'photo3'     => [
                'styles'  => [
                    'small' => '420l',
                    'large' => '2048l',
                ],
                'path'    => ":webroot/upload/:model/:id/:hash_:style.:extension",
                'quality' => 100,
            ],
            'photo4'     => [
                'styles'  => [
                    'small' => '420l',
                    'large' => '2048l',
                ],
                'path'    => ":webroot/upload/:model/:id/:hash_:style.:extension",
                'quality' => 100,
            ],
            'photo5'     => [
                'styles'  => [
                    'small' => '420l',
                    'large' => '2048l',
                ],
                'path'    => ":webroot/upload/:model/:id/:hash_:style.:extension",
                'quality' => 100,
            ],
            'site_photo' => [
                'styles'      => [
                    'small' => '80w',
                ],
                'path'        => ":webroot/upload/:model/:id/:hash_:style.:extension",
                'quality'     => 100,
                'default_url' => 'no-image-link.png',
            ],
        ],
    ];
    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        'comment_like_count' => ['numeric' => ['rule' => ['numeric']]],
        'comment_read_count' => ['numeric' => ['rule' => ['numeric']]],
        'del_flg'            => ['boolean' => ['rule' => ['boolean']]],
        'photo1'             => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'photo2'             => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'photo3'             => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'photo4'             => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'photo5'             => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'site_photo'         => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
    ];

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        'Post' => [
            "counterCache" => true,
            'counterScope' => ['Comment.del_flg' => false]
        ],
        'User',
        'Team',
    ];

    /**
     * hasMany associations
     *
     * @var array
     */
    public $hasMany = [
        'CommentLike'   => [
            'dependent' => true,
        ],
        'CommentRead'   => [
            'dependent' => true,
        ],
        'MyCommentLike' => [
            'className' => 'CommentLike',
            'fields'    => ['id']
        ]
    ];

    /**
     * コメント
     *
     * @param      $postData
     * @param null $uid
     * @param null $team_id
     *
     * @return bool|mixed
     */
    public function add($postData, $uid = null, $team_id = null)
    {
        $this->setUidAndTeamId($uid, $team_id);
        $postData['Comment']['user_id'] = $this->uid;
        $postData['Comment']['team_id'] = $this->team_id;
        $res = $this->save($postData);
        //投稿データのmodifiedを更新
        $this->Post->id = $postData['Comment']['post_id'];
        $this->Post->saveField('modified', REQUEST_TIMESTAMP);

        return $res;
    }

    public function getPostsComment($post_id, $cut_num = 0)
    {
        $options = [
            'conditions' => [
                'Comment.post_id' => $post_id,
                'Comment.team_id' => $this->current_team_id,
            ],
            'order'      => [
                'Comment.created' => 'asc'
            ],
            'contain'    => [
                'User'          => [
                    'fields' => $this->User->profileFields
                ],
                'MyCommentLike' => [
                    'conditions' => [
                        'MyCommentLike.user_id' => $this->my_uid,
                        'MyCommentLike.team_id' => $this->current_team_id,
                    ]
                ],
            ],
        ];
        $res = $this->find('all', $options);
        //最後のコメントから指定件数を削除
        if ($cut_num > 0) {
            for ($i = 0; $i < $cut_num; $i++) {
                array_pop($res);
            }
        }

        //既読済みに
        /** @noinspection PhpDeprecationInspection */
        $comment_list = Set::classicExtract($res, '{n}.Comment.id');
        $this->CommentRead->red($comment_list);

        return $res;
    }

    function commentEdit($data)
    {
        if (isset($data['photo_delete']) && !empty($data['photo_delete'])) {
            foreach ($data['photo_delete'] as $index => $val) {
                if ($val) {
                    $data['Comment']['photo' . $index] = null;
                }
            }
        }
        $res = $this->save($data);
        return $res;
    }

    function getCountCommentUniqueUser($post_id, $without_user_id_list = [])
    {
        $options = [
            'conditions' => [
                'post_id' => $post_id,
                'team_id' => $this->current_team_id,
            ],
            'fields'     => [
                'COUNT(DISTINCT user_id) as count',
            ]
        ];
        if (!empty($without_user_id_list)) {
            $options['conditions']['NOT']['user_id'] = $without_user_id_list;
        }
        $res = $this->find('count', $options);
        return $res;
    }

    function getCommentedUniqueUsersList($post_id, $without_me = true)
    {
        $options = [
            'conditions' => [
                'post_id' => $post_id,
                'team_id' => $this->current_team_id,
            ],
            'fields'     => [
                'DISTINCT user_id',
            ]
        ];
        if ($without_me) {
            $options['conditions']['NOT']['user_id'] = $this->my_uid;
        }
        $res = $this->find('all', $options);
        /** @noinspection PhpDeprecationInspection */
        $res = Set::combine($res, '{n}.Comment.user_id', '{n}.Comment.user_id');
        return $res;
    }

}
