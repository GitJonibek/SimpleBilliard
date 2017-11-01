<?php
App::uses('AppModel', 'Model');

/**
 * Class PricePlanPurchaseTeam
 *
 * Teams that purchased the campaigns plan
 * 料金プランを購入したチーム
 */
class PricePlanPurchaseTeam extends AppModel
{
    public $useTable = 'price_plan_purchase_teams';

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = [
        'price_plan_id' => [
            'numeric'       => ['rule' => ['numeric'],],
            'notBlank'      => [
                'required' => 'create',
                'rule'     => 'notBlank',
            ],
        ]
    ];

    /**
     * Returns true if the team has purchased a campaign plan
     *
     * @param int $teamId
     *
     * @return bool
     */
    function purchased(int $teamId): bool
    {
        $purchasedPlan = $this->getByTeamId($teamId, ['id']);
        if (!empty($purchasedPlan)) {
            return true;
        }
        return false;
    }
}

