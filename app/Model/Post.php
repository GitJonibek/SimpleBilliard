<?php
App::uses('AppModel', 'Model');
App::uses('UploadHelper', 'View/Helper');
App::uses('TimeExHelper', 'View/Helper');
App::uses('TextExHelper', 'View/Helper');
App::uses('View', 'View');
App::uses('PostShareCircle', 'Model');

/**
 * Post Model
 *
 * @property User            $User
 * @property Team            $Team
 * @property CommentMention  $CommentMention
 * @property Comment         $Comment
 * @property Goal            $Goal
 * @property GivenBadge      $GivenBadge
 * @property PostLike        $PostLike
 * @property PostMention     $PostMention
 * @property PostShareUser   $PostShareUser
 * @property PostShareCircle $PostShareCircle
 * @property PostRead        $PostRead
 * @property ActionResult    $ActionResult
 * @property KeyResult       $KeyResult
 * @property Circle          $Circle
 * @property AttachedFile    $AttachedFile
 * @property PostFile        $PostFile
 * @property PostSharedLog   $PostSharedLog
 */
class Post extends AppModel
{
    /**
     * post type
     */
    const TYPE_NORMAL = 1;
    const TYPE_CREATE_GOAL = 2;
    const TYPE_ACTION = 3;
    const TYPE_BADGE = 4;
    const TYPE_KR_COMPLETE = 5;
    const TYPE_GOAL_COMPLETE = 6;
    const TYPE_CREATE_CIRCLE = 7;
    const TYPE_MESSAGE = 8;

    static public $TYPE_MESSAGE = [
        self::TYPE_NORMAL        => null,
        self::TYPE_CREATE_GOAL   => null,
        self::TYPE_ACTION        => null,
        self::TYPE_BADGE         => null,
        self::TYPE_KR_COMPLETE   => null,
        self::TYPE_GOAL_COMPLETE => null,
        self::TYPE_CREATE_CIRCLE => null,
        self::TYPE_MESSAGE       => null,
    ];

    function _setTypeMessage()
    {
        self::$TYPE_MESSAGE[self::TYPE_CREATE_GOAL] = __d('gl', "あたらしいゴールをつくりました。");
        self::$TYPE_MESSAGE[self::TYPE_CREATE_CIRCLE] = __d('gl', "あたらしいサークルをつくりました。");
    }

    const SHARE_PEOPLE = 2;
    const SHARE_ONLY_ME = 3;
    const SHARE_CIRCLE = 4;

    public $orgParams = [
        'author_id'     => null,
        'circle_id'     => null,
        'user_id'       => null,
        'post_id'       => null,
        'goal_id'       => null,
        'key_result_id' => null,
        'filter_goal'   => null,
        'type'          => null,
    ];

    public $uses = [
        'AttachedFile'
    ];

    public $actsAs = [
        'Upload' => [
            'photo1'     => [
                'styles'  => [
                    'small' => '460l',
                    'large' => '2048l',
                ],
                'path'    => ":webroot/upload/:model/:id/:hash_:style.:extension",
                'quality' => 100,
            ],
            'photo2'     => [
                'styles'  => [
                    'small' => '460l',
                    'large' => '2048l',
                ],
                'path'    => ":webroot/upload/:model/:id/:hash_:style.:extension",
                'quality' => 100,
            ],
            'photo3'     => [
                'styles'  => [
                    'small' => '460l',
                    'large' => '2048l',
                ],
                'path'    => ":webroot/upload/:model/:id/:hash_:style.:extension",
                'quality' => 100,
            ],
            'photo4'     => [
                'styles'  => [
                    'small' => '460l',
                    'large' => '2048l',
                ],
                'path'    => ":webroot/upload/:model/:id/:hash_:style.:extension",
                'quality' => 100,
            ],
            'photo5'     => [
                'styles'  => [
                    'small' => '460l',
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
        'comment_count'   => ['numeric' => ['rule' => ['numeric'],],],
        'post_like_count' => ['numeric' => ['rule' => ['numeric'],],],
        'post_read_count' => ['numeric' => ['rule' => ['numeric'],],],
        'important_flg'   => ['boolean' => ['rule' => ['boolean'],],],
        'del_flg'         => ['boolean' => ['rule' => ['boolean'],],],
        'photo1'          => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'photo2'          => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'photo3'          => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'photo4'          => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'photo5'          => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'site_photo'      => [
            'image_max_size' => ['rule' => ['attachmentMaxSize', 10485760],], //10mb
            'image_type'     => ['rule' => ['attachmentContentType', ['image/jpeg', 'image/gif', 'image/png']],]
        ],
        'body'            => [
            'isString' => ['rule' => 'isString', 'message' => 'Invalid Submission']
        ]

    ];

    //The Associations below have been created with all possible keys, those that are not needed can be removed

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        'User',
        'Team',
        'Goal',
        'Circle',
        'ActionResult',
        'KeyResult',
    ];

    /**
     * hasMany associations
     *
     * @var array
     */
    public $hasMany = [
        'CommentMention',
        'Comment'         => [
            'dependent' => true,
        ],
        'CommentId'       => [
            'className' => 'Comment',
            'fields'    => ['CommentId.id', 'CommentId.user_id'],
        ],
        'PostShareUser'   => [
            'dependent' => true,
        ],
        'PostShareCircle' => [
            'dependent' => true,
        ],
        'GivenBadge',
        'PostLike'        => [
            'dependent' => true,
        ],
        'PostMention',
        'PostRead'        => [
            'dependent' => true,
        ],
        'MyPostLike'      => [
            'className' => 'PostLike',
            'fields'    => ['id']
        ],
        'PostFile',
    ];

    function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);

        $this->_setTypeMessage();
    }

    public function beforeValidate($options = [])
    {
        parent::beforeValidate($options);

        // OGP 画像が存在する場合、画像の形式をチェックして
        // 通常の画像形式でない場合はデフォルトの画像を表示するようにする
        // （validate の段階でチェックすると投稿エラーになってしまうため）
        if (isset($this->data['Post']['site_photo']['type'])) {
            if (isset($this->validate['site_photo']['image_type']['rule'][1])) {
                $image_types = $this->validate['site_photo']['image_type']['rule'][1];
                if (!in_array($this->data['Post']['site_photo']['type'], $image_types)) {
                    // 画像形式が許容されていない場合、画像が存在しないものとする
                    $this->data['Post']['site_photo'] = null;
                }
            }
        }
        return true;
    }

