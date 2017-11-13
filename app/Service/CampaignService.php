<?php
App::import('Service', 'AppService');
App::uses('CampaignTeam', 'Model');
App::uses('CampaignPricePlan', 'Model');
App::uses('CampaignPriceGroup', 'Model');
App::uses('TeamMember', 'Model');
App::import('Service', 'PaymentService');

use Goalous\Model\Enum as Enum;

/**
 * Class CampaignService
 */
class CampaignService extends AppService
{
    // Cached campaign price plans each group id
    private $cache_plans = [];

    /**
     * Return true if the team is entitled to monthly campaign
     *
     * @param int $teamId
     *
     * @return bool
     */
    function isCampaignTeam(int $teamId): bool
    {
        /** @var CampaignTeam $CampaignTeam */
        $CampaignTeam = ClassRegistry::init('CampaignTeam');

        return $CampaignTeam->isCampaignTeam($teamId);
    }

    /**
     * Get Campaign assigned to the team.
     *
     * @param int   $teamId
     * @param array $fields
     *
     * @return array
     */
    function getCampaignTeam(int $teamId, array $fields = []): array
    {
        /** @var CampaignTeam $CampaignTeam */
        $CampaignTeam = ClassRegistry::init('CampaignTeam');

        return $CampaignTeam->getByTeamId($teamId, $fields);
    }

    /**
     * Get Price plan information for campaign team
     *
     * @param int $teamId
     *
     * @return array
     */
    function getPricePlanPurchaseTeam(int $teamId): array
    {
        /** @var PricePlanPurchaseTeam $PricePlanPurchaseTeam */
        $PricePlanPurchaseTeam = ClassRegistry::init('PricePlanPurchaseTeam');

        $options = [
            'fields' => [
                'PricePlanPurchaseTeam.id',
                'PricePlanPurchaseTeam.price_plan_id',
                'PricePlanPurchaseTeam.price_plan_code',
                'CampaignTeam.id',
                'CampaignTeam.campaign_type',
                'CampaignTeam.price_plan_group_id',
            ],
            'joins'  => [
                [
                    'type'       => 'INNER',
                    'table'      => 'campaign_teams',
                    'alias'      => 'CampaignTeam',
                    'conditions' => [
                        'PricePlanPurchaseTeam.team_id = CampaignTeam.team_id',
                        'CampaignTeam.team_id' => $teamId,
                        'CampaignTeam.del_flg' => false,
                    ]
                ]
            ]
        ];

        $res = $PricePlanPurchaseTeam->find('first', $options);
        return $res;
    }

    /**
     * Returns true if the team have purchased a campaign plan
     *
     * @param int $teamId
     *
     * @return bool
     */
    function purchased(int $teamId): bool
    {
        /** @var PricePlanPurchaseTeam $PricePlanPurchaseTeam */
        $PricePlanPurchaseTeam = ClassRegistry::init('PricePlanPurchaseTeam');

        return $PricePlanPurchaseTeam->purchased($teamId);
    }

    /**
     * Returns the maximum number of users allowed for the team subscribed plan
     *
     * @param int $teamId
     *
     * @return int
     */
    function getMaxAllowedUsers(int $teamId): int
    {
        /** @var CampaignPricePlan $CampaignPricePlan */
        $CampaignPricePlan = ClassRegistry::init('CampaignPricePlan');
        /** @var PricePlanPurchaseTeam $PricePlanPurchaseTeam */
        $PricePlanPurchaseTeam = ClassRegistry::init('PricePlanPurchaseTeam');

        $purchasedPlan = $PricePlanPurchaseTeam->getByTeamId($teamId, ['price_plan_id']);
        if (empty($purchasedPlan)) {
            return 0;
        }

        $priceId = $purchasedPlan['price_plan_id'];
        $pricePlan = $CampaignPricePlan->getById($priceId, ['max_members']);
        if (empty($pricePlan)) {
            CakeLog::debug("CampaignPricePlan not found with id: $priceId");
            return 0;
        }

        $maxMembers = $pricePlan['max_members'];
        return $maxMembers;
    }

