<?php
App::uses('AppModel', 'Model');

/**
 * PostLike Model
 *
 * @property Post $Post
 * @property User $User
 * @property Team $Team
 */
class PostLike extends AppModel
{

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        'post_id' => ['uuid' => ['rule' => ['uuid'],],],
        'user_id' => ['uuid' => ['rule' => ['uuid'],],],
        'team_id' => ['uuid' => ['rule' => ['uuid'],],],
        'del_flg' => ['boolean' => ['rule' => ['boolean'],],],
    ];

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        'Post',
        'User',
        'Team',
    ];
}