    /**
     * 投稿
     *
     * @param      $postData
     * @param null $uid
     * @param null $team_id
     *
     * @return bool|mixed
     */
    public function addNormal($postData, $uid = null, $team_id = null)
    {
        if (!isset($postData['Post']) || empty($postData['Post'])) {
            return false;
        }
        $this->setUidAndTeamId($uid, $team_id);
        $share = null;
        if (isset($postData['Post']['share']) && !empty($postData['Post']['share'])) {
            $share = explode(",", $postData['Post']['share']);
            foreach ($share as $key => $val) {
                if (stristr($val, 'public')) {
                    $teamAllCircle = $this->Circle->getTeamAllCircle();
                    $share[$key] = 'circle_' . $teamAllCircle['Circle']['id'];
                }
            }
        }
        $postData['Post']['user_id'] = $this->uid;
        $postData['Post']['team_id'] = $this->team_id;
        if (!isset($postData['Post']['type'])) {
            $postData['Post']['type'] = Post::TYPE_NORMAL;
        }

        // 本文の末尾に OGP のURLが存在する場合は削除する
        // ただし、本文が URL のみの場合はそのまま残す
        if (isset($postData['Post']['site_info']) && $postData['Post']['site_info']) {
            $site_info = json_decode($postData['Post']['site_info'], true);
            $body = rtrim($postData['Post']['body']);
            $body_len = strlen($body);
            if (($pos = strrpos($body, $site_info['url'])) !== false) {
                if ($pos == $body_len - strlen($site_info['url'])) {
                    $body = rtrim(substr($body, 0, $pos));
                    if (strlen($body)) {
                        $postData['Post']['body'] = $body;
                    }
                }
            }
        }

        $this->begin();
        $res = $this->save($postData);
        if (empty($res)) {
            $this->rollback();
            return false;
        }

        $post_id = $this->getLastInsertID();
        $results = [];
        // ファイルが添付されている場合
        if (isset($postData['file_id']) && is_array($postData['file_id'])) {
            $results[] = $this->PostFile->AttachedFile->saveRelatedFiles($post_id,
                                                                         AttachedFile::TYPE_MODEL_POST,
                                                                         $postData['file_id']);
        }
        if (!empty($share)) {
            //ユーザとサークルに分割
            $users = [];
            $circles = [];
            foreach ($share as $val) {
                //ユーザの場合
                if (stristr($val, 'user_')) {
                    $users[] = str_replace('user_', '', $val);
                }
                //サークルの場合
                elseif (stristr($val, 'circle_')) {
                    $circles[] = str_replace('circle_', '', $val);
                }
            }
            if ($users) {
                //共有ユーザ保存
                $results[] = $this->PostShareUser->add($this->getLastInsertID(), $users);
            }
            if ($circles) {
                //共有サークル保存
                $results[] = $this->PostShareCircle->add($this->getLastInsertID(), $circles);
                //共有サークル指定されてた場合の未読件数更新
                $results[] = $this->User->CircleMember->incrementUnreadCount($circles);
                //共有サークル指定されてた場合、更新日時更新
                $results[] = $this->User->CircleMember->updateModified($circles);
                $results[] = $this->PostShareCircle->Circle->updateModified($circles);
            }
        }
        // どこかでエラーが発生した場合は rollback
        foreach ($results as $r) {
            if (!$r) {
                $this->rollback();
                $this->PostFile->AttachedFile->deleteAllRelatedFiles($post_id, AttachedFile::TYPE_MODEL_POST);
                return false;
            }
        }
        $this->commit();

        // 添付ファイルが存在する場合は一時データを削除
        if (isset($postData['file_id']) && is_array($postData['file_id'])) {
            $Redis = ClassRegistry::init('GlRedis');
            foreach ($postData['file_id'] as $hash) {
                $Redis->delPreUploadedFile($this->current_team_id, $this->my_uid, $hash);
            }
        }

        return true;
    }

    /**
     * メッセージのメンバーを変更
     *
     * @param      $postData
     * @param null $uid
     * @param null $team_id
     *
     * @return bool|mixed
     */
    public function editMessageMember($postData, $uid = null, $team_id = null)
    {
        if (!isset($postData['Post']) || empty($postData['Post'])) {
            return false;
        }

        $this->setUidAndTeamId($uid, $team_id);
        $share = null;
        if (isset($postData['Post']['share']) && !empty($postData['Post']['share'])) {
            $share = explode(",", $postData['Post']['share']);
        }
        $postData['Post']['user_id'] = $this->uid;
        $postData['Post']['team_id'] = $this->team_id;

        $this->begin();
        $post_id = $postData['Post']['post_id'];
        $results = [];
        if (!empty($share)) {
            $users = [];
            foreach ($share as $val) {
                if (stristr($val, 'user_')) {
                    $users[] = str_replace('user_', '', $val);
                }
            }
            if ($users) {
                //共有ユーザ保存
                $results[] = $this->PostShareUser->add($post_id, $users);
            }
        }
        // どこかでエラーが発生した場合は rollback
        $this->commit();
        return true;
    }

    /**
     * @param        $start
     * @param        $end
     * @param string $order
     * @param string $order_direction
     * @param int    $limit
     *
     * @return array|null|void
     */
    public function getRelatedPostList($start, $end, $order = "modified", $order_direction = "desc", $limit = 1000)
    {
        $g_list = [];
        $g_list = array_merge($g_list, $this->Goal->Follower->getFollowList($this->my_uid));
        $g_list = array_merge($g_list, $this->Goal->Collaborator->getCollaboGoalList($this->my_uid, true));
        $g_list = array_merge($g_list, $this->Goal->User->TeamMember->getCoachingGoalList($this->my_uid));

        if (empty($g_list)) {
            return [];
        }
        $options = [
            'conditions' => [
                'team_id'                  => $this->current_team_id,
                'modified BETWEEN ? AND ?' => [$start, $end],
                'goal_id'                  => $g_list,
            ],
            'order'      => [$order => $order_direction],
            'limit'      => $limit,
            'fields'     => ['id'],
        ];
        $res = $this->find('list', $options);
        return $res;
    }

    public function isMyPost($post_id)
    {
        $options = [
            'conditions' => [
                'id'      => $post_id,
                'team_id' => $this->current_team_id,
                'user_id' => $this->my_uid,
            ]
        ];
        $res = $this->find('list', $options);
        if (!empty($res)) {
            return true;
        }
        return false;
    }

    public function getConditionGetMyPostList()
    {
        return ['Post.user_id' => $this->my_uid];
    }

    public function getPostById($post_id)
    {
        $options = [
            'conditions' => [
                'Post.id' => $post_id,
                'team_id' => $this->current_team_id,
            ],
            'contain'    => [
                'User'          => [],
                'PostShareUser' => [],
                'PostFile'      => [
                    'order'        => ['PostFile.index_num asc'],
                    'AttachedFile' => [
                        'User' => [
                            'fields' => $this->User->profileFields
                        ]
                    ]
                ]
            ]
        ];
        $res = $this->find('first', $options);
        return $res;
    }

    public function getPhotoPath($user_arr)
    {
        $upload = new UploadHelper(new View());
        return $upload->uploadUrl($user_arr, 'User.photo', ['style' => 'medium']);
    }

