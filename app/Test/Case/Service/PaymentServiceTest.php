<?php
App::uses('GoalousTestCase', 'Test');
App::import('Service', 'PaymentService');

/**
 * Class PaymentServiceTest
 *
 * @property PaymentService $PaymentService
 * @property PaymentSetting $PaymentSetting
 */
class PaymentServiceTest extends GoalousTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.payment_setting',
        'app.payment_setting_change_log',
        'app.credit_card',
        'app.team',
        'app.team_member',
        'app.user'
    );

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->setDefaultTeamIdAndUid();
        $this->PaymentService = ClassRegistry::init('PaymentService');
        $this->PaymentSetting = ClassRegistry::init('PaymentSetting');
        $this->Team = ClassRegistry::init('Team');
    }

    public function test_validateCreate_validate()
    {
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'team_id'          => 1,
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => 'ISAO',
            'company_address'  => 'Here Japan',
            'company_tel'      => '123456789',
            'email'            => 'test@goalous.com',
            'payment_base_day' => 15,
            'currency'         => 1
        ];

        $res = $this->PaymentService->validateCreate($payment);
        $this->assertTrue($res);
    }

    public function test_validateCreate_validateError_token()
    {
        // No Token
        $payment = [
            'token'            => '',
            'team_id'          => 1,
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => 'ISAO',
            'company_address'  => 'Here Japan',
            'company_tel'      => '123456789',
            'email'            => 'test@goalous.com',
            'payment_base_day' => 15,
            'currency'         => 1
        ];
        $res = $this->PaymentService->validateCreate($payment);
        $this->assertFalse($res === true);
    }

    public function test_validateCreate_validateError_teamId()
    {
        // No team ID
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => 'ISAO',
            'company_address'  => 'Here Japan',
            'company_tel'      => '123456789',
            'email'            => 'test@goalous.com',
            'payment_base_day' => 15,
            'currency'         => 1
        ];
        $res = $this->PaymentService->validateCreate($payment);
        $this->assertFalse($res === true);
    }

    public function test_validateCreate_validateError_wrongType()
    {
        // Wrong type
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'team_id'          => 1,
            'type'             => 3,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => 'ISAO',
            'company_address'  => 'Here Japan',
            'company_tel'      => '123456789',
            'email'            => 'test@goalous.com',
            'payment_base_day' => 15,
            'currency'         => 1
        ];
        $res = $this->PaymentService->validateCreate($payment);
        $this->assertFalse($res === true);
    }

    public function test_validateCreate_validateError_payerName()
    {
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'team_id'          => 1,
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => '',
            'company_name'     => 'ISAO',
            'company_address'  => 'Here Japan',
            'company_tel'      => '123456789',
            'email'            => 'test@goalous.com',
            'payment_base_day' => 15,
            'currency'         => 1
        ];

        $res = $this->PaymentService->validateCreate($payment);
        $this->assertFalse($res === true);
    }

    public function test_validateCreate_validateError_companyName()
    {
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'team_id'          => 1,
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => '',
            'company_address'  => 'Here Japan',
            'company_tel'      => '123456789',
            'email'            => 'test@goalous.com',
            'payment_base_day' => 15,
            'currency'         => 1
        ];

        $res = $this->PaymentService->validateCreate($payment);
        $this->assertFalse($res === true);
    }

    public function test_validateCreate_validateError_Address()
    {
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'team_id'          => 1,
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => 'ISAO',
            'company_tel'      => '123456789',
            'email'            => 'test@goalous.com',
            'payment_base_day' => 15,
            'currency'         => 1
        ];

        $res = $this->PaymentService->validateCreate($payment);
        $this->assertFalse($res === true);
    }

    public function test_validateCreate_validateError_tel()
    {
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'team_id'          => 1,
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => 'ISAO',
            'company_address'  => 'Here Japan',
            'company_tel'      => '',
            'email'            => 'test@goalous.com',
            'payment_base_day' => 15,
            'currency'         => 1
        ];

        $res = $this->PaymentService->validateCreate($payment);
        $this->assertFalse($res === true);
    }

    public function test_validateCreate_validateError_noEmail()
    {
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'team_id'          => 1,
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => 'ISAO',
            'company_address'  => 'Here Japan',
            'company_tel'      => '123456789',
            'email'            => '',
            'payment_base_day' => 15,
            'currency'         => 1
        ];

        $res = $this->PaymentService->validateCreate($payment);
        $this->assertFalse($res === true);
    }

    public function test_validateCreate_validateError_wrongEmail()
    {
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'team_id'          => 1,
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => 'ISAO',
            'company_address'  => 'Here Japan',
            'company_tel'      => '123456789',
            'email'            => 'tesxxxxx.com',
            'payment_base_day' => 15,
            'currency'         => 1
        ];

        $res = $this->PaymentService->validateCreate($payment);
        $this->assertFalse($res === true);
    }

    public function test_validateCreate_validateError_wrongPaymentDay()
    {
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'team_id'          => 1,
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => 'ISAO',
            'company_address'  => 'Here Japan',
            'company_tel'      => '123456789',
            'email'            => 'test@goalous.com',
            'payment_base_day' => 33,
            'currency'         => 1
        ];

        $res = $this->PaymentService->validateCreate($payment);
        $this->assertFalse($res === true);
    }

    public function test_validateCreate_validateError_wrongCurrency()
    {
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'team_id'          => 1,
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => 'ISAO',
            'company_address'  => 'Here Japan',
            'company_tel'      => '123456789',
            'email'            => 'test@goalous.com',
            'payment_base_day' => 15,
            'currency'         => 12
        ];

        $res = $this->PaymentService->validateCreate($payment);
        $this->assertFalse($res === true);
    }

    public function test_createCreditCardPayment()
    {
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'team_id'          => 1,
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => 'ISAO',
            'company_address'  => 'Here Japan',
            'company_tel'      => '123456789',
            'email'            => 'test@goalous.com',
            'payment_base_day' => 15,
            'currency'         => 1
        ];
        $customerCode = 'cus_B3ygr9hxqg5evH';

        $userID = $this->createActiveUser(1);
        $res = $this->PaymentService->createCreditCardPayment($payment, $customerCode, $userID);
        $this->assertTrue($res);
    }

    public function test_createCreditCardPayment_noCustomerCode()
    {
        $payment = [
            'token'            => 'tok_1Ahqr1AM8AoVOHcFBeqD77cx',
            'team_id'          => 1,
            'type'             => 1,
            'amount_per_user'  => 1800,
            'payer_name'       => 'Goalous Taro',
            'company_name'     => 'ISAO',
            'company_address'  => 'Here Japan',
            'company_tel'      => '123456789',
            'email'            => 'test@goalous.com',
            'payment_base_day' => 15,
            'currency'         => 1
        ];
        $customerCode = '';

        $userID = $this->createActiveUser(1);
        $res = $this->PaymentService->createCreditCardPayment($payment, $customerCode, $userID);
        $this->assertFalse($res);
    }

    public function test_getNextBaseDate_basic()
    {
        $teamId = 1;
        $this->Team->current_team_id = $teamId;
        $this->Team->id = $teamId;
        $this->Team->save([
            'timezone' => 12,
        ]);
        $this->PaymentSetting->save([
            'team_id'          => $teamId,
            'payment_base_day' => 2,
        ], false);
        $currentTimestamp = strtotime("2017-01-01");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-01-02');

        $currentTimestamp = strtotime("2017-01-02");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-02-02');

        $currentTimestamp = strtotime("2017-01-03");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-02-02');

        $currentTimestamp = strtotime("2017-12-31");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2018-01-02');
    }

    public function test_getNextBaseDate_timezone()
    {
        $teamId = 1;
        $this->Team->current_team_id = $teamId;
        $this->Team->id = $teamId;
        $this->Team->save([
            'timezone' => 12.0,
        ]);
        $teamId = 1;
        $this->PaymentSetting->save([
            'team_id'          => $teamId,
            'payment_base_day' => 2,
        ], false);

        $currentTimestamp = strtotime("2017-01-01 11:59:59");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-01-02');

        $currentTimestamp = strtotime("2017-01-01 12:00:00");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-02-02');

        $currentTimestamp = strtotime("2017-01-01 12:00:01");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-02-02');

        // timezone minus
        $this->Team->save([
            'timezone' => -12.0,
        ]);
        $this->Team->current_team = [];
        $this->_clearCache();

        $currentTimestamp = strtotime("2017-01-02 11:59:59");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-01-02');

        $currentTimestamp = strtotime("2017-01-02 12:00:00");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-02-02');

        $currentTimestamp = strtotime("2017-01-02 12:00:01");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-02-02');

        // timezone *.5
        $this->Team->save([
            'timezone' => -3.5,
        ]);
        $this->Team->current_team = [];
        $this->_clearCache();

        $currentTimestamp = strtotime("2017-01-02 03:29:59");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-01-02');

        $currentTimestamp = strtotime("2017-01-02 03:30:00");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-02-02');

        $currentTimestamp = strtotime("2017-01-02 03:30:01");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-02-02');

    }

    public function test_getNextBaseDate_checkDate()
    {
        $teamId = 1;
        $this->Team->current_team_id = $teamId;
        $this->Team->id = $teamId;
        $this->Team->save([
            'timezone' => 0,
        ]);
        $teamId = 1;
        $this->PaymentSetting->save([
            'team_id'          => $teamId,
            'payment_base_day' => 28,
        ], false);
        $this->PaymentService->clearCachePaymentSettings();

        $currentTimestamp = strtotime("2017-02-27");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-02-28');

        $currentTimestamp = strtotime("2017-02-28");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-03-28');


        $currentTimestamp = strtotime("2017-02-27");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-02-28');

        $currentTimestamp = strtotime("2017-02-28");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-03-28');

        // No exist day
        $this->PaymentSetting->save([
            'team_id'          => $teamId,
            'payment_base_day' => 29,
        ], false);
        $this->PaymentService->clearCachePaymentSettings();

        $currentTimestamp = strtotime("2017-02-28");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-03-29');

        $this->PaymentSetting->save([
            'team_id'          => $teamId,
            'payment_base_day' => 31,
        ], false);
        $this->PaymentService->clearCachePaymentSettings();

        $currentTimestamp = strtotime("2017-04-30");
        $res = $this->PaymentService->getNextBaseDate($currentTimestamp);
        $this->assertEquals($res, '2017-05-31');
    }

    public function test_getUseDaysUntilNext()
    {
        $teamId = 1;
        $this->Team->current_team_id = $teamId;
        $this->Team->id = $teamId;
        $this->Team->save([
            'timezone' => 0,
        ]);
        $teamId = 1;
        $this->PaymentSetting->save([
            'team_id'          => $teamId,
            'payment_base_day' => 28,
        ], false);
        $this->PaymentService->clearCachePaymentSettings();

        $currentTimestamp = strtotime("2017-02-01");
        $res = $this->PaymentService->getUseDaysByNext($currentTimestamp);
        $this->assertEquals($res, 27);

        $currentTimestamp = strtotime("2017-02-27");
        $res = $this->PaymentService->getUseDaysByNext($currentTimestamp);
        $this->assertEquals($res, 1);

        $currentTimestamp = strtotime("2017-02-28");
        $res = $this->PaymentService->getUseDaysByNext($currentTimestamp);
        $this->assertEquals($res, 28);

        $currentTimestamp = strtotime("2017-03-01");
        $res = $this->PaymentService->getUseDaysByNext($currentTimestamp);
        $this->assertEquals($res, 27);

    }

    public function test_getAllUseDaysOfMonth()
    {
        $teamId = 1;
        $this->Team->current_team_id = $teamId;
        $this->Team->id = $teamId;
        $this->Team->save([
            'timezone' => 0,
        ]);
        $teamId = 1;
        $this->PaymentSetting->save([
            'team_id'          => $teamId,
            'payment_base_day' => 1,
        ], false);
        $this->PaymentService->clearCachePaymentSettings();

        $currentTimestamp = strtotime("2017-01-01");
        $res = $this->PaymentService->getAllUseDaysOfMonth($currentTimestamp);
        $this->assertEquals($res, 31);

        $currentTimestamp = strtotime("2016-12-31");
        $res = $this->PaymentService->getAllUseDaysOfMonth($currentTimestamp);
        $this->assertEquals($res, 31);

        $currentTimestamp = strtotime("2017-01-31");
        $res = $this->PaymentService->getAllUseDaysOfMonth($currentTimestamp);
        $this->assertEquals($res, 31);

        $currentTimestamp = strtotime("2017-02-01");
        $res = $this->PaymentService->getAllUseDaysOfMonth($currentTimestamp);
        $this->assertEquals($res, 28);
    }

    public function test_calcTotalChargeByAddUsers()
    {
        $teamId = 1;
        $this->Team->current_team_id = $teamId;
        $this->Team->id = $teamId;
        $this->Team->save([
            'timezone' => 0,
        ]);
        $teamId = 1;
        $this->PaymentSetting->save([
            'team_id'          => $teamId,
            'payment_base_day' => 1,
            'amount_per_user' => 1980,
            'currency' => $this->PaymentSetting::CURRENCY_JPY
        ], false);
        $this->PaymentService->clearCachePaymentSettings();

        $currentTimestamp = strtotime("2017-01-01");
        $userCnt = 1;
        $res = $this->PaymentService->calcTotalChargeByAddUsers($userCnt, $currentTimestamp);

        $currentTimestamp = strtotime("2017-01-01");
        $userCnt = 1;
        $res = $this->PaymentService->calcTotalChargeByAddUsers($userCnt, $currentTimestamp);

        $this->assertEquals($res, "¥1,980");


        // TODO: confirm how display label (円 or ¥)
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->PaymentService);
        parent::tearDown();
    }
}
