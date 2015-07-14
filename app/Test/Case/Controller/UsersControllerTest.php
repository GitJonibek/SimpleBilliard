<?php
App::uses('UsersController', 'Controller');

/**
 * UsersController Test Case
 * @method testAction($url = '', $options = array()) ControllerTestCase::_testAction

 */
class UsersControllerTest extends ControllerTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.action_result',
        'app.evaluator',
        'app.evaluation_setting',
        'app.member_type',
        'app.goal',
        'app.follower',
        'app.collaborator',
        'app.local_name',
        'app.cake_session',
        'app.user', 'app.notify_setting',
        'app.image',
        'app.badge',
        'app.team',
        'app.comment_like',
        'app.comment',
        'app.post',
        'app.comment_mention',
        'app.given_badge',
        'app.post_like',
        'app.post_mention',
        'app.post_read',
        'app.images_post',
        'app.comment_read',
        'app.group',
        'app.team_member',
        'app.job_category',
        'app.invite',
        'app.evaluate_term',
        'app.evaluation',
        'app.thread',
        'app.message',
        'app.email',
        'app.send_mail',
        'app.send_mail_to_user',
        'app.oauth_token',
        'app.post_share_user',
        'app.post_share_circle',
        'app.circle',
        'app.circle_member',
    );

    /**
     * testRegister method
     *
     * @return void
     */
    public function testRegister()
    {

        $this->generateMockSecurity();
        Configure::write('Config.language', 'ja');

        $this->testAction('/users/register', ['method' => 'GET', 'return' => 'contents']);
        $this->assertTextContains('新しいアカウントを作成', $this->view, "[ユーザ登録画面]通常のアクセス");

        $this->generateMockSecurity();
        $data = [
            'User'  => [
                'first_name' => '',
            ],
            'Email' => []
        ];
        $this->testAction(
            '/users/register',
            [
                'return' => 'contents',
                'data'   => $data,
                'method' => 'post',
            ]
        );
        $this->assertTextContains('help-block text-danger', $this->view, "【異常系】[ユーザ登録画面]Post");

        $this->generateMockSecurity();
        $data = [
            'User'  => [
                'first_name'       => 'taro',
                'last_name'        => 'sato',
                'password'         => '12345678',
                'password_confirm' => '12345678',
                'agree_tos'        => true,
                'local_date'       => date('Y-m-d H:i:s'),
            ],
            'Email' => [
                ['email' => 'taro@sato.com'],
            ]
        ];
        $this->testAction(
            '/users/register',
            [
                'return' => 'contents',
                'data'   => $data,
                'method' => 'post',
            ]
        );
        $this->assertTextNotContains('help-block text-danger', $this->view, "【正常系】[ユーザ登録画面]Post");

        $intite_token = 'token_test002';
        $this->testAction(
            '/users/register/invite_token:' . $intite_token,
            [
                'return' => 'contents',
                'method' => 'get',
            ]
        );
        $this->assertTextContains('<input type="hidden" name="data[Email][0][email]"', $this->view, "【正常系】[ユーザ登録画面]招待");

        //異常系（トークンが存在しない）
        $this->testAction(
            '/users/register/invite_token:not_found_token',
            [
                'return' => 'contents',
                'method' => 'get',
            ]
        );

        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session'  => ['setFlash'],
                'Auth'     => ['user'],
                'Security' => ['_validateCsrf', '_validatePost'],
            ]
        ]);
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));
        $value_map = [
            ["id", 1234567890],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        $Users->Circle->CircleMember->my_uid = 1234567890;

        $data = [
            'User'  => [
                'first_name'       => 'taro',
                'last_name'        => 'sato',
                'password'         => '12345678',
                'password_confirm' => '12345678',
                'agree_tos'        => true,
                'local_date'       => date('Y-m-d H:i:s'),
            ],
            'Email' => [
                ['email' => 'taro@sato.comaaaaaa'],
            ]
        ];
        $this->testAction(
            '/users/register/invite_token:' . $intite_token,
            [
                'return' => 'contents',
                'data'   => $data,
                'method' => 'post',
            ]
        );
        $this->assertTextNotContains('help-block text-danger', $this->view, "【正常系】[ユーザ登録画面]招待Post");
    }

    function testRegisterValidationError()
    {
        $invite_token = 'token_test002';

        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session'  => ['setFlash'],
                'Auth'     => ['user'],
                'Security' => ['_validateCsrf', '_validatePost'],
            ]
        ]);
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));
        $value_map = [
            ["id", 1234567890],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        $Users->Circle->CircleMember->my_uid = 1234567890;

        $data = [
            'User'  => [
                'first_name'       => '',
                'last_name'        => '',
                'password'         => '12345678',
                'password_confirm' => '12345678',
                'agree_tos'        => true,
                'local_date'       => date('Y-m-d H:i:s'),
            ],
            'Email' => [
                ['email' => 'taro@sato.comaaaaaa'],
            ]
        ];
        $this->testAction(
            '/users/register/invite_token:' . $invite_token,
            [
                'return' => 'contents',
                'data'   => $data,
                'method' => 'post',
            ]
        );
    }

    function testSentMailSuccess()
    {
        Configure::write('Config.language', 'ja');

        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session->expects($this->any())->method('read')
                       ->will($this->returnValueMap([['tmp_email', 'test@aaa.com']]));
        $res = $this->testAction('/users/sent_mail', ['method' => 'GET', 'return' => 'contents']);
        $this->assertContains("おめでとうございます！", $res, "[正常]ユーザ仮登録");
    }

    function testLogin()
    {
        $this->testAction('/users/login', ['method' => 'GET', 'return' => 'contents']);
    }

    function testLoginAlreadyLoggedIn()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, true],
            ['language', 'jpn'],
            ['auto_language_flg', true],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );

        $this->testAction('/users/login', ['method' => 'GET', 'return' => 'contents']);
    }

    function testLoggedInSuccess()
    {
        Configure::write('Config.language', 'en');

        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, null],
            ['language', 'jpn'],
            ['auto_language_flg', true],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('login')
                    ->will($this->returnValue(true));
        $this->generateMockSecurity();
        $data = [
            'User' => [
                'email'    => "to@email.com",
                'password' => "12345678",
            ]
        ];
        $this->testAction('/users/login', ['data' => $data, 'method' => 'post', 'return' => 'vars']);
    }

    function testLoggedInFailed()
    {
        Configure::write('Config.language', 'ja');

        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session'  => ['setFlash'],
                'Auth',
                'Security' => ['_validateCsrf', '_validatePost'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, null],
            ['language', 'jpn'],
            ['auto_language_flg', true],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(false));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));
        $data = [
            'User' => [
                'email'    => "abcdefgto@email.com",
                'password' => "12345678",
            ]
        ];
        $Users->Auth->logout();
        $this->testAction('/users/login', ['data' => $data, 'method' => 'post']);
    }

    function testLoginWithUvParam()
    {
        $this->testAction('/users/login?uv_login=1', ['method' => 'GET']);
    }

    function testLogout()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session' => [
                    'read'
                ],
                'Auth',
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, [
                'display_username' => 'test taro'
            ]],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        $session_value_map = [
            [null, [
                'test'   => 'test',
                'Config' => ''
            ]],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session->expects($this->any())->method('read')
                       ->will($this->returnValueMap($session_value_map)
                       );
        $this->testAction('/users/logout', ['method' => 'GET']);
    }

    function testSentMailFail()
    {
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['tmp_email', null],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session->staticExpects($this->any())->method('read')
                       ->will($this->returnValueMap($value_map)
                       );
        try {
            $this->testAction('/users/sent_mail', ['method' => 'GET', 'return' => 'contents']);
        } catch (NotFoundException $e) {
            $this->controller->beforeRender();

        }
        $this->assertTrue(isset($e), "[異常]ユーザ登録");
    }

    function testVerifySuccess()
    {
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, null],
            ['Auth.redirect', '/aaa'],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session->expects($this->any())->method('read')
                       ->will($this->returnValueMap($value_map));
        $this->testAction('/users/verify/1234567890', ['method' => 'GET', 'return' => 'contents']);
    }

    function testVerifyEmailNotLoggedIn()
    {
        $this->testAction('/users/verify/12345678', ['method' => 'GET', 'return' => 'contents']);
    }

    function testVerifyEmailLoggedInYet()
    {
        $this->testAction('/users/verify/12345', ['method' => 'GET', 'return' => 'contents']);
    }

    function testVerifyEmailNotFound()
    {
        $this->testAction('/users/verify/123456', ['method' => 'GET', 'return' => 'contents']);
    }

    function testSetAppLanguageAutoOn()
    {
        Configure::write('Config.language', 'en');

        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth',
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, 1],
            ['language', 'jpn'],
            ['auto_language_flg', true],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        $this->testAction('/users/register', ['method' => 'GET']);
        $this->assertEquals('en', Configure::read('Config.language'), "自動言語設定がonの場合は言語設定が無視される");
    }

    function testSetAppLanguageAutoOff()
    {
        Configure::write('Config.language', 'en');

        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth',
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, 1],
            ['language', 'jpn'],
            ['auto_language_flg', false],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        $this->testAction('/users/register', ['method' => 'GET']);
        $this->assertEquals('jpn', Configure::read('Config.language'), "自動言語設定がoffの場合は言語設定が適用される");
    }

    function generateMockSecurity()
    {
        $Users = $this->generate('Users', [
            'components' => [
                'Security' => ['_validateCsrf', '_validatePost'],
            ],
        ]);
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));
    }

    function testAddProfileJpn()
    {
        Configure::write('Config.language', 'ja');

        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, [
                'last_first' => true,
                'language'   => 'jpn'
            ]],
            ['language', 'jpn'],
            ['auto_language_flg', true],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session->expects($this->any())->method('read')
                       ->will($this->returnValueMap([['add_new_mode', MODE_NEW_PROFILE]]));

        $this->testAction('/users/add_profile', ['method' => 'GET', 'return' => 'contents']);
        $this->assertContains('姓(日本語)', $this->contents, "[正常]日本語でローカル名の入力項目が表示される");
    }

    function testAddProfilePut()
    {
        Configure::write('Config.language', 'ja');

        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth'     => ['user', 'loggedIn'],
                'Security' => ['_validateCsrf', '_validatePost'],
            ]
        ]);
        $value_map = [
            [null, [
                'id'         => 'xxx',
                'last_first' => true,
                'language'   => 'jpn'
            ]],
            ['language', 'jpn'],
            ['auto_language_flg', true],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));

        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session->expects($this->any())->method('read')
                       ->will($this->returnValueMap([['add_new_mode', MODE_NEW_PROFILE]]));
        $data = [
            'User'      => [
                'id'         => 'xxx',
                'last_name'  => 'mei',
                'first_name' => 'sei',
            ],
            'LocalName' => [
                [
                    'first_name' => null,
                    'last_name'  => null,
                    'language'   => 'jpn',
                ]
            ]
        ];
        $this->testAction('/users/add_profile', ['method' => 'PUT', 'data' => $data, 'return' => 'contents']);
        $this->assertRegExp("/" . preg_quote("/teams/add", "/") . "$/", $this->headers["Location"],
                            "[正常]Post後にチーム作成画面へ遷移");
    }

    function testAddProfilePutInvite()
    {
        Configure::write('Config.language', 'ja');

        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth'     => ['user', 'loggedIn'],
                'Security' => ['_validateCsrf', '_validatePost'],
            ]
        ]);
        $value_map = [
            [null, [
                'id'         => 'xxx',
                'last_first' => true,
                'language'   => 'jpn'
            ]],
            ['language', 'jpn'],
            ['auto_language_flg', true],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));

        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session->expects($this->any())->method('read')
                       ->will($this->returnValueMap([['add_new_mode', MODE_NEW_PROFILE]]));
        $data = [
            'User'      => [
                'id'         => 'xxx',
                'last_name'  => 'mei',
                'first_name' => 'sei',
            ],
            'LocalName' => [
                [
                    'first_name' => "めい",
                    'last_name'  => "せい",
                    'language'   => 'jpn',
                ]
            ]
        ];
        $this->testAction('/users/add_profile/invite_token:test',
                          ['method' => 'PUT', 'data' => $data, 'return' => 'contents']);

        $this->assertRegExp("/" . preg_quote("/", "/") . "$/", $this->headers["Location"],
                            "[正常]Post後にホーム画面へ遷移");
    }

    function testAddProfileEng()
    {
        Configure::write('Config.language', 'ja');

        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, [
                'last_first' => true,
                'language'   => 'eng'
            ]],
            ['language', 'eng'],
            ['auto_language_flg', true],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session->expects($this->any())->method('read')
                       ->will($this->returnValueMap([['add_new_mode', MODE_NEW_PROFILE]]));

        $this->testAction('/users/add_profile', ['method' => 'GET', 'return' => 'contents']);
        $this->assertNotContains('姓(母国語)', $this->contents, "[正常]英語でローカル名の入力項目が表示されない");
    }

    function testAddProfileException()
    {
        Configure::write('Config.language', 'ja');

        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, [
                'last_first' => true,
                'language'   => 'eng'
            ]],
            ['language', 'eng'],
            ['auto_language_flg', true],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session->expects($this->any())->method('read')
                       ->will($this->returnValueMap([['add_new_mode', null]]));

        try {
            $this->testAction('/users/add_profile', ['method' => 'GET', 'return' => 'contents']);
        } catch (NotFoundException $e) {

        }
        $this->assertTrue(isset($e), "[異常]新規ユーザ登録モード以外は例外発生");
    }

    function testPasswordReset()
    {
        $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $this->testAction('/users/password_reset');
        $this->testAction('/users/password_reset', ['method' => 'GET']);
    }

    function testPasswordResetWithParam()
    {
        $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $this->testAction('/users/password_reset/aaaaa');
        $this->testAction('/users/password_reset/aaaaa', ['method' => 'GET']);

    }

    function testPasswordResetAuthenticated()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, [
                'id' => "xxxxxx",
            ]],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        try {
            $this->testAction('users/password_reset');
        } catch (Exception $e) {
        }
        $this->assertTrue(isset($e), "[異常]パスワードリセット ログイン中の例外");

    }

    function testPasswordResetPost()
    {
        $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);

        App::uses('UserTest', 'Test/Case/Model');
        $UserTest = new UserTest;
        $UserTest->setUp();
        $uid = $UserTest->generateBasicUser();
        /** @noinspection PhpUndefinedMethodInspection */
        $email = $UserTest->User->Email->findByUserId($uid);

        $data = ['User' => ['email' => $email['Email']['email']]];
        $this->testAction('users/password_reset', ['data' => $data]);
    }

    function testPasswordResetPostToken()
    {
        $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        App::uses('UserTest', 'Test/Case/Model');
        $UserTest = new UserTest;
        $UserTest->setUp();
        $uid = $UserTest->generateBasicUser();
        /** @noinspection PhpUndefinedMethodInspection */
        $user = $UserTest->User->findById($uid);
        $user['User']['password_token'] = "test123456788";
        $email = $UserTest->User->Email->findById($user['User']['primary_email_id']);
        $email['Email']['email_token_expires'] = time() + (24 * 60 * 60);
        $UserTest->User->Email->save($email);
        $UserTest->User->save($user);
        $this->testAction('users/password_reset/' . $user['User']['password_token']);
        $this->testAction('users/password_reset/' . $user['User']['password_token'], ['method' => 'GET']);
    }

    function testPasswordResetPostPassword()
    {
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);

        $basic_data = [
            'User'  => [
                'first_name'     => 'basic',
                'last_name'      => 'user',
                'password'       => 'aaaaaaaaaa',
                'password_token' => 'abcde',
                'active_flg'     => true,
            ],
            'Email' => [
                [
                    'email'               => 'basic@email.com',
                    'email_verified'      => true,
                    'email_token_expires' => time() + (60 * 60)
                ]
            ]
        ];
        /** @noinspection PhpUndefinedFieldInspection */
        $Users->User->saveAll($basic_data);
        /** @noinspection PhpUndefinedFieldInspection */
        $Users->User->save(['primary_email_id' => $Users->User->Email->getLastInsertID()]);

        $data = [
            'User' => [
                'password'         => '12345678',
                'password_confirm' => '12345678',
            ]
        ];
        $this->testAction('users/password_reset/abcde', ['data' => $data, 'method' => 'POST']);
    }

    function testTokenResend()
    {
        $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $this->_testAction('users/token_resend');
    }

    function testTokenResendAuthenticated()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, [
                'id' => "xxxxxx",
            ]],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        try {
            $this->testAction('users/token_resend');
        } catch (Exception $e) {
        }
        $this->assertTrue(isset($e), "[異常]トークン再送。ログイン中は例外処理");
    }

    function testTokenResendPostEmail()
    {
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $basic_data = [
            'User'  => [
                'first_name'     => 'basic',
                'last_name'      => 'user',
                'password'       => 'aaaaaaaaaa',
                'password_token' => 'abcde',
                'active_flg'     => false,
            ],
            'Email' => [
                [
                    'email'               => 'basic@email.com',
                    'email_verified'      => false,
                    'email_token_expires' => date('Y-m-d H:i:s', time() + 60 * 60)
                ]
            ]
        ];
        /** @noinspection PhpUndefinedFieldInspection */
        $Users->User->saveAll($basic_data);
        /** @noinspection PhpUndefinedFieldInspection */
        $Users->User->save(['primary_email_id' => $Users->User->Email->getLastInsertID()]);

        $data = [
            'User' => [
                'email' => 'basic@email.com',
            ]
        ];
        $this->testAction('users/token_resend', ['data' => $data, 'method' => 'POST']);
    }

    function testSetting()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            [null, [
                'id'       => "12",
                'language' => 'jpn',
            ]],
            ['id', "12"],
            ['language', "jpn"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));

        $res = $this->testAction('users/settings', ["method" => 'GET', 'return' => 'contents']);
        $this->assertContains('日本語', $res, "[正常]ユーザ設定画面で言語名が表示されている");
        $this->assertContains('ろーかる名', $res, "[正常]ユーザ設定画面で言語別の名前が表示されている");
    }

    function testSettingPutSuccess()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth'     => ['user', 'loggedIn'],
                'Security' => ['_validateCsrf', '_validatePost'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));

        $value_map = [
            [null, [
                'id'       => "12",
                'language' => 'jpn',
            ]],
            ['id', "12"],
            ['language', "jpn"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        $data = [
            'User' => [
                'update_email_flg' => true,
            ]
        ];

        $res = $this->testAction('users/settings', ['method' => 'PUT', 'data' => $data, 'return' => 'contents']);
        $this->assertContains('日本語', $res, "[正常]ユーザ設定画面で言語名が表示されている");
        $this->assertContains('ろーかる名', $res, "[正常]ユーザ設定画面で言語別の名前が表示されている");
    }

    function testSettingPutFail()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "12"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        $data = [
            'User' => [
                'first_name' => null,
                'last_name'  => null,
            ]
        ];

        $this->testAction('users/settings', ['method' => 'PUT', 'data' => $data]);
    }

    function testChangePasswordFail()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "12"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        $data = [
            'User' => [
                'old_password'     => null,
                'password'         => null,
                'password_confirm' => null
            ]
        ];

        $this->testAction('users/change_password', ['method' => 'PUT', 'data' => $data]);
    }

    function testChangePasswordSuccess()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "12"],
        ];
        $uid = "12";
        $Users->User->id = $uid;
        $Users->User->saveField('password', $Users->User->generateHash('12345678'));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        $data = [
            'User' => [
                'id'               => '12',
                'old_password'     => '12345678',
                'password'         => '12345678',
                'password_confirm' => '12345678'
            ]
        ];
        $this->testAction('users/change_password', ['method' => 'PUT', 'data' => $data]);
    }

    function testChangePasswordFailNotSame()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "12"],
        ];
        $uid = "12";
        $Users->User->id = $uid;
        $Users->User->saveField('password', $Users->User->generateHash('12345678'));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        $data = [
            'User' => [
                'id'               => '12',
                'old_password'     => '1234567890',
                'password'         => '12345678',
                'password_confirm' => '12345678'
            ]
        ];
        $this->testAction('users/change_password', ['method' => 'PUT', 'data' => $data]);
    }

    function testChangePasswordException()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "12"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        try {
            $this->testAction('users/change_password', ['method' => 'GET']);
        } catch (NotFoundException $e) {
        }
        $this->assertTrue(isset($e), "[例外]パスワード変更");
    }

    function testChangeEmailVerifySuccess()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "12"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        $token = "token_test0123456789";
        try {
            $this->testAction('users/change_email_verify/' . $token, ['method' => 'GET']);
        } catch (NotFoundException $e) {
        }
        $this->assertFalse(isset($e), "[正常]メアド追加");
    }

    function testChangeEmailVerifyFail()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "12"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        $token = "token_test0123456789aaaa";
        try {
            $this->testAction('users/change_email_verify/' . $token, ['method' => 'GET']);
        } catch (NotFoundException $e) {
        }
        $this->assertFalse(isset($e), "[異常]メアド変更");
    }

    function testChangeEmailFail()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "12"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        try {
            $this->testAction('users/change_email', ['method' => 'POST', 'data' => []]);
        } catch (NotFoundException $e) {
        }
        $this->assertTrue(isset($e), "[異常]メアド追加");
    }

    function testChangeEmailFailNotData()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "12"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        try {
            $this->testAction('users/change_email', ['method' => 'PUT', 'data' => ['User' => ['email' => null]]]);
        } catch (NotFoundException $e) {
        }
    }

    function testChangeEmailSuccess()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "10"],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        try {
            $this->testAction('users/change_email',
                              ['method' => 'PUT', 'data' => ['User' => ['email' => 'abcde@1234.com']]]);
        } catch (NotFoundException $e) {
        }
    }

    function testAcceptInvite()
    {
        $invite_token = 'token_test002';
        //$invite_id = '2';

        //ユーザ有,未ログイン,
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', null],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(false));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        $this->testAction('users/accept_invite/' . $invite_token, ['method' => 'GET', 'return' => 'contents']);

        //ユーザなし
        $invite_token = "token_not_user_001";
        $this->testAction('users/accept_invite/' . $invite_token, ['method' => 'GET', 'return' => 'contents']);

        //新規
        $invite_token = 'token_test003';
        $this->testAction('users/accept_invite/' . $invite_token, ['method' => 'GET', 'return' => 'contents']);
    }

    function testAcceptInviteLoggedInForMe()
    {
        $intite_token = 'token_test002';
        //ユーザ有,ログイン済,自分あてのtoken
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "2"],
            [null, true]
        ];
        $user = $Users->User->getDetail("2");
        $Users->User->me = $user['User'];
        $Users->Circle->CircleMember->my_uid = 2;

        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        $this->testAction('users/accept_invite/' . $intite_token, ['method' => 'GET', 'return' => 'contents']);
    }

    function testAcceptInviteLoggedInForMeNotToUserId()
    {
        $intite_token = 'token_test004';
        //ユーザ有,ログイン済,自分あてのtoken
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "1"],
            [null, true]
        ];
        $user = $Users->User->getDetail("1");
        $Users->User->me = $user['User'];
        $Users->Circle->CircleMember->my_uid = 1;

        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        $this->testAction('users/accept_invite/' . $intite_token, ['method' => 'GET', 'return' => 'contents']);
    }

    function testAcceptInviteLoggedInForOther()
    {

        $intite_token = 'token_test002';
        //ユーザ有,ログイン済,他人あてのtoken
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth' => ['user', 'loggedIn'],
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        $value_map = [
            ['id', "10"],
            [null, true]
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        $user = $Users->User->getDetail("10");
        $Users->User->me = $user['User'];
        $Users->Circle->CircleMember->my_uid = 10;

        try {
            $this->testAction('users/accept_invite/' . $intite_token, ['method' => 'GET', 'return' => 'contents']);
        } catch (RuntimeException $e) {

        }
        $this->assertFalse(isset($e), "[異常]招待で別のユーザ宛");
    }

    function testAjaxSelect2Success()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->_getUsersCommonMock();
        $Users->User->TeamMember->current_team_id = 1;
        $Users->User->TeamMember->my_uid = 1;

        /** @noinspection PhpUndefinedFieldInspection */
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/users/ajax_select2_get_users?term=firstname&page_limit=10', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxSelect2GetUsers()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->_getUsersCommonMock();
        $Users->User->TeamMember->current_team_id = 1;
        $Users->User->TeamMember->my_uid = 1;
        $Users->User->CircleMember->my_uid = 1;
        $Users->User->CircleMember->current_team_id = 1;
        $Users->User->CircleMember->Circle->PostShareCircle->my_uid = 1;
        $Users->User->CircleMember->Circle->PostShareCircle->current_team_id = 1;

        /** @noinspection PhpUndefinedFieldInspection */
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/users/ajax_select2_get_circles_users?term=firstname&page_limit=10&circle_type=all', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxSelect2GetSecretCircles()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->_getUsersCommonMock();
        $Users->User->TeamMember->current_team_id = 1;
        $Users->User->TeamMember->my_uid = 1;
        $Users->User->CircleMember->my_uid = 1;
        $Users->User->CircleMember->current_team_id = 1;
        $Users->User->CircleMember->Circle->PostShareCircle->my_uid = 1;
        $Users->User->CircleMember->Circle->PostShareCircle->current_team_id = 1;

        /** @noinspection PhpUndefinedFieldInspection */
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $keyword = urlencode('秘密サークル');
        $res = $this->testAction("/users/ajax_select2_get_secret_circles?term=$keyword&page_limit=10", ['method' => 'GET']);
        $json_data = json_decode($res, true);
        $this->assertNotEmpty($json_data['results']);

        $keyword = urlencode('チーム全体サークル');
        $res = $this->testAction("/users/ajax_select2_get_secret_circles?term=$keyword&page_limit=10", ['method' => 'GET']);
        $json_data = json_decode($res, true);
        $this->assertEmpty($json_data['results']);

        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxSelect2CircleUserAll()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->_getUsersCommonMock();
        $Users->User->current_team_id = 1;
        $Users->User->my_uid = 1;
        $Users->User->TeamMember->current_team_id = 1;
        $Users->User->TeamMember->my_uid = 1;
        $Users->User->CircleMember->my_uid = 1;
        $Users->User->CircleMember->current_team_id = 1;
        $Users->User->CircleMember->Circle->PostShareCircle->my_uid = 1;
        $Users->User->CircleMember->Circle->PostShareCircle->current_team_id = 1;
        /** @noinspection PhpUndefinedFieldInspection */
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/users/ajax_get_select2_circle_user_all', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetPostCount()
    {
        $this->_getUsersCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/users/ajax_get_post_count/', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetActionCount()
    {
        $this->_getUsersCommonMock();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/users/ajax_get_action_count/', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testRegistrationWithSetPasswordAuthenticated()
    {
        $Users = $this->_getUsersCommonMock();
        $value_map = [
            ['id', "1"],
            [null, true]
        ];

        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map));
        try {
            $this->testAction('/users/registration_with_set_password/', ['method' => 'GET']);
        } catch (NotFoundException $e) {

        }
        $this->assertTrue(isset($e));
    }

    function testRegistrationWithSetPasswordNoParam()
    {
        $this->_getUsersMockNotAuthenticated();
        try {
            $this->testAction('/users/registration_with_set_password/', ['method' => 'GET']);
        } catch (NotFoundException $e) {
        }
        $this->assertTrue(isset($e));
    }

    function testRegistrationWithSetPasswordTokenValidateFail()
    {
        $this->_getUsersMockNotAuthenticated();
        $this->testAction('/users/registration_with_set_password/invite_token:test', ['method' => 'GET']);
    }

    function testRegistrationWithSetPasswordGetSuccess()
    {
        $Users = $this->_getUsersMockNotAuthenticated();
        $save_user = [
            'first_name'  => 'test',
            'last_name'   => 'tarou',
            'no_pass_flg' => true,
        ];
        $Users->User->save($save_user);
        $save_email = [
            'email'   => 'test_invite@aaaaa.com',
            'user_id' => $Users->User->getLastInsertID(),
        ];
        $Users->User->Email->save($save_email);
        $save_invite = [
            'from_user_id'        => 1,
            'to_user_id'          => 2,
            'team_id'             => 1,
            'email'               => 'test_invite@aaaaa.com',
            'email_token'         => 'test_token_csv',
            'email_token_expires' => time() + 60 * 60 * 24,
            'type'                => 1,
        ];
        $Users->Invite->save($save_invite);
        $this->testAction('/users/registration_with_set_password/invite_token:test_token_csv', ['method' => 'GET']);
    }

    function testRegistrationWithSetPasswordPostEmailNotMatch()
    {
        $Users = $this->_getUsersMockNotAuthenticated();
        $save_user = [
            'first_name'  => 'test',
            'last_name'   => 'tarou',
            'no_pass_flg' => true,
        ];
        $Users->User->save($save_user);
        $save_email = [
            'email'   => 'test_invite@aaaaa.com',
            'user_id' => $Users->User->getLastInsertID(),
        ];
        $Users->User->Email->save($save_email);
        $save_invite = [
            'from_user_id'        => 1,
            'to_user_id'          => 2,
            'team_id'             => 1,
            'email'               => 'test_invite@aaaaa.com',
            'email_token'         => 'test_token_csv',
            'email_token_expires' => time() + 60 * 60 * 24,
            'type'                => 1,
        ];
        $Users->Invite->save($save_invite);
        $post_data = [
            'Email' =>
                ['email' => 'test@aaa.com']
        ];
        $this->testAction('/users/registration_with_set_password/invite_token:test_token_csv',
                          ['method' => 'POST', 'data' => $post_data]);
    }

    function testRegistrationWithSetPasswordPostEmailSuccess()
    {
        $Users = $this->_getUsersMockNotAuthenticated();
        $save_user = [
            'first_name'  => 'test',
            'last_name'   => 'tarou',
            'no_pass_flg' => true,
        ];
        $Users->User->save($save_user);
        $save_email = [
            'email'   => 'test_invite@aaaaa.com',
            'user_id' => $Users->User->getLastInsertID(),
        ];
        $Users->User->Email->save($save_email);
        $save_invite = [
            'from_user_id'        => 1,
            'to_user_id'          => 2,
            'team_id'             => 1,
            'email'               => 'test_invite@aaaaa.com',
            'email_token'         => 'test_token_csv',
            'email_token_expires' => time() + 60 * 60 * 24,
            'type'                => 1,
        ];
        $Users->Invite->save($save_invite);
        $post_data = [
            'User'  => [
                'password'         => 'testtesttest',
                'password_confirm' => 'testtesttest',
                'local_date'       => '2014/11/11',
            ],
            'Email' =>
                ['email' => 'test_invite@aaaaa.com']
        ];
        $this->testAction('/users/registration_with_set_password/invite_token:test_token_csv',
                          ['method' => 'POST', 'data' => $post_data]);
    }

    function testAjaxGetModal2faRegisterNoSession()
    {
        $this->_getUsersCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/users/ajax_get_modal_2fa_register', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testAjaxGetModal2faRegisterExistsSession()
    {
        $Users = $this->_getUsersCommonMock();
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValueMap([['2fa_secret_key', '123456']]));
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/users/ajax_get_modal_2fa_register', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testRegister2faNotFoundSecret()
    {
        $this->_getUsersCommonMock();
        $this->testAction('/users/register_2fa', ['method' => 'POST']);
    }

    function testRegister2faNoData()
    {
        $Users = $this->_getUsersCommonMock();
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValueMap([['2fa_secret_key', '123456']]));
        $this->testAction('/users/register_2fa', ['method' => 'POST']);
    }

    function testRegister2faVerifyFail()
    {
        $Users = $this->_getUsersCommonMock();
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValueMap([['2fa_secret_key', 'R2ZR6FTBJUHDUOC7']]));
        $this->testAction('/users/register_2fa', ['method' => 'POST', 'data' => ['User' => ['2fa_code' => '123456']]]);
    }

    function testRegister2faVerifySuccess()
    {
        $Users = $this->_getUsersCommonMock();
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValueMap([['2fa_secret_key', 'R2ZR6FTBJUHDUOC7']]));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->TwoFa
            ->expects($this->any())
            ->method('verifyKey')
            ->with('R2ZR6FTBJUHDUOC7', '123456')
            ->will($this->returnValue(true));
        $this->testAction('/users/register_2fa', ['method' => 'POST', 'data' => ['User' => ['2fa_code' => '123456']]]);
    }

    function testAjaxGetModal2faDelete()
    {
        $this->_getUsersCommonMock();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->testAction('/users/ajax_get_modal_2fa_delete', ['method' => 'GET']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    function testDelete2Fa()
    {
        $this->_getUsersCommonMock();
        $this->testAction('/users/delete_2fa', ['method' => 'POST']);
    }

    function testSetDefaultTeamSuccess()
    {
        $Users = $this->_getUsersCommonMock();
        $Users->_setDefaultTeam(1);
    }

    function testSetDefaultTeamFail()
    {
        $Users = $this->_getUsersCommonMock();
        $Users->_setDefaultTeam(9999);
    }

    function testViewGoalsMine()
    {
        $this->_getUsersCommonMock();
        $this->testAction('/users/view_goals/user_id:1');
    }
    function testViewGoalsOthers()
    {
        $this->_getUsersCommonMock();
        $this->testAction('/users/view_goals/user_id:2');
    }

    function testViewPosts()
    {
        $this->_getUsersCommonMock();
        $this->testAction('/users/view_posts/user_id:1');
    }
    function testViewActions()
    {
        $this->_getUsersCommonMock();
        $this->testAction('/users/view_actions/user_id:1');
    }
    function testViewInfo()
    {
        $this->_getUsersCommonMock();
        $this->testAction('/users/view_info/user_id:1');
    }
    function testViewInfoNoParams()
    {
        $this->_getUsersCommonMock();
        try {
            $this->testAction('/users/view_info/');
        } catch (NotFoundException $e) {
        }
    }
    function testViewInfoNoTeamMember()
    {
        $this->_getUsersCommonMock();
        try {
            $this->testAction('/users/view_info/user_id:14');
        } catch (NotFoundException $e) {
        }
    }

    function _getUsersCommonMock()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Auth'     => ['user', 'loggedIn'],
                'Security' => ['_validateCsrf', '_validatePost'],
                'Ogp',
                'NotifyBiz',
                'GlEmail',
                'TwoFa',
            ]
        ]);
        $value_map = [
            [null, [
                'id'         => '1',
                'last_first' => true,
                'language'   => 'jpn'
            ]],
            ['id', '1'],
            ['language', 'jpn'],
            ['auto_language_flg', true],
        ];
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));

        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->expects($this->any())->method('loggedIn')
                    ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Auth->staticExpects($this->any())->method('user')
                    ->will($this->returnValueMap($value_map)
                    );
        /** @noinspection PhpUndefinedFieldInspection */
        $Users->User->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Users->User->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Users->Goal->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Users->Goal->current_team_id = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Users->User->CircleMember->my_uid = '1';
        /** @noinspection PhpUndefinedFieldInspection */
        $Users->User->CircleMember->current_team_id = '1';

        return $Users;
    }

    function _getUsersMockNotAuthenticated()
    {
        /**
         * @var UsersController $Users
         */
        $Users = $this->generate('Users', [
            'components' => [
                'Session',
                'Security' => ['_validateCsrf', '_validatePost'],
                'Ogp',
                'NotifyBiz',
                'GlEmail',
            ]
        ]);
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validateCsrf')
            ->will($this->returnValue(true));
        /** @noinspection PhpUndefinedMethodInspection */
        $Users->Security
            ->expects($this->any())
            ->method('_validatePost')
            ->will($this->returnValue(true));

        return $Users;
    }
}
