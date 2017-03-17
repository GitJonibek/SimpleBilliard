<?php
App::uses('GoalousTestCase', 'Test');
App::uses('Message', 'Model');
App::import('Service/Api', 'ApiTopicService');

/**
 * Class ApiTopicServiceTest
 *
 * @property ApiMessageService $ApiMessageService
 */
class ApiMessageServiceTest extends GoalousTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.message'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->ApiMessageService = ClassRegistry::init('ApiMessageService');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->ApiMessageService);
        parent::tearDown();
    }

    function test_convertKeyNames()
    {
        //TODO: it should be written later.
    }

    function test_findMessages()
    {
        //TODO: it should be written later.
    }

    function test_setPaging()
    {
        //TODO: it should be written later.
    }
}
