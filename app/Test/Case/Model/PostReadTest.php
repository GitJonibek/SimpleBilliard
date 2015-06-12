<?php
App::uses('PostRead', 'Model');

/**
 * PostRead Test Case
 *
 * @property  PostRead $PostRead
 */
class PostReadTest extends CakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.post_read',
        'app.post',
        'app.user', 'app.notify_setting',
        'app.team',
        'app.goal',
        'app.action_result',
        'app.key_result',
        'app.circle',
    );

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->PostRead = ClassRegistry::init('PostRead');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->PostRead);

        parent::tearDown();
    }

    public function testRed()
    {
        $uid = '1';
        $team_id = '1';
        $post_uid = '2';
        $this->PostRead->my_uid = $uid;
        $this->PostRead->current_team_id = $team_id;
        $test_save_data = [
            'Post' => [
                'user_id' => $post_uid,
                'team_id' => $team_id,
                'body'    => 'test',
            ],
        ];
        $this->PostRead->Post->save($test_save_data);
        $post_id = $this->PostRead->Post->getLastInsertID();
        $this->PostRead->red($this->PostRead->Post->getLastInsertID());
        $options = [
            'conditions' => [
                'post_id' => $this->PostRead->Post->getLastInsertID(),
                'user_id' => $uid
            ]
        ];
        $post_read = $this->PostRead->find('first', $options);
        $this->assertEquals($uid, $post_read['PostRead']['user_id']);

        $before_data = $post_read;
        $this->PostRead->red($post_id);
        $after_data = $this->PostRead->find('first', $options);
        $this->assertEquals($before_data, $after_data);

        $this->PostRead->Post->create();
        $this->PostRead->Post->save($test_save_data);
        $second_post_id = $this->PostRead->Post->getLastInsertID();
        $post_list = [$post_id, $second_post_id];
        $this->PostRead->red($post_list);
    }

    public function testRedIfPoster()
    {
        $uid = '1';
        $team_id = '1';
        $this->PostRead->my_uid = $uid;
        $this->PostRead->current_team_id = $team_id;
        $test_save_data = [
            'Post' => [
                'user_id' => $uid,
                'team_id' => $team_id,
                'body'    => 'test',

            ],
        ];
        $this->PostRead->Post->save($test_save_data);
        $before_data = $this->PostRead->read();
        $this->PostRead->red($this->PostRead->Post->getLastInsertID());
        $after_data = $this->PostRead->read();
        $this->assertEquals($before_data, $after_data);
    }
}