    public function get($page = 1, $limit = 20, $start = null, $end = null, $params = null, $contains_message = false)
    {
        if (!$start) {
            $start = strtotime("-1 month", REQUEST_TIMESTAMP);
        }
        elseif (is_string($start)) {
            $start = strtotime($start);
        }
        if (!$end) {
            $end = REQUEST_TIMESTAMP;
        }
        elseif (is_string($end)) {
            $end = strtotime($end);
        }
        if (isset($params['named']['page']) || !empty($params['named']['page'])) {
            $page = $params['named']['page'];
            unset($params['named']['page']);
        }
        // このパラメータが指定された場合、post_time_before より前の投稿のみを読み込む
        // 実際の値は投稿の並び順によって created か modified になる
        $post_time_before = null;
        if (isset($params['named']['post_time_before']) && !empty($params['named']['post_time_before'])) {
            $post_time_before = $params['named']['post_time_before'];
        }
        $org_param_exists = false;
        if ($params) {
            foreach ($this->orgParams as $key => $val) {
                if (array_key_exists($key, $params)) {
                    $org_param_exists = true;
                    $this->orgParams[$key] = $params[$key];
                }
                elseif (isset($params['named']) && array_key_exists($key, $params['named'])) {
                    $org_param_exists = true;
                    $this->orgParams[$key] = $params['named'][$key];
                }
            }
        }

        $post_filter_conditions = [
            'OR'                            => [],
            'Post.modified BETWEEN ? AND ?' => [$start, $end],
        ];
        /**
         * @var DboSource $db
         */
        $db = $this->getDataSource();
        $single_post_id = null;

        //独自パラメータ指定なし
        if (!$org_param_exists) {

            $related_post_ids = $this->getRelatedPostList($start,$end);

            //自分の投稿
            $post_filter_conditions['OR'][] = $this->getConditionGetMyPostList();
            //自分が共有範囲指定された投稿
            $post_filter_conditions['OR'][] =
                $db->expression('Post.id IN (' . $this->getSubQueryFilterPostIdShareWithMe($db, $start, $end) . ')');
            //自分のサークルが共有範囲指定された投稿
            $post_filter_conditions['OR'][] =
                $db->expression('Post.id IN (' . $this->getSubQueryFilterMyCirclePostId($db, $start, $end) . ')');

            //ゴール投稿全て
            $post_filter_conditions['OR'][] = $this->getConditionGoalPostId($related_post_ids);
        }
        //パラメータ指定あり
        else {
            //サークル指定
            if ($this->orgParams['circle_id']) {
                //サークル所属チェック
                $is_secret = $this->Circle->isSecret($this->orgParams['circle_id']);
                $is_exists_circle = $this->Circle->isBelongCurrentTeam($this->orgParams['circle_id'],
                                                                       $this->current_team_id);
                $is_belong_circle_member = $this->User->CircleMember->isBelong($this->orgParams['circle_id']);
                if (!$is_exists_circle || ($is_secret && !$is_belong_circle_member)) {
                    throw new RuntimeException(__d('gl', "サークルが存在しないか、権限がありません。"));
                }
                $post_filter_conditions['OR'][] =
                    $db->expression('Post.id IN (' . $this->getSubQueryFilterMyCirclePostId($db, $start, $end,
                                                                                            $this->orgParams['circle_id'],
                                                                                            PostShareCircle::SHARE_TYPE_SHARED) . ')');

            }
            //単独投稿指定
            elseif ($this->orgParams['post_id']) {
                //アクセス可能かチェック
                if (
                    //ゴール投稿か？であれば参照可能
                    $this->isGoalPost($this->orgParams['post_id']) ||
                    //自分の投稿か？であれば参照可能
                    $this->isMyPost($this->orgParams['post_id']) ||
                    // 公開サークルに共有された投稿か？であれば参照可能
                    $this->PostShareCircle->isShareWithPublicCircle($this->orgParams['post_id']) ||
                    //自分が共有範囲指定された投稿か？であれば参照可能
                    $this->PostShareUser->isShareWithMe($this->orgParams['post_id']) ||
                    //自分のサークルが共有範囲指定された投稿か？であれば参照可能
                    $this->PostShareCircle->isMyCirclePost($this->orgParams['post_id'])
                ) {
                    $single_post_id = $this->orgParams['post_id'];
                }
            }
            //特定のKR指定
            elseif ($this->orgParams['key_result_id']) {
                $post_filter_conditions['OR'][] =
                    $db->expression('Post.id IN (' . $this->getSubQueryFilterKrPostList($db,
                                                                                        $this->orgParams['key_result_id'],
                                                                                        self::TYPE_ACTION,
                                                                                        $start, $end) . ')');
            }
            //特定ゴール指定
            elseif ($this->orgParams['goal_id']) {
                //アクションのみの場合
                if ($this->orgParams['type'] == self::TYPE_ACTION) {
                    $post_filter_conditions['OR'][] =
                        $db->expression('Post.id IN (' . $this->getSubQueryFilterGoalPostList($db,
                                                                                              $this->orgParams['goal_id'],
                                                                                              self::TYPE_ACTION, $start,
                                                                                              $end) . ')');

                }
            }
            //投稿主指定
            elseif ($this->orgParams['author_id']) {
                //アクションのみの場合
                if ($this->orgParams['type'] == self::TYPE_ACTION) {
                    $post_filter_conditions['OR'][] =
                        $db->expression('Post.id IN (' . $this->getSubQueryFilterGoalPostList($db, null,
                                                                                              self::TYPE_ACTION, $start,
                                                                                              $end) . ')');
                }
            }
            //ゴールのみの場合
            elseif ($this->orgParams['filter_goal']) {
                $post_filter_conditions['OR'][] = $this->getConditionGoalPostId();
            }
            // ユーザーID指定
            elseif ($this->orgParams['user_id']) {
                // 自分個人に共有された投稿
                $post_filter_conditions['OR'][] =
                    $db->expression('Post.id IN (' . $this->getSubQueryFilterPostIdShareWithMe($db, $start, $end,
                                                                                               ['user_id' => $this->orgParams['user_id']]) . ')');

                // 自分が閲覧可能なサークルへの投稿一覧
                // （公開サークルへの投稿 + 自分が所属している秘密サークルへの投稿）
                //getSubQueryFilterAccessibleCirclePostList
                $post_filter_conditions['OR'][] =
                    $db->expression('Post.id IN (' . $this->getSubQueryFilterAccessibleCirclePostList($db, $start, $end,
                                                                                                      ['user_id' => $this->orgParams['user_id']]) . ')');

                // 自分自身の user_id が指定された場合は、自分の投稿を含める
                if ($this->my_uid == $this->orgParams['user_id']) {
                    $post_filter_conditions['OR'][] = $this->getConditionGetMyPostList();
                }
            }
        }

        if (!empty($this->orgParams['post_id'])) {
            //単独投稿指定の場合はそのまま
            $post_list = $single_post_id;
        }
        else {
            //単独投稿以外は再度、件数、オーダーの条件を入れ取得
            $post_options = [
                'conditions' => $post_filter_conditions,
                'limit'      => $limit,
                'page'       => $page,
                'order'      => [
                    'Post.modified' => 'desc'
                ],
            ];
            if ($this->orgParams['type'] == self::TYPE_ACTION) {
                $post_options['order'] = ['ActionResult.id' => 'desc'];
                $post_options['contain'] = ['ActionResult'];
            }
            if ($this->orgParams['type'] == self::TYPE_NORMAL) {
                $post_options['conditions']['Post.type'] = self::TYPE_NORMAL;
            }
            if ($contains_message === false) {
                $post_options['conditions']['NOT']['Post.type'] = self::TYPE_MESSAGE;
            }

            // 独自パラメータ無しの場合（ホームフィードの場合）
            if (!$org_param_exists) {
                $post_options['order'] = ['Post.created' => 'desc'];
            }
            // 読み込む投稿の更新時間が指定されている場合
            if ($post_time_before) {
                $order_col = key($post_options['order']);
                $post_options['conditions']["$order_col <="] = $post_time_before;
            }
            $post_list = $this->find('list', $post_options);
        }

        //投稿を既読に
        $this->PostRead->red($post_list);

        $options = [
            'conditions' => [
                'Post.id' => $post_list,
            ],
            'order'      => [
                'Post.created' => 'desc'
            ],
            'contain'    => [
                'User'            => [
                    'fields' => $this->User->profileFields
                ],
                'MyPostLike'      => [
                    'conditions' => [
                        'MyPostLike.user_id' => $this->my_uid,
                        'MyPostLike.team_id' => $this->current_team_id,
                    ],
                ],
                'Circle',
                'Comment'         => [
                    'conditions'    => ['Comment.team_id' => $this->current_team_id],
                    'order'         => [
                        'Comment.created' => 'desc'
                    ],
                    'limit'         => 3,
                    'User'          => ['fields' => $this->User->profileFields],
                    'MyCommentLike' => [
                        'conditions' => [
                            'MyCommentLike.user_id' => $this->my_uid,
                            'MyCommentLike.team_id' => $this->current_team_id,
                        ]
                    ],
                    'CommentFile'   => [
                        'order'        => ['CommentFile.index_num asc'],
                        'AttachedFile' => [
                            'User' => [
                                'fields' => $this->User->profileFields
                            ]
                        ]
                    ]
                ],
                'CommentId',
                'PostShareCircle' => [
                    'fields' => [
                        "PostShareCircle.id",
                        "PostShareCircle.circle_id",
                    ]
                ],
                'PostShareUser'   => [
                    'fields' => [
                        "PostShareUser.id",
                        "PostShareUser.user_id",
                    ]
                ],
                'Goal'            => [
                    'fields'    => [
                        'name',
                        'photo_file_name',
                        'id',
                        'end_date',
                        'completed'
                    ],
                    'User'      => [
                        'fields'     => $this->User->profileFields,
                        'TeamMember' => [
                            'fields'     => [
                                'coach_user_id',
                            ],
                            'conditions' => [
                                'coach_user_id' => $this->my_uid,
                            ]
                        ],
                    ],
                    'Purpose'   => [
                        'fields' => [
                            'name'
                        ]
                    ],
                    'MyCollabo' => [
                        'conditions' => [
                            'MyCollabo.type'    => Collaborator::TYPE_COLLABORATOR,
                            'MyCollabo.user_id' => $this->my_uid,
                        ],
                        'fields'     => [
                            'MyCollabo.id',
                            'MyCollabo.role',
                            'MyCollabo.description',
                        ],
                    ],
                    'MyFollow'  => [
                        'conditions' => [
                            'MyFollow.user_id' => $this->my_uid,
                        ],
                        'fields'     => [
                            'MyFollow.id',
                        ],
                    ]
                ],
                'KeyResult'       => [
                    'fields' => [
                        'id',
                        'name',
                        'end_date'
                    ],
                ],
                'ActionResult'    => [
                    'fields'           => [
                        'id',
                        'note',
                        'name',
                        'photo1_file_name',
                        'photo2_file_name',
                        'photo3_file_name',
                        'photo4_file_name',
                        'photo4_file_name',
                        'photo5_file_name',
                    ],
                    'KeyResult'        => [
                        'fields' => [
                            'id',
                            'name',
                        ],
                    ],
                    'ActionResultFile' => [
                        'order'        => ['ActionResultFile.index_num asc'],
                        'AttachedFile' => [
                            'User' => [
                                'fields' => $this->User->profileFields
                            ]
                        ]
                    ]
                ],
                'PostFile'        => [
                    'order'        => ['PostFile.index_num asc'],
                    'AttachedFile' => [
                        'User' => [
                            'fields' => $this->User->profileFields
                        ]
                    ]
                ]
            ],
        ];

        if (is_array($post_list)) {
            $options['conditions'][] = ['Post.modified BETWEEN ? AND ?' => [$start, $end]];
        }

        if ($this->orgParams['circle_id']) {
            $options['order'] = [
                'Post.modified' => 'desc'
            ];
        }

        if (!empty($this->orgParams['post_id'])) {
            //単独の場合はコメントの件数上限外す
            unset($options['contain']['Comment']['limit']);
        }

        if ($this->orgParams['type'] == self::TYPE_ACTION) {
            $options['order'] = ['ActionResult.id' => 'desc'];
        }
        $res = $this->find('all', $options);
        //コメントを逆順に
        foreach ($res as $key => $val) {
            if (!empty($val['Comment'])) {
                $res[$key]['Comment'] = array_reverse($res[$key]['Comment']);
            }
        }

        //コメントを既読に
        if (!empty($res)) {
            /** @noinspection PhpDeprecationInspection */
            $comment_list = Hash::extract($res, '{n}.Comment.{n}.id');
            $this->Comment->CommentRead->red($comment_list);
        }

        //１件のサークル名をランダムで取得
        $res = $this->getRandomShareCircleNames($res);
        //１件のユーザ名をランダムで取得
        $res = $this->getRandomShareUserNames($res);
        //シェアモードの特定
        $res = $this->getShareMode($res);
        //シェアメッセージの特定
        $res = $this->getShareMessages($res);
        //未読件数を取得
        $res = $this->getCommentMyUnreadCount($res);
        return $res;
    }

