<?php
App::uses('AppModel', 'Model');

/**
 * Thread Model
 *
 * @property User    $FromUser
 * @property User    $ToUser
 * @property Team    $Team
 * @property Message $Message
 */
class Thread extends AppModel
{
    /**
     * スレッドタイプ
     */
    const TYPE_CREATED_GOAL = 1;
    const TYPE_FEEDBACK = 2;
    static public $TYPE = [
        self::TYPE_CREATED_GOAL => "",
        self::TYPE_FEEDBACK     => "",
    ];

    /**
     * スレッドタイプの名前をセット
     */
    private function _setTypeName()
    {
        self::$TYPE[self::TYPE_CREATED_GOAL] = __("Create a goal");
        self::$TYPE[self::TYPE_FEEDBACK] = __("Feedback");
    }

    /**
     * ステータス
     */
    const STATUS_OPEN = 1;
    const STATUS_CLOSE = 2;
    static public $STATUS = [
        self::STATUS_OPEN  => "",
        self::STATUS_CLOSE => "",
    ];

    /**
     * ステータスの名前をセット
     */
    private function _setStatusName()
    {
        //TODO 仮
        self::$STATUS[self::STATUS_OPEN] = __("Open");
        self::$STATUS[self::STATUS_CLOSE] = __("Closed");

    }

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        'type'    => ['numeric' => ['rule' => ['numeric'],],],
        'status'  => ['numeric' => ['rule' => ['numeric'],],],
        'name'    => ['notEmpty' => ['rule' => ['notEmpty'],],],
        'del_flg' => ['boolean' => ['rule' => ['boolean'],],],
    ];

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        'FromUser' => ['className' => 'User', 'foreignKey' => 'from_user_id',],
        'ToUser'   => ['className' => 'User', 'foreignKey' => 'to_user_id',],
        'Team',
    ];

    /**
     * hasMany associations
     *
     * @var array
     */
    public $hasMany = [
        'Message',
    ];

    function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->_setTypeName();
        $this->_setStatusName();
    }

}