    /**
     * Check if additional users will exceed the campaign limit
     *
     * @param int $teamId
     * @param int $additionalUsersCount
     *
     * @return bool
     */
    function willExceedMaximumCampaignAllowedUser(int $teamId, int $additionalUsersCount)
    {
        /** @var TeamMember $TeamMember */
        $TeamMember = ClassRegistry::init("TeamMember");

        $currentUserCount = $TeamMember->countChargeTargetUsers($teamId);
        $campaignMaximumUsers = CampaignService::getMaxAllowedUsers($teamId);

        return $campaignMaximumUsers < ($currentUserCount + $additionalUsersCount);
    }

    /**
     * Return team current price plan
     *
     * @param int $teamId
     *
     * @return array|null
     */
    function getTeamPricePlan(int $teamId)
    {
        /** @var ViewCampaignPricePlan $ViewCampaignPricePlan */
        $ViewCampaignPricePlan = ClassRegistry::init('ViewCampaignPricePlan');
        /** @var PricePlanPurchaseTeam $PricePlanPurchaseTeam */
        $PricePlanPurchaseTeam = ClassRegistry::init('PricePlanPurchaseTeam');

        $purchasedPlan = $PricePlanPurchaseTeam->getByTeamId($teamId, ['price_plan_id']);
        if (empty($purchasedPlan)) {
            return null;
        }

        $priceId = $purchasedPlan['price_plan_id'];
        $pricePlan = $ViewCampaignPricePlan->getById($priceId);
        if (empty($pricePlan)) {
            CakeLog::debug("CampaignPricePlan not found with id: $priceId");
            return null;
        }

        return $pricePlan;
    }

    /*
     * find price plans belongs team campaign group
     *
     * @param int $teamId
     * @return array
     */
    function findList(int $teamId): array
    {
        /** @var CampaignTeam $CampaignTeam */
        $CampaignTeam = ClassRegistry::init("CampaignTeam");
        /** @var PaymentService $PaymentService */
        $PaymentService = ClassRegistry::init("PaymentService");

        $res = [];
        $campaigns = $CampaignTeam->findPricePlans($teamId);
        foreach($campaigns as $campaign) {
            $currencyType = $campaign['currency'];
            $subTotalCharge = $campaign['price'];
            $tax = $currencyType == Enum\PaymentSetting\Currency::JPY ? $PaymentService->calcTax('JP', $subTotalCharge) : 0;
            $totalCharge = $subTotalCharge + $tax;
            $res[] = [
                'id'               => $campaign['id'],
                'sub_total_charge' => $PaymentService->formatCharge($subTotalCharge, $currencyType),
                'tax'              => $PaymentService->formatCharge($tax, $currencyType),
                'total_charge'     => $PaymentService->formatCharge($totalCharge, $currencyType),
                'member_count'     => $campaign['max_members'],
            ];
        }
        return $res;
    }

    /*
     * find price plans by group id
     *
     * @param int $groupId
     * @return array
     */
    function findAllPlansByGroupId(int $groupId): array
    {
        // Get cached data from class variable
        $plans = Hash::get($this->cache_plans, $groupId);
        if (!empty($plans)) {
            return $plans;
        }

        // Get cached data from Redis
        /** @var GlRedis $GlRedis */
        $GlRedis = ClassRegistry::init("GlRedis");
        $plans = $GlRedis->getMstCampaignPlans($groupId);
        if (!empty($plans)) {
            // Set class variable to cache
            $this->cache_plans[$groupId] = $plans;
            return $plans;
        }

        // Get DB data
        /** @var ViewCampaignPricePlan $ViewCampaignPricePlan */
        $ViewCampaignPricePlan = ClassRegistry::init('ViewCampaignPricePlan');
        $plans = $ViewCampaignPricePlan->findAllPlansByGroupId($groupId);
        if (empty($plans)) {
            return [];
        }
        // Cache data
        $GlRedis->saveMstCampaignPlans($groupId, $plans);
        $this->cache_plans[$groupId] = $plans;

        return $plans;
    }