    /**
     * 自分の閲覧可能な投稿のID一覧を返す
     * （公開サークルへの投稿 + 自分が所属している秘密サークルへの投稿）
     *
     * @param DboSource $db
     * @param           $start
     * @param           $end
     * @param array     $params
     *                 'user_id' : 指定すると投稿者IDで絞る
     *
     * @return array|null
     */
    public function getSubQueryFilterAccessibleCirclePostList(DboSource $db, $start, $end, array $params = [])
    {
        // パラメータデフォルト
        $params = array_merge(['user_id' => null], $params);

        $my_circle_list = $this->Circle->CircleMember->getMyCircleList();
        $query = [
            'fields'     => ['PostShareCircle.post_id'],
            'table'      => $db->fullTableName($this->PostShareCircle),
            'alias'      => 'PostShareCircle',
            'joins'      => [
                [
                    'type'       => 'LEFT',
                    'table'      => $db->fullTableName($this->Circle),
                    'alias'      => 'Circle',
                    'conditions' => '`PostShareCircle`.`circle_id`=`Circle`.`id`',
                ],
            ],
            'conditions' => [
                'OR'                                       => [
                    'PostShareCircle.circle_id' => $my_circle_list,
                    'Circle.public_flg'         => 1
                ],
                'PostShareCircle.team_id'                  => $this->current_team_id,
                'PostShareCircle.modified BETWEEN ? AND ?' => [$start, $end],
            ],
        ];

        if ($params['user_id'] !== null) {
            $query['joins'][] = [
                'type'       => 'LEFT',
                'table'      => $db->fullTableName($this),
                'alias'      => 'Post',
                'conditions' => '`PostShareCircle`.`post_id`=`Post`.`id`',
            ];
            $query['conditions']['Post.user_id'] = $params['user_id'];
        }
        $res = $db->buildStatement($query, $this);
        return $res;
    }

    public function getSubQueryFilterMyCirclePostId(DboSource $db, $start, $end, $my_circle_list = null, $share_type = null)
    {
        if (!$my_circle_list) {
            $my_circle_list = $this->Circle->CircleMember->getMyCircleList(true);
        }

        $query = [
            'fields'     => ['PostShareCircle.post_id'],
            'table'      => $db->fullTableName($this->PostShareCircle),
            'alias'      => 'PostShareCircle',
            'conditions' => [
                'PostShareCircle.circle_id'                => $my_circle_list,
                'PostShareCircle.team_id'                  => $this->current_team_id,
                'PostShareCircle.modified BETWEEN ? AND ?' => [$start, $end],
            ],
        ];
        if ($share_type !== null) {
            $query['conditions']['PostShareCircle.share_type'] = $share_type;
        }
        if (!is_array($my_circle_list) && $this->Circle->isTeamAllCircle($my_circle_list)) {
            $query['conditions']['NOT'] = [
                'type' => [
                    self::TYPE_ACTION,
                    self::TYPE_CREATE_GOAL,
                    self::TYPE_GOAL_COMPLETE,
                    self::TYPE_KR_COMPLETE
                ]
            ];
        }
        $res = $db->buildStatement($query, $this);
        return $res;
    }

