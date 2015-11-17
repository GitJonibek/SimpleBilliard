<?php App::uses('GoalousTestCase', 'Test');
App::uses('Device', 'Model');

/**
 * Device Test Case
 *
 * @property Device $Device
 */
class DeviceTest extends GoalousTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.device',
        'app.user',
        'app.team',
        'app.badge',
        'app.circle',
        'app.circle_member',
        'app.post_share_circle',
        'app.post',
        'app.goal',
        'app.purpose',
        'app.goal_category',
        'app.key_result',
        'app.action_result',
        'app.action_result_file',
        'app.attached_file',
        'app.comment_file',
        'app.comment',
        'app.comment_like',
        'app.comment_read',
        'app.post_file',
        'app.collaborator',
        'app.approval_history',
        'app.follower',
        'app.evaluation',
        'app.evaluate_term',
        'app.evaluator',
        'app.evaluate_score',
        'app.post_share_user',
        'app.post_like',
        'app.post_read',
        'app.comment_mention',
        'app.given_badge',
        'app.post_mention',
        'app.group',
        'app.member_group',
        'app.group_vision',
        'app.invite',
        'app.job_category',
        'app.team_member',
        'app.member_type',
        'app.thread',
        'app.message',
        'app.evaluation_setting',
        'app.team_vision',
        'app.email',
        'app.notify_setting',
        'app.oauth_token',
        'app.local_name'
    );

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Device = ClassRegistry::init('Device');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Device);

        parent::tearDown();
    }

    function testDummy()
    {

    }

    public function testAddDevice()
    {
        $data = [
            'Device' => [
                'user_id'      => 1,
                'device_token' => 'dummy-dummy-dummy',
                'os_type'      => 0,
            ]
        ];
        $res = $this->Device->add($data);
        $this->assertTrue($res);
    }

    public function testGetDevicesByUserId1()
    {
        $data = $this->Device->getDevicesByUserId(1);
        $this->assertTrue(count($data) === 1);
    }

    public function testGetDevicesByUserId2()
    {
        $data = $this->Device->getDevicesByUserId(1);
        $this->assertTrue($data[0]['Device']['device_token'] === 'ios_dummy1');
    }

    public function testGetDevicesByUserId3()
    {
        $data = $this->Device->getDevicesByUserId(2);
        $this->assertTrue($data[0]['Device']['device_token'] === 'android_dummy1');
    }

    public function testGetDevicesByUserId4()
    {
        $data = $this->Device->getDevicesByUserId(3);
        $this->assertTrue(count($data) === 2);
    }

    public function testGetDevicesByUserId5()
    {
        $data = $this->Device->getDevicesByUserId(3);
        $this->assertTrue($data[0]['Device']['device_token'] === 'ios_dummy2');
    }

    public function testGetDevicesByUserId6()
    {
        $data = $this->Device->getDevicesByUserId(3);
        $this->assertTrue($data[1]['Device']['device_token'] === 'android_dummy2');
    }

    public function testGetDevicesByUserIdNotFound()
    {
        $data = $this->Device->getDevicesByUserId(99);
        $this->assertTrue(empty($data));
    }

    public function testGetDevicesByUserIdNotFoundDelData()
    {
        $data = $this->Device->getDevicesByUserId(4);
        $this->assertTrue(empty($data));
    }

    public function testGetDeviceTokens1()
    {
        $data = $this->Device->getDeviceTokens(1);
        $this->assertTrue(count($data) === 1);
    }

    public function testGetDeviceTokens2()
    {
        $data = $this->Device->getDeviceTokens(1);
        $this->assertTrue($data[0] === "ios_dummy1");
    }

    public function testGetDeviceTokens3()
    {
        $data = $this->Device->getDeviceTokens(3);
        $this->assertTrue(count($data) === 2);
    }

    public function testGetDeviceTokens4()
    {
        $data = $this->Device->getDeviceTokens(3);
        $this->assertTrue($data[0] === "ios_dummy2");
    }

    public function testGetDeviceTokens5()
    {
        $data = $this->Device->getDeviceTokens(3);
        $this->assertTrue($data[1] === "android_dummy2");
    }

    public function testGetDeviceTokensNotFound()
    {
        $data = $this->Device->getDeviceTokens(99);
        $this->assertTrue(empty($data));
    }

    public function testGetDevicesByUserIdAndDeviceToken1()
    {
        $data = $this->Device->getDevicesByUserIdAndDeviceToken(1, 'ios_dummy1');
        $this->assertTrue(count($data) === 1);
    }

    public function testGetDevicesByUserIdAndDeviceToken2()
    {
        $data = $this->Device->getDevicesByUserId(3, 'ios_dummy2');
        $this->assertTrue($data[0]['Device']['device_token'] === 'ios_dummy2');
    }

    public function testGetDevicesByUserIdAndDeviceTokenNotFound()
    {
        $data = $this->Device->getDevicesByUserId(99, 'dummy!');
        $this->assertTrue(empty($data));
    }

    public function testGetDevicesByUserIdAndDeviceTokenNotFoundDelData()
    {
        $data = $this->Device->getDevicesByUserId(4, 'android_dummy3');
        $this->assertTrue(empty($data));
    }

}
