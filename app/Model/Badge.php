<?php
App::uses('AppModel', 'Model');

/**
 * Badge Model
 *
 * @property User $User
 * @property Team $Team
 */
class Badge extends AppModel
{
    /**
     * タイプ
     */
    const TYPE_PRAISE = 1;
    const TYPE_SKILL = 2;
    static public $TYPE = [null => "", self::TYPE_PRAISE => "", self::TYPE_SKILL => ""];

    /**
     * タイプの名前をセット
     */
    private function _setTypeName()
    {
        self::$TYPE[null] = __d\('app', "選択してください");
        self::$TYPE[self::TYPE_PRAISE] = __d\('app', "賞賛");
        self::$TYPE[self::TYPE_SKILL] = __d\('app', "スキル");
    }

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        'name'       => ['notEmpty' => ['rule' => ['notEmpty']]],
        'active_flg' => ['boolean' => ['rule' => ['boolean']]],
        'del_flg'    => ['boolean' => ['rule' => ['boolean']]],
    ];

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = [
        'User',
        'Team',
    ];

    function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->_setTypeName();
    }

}