    public function getConditionGoalPostId($post_ids = null)
    {
        if(!is_null($post_ids)) {
            $res = [
                'NOT'     => [
                    'Post.goal_id' => null,
                ],
                'Post.id' => $post_ids
            ];
        }
        else{
            $res = [
                'NOT'     => [
                    'Post.goal_id' => null,
                ],
            ];
        }
        return $res;
    }

    public function getConditionKRFilterPostId()
    {
        $res = [
            'NOT' => [
                'Post.goal_id' => null,
            ]
        ];
        return $res;
    }

    /**
     * 自分に共有された投稿のID一覧を返す
     *
     * @param DboSource $db
     * @param           $start
     * @param           $end
     * @param array     $params
     *                 'user_id' : 指定すると投稿者で絞る
     *
     * @return array|null
     */
    public function getSubQueryFilterPostIdShareWithMe(DboSource $db, $start, $end, array $params = [])
    {
        // パラメータデフォルト
        $params = array_merge(['user_id' => null], $params);
        $query = [
            'fields'     => ['PostShareUser.post_id'],
            'table'      => $db->fullTableName($this->PostShareUser),
            'alias'      => 'PostShareUser',
            'conditions' => [
                'PostShareUser.user_id'                  => $this->my_uid,
                'PostShareUser.team_id'                  => $this->current_team_id,
                'PostShareUser.modified BETWEEN ? AND ?' => [$start, $end],
            ],
        ];
        if ($params['user_id'] !== null) {
            $query['conditions']['Post.user_id'] = $params['user_id'];
            $query['joins'][] = [
                'type'       => 'LEFT',
                'table'      => $db->fullTableName($this),
                'alias'      => 'Post',
                'conditions' => '`PostShareUser`.`post_id`=`Post`.`id`',
            ];
        }
        $res = $db->buildStatement($query, $this);
        return $res;
    }

    public function getSubQueryFilterGoalPostList(DboSource $db, $goal_id = null, $type = self::TYPE_ACTION, $start = null, $end = null)
    {
        $query = [
            'fields'     => ['Post.id'],
            'table'      => $db->fullTableName($this),
            'alias'      => 'Post',
            'conditions' => [
                'Post.type'    => $type,
                'Post.team_id' => $this->current_team_id,
            ],
        ];
        if ($start && $end) {
            $query['conditions']['Post.modified BETWEEN ? AND ?'] = [$start, $end];
        }
        if ($goal_id) {
            $query['conditions']['Post.goal_id'] = $goal_id;
        }
        if ($this->orgParams['author_id']) {
            $query['conditions']['Post.user_id'] = $this->orgParams['author_id'];
        }
        $res = $db->buildStatement($query, $this);
        return $res;
    }

    public function isGoalPost($post_id)
    {
        $post = $this->find('first', ['conditions' => ['Post.id' => $post_id], 'fields' => ['Post.goal_id']]);
        if (!isset($post['Post']['goal_id']) || !$post['Post']['goal_id']) {
            return false;
        }
        return true;
    }

    public function getSubQueryFilterKrPostList(DboSource $db, $key_result_id, $type, $start = null, $end = null)
    {
        $query = [
            'fields'     => ['Post.id'],
            'table'      => $db->fullTableName($this),
            'alias'      => 'Post',
            'joins'      => [
                [
                    'type'       => 'LEFT',
                    'table'      => $db->fullTableName($this->ActionResult),
                    'alias'      => 'ActionResult',
                    'conditions' => '`ActionResult`.`id`=`Post`.`action_result_id`',
                ],
            ],
            'conditions' => [
                'Post.type'                  => $type,
                'Post.team_id'               => $this->current_team_id,
                'ActionResult.key_result_id' => $key_result_id,
            ],
        ];

        if ($start !== null && $end !== null) {
            $query['conditions']['Post.modified BETWEEN ? AND ?'] = [$start, $end];
        }
        $res = $db->buildStatement($query, $this);
        return $res;
    }

    function getRandomShareCircleNames($data)
    {
        foreach ($data as $key => $val) {
            if (!empty($val['PostShareCircle'])) {
                $circle_list = [];
                foreach ($val['PostShareCircle'] as $circle) {
                    $circle_list[] = $circle['circle_id'];
                }
                $circle_name = $this->PostShareCircle->Circle->getNameRandom($circle_list);
                $data[$key]['share_circle_name'] = $circle_name;
            }
        }
        return $data;
    }

    function getRandomShareUserNames($data)
    {
        foreach ($data as $key => $val) {
            if (!empty($val['PostShareUser'])) {
                $user_list = [];
                foreach ($val['PostShareUser'] as $user) {
                    $user_list[] = $user['user_id'];
                }
                $user_name = $this->User->getNameRandom($user_list);
                $data[$key]['share_user_name'] = $user_name;
            }
        }
        return $data;
    }

    function getShareMode($data)
    {
        foreach ($data as $key => $val) {
            if (!empty($val['PostShareCircle'])) {
                $data[$key]['share_mode'] = self::SHARE_CIRCLE;
            }
            else {
                if (!empty($val['PostShareUser'])) {
                    $data[$key]['share_mode'] = self::SHARE_PEOPLE;
                }
                else {
                    $data[$key]['share_mode'] = self::SHARE_ONLY_ME;
                }
            }
        }
        return $data;
    }

    function getShareMessages($data)
    {
        foreach ($data as $key => $val) {
            $data[$key]['share_text'] = null;
            switch ($val['share_mode']) {
                case self::SHARE_PEOPLE:
                    if (count($val['PostShareUser']) == 1) {
                        $data[$key]['share_text'] = __d('gl', "%sに共有",
                                                        $data[$key]['share_user_name']);
                    }
                    else {
                        $data[$key]['share_text'] = __d('gl', '%1$sと他%2$s人に共有',
                                                        $data[$key]['share_user_name'],
                                                        count($val['PostShareUser']) - 1);
                    }
                    break;
                case self::SHARE_ONLY_ME:
                    //自分だけ
                    $data[$key]['share_text'] = __d('gl', "自分のみ");
                    break;
                case self::SHARE_CIRCLE:
                    //共有ユーザがいない場合
                    if (count($val['PostShareUser']) == 0) {
                        if (count($val['PostShareCircle']) == 1) {
                            $data[$key]['share_text'] = __d('gl', "%sに共有",
                                                            $data[$key]['share_circle_name']);
                        }
                        else {
                            $data[$key]['share_text'] = __d('gl', '%1$s他%2$sサークルに共有',
                                                            $data[$key]['share_circle_name'],
                                                            count($val['PostShareCircle']) - 1);
                        }
                    }
                    //共有ユーザが１人いる場合
                    elseif (count($val['PostShareUser']) == 1) {
                        if (count($val['PostShareCircle']) == 1) {
                            $data[$key]['share_text'] = __d('gl', '%1$sと%2$sに共有',
                                                            $data[$key]['share_circle_name'],
                                                            $data[$key]['share_user_name']);
                        }
                        else {
                            $data[$key]['share_text'] = __d('gl', '%1$sと%2$s他%3$sサークルに共有',
                                                            $data[$key]['share_user_name'],
                                                            $data[$key]['share_circle_name'],
                                                            count($val['PostShareCircle']) - 1);
                        }

                    }
                    //共有ユーザが２人以上いる場合
                    else {
                        if (count($val['PostShareCircle']) == 1) {
                            $data[$key]['share_text'] = __d('gl', '%1$s,%2$sと他%3$s人に共有',
                                                            $data[$key]['share_circle_name'],
                                                            $data[$key]['share_user_name'],
                                                            count($val['PostShareUser']) - 1);
                        }
                        else {
                            $data[$key]['share_text'] = __d('gl', '%1$sと他%2$s人,%3$s他%4$sサークルに共有',
                                                            $data[$key]['share_user_name'],
                                                            count($val['PostShareUser']) - 1,
                                                            $data[$key]['share_circle_name'],
                                                            count($val['PostShareCircle']) - 1);
                        }
                    }

                    break;
            }
        }
        return $data;
    }

