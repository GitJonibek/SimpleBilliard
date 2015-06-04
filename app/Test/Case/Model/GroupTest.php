<?php
App::uses('Group', 'Model');

/**
 * Group Test Case
 *
 * @property Group $Group
 */
class GroupTest extends CakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.group',
        'app.team',
        'app.team_member'
    );

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Group = ClassRegistry::init('Group');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Group);

        parent::tearDown();
    }

    function testGetByName()
    {
        $this->Group->current_team_id = 1;
        $this->assertEmpty($this->Group->getByName('test', null));
    }

    function testGetByAllName()
    {
        $team_id = 999;
        $params = [
            'team_id' => $team_id,
            'name' => 'SDG'
        ];
        $this->Group->save($params);
        $res = $this->Group->getByAllName($team_id);
        $this->assertContains('SDG', $res);
    }

}
