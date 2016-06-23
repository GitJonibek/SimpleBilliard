<?php App::uses('CakeTestFixtureEx', 'Test/Fixture');

/**
 * CollaboratorFixture
 */
class CollaboratorFixture extends CakeTestFixtureEx
{

    /**
     * Fields
     *
     * @var array
     */
    public $fields = array(
        'id'              => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary', 'comment' => 'コラボレータID'),
        'team_id'         => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'index', 'comment' => 'チームID(belongsToでTeamモデルに関連)'),
        'goal_id'         => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'index', 'comment' => 'ゴールID(belongsToでGoalモデルに関連)'),
        'user_id'         => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'index', 'comment' => 'ユーザID(belongsToでUserモデルに関連)'),
        'type'            => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'タイプ(0 = コラボレータ,1 = リーダー)'),
        'role'            => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '役割', 'charset' => 'utf8'),
        'description'     => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '説明', 'charset' => 'utf8'),
        'priority'        => array('type' => 'integer', 'null' => false, 'default' => '3', 'unsigned' => false, 'comment' => '重要度(1〜5)'),
        'valued_flg'      => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => '価値フラグ(0 = 処理前,1 = 承認, 2 = 保留,3 = 修正依頼, 4 = 差し戻し)'),
        'del_flg'         => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '削除フラグ'),
        'deleted'         => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true, 'comment' => '削除した日付時刻'),
        'created'         => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true, 'key' => 'index', 'comment' => '追加した日付時刻'),
        'modified'        => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true, 'comment' => '更新した日付時刻'),
        'indexes'         => array(
            'PRIMARY' => array('column' => 'id', 'unique' => 1),
            'team_id' => array('column' => 'team_id', 'unique' => 0),
            'user_id' => array('column' => 'user_id', 'unique' => 0),
            'goal_id' => array('column' => 'goal_id', 'unique' => 0),
            'created' => array('column' => 'created', 'unique' => 0)
        ),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id'          => 1,
            'team_id'     => 1,
            'goal_id'     => 1,
            'user_id'     => 1,
            'type'        => 1,
            'role'        => 'test',
            'description' => 'test',
        ],
        [
            'id'          => 2,
            'team_id'     => 1,
            'goal_id'     => 1,
            'user_id'     => 2,
            'type'        => 0,
            'role'        => 'test',
            'description' => 'test',
        ],
        [
            'id'          => 3,
            'team_id'     => 1,
            'goal_id'     => 7,
            'user_id'     => 1,
            'type'        => 0,
            'role'        => 'test',
            'description' => 'test',
        ],
        [
            'id'          => 4,
            'team_id'     => 1,
            'goal_id'     => 9,
            'user_id'     => 2,
            'type'        => 1,
            'role'        => 'test',
            'description' => 'test',
        ],
    ];

}