    function getCommentMyUnreadCount($data)
    {
        foreach ($data as $key => $val) {
            if ($val['Post']['comment_count'] > 3) {
                $comment_list = [];
                foreach ($val['CommentId'] as $comment_id) {
                    if ($comment_id['user_id'] != $this->my_uid) {
                        $comment_list[] = $comment_id['id'];
                    }
                }
                //未読件数 = 自分以外のコメント数 - 自分以外のコメントの自分の既読数
                $data[$key]['unread_count'] =
                    count($comment_list) - $this->Comment->CommentRead->countMyRead($comment_list);
            }
        }
        return $data;
    }

    /**
     * 投稿の編集
     *
     * @param $data
     *
     * @return bool
     */
    function postEdit($data)
    {
        $this->begin();
        $results = [];

        // 投稿データ保存
        $results[] = $this->save($data);

        // ファイルが添付されている場合
        if ((isset($data['file_id']) && is_array($data['file_id'])) ||
            (isset($data['deleted_file_id']) && is_array($data['deleted_file_id']))
        ) {
            $results[] = $this->PostFile->AttachedFile->updateRelatedFiles(
                $data['Post']['id'],
                AttachedFile::TYPE_MODEL_POST,
                isset($data['file_id']) ? $data['file_id'] : [],
                isset($data['deleted_file_id']) ? $data['deleted_file_id'] : []);
        }

        // どこかでエラーが発生した場合は rollback
        foreach ($results as $r) {
            if (!$r) {
                $this->rollback();
                return false;
            }
        }
        $this->commit();

        // 添付ファイルが存在する場合は一時データを削除
        if (isset($data['file_id']) && is_array($data['file_id'])) {
            $Redis = ClassRegistry::init('GlRedis');
            foreach ($data['file_id'] as $hash) {
                if (!is_numeric($hash)) {
                    $Redis->delPreUploadedFile($this->current_team_id, $this->my_uid, $hash);
                }
            }
        }

        return true;
    }

    /**
     * 投稿の全共有メンバーのリストを返す
     *
     * @param $post_id
     *
     * @return array
     */
    function getShareAllMemberList($post_id)
    {
        $post = $this->findById($post_id);
        if (empty($post)) {
            return [];
        }
        $share_member_list = [];
        //サークル共有ユーザを追加
        $share_member_list = $share_member_list + $this->PostShareCircle->getShareCircleMemberList($post_id);
        //メンバー共有なら
        $share_member_list = $share_member_list + $this->PostShareUser->getShareUserListByPost($post_id);
        //Postの主が自分ではないなら追加
        $posted_user_id = viaIsSet($post['Post']['user_id']);
        if ($this->my_uid != $posted_user_id) {
            $share_member_list[] = $posted_user_id;
        }
        $share_member_list = array_unique($share_member_list);

        //自分自身を除外
        $key = array_search($this->my_uid, $share_member_list);
        if ($key !== false) {
            unset($share_member_list[$key]);
        }
        return $share_member_list;
    }

    /**
     * @param       $type
     * @param       $goal_id
     * @param null  $uid
     * @param bool  $public
     * @param null  $model_id
     * @param array $share
     * @param int   $share_type
     *
     * @return mixed
     * @throws Exception
     */
    function addGoalPost($type, $goal_id, $uid = null, $public = true, $model_id = null, $share = null, $share_type = PostShareCircle::SHARE_TYPE_SHARED)
    {
        if (!$uid) {
            $uid = $this->my_uid;
        }

        $data = [
            'user_id' => $uid,
            'team_id' => $this->current_team_id,
            'type'    => $type,
            'goal_id' => $goal_id,
        ];

        switch ($type) {
            case self::TYPE_ACTION:
                $data['action_result_id'] = $model_id;
                break;
            case self::TYPE_KR_COMPLETE:
                $data['key_result_id'] = $model_id;
                break;
        }
        $res = $this->save($data);
        if ($res) {
            if ($public && $team_all_circle_id = $this->Circle->getTeamAllCircleId()) {
                return $this->PostShareCircle->add($this->getLastInsertID(), [$team_all_circle_id]);
            }
            if ($share) {
                return $this->doShare($this->getLastInsertID(), $share, $share_type);
            }
        }
        return $res;
    }

    function doShare($post_id, $share, $share_type = PostShareCircle::SHARE_TYPE_SHARED)
    {
        if (!$share) {
            return false;
        }
        $share = explode(",", $share);
        $public = false;
        //TODO 近々、ここは「チーム全体」をサークル化する為、この処理はいずれ削除する。
        foreach ($share as $key => $val) {
            if (stristr($val, 'public')) {
                $public = true;
                unset($share[$key]);
            }
        }
        //TODO ここまで
        //ユーザとサークルに分割
        $users = [];
        $circles = [];
        foreach ($share as $val) {
            //ユーザの場合
            if (stristr($val, 'user_')) {
                $users[] = str_replace('user_', '', $val);
            }
            //サークルの場合
            elseif (stristr($val, 'circle_')) {
                $circles[] = str_replace('circle_', '', $val);
            }
        }
        if ($public && $team_all_circle_id = $this->Circle->getTeamAllCircleId()) {
            $circles[] = $team_all_circle_id;
        }
        //共有ユーザ保存
        $this->PostShareUser->add($post_id, $users, null, $share_type);
        //共有サークル保存
        $this->PostShareCircle->add($post_id, $circles, null, $share_type);
        if ($share_type == PostShareCircle::SHARE_TYPE_SHARED) {
            //共有サークル指定されてた場合の未読件数更新
            $this->User->CircleMember->incrementUnreadCount($circles);
            //共有サークル指定されてた場合、更新日時更新
            $this->User->CircleMember->updateModified($circles);
            $this->PostShareCircle->Circle->updateModified($circles);
        }
        return true;
    }

    /**
     * Description : Added a new method for insertion of new public circles in the post table
     *
     * @param      $circle_id
     * @param null $uid
     *
     * @return mixed
     * @throws Exception
     */
    function createCirclePost($circle_id, $uid = null)
    {
        if (!$uid) {
            $uid = $this->my_uid;
        }

        $data = [
            'user_id'   => $uid,
            'team_id'   => $this->current_team_id,
            'type'      => self::TYPE_CREATE_CIRCLE,
            'circle_id' => $circle_id,
        ];
        $res = $this->save($data);
        if ($team_all_circle_id = $this->Circle->getTeamAllCircleId()) {
            $this->PostShareCircle->add($this->getLastInsertID(), [$team_all_circle_id]);
        }
        return $res;
    }