    /*
     * Get price plan by code
     *
     * @param int $planCode
     * @return array
     */
    function getPlanByCode(string $planCode): array
    {
        $parsedPlanCode = $this->parsePlanCode($planCode);
        $groupId = Hash::get($parsedPlanCode, 'group_id');
        if (empty($groupId)) {
            return [];
        }

        $plans = $this->findAllPlansByGroupId($groupId);
        if (empty($plans)) {
            return [];
        }

        $plansEachCode = Hash::combine($plans, '{n}.code', '{n}');
        $plan = Hash::get($plansEachCode, $planCode) ?? [];
        return $plan;
    }

    /*
     * find price plans for upgrading
     *
     * @param int $teamId
     * @return array
     */
    function findPlansForUpgrading(int $teamId, array $currentPlan): array
    {
        if (empty($currentPlan)) {
            return [];
        }

        /** @var PaymentService $PaymentService */
        $PaymentService = ClassRegistry::init("PaymentService");
        /** @var ViewCampaignPricePlan $ViewCampaignPricePlan */
        $ViewCampaignPricePlan = ClassRegistry::init('ViewCampaignPricePlan');


        $codeInfo = $this->parsePlanCode($currentPlan['code']);
        $pricePlans = $this->findAllPlansByGroupId($codeInfo['group_id']);

        // Calc remaining days by next base data
        foreach($pricePlans as $k => $plan) {
            $pricePlans[$k]['is_current_plan'] = $currentPlan['id'] == $plan['id'];

            $currencyType = (int)$plan['currency'];
            $plan[$k]['format_price'] = $PaymentService->formatCharge($plan['price'], $currencyType);
            if ($plan['max_members'] <= $currentPlan['max_members']) {
                $pricePlans[$k]['can_select'] = false;
                continue;
            }
            $pricePlans[$k]['can_select'] = true;

            // Calc charge amount
            $chargeInfo = $this->calcRelatedTotalChargeForUpgradingPlan(
                $teamId,
                new Enum\PaymentSetting\Currency($currencyType),
                $plan['code'],
                $currentPlan['code']
            );

            $pricePlans[$k] = am($pricePlans[$k], [
                'sub_total_charge' => $PaymentService->formatCharge($chargeInfo['sub_total_charge'], $currencyType),
                'tax'              => $PaymentService->formatCharge($chargeInfo['tax'], $currencyType),
                'total_charge'     => $PaymentService->formatCharge($chargeInfo['total_charge'], $currencyType),
            ]);
        }
        return $pricePlans;
    }

