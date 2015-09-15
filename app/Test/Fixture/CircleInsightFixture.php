<?php

/**
 * CircleInsightFixture
 */
class CircleInsightFixture extends CakeTestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    public $fields = array(
        'id'              => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
        'team_id'         => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'index'),
        'target_date'     => array('type' => 'date', 'null' => true, 'default' => null),
        'timezone'        => array('type' => 'float', 'null' => true, 'default' => null, 'unsigned' => false),
        'circle_id'       => array('type' => 'biginteger', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'index'),
        'user_count'      => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 10, 'unsigned' => true),
        'del_flg'         => array('type' => 'boolean', 'null' => false, 'default' => '0'),
        'deleted'         => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true),
        'created'         => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true),
        'modified'        => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => true),
        'indexes'         => array(
            'PRIMARY'             => array('column' => 'id', 'unique' => 1),
            'team_id_target_date' => array('column' => array('team_id', 'target_date'), 'unique' => 0),
            'circle_id'           => array('column' => 'circle_id', 'unique' => 0)
        ),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    /**
     * Records
     *
     * @var array
     */
    public $records = [];

}
