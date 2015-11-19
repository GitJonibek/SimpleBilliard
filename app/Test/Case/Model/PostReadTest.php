<?php App::uses('GoalousTestCase', 'Test');
App::uses('PostRead', 'Model');

/**
 * PostRead Test Case
 *
 * @property  PostRead $PostRead
 */
class PostReadTest extends GoalousTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.comment',
        'app.comment_read',
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
        $this->PostRead->red($this->PostRead->Post->getLastInsertID(), true);
        $options = [
            'conditions' => [
                'post_id' => $this->PostRead->Post->getLastInsertID(),
                'user_id' => $uid
            ]
        ];
        $post_read = $this->PostRead->find('first', $options);
        $this->assertEquals($uid, $post_read['PostRead']['user_id']);

        $before_data = $post_read;
        $this->PostRead->red($post_id, true);
        $after_data = $this->PostRead->find('first', $options);
        $this->assertEquals($before_data, $after_data);

        $this->PostRead->Post->create();
        $this->PostRead->Post->save($test_save_data);
        $second_post_id = $this->PostRead->Post->getLastInsertID();
        $post_list = [$post_id, $second_post_id];
        $this->PostRead->red($post_list, true);
    }

    public function testRedDuplicated()
    {
        $uid = '1';
        $team_id = '1';
        $post_uid = '2';
        $this->PostRead->my_uid = $uid;
        $this->PostRead->current_team_id = $team_id;

        $this->PostRead->Post->create();
        $this->PostRead->Post->save(['user_id' => $post_uid, 'team_id' => $team_id, 'body' => 'test']);
        $last_id = $this->PostRead->Post->getLastInsertID();

        $this->PostRead->Post->create();
        $this->PostRead->Post->save(['user_id' => $post_uid, 'team_id' => $team_id, 'body' => 'test']);
        $last_id2 = $this->PostRead->Post->getLastInsertID();

        $res = $this->PostRead->red($last_id, true);
        $this->assertTrue($res);

        $PostReadMock = $this->getMockForModel('PostRead', array('pickUnMyPosts'));
        /** @noinspection PhpUndefinedMethodInspection */
        $PostReadMock->expects($this->any())
                     ->method('pickUnMyPosts')
                     ->will($this->returnValue([$last_id, $last_id2]));
        $PostReadMock->my_uid = $uid;
        $PostReadMock->current_team_id = $team_id;
        $this->PostRead = $PostReadMock;
        $res = $this->PostRead->red([$last_id, $last_id2], true);
        $this->assertTrue($res);

        $res = $this->PostRead->red([$last_id, $last_id2], true);
        $this->assertFalse($res);
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
        $this->PostRead->red($this->PostRead->Post->getLastInsertID(), true);
        $after_data = $this->PostRead->read();
        $this->assertEquals($before_data, $after_data);
    }

    public function testSaveAllAtOnceNoModelName()
    {
        $uid = '1';
        $team_id = '1';
        $this->PostRead->my_uid = $uid;
        $this->PostRead->current_team_id = $team_id;
        $before_count = $this->PostRead->find('count');
        $data = [
            [
                'post_id' => 1,
                'user_id' => $uid,
                'team_id' => $team_id,
            ]
        ];
        $this->PostRead->saveAllAtOnce($data);
        $after_count = $this->PostRead->find('count');
        $this->assertEquals($before_count + 1, $after_count);
    }

    public function testSaveAllAtOnceWithModelName()
    {
        $uid = '1';
        $team_id = '1';
        $this->PostRead->my_uid = $uid;
        $this->PostRead->current_team_id = $team_id;
        $before_count = $this->PostRead->find('count');
        $data = [
            [
                'PostRead' => [
                    'post_id' => 1,
                    'user_id' => $uid,
                    'team_id' => $team_id,
                ]
            ]
        ];
        $this->PostRead->saveAllAtOnce($data);
        $after_count = $this->PostRead->find('count');
        $this->assertEquals($before_count + 1, $after_count);
    }

    public function testSaveAllAtOnceNoData()
    {
        $uid = '1';
        $team_id = '1';
        $this->PostRead->my_uid = $uid;
        $this->PostRead->current_team_id = $team_id;
        $before_count = $this->PostRead->find('count');
        $data = [];
        $this->PostRead->saveAllAtOnce($data);
        $after_count = $this->PostRead->find('count');
        $this->assertEquals($before_count, $after_count);
    }

}