    /*
     * Parse price plan code
     * Ex. code "1-2"→ ["group_id" => 1, "detail_no" => 2"]
     * @param string $code
     *
     * @return array
     */
    function parsePlanCode(string $code): array
    {
        try {
            $ar = explode('-', $code);
            if (count($ar) != 2) {
                throw new Exception(sprintf("Failed to parse price plan code. code:%s", $code));
            }
            if (!AppUtil::isInt($ar[0]) || !AppUtil::isInt($ar[1])) {
                throw new Exception(sprintf("Failed to parse price plan code. %s", AppUtil::jsonOneLine($ar)));
            }
            $res = ['group_id' => $ar[0], 'detail_no' => $ar[1]];
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }

    /**
     * Calc balance for upgrading plan.
     * Ex. Upgrade plan from max members 50 to 200 and use days until next payment base date:20
     * (100,000 - 50,000) × 20 days / 1 month
     *
     * @param int                          $teamId
     * @param Enum\PaymentSetting\Currency $currencyType
     * @param string                       $upgradePlanCode
     * @param string                       $currentPlanCode
     *
     * @return array
     * @internal param int $upgradePlanPrice
     * @internal param int $currentPlanPrice
     */
    function calcRelatedTotalChargeForUpgradingPlan
    (
        int $teamId,
        Enum\PaymentSetting\Currency $currencyType,
        string $upgradePlanCode,
        string $currentPlanCode
    ): array {
        try {
            /** @var PaymentService $PaymentService */
            $PaymentService = ClassRegistry::init("PaymentService");

            if ($upgradePlanCode === $currentPlanCode) {
                throw new Exception(sprintf("Upgrading plan and current plan are same plan. %s",
                    AppUtil::jsonOneLine(compact('teamId', 'upgradePlanCode', 'currentPlanCode'))
                ));
            }

            $currentPlan = $this->getPlanByCode($currentPlanCode);
            if (empty($currentPlan)) {
                throw new Exception(sprintf("Current plan doesn't exit. %s",
                    AppUtil::jsonOneLine(compact('teamId', 'currentPlanCode'))
                ));
            }

            $upgradePlan = $this->getPlanByCode($upgradePlanCode);
            if (empty($upgradePlan)) {
                throw new Exception(sprintf("Upgrading plan doesn't exit. %s",
                    AppUtil::jsonOneLine(compact('teamId', 'upgradePlanCode'))
                ));
            }

            $paymentSetting = $PaymentService->get($teamId);
            if (empty($paymentSetting)) {
                throw new Exception(sprintf("Not exist payment setting data. %s",
                    AppUtil::jsonOneLine(compact('teamId'))
                ));
            }

            $useDaysByNext = $PaymentService->getUseDaysByNextBaseDate($teamId);
            $allUseDays = $PaymentService->getCurrentAllUseDays($teamId);
            // Ex. Upgrade plan from max members 50 to 200 and use days until next payment base date:20
            // (100,000 - 50,000) × 20 days / 1 month
            $subTotalCharge = ($upgradePlan['price'] - $currentPlan['price']) * ($useDaysByNext / $allUseDays);
            $subTotalCharge = $PaymentService->processDecimalPointForAmount($currencyType->getValue(), $subTotalCharge);

            $tax = $PaymentService->calcTax($paymentSetting['company_country'], $subTotalCharge);
            $totalCharge = $subTotalCharge + $tax;
            $res = [
                'sub_total_charge' => $subTotalCharge,
                'tax'              => $tax,
                'total_charge'     => $totalCharge,
            ];
        } catch (Exception $e) {
            CakeLog::emergency(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            CakeLog::emergency($e->getTraceAsString());
            $res = [
                'sub_total_charge' => 0,
                'tax'              => 0,
                'total_charge'     => 0,
            ];
        }

        return $res;
    }

    /**
     * Check is allowed price plan as team campaign groups
     *
     * @param int    $teamId
     * @param int    $pricePlanId
     * @param string $companyCountry
     *
     * @return bool
     */
    function isAllowedPricePlan(int $teamId, int $pricePlanId, string $companyCountry): bool
    {
        /** @var CampaignTeam $CampaignTeam */
        $CampaignTeam = ClassRegistry::init("CampaignTeam");
        /** @var CampaignPricePlan $CampaignPricePlan */
        $CampaignPricePlan = ClassRegistry::init("CampaignPricePlan");
        /** @var CampaignPriceGroup $CampaignPriceGroup */
        $CampaignPriceGroup = ClassRegistry::init("CampaignPriceGroup");
        /** @var TeamMember $TeamMember */
        $TeamMember = ClassRegistry::init("TeamMember");
        /** @var PaymentService $PaymentService */
        $PaymentService = ClassRegistry::init("PaymentService");

        // Check price plan belonging team
        if (!$CampaignTeam->isTeamPricePlan($teamId, $pricePlanId)) {
            return false;
        }

        // Check upper price plan max users
        $pricePlan = $CampaignPricePlan->getById($pricePlanId);
        if (empty($pricePlan)) {
            return false;
        }
        $chargeUserCount = $TeamMember->countChargeTargetUsers($teamId);
        if ($pricePlan['max_members'] < $chargeUserCount) {
            return false;
        }

        // Check currency
        $currency = $CampaignPriceGroup->getCurrency($pricePlan['group_id']);
        $requestedCountry = $PaymentService->getCurrencyTypeByCountry($companyCountry);
        if ($currency != $requestedCountry) {
            return false;
        }

        return true;
    }

    /**
     * get campaign for charging
     *
     * @param int $pricePlanId
     *
     * @return array
     */
    function getChargeInfo(int $pricePlanId): array
    {
        /** @var ViewCampaignPricePlan $ViewCampaignPricePlan */
        $ViewCampaignPricePlan = ClassRegistry::init('ViewCampaignPricePlan');
        /** @var PaymentService $PaymentService */
        $PaymentService = ClassRegistry::init("PaymentService");

        $campaign = $ViewCampaignPricePlan->getById($pricePlanId);
        $subTotalCharge = $campaign['price'];
        $currencyType = $campaign['currency'];
        $tax = $currencyType == Enum\PaymentSetting\Currency::JPY ? $PaymentService->calcTax('JP', $subTotalCharge) : 0;
        $totalCharge = $subTotalCharge + $tax;
        $chargeInfo = [
            'id'               => $campaign['id'],
            'sub_total_charge' => $subTotalCharge,
            'tax'              => $tax,
            'total_charge'     => $totalCharge,
            'member_count'     => $campaign['max_members'],
        ];

        return $chargeInfo;
    }

    /**
     * Get charge info for campaign team
     *
     * @param int $teamId
     *
     * @return array
     */
    function getTeamChargeInfo(int $teamId): array
    {
        /** @var PricePlanPurchaseTeam $PricePlanPurchaseTeam */
        $PricePlanPurchaseTeam = ClassRegistry::init('PricePlanPurchaseTeam');

        $purchasedPlan = $PricePlanPurchaseTeam->getByTeamId($teamId, ['price_plan_id']);
        if (empty($purchasedPlan)) {
            return null;
        }
        $priceId = $purchasedPlan['price_plan_id'];
        return CampaignService::getChargeInfo($priceId);
    }

    /**
     * Get Currency info from team price group
     *
     * @param int $pricePlanId
     *
     * @return int|null
     */
    function getPricePlanCurrency(int $pricePlanId)
    {
        /** @var ViewCampaignPricePlan $ViewCampaignPricePlan */
        $ViewCampaignPricePlan = ClassRegistry::init('ViewCampaignPricePlan');
        $campaign = $ViewCampaignPricePlan->getById($pricePlanId, ['currency']);

        return $campaign['currency'];
    }

    /**
     * Save PricePlanPurchaseTeam to DB
     *
     * @param int $teamId
     * @param int $pricePlanId
     *
     * @return array
     */
    function savePricePlanPurchase(int $teamId, int $pricePlanId): array
    {
        /** @var CampaignPricePlan $CampaignPricePlan */
        $CampaignPricePlan = ClassRegistry::init('CampaignPricePlan');
        /** @var PricePlanPurchaseTeam $PricePlanPurchaseTeam */
        $PricePlanPurchaseTeam = ClassRegistry::init('PricePlanPurchaseTeam');

        $pricePlan = $CampaignPricePlan->getById($pricePlanId, ['code']);
        $pricePlanPurchase = [
            'team_id'           => $teamId,
            'price_plan_id'     => $pricePlanId,
            'price_plan_code'   => $pricePlan['code'],
            'purchase_datetime' => time(),
        ];

        $PricePlanPurchaseTeam->create();
        return $PricePlanPurchaseTeam->save($pricePlanPurchase);
    }

    /**
     * Save CampaignChargeHistory to DB
     *
     * @param int $teamId
     * @param int $historyId
     * @param int $pricePlanPurchaseId
     *
     * @return array
     */
    function saveCampaignChargeHistory(int $teamId, int $historyId, int $pricePlanPurchaseId): array
    {
        /** @var CampaignTeam $CampaignTeam */
        $CampaignTeam = ClassRegistry::init('CampaignTeam');
        /** @var CampaignChargeHistory $CampaignChargeHistory */
        $CampaignChargeHistory = ClassRegistry::init('CampaignChargeHistory');

        $campaignTeam = $CampaignTeam->getByTeamId($teamId, ['id']);
        $campaignHistory = [
            'charge_history_id'           => $historyId,
            'campaign_team_id'            => $campaignTeam['id'],
            'price_plan_purchase_team_id' => $pricePlanPurchaseId,

        ];
        $CampaignChargeHistory->create();
        return $CampaignChargeHistory->save($campaignHistory);
    }
}
