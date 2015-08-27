<?php
App::uses('LocalName', 'Model');

/**
 * LocalName Test Case
 *
 * @property LocalName $LocalName
 */
class LocalNameTest extends CakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.local_name',
        'app.user', 'app.notify_setting',
        'app.team',
        'app.badge',
        'app.comment_like',
        'app.comment',
        'app.post',
        'app.comment_mention',
        'app.given_badge',
        'app.post_like',
        'app.post_mention',
        'app.post_read',
        'app.image',
        'app.images_post',
        'app.comment_read',
        'app.group',
        'app.team_member',
        'app.job_category',
        'app.invite',

        'app.thread',
        'app.message',
        'app.email',
        'app.oauth_token'
    );

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->LocalName = ClassRegistry::init('LocalName');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->LocalName);

        parent::tearDown();
    }

    function testGetName()
    {
        $this->LocalName->save(['user_id' => 1, 'language' => 'jpn', 'first_name' => 'test', 'last_name' => 'test']);
        $actual = $this->LocalName->getName(1, 'jpn');
        $this->assertNotEmpty($actual);
        $actual = $this->LocalName->getName(1, 'eng');
        $this->assertEmpty($actual);
    }

}
