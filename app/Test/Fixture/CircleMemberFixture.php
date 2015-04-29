<?php

/**
 * CircleMemberFixture

 */
class CircleMemberFixture extends CakeTestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    public $fields = array(
        'id'                    => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary', 'comment' => 'サークルメンバーID'),
        'circle_id'             => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'index', 'comment' => 'サークルID(belongsToでCircleモデルに関連)'),
        'team_id'               => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'index', 'comment' => 'チームID(belongsToでTeamモデルに関連)'),
        'user_id'               => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'index', 'comment' => 'ユーザID(belongsToでUserモデルに関連)'),
        'admin_flg'             => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index', 'comment' => '管理者フラグ'),
        'unread_count'          => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => true, 'comment' => '未読数'),
        'show_for_all_feed_flg' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => 'オールフィード表示フラグ'),
        'del_flg'               => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index', 'comment' => '削除フラグ'),
        'deleted'               => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true, 'comment' => '部署を削除した日付時刻'),
        'created'               => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true, 'comment' => '部署を追加した日付時刻'),
        'modified'              => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true, 'comment' => '部署を更新した日付時刻'),
        'indexes'               => array(
            'PRIMARY'   => array('column' => 'id', 'unique' => 1),
            'team_id'   => array('column' => 'team_id', 'unique' => 0),
            'circle_id' => array('column' => 'circle_id', 'unique' => 0),
            'user_id'   => array('column' => 'user_id', 'unique' => 0),
            'del_flg'   => array('column' => 'del_flg', 'unique' => 0),
            'admin_flg' => array('column' => 'admin_flg', 'unique' => 0)
        ),
        'tableParameters'       => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );
    /**
     * Records
     *
     * @var array
     */
    public $records = array(
        array(
            'id'           => 1,
            'circle_id'    => 1,
            'team_id'      => 1,
            'user_id'      => 1,
            'admin_flg'    => 1,
            'unread_count' => 1,
            'del_flg'      => 0,
            'deleted'      => null,
            'created'      => 1,
            'modified'     => 1
        ),
        array(
            'id'           => 2,
            'circle_id'    => 2,
            'team_id'      => 1,
            'user_id'      => 1,
            'admin_flg'    => 0,
            'unread_count' => 1,
            'del_flg'      => 0,
            'deleted'      => null,
            'created'      => 1,
            'modified'     => 1
        ),
        array(
            'id'           => 3,
            'circle_id'    => 1,
            'team_id'      => 1,
            'user_id'      => 2,
            'admin_flg'    => 0,
            'unread_count' => 0,
            'del_flg'      => 0,
            'deleted'      => null,
            'created'      => 1,
            'modified'     => 1
        ),
        array(
            'id'           => 4,
            'circle_id'    => 1,
            'team_id'      => 1,
            'user_id'      => 12,
            'admin_flg'    => 0,
            'unread_count' => 0,
            'del_flg'      => 0,
            'deleted'      => null,
            'created'      => 1,
            'modified'     => 1
        ),
        array(
            'id'           => '',
            'circle_id'    => '',
            'team_id'      => '',
            'user_id'      => '',
            'admin_flg'    => 1,
            'unread_count' => 0,
            'del_flg'      => 1,
            'deleted'      => 5,
            'created'      => 5,
            'modified'     => 5
        ),
        array(
            'id'           => '',
            'circle_id'    => '',
            'team_id'      => '',
            'user_id'      => '',
            'admin_flg'    => 1,
            'unread_count' => 0,
            'del_flg'      => 1,
            'deleted'      => 6,
            'created'      => 6,
            'modified'     => 6
        ),
        array(
            'id'           => '',
            'circle_id'    => '',
            'team_id'      => '',
            'user_id'      => '',
            'admin_flg'    => 1,
            'unread_count' => 0,
            'del_flg'      => 1,
            'deleted'      => 7,
            'created'      => 7,
            'modified'     => 7
        ),
        array(
            'id'           => '',
            'circle_id'    => '',
            'team_id'      => '',
            'user_id'      => '',
            'admin_flg'    => 1,
            'unread_count' => 0,
            'del_flg'      => 1,
            'deleted'      => 8,
            'created'      => 8,
            'modified'     => 8
        ),
        array(
            'id'           => '',
            'circle_id'    => '',
            'team_id'      => '',
            'user_id'      => '',
            'admin_flg'    => 1,
            'unread_count' => 0,
            'del_flg'      => 1,
            'deleted'      => 9,
            'created'      => 9,
            'modified'     => 9
        ),
        array(
            'id'           => '',
            'circle_id'    => '',
            'team_id'      => '',
            'user_id'      => '',
            'admin_flg'    => 1,
            'unread_count' => 0,
            'del_flg'      => 1,
            'deleted'      => 10,
            'created'      => 10,
            'modified'     => 10
        ),
    );

}
