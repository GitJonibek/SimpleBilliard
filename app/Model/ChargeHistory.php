<?php
App::uses('AppModel', 'Model');

/**
 * ChargeHistory Model
 */
class ChargeHistory extends AppModel
{
    const TRANSACTION_RESULT_ERROR = 0;
    const TRANSACTION_RESULT_SUCCESS = 1;
    const TRANSACTION_RESULT_FAIL = 2;

    const PAYMENT_TYPE_INVOICE = 0;
    const PAYMENT_TYPE_CREDIT_CARD = 1;

    const CHARGE_TYPE_MONTHLY = 0;
    const CHARGE_TYPE_ADD_USER = 1;
    const CHARGE_TYPE_ACTIVATE_USER = 2;

    /* Validation rules
    *
    * @var array
    */
    public $validate = [
        'team_id'          => [
            'numeric'  => [
                'rule' => ['numeric'],
            ],
            'notBlank' => [
                'required' => true,
                'rule'     => 'notBlank',
            ],
        ],
        'payment_type'     => [
            'inList'   => [
                'rule' => [
                    'inList',
                    [
                        PaymentSetting::PAYMENT_TYPE_INVOICE,
                        PaymentSetting::PAYMENT_TYPE_CREDIT_CARD
                    ]
                ],
            ],
            'notBlank' => [
                'required' => true,
                'rule'     => 'notBlank',
            ],
        ],
        'charge_type'      => [
            'inList'   => [
                'rule' => [
                    'inList',
                    [
                        PaymentSetting::CHARGE_TYPE_MONTHLY_FEE,
                        PaymentSetting::CHARGE_TYPE_USER_INCREMENT_FEE,
                        PaymentSetting::CHARGE_TYPE_USER_ACTIVATION_FEE
                    ]
                ],
            ],
            'notBlank' => [
                'required' => true,
                'rule'     => 'notBlank',
            ],
        ],
        'amount_per_user'  => [
            'numeric'  => [
                'rule' => ['numeric'],
            ],
            'notBlank' => [
                'required' => true,
                'rule'     => 'notBlank',
            ],
        ],
        'total_amount'     => [
            'numeric'  => [
                'rule' => ['numeric'],
            ],
            'notBlank' => [
                'required' => true,
                'rule'     => 'notBlank',
            ],
        ],
        'tax'              => [
            'numeric'  => [
                'rule' => ['numeric'],
            ],
            'notBlank' => [
                'required' => true,
                'rule'     => 'notBlank',
            ],
        ],
        'charge_users'     => [
            'numeric'  => [
                'rule' => ['numeric'],
            ],
            'notBlank' => [
                'required' => true,
                'rule'     => 'notBlank',
            ],
        ],
        'currency'         => [
            'inList'   => [
                'rule' => [
                    'inList',
                    [
                        PaymentSetting::CURRENCY_TYPE_JPY,
                        PaymentSetting::CURRENCY_TYPE_USD
                    ]
                ],
            ],
            'notBlank' => [
                'required' => true,
                'rule'     => 'notBlank',
            ],
        ],
        'charge_datetime'  => [
            'numeric'  => [
                'rule' => ['numeric'],
            ],
            'notBlank' => [
                'required' => true,
                'rule'     => 'notBlank',
            ],
        ],
        'result_type'      => [
            'numeric'  => [
                'rule' => ['numeric'],
            ],
            'notBlank' => [
                'required' => true,
                'rule'     => 'notBlank',
            ],
        ],
        'max_charge_users' => [
            'numeric' => [
                'rule' => ['numeric'],
            ],
        ],
    ];

    /**
     * Get latest max charge users
     *
     * @return int
     */
    function getLatestMaxChargeUsers(): int
    {
        $res = $this->find('first', [
                'fields'     => ['max_charge_users'],
                'conditions' => [
                    'team_id' => $this->current_team_id,
                ],
                'order'      => ['id' => 'DESC'],
            ]
        );
        return (int)Hash::get($res, 'ChargeHistory.max_charge_users');
    }

    /**
     * Filter: team_id and charge date(Y-m-d 00:00:00　〜　Y-m-d 23:59:59)
     *
     * @param int    $teamId
     * @param string $date
     *
     * @return array
     */
    public function getByChargeDate(int $teamId, string $date): array
    {
        $dateStart = AppUtil::getStartTimestampByTimezone($date);
        $dateEnd = AppUtil::getEndTimestampByTimezone($date);
        $options = [
            'fields'     => [
                'id',
                'charge_datetime'
            ],
            'conditions' => [
                'team_id'            => $teamId,
                'charge_datetime >=' => $dateStart,
                'charge_datetime <=' => $dateEnd,
                'del_flg'            => false
            ],
        ];
        return $this->find('first', $options);
    }

    /**
     * @param int $teamId
     * @param int $startTs
     * @param int $endTs
     *
     * @return array
     */
    public function findForInvoiceByStartEnd(int $teamId, int $startTs, int $endTs)
    {
        $options = [
            'conditions' => [
                'team_id'            => $teamId,
                'payment_type'       => self::PAYMENT_TYPE_INVOICE,
                'charge_datetime >=' => $startTs,
                'charge_datetime <=' => $endTs,
            ]
        ];

        $res = $this->find('all', $options);
        return Hash::extract($res, '{n}.ChargeHistory');
    }

    /**
     * @param int $teamId
     * @param int $subTotalCharge
     * @param int $tax
     * @param int $amountPerUser
     * @param int $usersCount
     *
     * @return mixed
     */
    public function addInvoiceCharge(int $teamId, int $subTotalCharge, int $tax, int $amountPerUser, int $usersCount)
    {
        $historyData = [
            'team_id'          => $teamId,
            'payment_type'     => PaymentSetting::PAYMENT_TYPE_INVOICE,
            'charge_type'      => self::CHARGE_TYPE_MONTHLY,
            'amount_per_user'  => $amountPerUser,
            'total_amount'     => $subTotalCharge,
            'tax'              => $tax,
            'charge_users'     => $usersCount,
            'currency'         => 1, // TODO: fix it.
            'charge_datetime'  => time(),
            'result_type'      => self::TRANSACTION_RESULT_SUCCESS,
            'max_charge_users' => $usersCount
        ];
        $ret = $this->save($historyData);
        $ret = Hash::extract($ret, 'ChargeHistory');
        return $ret;
    }

}