    /**
     * 投稿数のカウントを返却
     *
     * @param mixed  $user_id ユーザーIDもしくは'me'を指定する。
     * @param null   $start_date
     * @param null   $end_date
     * @param string $date_col
     *
     * @return int
     */
    function getCount($user_id = 'me', $start_date = null, $end_date = null, $date_col = 'modified')
    {
        $options = [
            'conditions' => [
                'team_id' => $this->current_team_id,
                'type'    => self::TYPE_NORMAL
            ]
        ];
        // ユーザーIDに'me'が指定された場合は、自分のIDをセットする
        if ($user_id == 'me') {
            $options['conditions']['user_id'] = $this->my_uid;
        }
        elseif ($user_id) {
            $options['conditions']['user_id'] = $user_id;
        }

        //期間で絞り込む
        if ($start_date) {
            $options['conditions']["$date_col >="] = $start_date;
        }
        if ($end_date) {
            $options['conditions']["$date_col <="] = $end_date;
        }
        $res = $this->find('count', $options);
        return $res;
    }

    /**
     * メッセージ数（返信も含める）を返す
     *
     * @param array $params
     *
     * @return int
     */
    function getMessageCount($params = [])
    {
        $params = array_merge(['user_id' => null,
                               'start'   => null,
                               'end'     => null,
                              ], $params);

        $options = [
            'conditions' => [
                'Post.team_id' => $this->current_team_id,
                'Post.type'    => self::TYPE_MESSAGE
            ]
        ];

        if ($params['start'] !== null) {
            $options['conditions']["Post.created >="] = $params['start'];
        }
        if ($params['end'] !== null) {
            $options['conditions']["Post.created <="] = $params['end'];
        }
        if ($params['user_id'] !== null) {
            $options['conditions']["Post.user_id"] = $params['user_id'];
        }
        $count = $this->find('count', $options);
        if (!$count) {
            return 0;
        }

        // ２件目以降のメッセージ
        $options = [
            'conditions' => [
                'Comment.team_id' => $this->current_team_id,
                'Post.type'       => self::TYPE_MESSAGE
            ],
            'contain'    => ['Post'],
        ];

        if ($params['start'] !== null) {
            $options['conditions']["Comment.created >="] = $params['start'];
        }
        if ($params['end'] !== null) {
            $options['conditions']["Comment.created <="] = $params['end'];
        }
        if ($params['user_id'] !== null) {
            $options['conditions']["Comment.user_id"] = $params['user_id'];
        }
        $count += $this->Comment->find('count', $options);
        return $count;
    }

    /**
     * メッセージ（返信も含める）をしたユニークユーザー数を返す
     *
     * @param array $params
     *
     * @return int
     */
    function getMessageUserCount($params = [])
    {
        $params = array_merge(['user_id' => null,
                               'start'   => null,
                               'end'     => null,
                              ], $params);

        $options = [
            'fields'     => [
                'Post.user_id',
                'Post.user_id', // key, value 両方 user_id にする
            ],
            'conditions' => [
                'Post.team_id' => $this->current_team_id,
                'Post.type'    => self::TYPE_MESSAGE
            ]
        ];
        if ($params['start'] !== null) {
            $options['conditions']["Post.created >="] = $params['start'];
        }
        if ($params['end'] !== null) {
            $options['conditions']["Post.created <="] = $params['end'];
        }
        if ($params['user_id'] !== null) {
            $options['conditions']["Post.user_id"] = $params['user_id'];
        }
        $list1 = $this->find('list', $options);

        // ２件目以降のメッセージ
        $options = [
            'fields'     => [
                'Comment.user_id',
                'Comment.user_id', // key, value 両方 user_id にする
            ],
            'conditions' => [
                'Comment.team_id' => $this->current_team_id,
                'Post.type'       => self::TYPE_MESSAGE
            ],
            'contain'    => ['Post'],
        ];
        if ($params['start'] !== null) {
            $options['conditions']["Comment.created >="] = $params['start'];
        }
        if ($params['end'] !== null) {
            $options['conditions']["Comment.created <="] = $params['end'];
        }
        if ($params['user_id'] !== null) {
            $options['conditions']["Comment.user_id"] = $params['user_id'];
        }
        $list2 = $this->Comment->find('list', $options);

        return count(array_unique(array_merge($list1, $list2)));
    }

    /**
     * @param      $post_ids
     * @param bool $only_one
     *
     * @return array|null
     */
    public function getForRed($post_ids, $only_one = false)
    {
        $options = [
            'conditions' => [
                'Post.id'      => $post_ids,
                'Post.team_id' => $this->current_team_id
            ],
            'fields'     => [
                'Post.id'
            ],
            'order'      => [
                'Post.created' => 'desc'
            ],
            'contain'    => [
                'Comment' => [
                    'conditions' => ['Comment.team_id' => $this->current_team_id],
                    'order'      => ['Comment.created' => 'desc'],
                    'limit'      => 3,
                    'fields'     => ['Comment.id']
                ],
            ],
        ];
        if ($only_one) {
            //単独の場合はコメントの件数上限外す
            unset($options['contain']['Comment']['limit']);
        }
        $res = $this->find('all', $options);
        return $res;
    }

    /**
     * execコマンドにて既読処理を行う
     *
     * @param          $post_id
     * @param bool|int $only_one
     */
    public function execRedPostComment($post_id, $only_one = 0)
    {
        $method_name = "red_post_comment";
        $set_web_env = "";
        $nohup = "nohup ";
        $php = "/usr/bin/php ";
        $cake_cmd = $php . APP . "Console" . DS . "cake.php";
        $cake_app = " -app " . APP;
        $cmd = " post {$method_name}";
        $cmd .= " -u " . $this->my_uid;
        $cmd .= " -t " . $this->current_team_id;
        $cmd .= " -p " . base64_encode(serialize($post_id));
        $cmd .= " -o " . $only_one;
        $cmd_end = " > /dev/null &";
        $all_cmd = $set_web_env . $nohup . $cake_cmd . $cake_app . $cmd . $cmd_end;
        exec($all_cmd);
    }

    /**
     * 期間内のいいねの数の合計を取得
     *
     * @param      $user_id
     * @param null $start_date
     * @param null $end_date
     *
     * @return mixed
     */
    public function getLikeCountSumByUserId($user_id, $start_date = null, $end_date = null)
    {
        $options = [
            'fields'     => [
                'SUM(post_like_count) as sum_like',
            ],
            'conditions' => [
                'user_id' => $user_id,
                'team_id' => $this->current_team_id,
                'type'    => [self::TYPE_NORMAL, self::TYPE_ACTION],
            ]
        ];
        //期間で絞り込む
        if ($start_date) {
            $options['conditions']['modified >'] = $start_date;
        }
        if ($end_date) {
            $options['conditions']['modified <'] = $end_date;
        }
        $res = $this->find('first', $options);
        return $res ? $res[0]['sum_like'] : 0;
    }

    public function getMessageList($user_id, $limit = null, $page = null)
    {
        $options = [
            'conditions' => [
                'team_id' => $this->current_team_id,
                'type'    => self::TYPE_MESSAGE,
                'OR'      => [
                    'user_id' => $user_id,
                ],
            ],
            'order'      => [
                'Post.modified' => 'desc',
            ],
            'contain'    => [
                'User',
                'PostShareUser' => [
                    'User',
                    'fields' => ['id', 'user_id']
                ],
                'Comment'       => [
                    'User',
                    'limit' => 1,
                    'order' => [
                        'Comment.created' => 'desc'
                    ]
                ],
            ],
        ];

        $post_id = $this->PostShareUser->getPostIdListByUserId($user_id);
        if (count($post_id) > 0) {
            $options['conditions']['OR']['Post.id'] = $post_id;
        }

        if (is_null($limit) === false) {
            $options['limit'] = $limit;
        }

        if (is_null($page) === false) {
            $options['page'] = $page;
        }

        $res = $this->find('all', $options);

        return $res;
    }

