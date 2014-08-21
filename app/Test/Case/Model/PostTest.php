<?php
App::uses('Post', 'Model');

/**
 * Post Test Case
 *
 * @property Post $Post
 */
class PostTest extends CakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.post',
        'app.user', 'app.notify_setting',
        'app.team',
        //'app.goal',
        'app.comment_mention',
        'app.comment',
        'app.comment_like',
        'app.comment_read',
        'app.given_badge',
        'app.post_like',
        'app.post_mention',
        'app.post_read',
        'app.image',
        'app.badge',
        'app.images_post',
        'app.post_share_user',
        'app.post_share_circle',
        'app.circle',
        'app.circle_member',
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
        $this->Post = ClassRegistry::init('Post');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Post);

        parent::tearDown();
    }

    public function testAdd()
    {
        $uid = '1';
        $team_id = '1';
        $postData = [
            'Post' => [
                'body'       => 'test',
                'public_flg' => 1
            ]
        ];
        $res = $this->Post->add($postData, Post::TYPE_NORMAL, $uid, $team_id);
        $this->assertNotEmpty($res, "[正常]投稿(uid,team_id指定)");

        $this->Post->me['id'] = $uid;
        $this->Post->current_team_id = $team_id;
        $res = $this->Post->add($postData);
        $this->assertNotEmpty($res, "[正常]投稿(uid,team_id指定なし)");
    }

    public function testGet()
    {
        $uid = '1';
        $team_id = '1';
        $this->Post->me['id'] = $uid;
        $this->Post->current_team_id = $team_id;
        $this->Post->PostRead->me['id'] = $uid;
        $this->Post->PostRead->current_team_id = $team_id;
        $this->Post->Comment->CommentRead->me['id'] = $uid;
        $this->Post->Comment->CommentRead->current_team_id = $team_id;
        $this->Post->PostShareCircle->me['id'] = $uid;
        $this->Post->PostShareCircle->current_team_id = $team_id;
        $this->Post->PostShareUser->me['id'] = $uid;
        $this->Post->PostShareUser->current_team_id = $team_id;
        $this->Post->User->CircleMember->me['id'] = $uid;
        $this->Post->User->CircleMember->current_team_id = $team_id;
        $this->Post->get(1, 20, "2014-01-01", "2014-01-31");
    }

    function testGetShareAllMemberList()
    {
        $uid = '1';
        $team_id = '1';
        $post_id = 1;
        $this->Post->me['id'] = $uid;
        $this->Post->current_team_id = $team_id;
        $this->Post->Team->TeamMember->me['id'] = $uid;
        $this->Post->Team->TeamMember->current_team_id = $team_id;
        $this->Post->PostShareCircle->me['id'] = $uid;
        $this->Post->PostShareCircle->current_team_id = $team_id;
        $this->Post->PostShareUser->me['id'] = $uid;
        $this->Post->PostShareUser->current_team_id = $team_id;

        $this->Post->getShareAllMemberList($post_id);
        $this->Post->id = $post_id;
        $this->Post->saveField('public_flg', true);
        $this->Post->getShareAllMemberList($post_id);
        $this->Post->getShareAllMemberList(9999999999);
    }

    public function testIsPublic()
    {
        $uid = '1';
        $team_id = '1';
        $this->Post->me['id'] = $uid;
        $this->Post->current_team_id = $team_id;
        $data = [
            'user_id'    => $uid,
            'team_id'    => $team_id,
            'public_flg' => true,
            'body'       => 'test'
        ];
        $this->Post->save($data);

        $res = $this->Post->isPublic($this->Post->id);
        $this->assertTrue($res);
    }

    public function testIsMyPost()
    {
        $uid = '1';
        $team_id = '1';
        $this->Post->me['id'] = $uid;
        $this->Post->current_team_id = $team_id;
        $data = [
            'user_id'    => $uid,
            'team_id'    => $team_id,
            'public_flg' => true,
            'body'       => 'test'
        ];
        $this->Post->save($data);
        $res = $this->Post->isMyPost($this->Post->id);
        $this->assertTrue($res);
    }

}
