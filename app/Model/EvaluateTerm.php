<?php
App::uses('AppModel', 'Model');

/**
 * EvaluateTerm Model
 *
 * @property Team       $Team
 * @property Evaluation $Evaluation
 * @property Evaluator  $Evaluator
 */
class EvaluateTerm extends AppModel
{
    const STATUS_EVAL_NOT_STARTED = 0;
    const STATUS_EVAL_IN_PROGRESS = 1;
    const STATUS_EVAL_FROZEN = 2;
    const STATUS_EVAL_FINISHED = 3;
    const TYPE_CURRENT = 0;
    const TYPE_PREVIOUS = 1;
    const TYPE_NEXT = 2;
    static private $TYPE = [
        self::TYPE_CURRENT,
        self::TYPE_PREVIOUS,
        self::TYPE_NEXT,
    ];

    private $previous_term = [];
    private $current_term = [];
    private $next_term = [];
    private $latest_term = [];

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
        'Team',
    ];

    /**
     * hasMany associations
     *
     * @var array
     */
    public $hasMany = [
        'Evaluation',
        'Evaluator',
    ];

    function getAllTerm($order_desc = true)
    {
        $options = [
            'conditions' => [
                'team_id' => $this->current_team_id
            ],
            'order'      => [
                'start_date' => 'asc'
            ]
        ];
        if ($order_desc) {
            $options['order']['start_date'] = 'desc';
        }
        $res = $this->find('all', $options);
        $res = Hash::combine($res, '{n}.EvaluateTerm.id', '{n}.EvaluateTerm');
        return $res;
    }

    function changeToInProgress($id)
    {
        $this->id = $id;
        return $this->saveField('evaluate_status', self::STATUS_EVAL_IN_PROGRESS);
    }

    /**
     * @param $id
     *
     * @return bool
     */
    function isAbleToStartEvaluation($id)
    {
        $options = [
            'conditions' => [
                'id'              => $id,
                'team_id'         => $this->current_team_id,
                'evaluate_status' => self::STATUS_EVAL_NOT_STARTED,
            ],
        ];
        $res = $this->find('first', $options);
        return (bool)$res;
    }

    function isStartedEvaluation($id)
    {
        $options = [
            'conditions' => [
                'id'      => $id,
                'team_id' => $this->current_team_id,
                'NOT'     => [
                    'evaluate_status' => self::STATUS_EVAL_NOT_STARTED,
                ]
            ]
        ];
        $res = $this->find('first', $options);
        return (bool)$res;
    }

    function changeFreezeStatus($id)
    {
        // Check freezable
        $options = [
            'conditions' => [
                'id'      => $id,
                'team_id' => $this->current_team_id,
            ]
        ];
        $res = $this->find('first', $options);
        if (empty($res)) {
            throw new RuntimeException(__d('gl', "この期間は凍結できません。"));
        }

        $isFrozen = $this->checkFrozenEvaluateTerm($id);
        if ($isFrozen) {
            $expect_status = self::STATUS_EVAL_IN_PROGRESS;
        }
        else {
            $expect_status = self::STATUS_EVAL_FROZEN;
        }

        $this->id = $id;
        $saveData = ['evaluate_status' => $expect_status];
        $res = $this->save($saveData);
        return $res;
    }

    function checkFrozenEvaluateTerm($id)
    {
        $options = [
            'conditions' => [
                'id'              => $id,
                'team_id'         => $this->current_team_id,
                'evaluate_status' => self::STATUS_EVAL_FROZEN
            ]
        ];
        $res = $this->find('first', $options);
        return (empty($res)) ? false : true;
    }

    /**
     * is available type? true or RuntimeException
     *
     * @param $type
     *
     * @return bool
     */
    private function _checkType($type)
    {
        if (!in_array($type, self::$TYPE)) {
            throw new RuntimeException("invalid type!");
        }
        return true;
    }

    /**
     * return term data
     *
     * @param $type
     *
     * @return array|null
     */
    public function getTermData($type)
    {
        $this->_checkType($type);
        if (!$this->current_term) {
            $this->current_term = $this->_getTermByDatetime(REQUEST_TIMESTAMP);
        }

        if ($type === self::TYPE_PREVIOUS) {
            if ($this->previous_term) {
                return $this->previous_term;
            }
            if (isset($this->current_term['start_date']) && !empty($this->current_term['start_date'])) {
                $this->previous_term = $this->_getTermByDatetime(strtotime("-1 day",
                                                                           $this->current_term['start_date']));
            }
            return $this->previous_term;
        }

        if ($type === self::TYPE_NEXT) {
            if ($this->next_term) {
                return $this->next_term;
            }
            if (isset($this->current_term['end_date']) && !empty($this->current_term['end_date'])) {
                $this->next_term = $this->_getTermByDatetime(strtotime("+1 day", $this->current_term['end_date']));
            }
            return $this->next_term;
        }

        return $this->current_term;
    }

    /**
     * @param $type
     *
     * @return null| int
     */
    public function getTermId($type)
    {
        $this->_checkType($type);
        $term = $this->getTermData($type);
        return viaIsSet($term['id']);
    }

    /**
     * @param $type
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function addTermData($type)
    {
        $this->_checkType($type);
        $new_start = null;
        $new_end = null;

        if ($type === self::TYPE_PREVIOUS) {
            if ($this->getTermData(self::TYPE_PREVIOUS)) {
                return false;
            }
            if (!$current = $this->getTermData(self::TYPE_CURRENT)) {
                return false;
            }
            $new_start = $this->_getStartEndWithoutExistsData(strtotime("-1 day", $current['start_date']))['start'];
            $new_end = $current['end_date'] - 1;
        }

        if ($type === self::TYPE_CURRENT) {
            if ($this->getTermData(self::TYPE_CURRENT)) {
                return false;
            }
            $new = $this->_getStartEndWithoutExistsData();
            $new_start = $new['start'];
            $new_end = $new['end'];
        }

        if ($type === self::TYPE_NEXT) {
            if ($this->getTermData(self::TYPE_NEXT)) {
                return false;
            }
            if (!$current = $this->getTermData(self::TYPE_CURRENT)) {
                return false;
            }
            $new_start = $current['end_date'] + 1;
            $new_end = $this->_getStartEndWithoutExistsData(strtotime("+1 day", $current['end_date']))['end'];
        }

        $team = $this->Team->getCurrentTeam();
        $data = [
            'start_date' => $new_start,
            'end_date'   => $new_end,
            'timezone'   => $team['Team']['timezone'],
            'team_id'    => $team['Team']['id'],
        ];
        $this->create();
        $res = $this->save($data);
        return $res;
    }

    /**
     * @param $id
     * @param $type
     * @param $start_term_month
     * @param $border_months
     * @param $timezone
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function updateTermData($id, $type, $start_term_month, $border_months, $timezone)
    {
        $this->_checkType($type);
        if ($type === self::TYPE_PREVIOUS) {
            return false;
        }

        $new_start = null;
        $new_end = null;
        $target_date = null;

        if ($type === self::TYPE_CURRENT) {
            if ($previous = $this->getTermData(self::TYPE_PREVIOUS)) {
                $new_start = $previous['end_date'] + 1;
                $target_date = strtotime("+1 day", $new_start);
            }
            else {
                $target_date = REQUEST_TIMESTAMP;
            }
        }

        if ($type === self::TYPE_NEXT) {
            if (!$current = $this->getTermData(self::TYPE_CURRENT)) {
                return false;
            }
            $new_start = $current['end_date'] + 1;
            $target_date = strtotime("+1 day", $new_start);
        }

        $new_term = $this->_getStartEndWithoutExistsData($target_date, $start_term_month, $border_months, $timezone);
        if (!$new_start) {
            $new_start = $new_term['start'];
        }
        $new_end = $new_term['end'];
        $this->id = $id;
        $res = $this->save(
            [
                'start_date' => $new_start,
                'end_date'   => $new_end,
                'time_zone'  => $timezone
            ]
        );
        return $res;
    }

    /**
     * reset term only property, not delete data
     *
     * @param $type
     */
    public function resetTermProperty($type)
    {
        $this->_checkType($type);
        if ($type === self::TYPE_CURRENT) {
            $this->current_term = null;
        }
        if ($type === self::TYPE_NEXT) {
            $this->next_term = null;
        }
        if ($type === self::TYPE_PREVIOUS) {
            $this->previous_term = null;
        }
    }

    /**
     * return new start date and end date calculated
     *
     * @param int  $target_date
     * @param null $start_term_month
     * @param null $border_months
     * @param null $timezone
     *
     * @return null|array
     */
    private function _getStartEndWithoutExistsData($target_date = REQUEST_TIMESTAMP,
                                                   $start_term_month = null,
                                                   $border_months = null,
                                                   $timezone = null)
    {
        $team = $this->Team->getCurrentTeam();
        if (empty($team)) {
            return null;
        }
        if (!$start_term_month) {
            $start_term_month = $team['Team']['start_term_month'];
        }
        if (!$border_months) {
            $border_months = $team['Team']['border_months'];
        }
        if (!$timezone) {
            $timezone = $team['Team']['timezone'];
        }

        $start_date = strtotime(date("Y-{$start_term_month}-1", $target_date));
        $start_date_tmp = date("Y-m-1", $start_date);
        $end_date = strtotime($start_date_tmp . "+ {$border_months} month");

        //指定日時が期間内の場合 in the case of target date include the term
        if ($start_date <= $target_date && $end_date > $target_date) {
            $term['start'] = $start_date - $timezone * 3600;
            $term['end'] = $end_date - 1 - $timezone * 3600;
            return $term;
        }
        //指定日時が開始日より前の場合 in the case of target date is earlier than start date
        elseif ($target_date < $start_date) {
            while ($target_date < $start_date) {
                $start_date_tmp = date("Y-m-1", $start_date);
                $start_date = strtotime($start_date_tmp . "- {$border_months} month");
            }
            $term['start'] = $start_date - $timezone * 3600;
            $start_date_tmp = date("Y-m-1", $start_date);
            $term['end'] = strtotime($start_date_tmp . "+ {$border_months} month") - $timezone * 3600 - 1;
            return $term;
        }
        //終了日が指定日時より前の場合 in the case of target date is later than end date
        elseif ($target_date > $end_date) {
            while ($target_date > $end_date) {
                $end_date_tmp = date("Y-m-1", $end_date);
                $end_date = strtotime($end_date_tmp . "+ {$border_months} month");
            }
            $term['end'] = $end_date - 1 - $timezone * 3600;
            $end_date_tmp = date("Y-m-1", $end_date);
            $term['start'] = strtotime($end_date_tmp . "- {$border_months} month") - $timezone * 3600;
            return $term;
        }
    }

    /**
     * return term data from datetime
     *
     * @param int $datetime unixtime
     *
     * @return array|null
     */
    private function _getTermByDatetime($datetime = REQUEST_TIMESTAMP)
    {
        $options = [
            'conditions' => [
                'start_date <=' => $datetime,
                'end_date >='   => $datetime,
            ]
        ];
        $res = $this->find('first', $options);
        $res = Hash::extract($res, 'EvaluateTerm');
        return $res;
    }
}