    public function convertData($data)
    {
        $upload = new UploadHelper(new View());
        $time = new TimeExHelper(new View());

        // angularJS 側で通常の（連想配列でない）配列を受け取る必要があるので、
        // 確実に通常の配列になるようにする
        $new_data = [];
        foreach ($data as $item) {
            // 最初のメッセージ作成者のデータ
            $item['PostUser'] = $item['User'];

            // 最後に送信されたメッセージ
            if (empty($item['Comment']) === false) {
                $item['User'] = $item['Comment'][0]['User'];
                $item['Post']['body'] = $item['Comment'][0]['body'];
                $item['Post']['created'] = $item['Comment'][0]['created'];
            }
            $item['Post']['created'] = $time->elapsedTime(h($item['Post']['created']));

            // 最初のメッセージ作成者の画像
            $item['PostUser']['photo_path'] =
                $upload->uploadUrl($item['PostUser'], 'User.photo', ['style' => 'medium_large']);

            // メッセージ受信者の画像
            foreach ($item['PostShareUser'] as $k => $v) {
                $v['User']['photo_path'] = $upload->uploadUrl($v['User'], 'User.photo', ['style' => 'medium_large']);
                $item['PostShareUser'][$k] = $v;
            }
            $new_data[] = $item;
        }

        return $new_data;
    }

    function getFilesOnCircle($circle_id, $page = 1, $limit = null,
                              $start = null, $end = null, $file_type = null)
    {
        $one_month = 60 * 60 * 24 * 31;
        $limit = $limit ? $limit : FILE_LIST_PAGE_NUMBER;
        $start = $start ? $start : REQUEST_TIMESTAMP - $one_month;
        $end = $end ? $end : REQUEST_TIMESTAMP;

        //PostFile,CommentFile,ActionResultFileからfile_idをまず集める
        /**
         * @var AttachedFile $AttachedFile
         */
        $AttachedFile = ClassRegistry::init('AttachedFile');
        $p_ids = $this->PostShareCircle->find('list', [
            'conditions' => [
                'circle_id'                => $circle_id,
                'modified BETWEEN ? AND ?' => [$start, $end],
            ],
            'fields'     => ['post_id', 'post_id']
        ]);
        $c_ids = $this->Comment->find('list', [
            'conditions' => ['post_id' => $p_ids],
            'fields'     => ['id', 'id']
        ]);

        $p_file_ids = $AttachedFile->PostFile->find('list', [
            'conditions' => ['post_id' => $p_ids],
            'fields'     => ['attached_file_id', 'attached_file_id']
        ]);
        $c_file_ids = $AttachedFile->CommentFile->find('list', [
            'conditions' => ['comment_id' => $c_ids],
            'fields'     => ['attached_file_id', 'attached_file_id']
        ]);
        $file_ids = $p_file_ids + $c_file_ids;
        $options = [
            'conditions' => [
                'AttachedFile.id'                    => $file_ids,
                'AttachedFile.display_file_list_flg' => true,
            ],
            'order'      => ['AttachedFile.created desc'],
            'limit'      => $limit,
            'page'       => $page,
            'contain'    => [
                'User'        => [
                    'fields' => $this->User->profileFields,
                ],
                'PostFile'    => [
                    'fields' => ['PostFile.post_id']
                ],
                'CommentFile' => [
                    'fields'  => ['CommentFile.comment_id'],
                    'Comment' => [
                        'fields' => ['Comment.post_id'],
                    ]
                ],
            ]
        ];
        if ($file_type) {
            $options['conditions']['AttachedFile.file_type'] = $AttachedFile->getFileTypeId($file_type);
        }

        $files = $AttachedFile->find('all', $options);
        return $files;
    }

    /**
     * $action_result_id に紐づく投稿を取得
     *
     * @param $action_result_id
     *
     * @return array|null
     */
    public function getByActionResultId($action_result_id)
    {
        $options = [
            'conditions' => [
                'team_id'          => $this->current_team_id,
                'action_result_id' => $action_result_id,
                'type'             => self::TYPE_ACTION,
            ]
        ];
        return $this->find('first', $options);
    }

    /**
     * 投稿したユニークユーザー数を返す
     *
     * @param array $params
     *
     * @return mixed
     */
    public function getUniqueUserCount($params = [])
    {
        $params = array_merge(['start'   => null,
                               'end'     => null,
                               'user_id' => null,
                              ], $params);

        $options = [
            'fields'     => [
                'COUNT(DISTINCT user_id) as cnt',
            ],
            'conditions' => [
                'Post.team_id' => $this->current_team_id,
                'Post.type'    => Post::TYPE_NORMAL,
            ],
        ];
        if ($params['start'] !== null) {
            $options['conditions']["Post.created >="] = $params['start'];
        }
        if ($params['end'] !== null) {
            $options['conditions']["Post.created <="] = $params['end'];
        }
        if ($params['user_id'] !== null) {
            $options['conditions']["Post.user_id"] = $params['user_id'];
        }
        $row = $this->find('first', $options);

        $count = 0;
        if (isset($row[0]['cnt'])) {
            $count = $row[0]['cnt'];
        }
        return $count;
    }

    /**
     * ユーザー別の投稿数ランキングを返す
     *
     * @param array $params
     *
     * @return mixed
     */
    public function getPostCountUserRanking($params = [])
    {
        $params = array_merge(['limit'   => null,
                               'start'   => null,
                               'end'     => null,
                               'user_id' => null,
                              ], $params);

        $options = [
            'fields'     => [
                'Post.user_id',
                'COUNT(*) as cnt',
            ],
            'conditions' => [
                'Post.team_id' => $this->current_team_id,
                'Post.type'    => self::TYPE_NORMAL,
            ],
            'group'      => ['Post.user_id'],
            'order'      => ['cnt' => 'DESC'],
            'limit'      => $params['limit'],
        ];
        if ($params['start'] !== null) {
            $options['conditions']["Post.created >="] = $params['start'];
        }
        if ($params['end'] !== null) {
            $options['conditions']["Post.created <="] = $params['end'];
        }
        if ($params['user_id'] !== null) {
            $options['conditions']['Post.user_id'] = $params['user_id'];
        }
        $rows = $this->find('all', $options);
        $ranking = [];
        foreach ($rows as $v) {
            $ranking[$v['Post']['user_id']] = $v[0]['cnt'];
        }
        return $ranking;
    }

    /**
     * ID指定で複数の投稿を返す
     *
     * @param array $post_ids
     * @param array $params
     *
     * @return array|null
     */
    public function getPostsById($post_ids, $params = [])
    {
        $params = array_merge(['include_action' => null,
                               'include_user'   => null],
                              $params);

        $options = [
            'conditions' => [
                'Post.team_id' => $this->current_team_id,
                'Post.id'      => $post_ids,
            ],
            'contain'    => []
        ];
        if ($params['include_action']) {
            $options['contain'][] = 'ActionResult';
        }
        if ($params['include_user']) {
            $options['contain'][] = 'User';
        }
        return $this->find('all', $options);
    }
}
