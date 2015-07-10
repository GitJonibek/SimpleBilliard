<?php
App::uses('AppModel', 'Model');

/**
 * PostShareCircle Model
 *
 * @property Post   $Post
 * @property Circle $Circle
 * @property Team   $Team
 */
class PostShareCircle extends AppModel
{
    //そのユーザのALLフィード、サークルページ両方に表示される
    const SHARE_TYPE_SHARED = 0;
    //そのユーザのALLフィードのみに表示される。サークルページには表示されない
    const SHARE_TYPE_ONLY_NOTIFY = 1;

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        'del_flg' => [
            'boolean' => [
                'rule' => ['boolean'],
            ],
        ],
    ];

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        'Post',
        'Circle',
        'Team',
    ];

    public function add($post_id, $circles, $team_id = null)
    {
        if (empty($circles)) {
            return false;
        }
        if (!$team_id) {
            $team_id = $this->current_team_id;
        }
        $data = [];
        foreach ($circles as $circle_id) {
            $data[] = [
                'circle_id' => $circle_id,
                'post_id'   => $post_id,
                'team_id'   => $team_id,
            ];
        }
        return $this->saveAll($data);

    }

    public function getMyCirclePostList($start, $end, $order = "modified", $order_direction = "desc", $limit = 1000, $my_circle_list = null)
    {
        if (!$my_circle_list) {
            $my_circle_list = $this->Circle->CircleMember->getMyCircleList(true);
        }
        $backupPrimaryKey = $this->primaryKey;
        $this->primaryKey = 'post_id';
        $options = [
            'conditions' => [
                'circle_id'                => $my_circle_list,
                'team_id'                  => $this->current_team_id,
                'modified BETWEEN ? AND ?' => [$start, $end],
            ],
            'order'      => [$order => $order_direction],
            'limit'      => $limit,
            'fields'     => ['post_id'],
        ];
        $res = $this->find('list', $options);
        $this->primaryKey = $backupPrimaryKey;
        return $res;
    }

    public function isMyCirclePost($post_id)
    {
        $my_circle_list = $this->Circle->CircleMember->getMyCircleList();
        $backupPrimaryKey = $this->primaryKey;
        $this->primaryKey = 'post_id';
        $options = [
            'conditions' => [
                'post_id'   => $post_id,
                'circle_id' => $my_circle_list,
                'team_id'   => $this->current_team_id,
            ],
            'fields'     => ['post_id'],
        ];
        $res = $this->find('list', $options);
        $this->primaryKey = $backupPrimaryKey;
        if (!empty($res)) {
            return true;
        }
        return false;
    }

    public function getShareCirclesAndMembers($post_id)
    {
        $circle_list = $this->getShareCircleList($post_id);
        $res = $this->Circle->getCirclesAndMemberById($circle_list);
        return $res;
    }

    public function getShareCircleList($post_id)
    {
        $options = [
            'conditions' => [
                'PostShareCircle.post_id' => $post_id,
                'PostShareCircle.team_id' => $this->current_team_id,
            ],
            'fields'     => [
                'PostShareCircle.circle_id',
            ],
        ];
        $res = $this->find('list', $options);
        return $res;
    }

    public function getShareCircleMemberList($post_id)
    {
        $circle_list = $this->getShareCircleList($post_id);
        $res = $this->Circle->CircleMember->getMemberList($circle_list, true);
        return $res;
    }

}
