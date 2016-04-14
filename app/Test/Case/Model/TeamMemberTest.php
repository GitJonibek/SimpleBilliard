<?php App::uses('GoalousTestCase', 'Test');
App::uses('TeamMember', 'Model');

/**
 * TeamMember Test Case
 *
 * @property TeamMember $TeamMember
 */
class TeamMemberTest extends GoalousTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'app.evaluation',
        'app.evaluate_score',
        'app.team_member',
        'app.member_group',
        'app.evaluator',
        'app.email',
        'app.local_name',
        'app.member_type',
        'app.user', 'app.notify_setting',
        'app.team',
        'app.group',
        'app.job_category',
        'app.evaluate_term',
        'app.circle',
        'app.circle_member',
    );

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->TeamMember = ClassRegistry::init('TeamMember');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->TeamMember);

        parent::tearDown();
    }

    function testGetActiveTeamList()
    {
        $uid = '1';
        $data = [
            'TeamMember' => [['user_id' => $uid,]],
            'Team'       => [
                'name' => 'test'
            ]
        ];
        $this->TeamMember->my_uid = $uid;
        Cache::delete($this->TeamMember->getCacheKey(CACHE_KEY_TEAM_LIST, true, $uid, false), 'team_info');
        $before_cunt = count($this->TeamMember->getActiveTeamList($uid));
        $this->TeamMember->Team->saveAll($data);
        $this->TeamMember->myTeams = null;
        Cache::delete($this->TeamMember->getCacheKey(CACHE_KEY_TEAM_LIST, true, $uid, false), 'team_info');
        $res = $this->TeamMember->getActiveTeamList($uid);
        $this->assertEquals(count($res), $before_cunt + 1);

        $this->TeamMember->Team->saveAll($data);
        $this->TeamMember->myTeams = null;
        Cache::delete($this->TeamMember->getCacheKey(CACHE_KEY_TEAM_LIST, true, $uid, false), 'team_info');
        $res = $this->TeamMember->getActiveTeamList($uid);
        $this->assertEquals(count($res), $before_cunt + 2);

        $this->TeamMember->delete();
        $this->TeamMember->myTeams = null;
        Cache::delete($this->TeamMember->getCacheKey(CACHE_KEY_TEAM_LIST, true, $uid, false), 'team_info');
        $res = $this->TeamMember->getActiveTeamList($uid);
        $this->assertEquals(count($res), $before_cunt + 1);

        $this->TeamMember->Team->saveAll($data);
        $this->TeamMember->myTeams = null;
        Cache::delete($this->TeamMember->getCacheKey(CACHE_KEY_TEAM_LIST, true, $uid, false), 'team_info');
        $res = $this->TeamMember->getActiveTeamList($uid);
        $this->assertEquals(count($res), $before_cunt + 2);

        $this->TeamMember->saveField('active_flg', false);
        $this->TeamMember->myTeams = null;
        Cache::delete($this->TeamMember->getCacheKey(CACHE_KEY_TEAM_LIST, true, $uid, false), 'team_info');
        $res = $this->TeamMember->getActiveTeamList($uid);
        $this->assertEquals(count($res), $before_cunt + 1);

    }

    function testPermissionCheck()
    {
        $team_id = null;
        $uid = '1';
        try {
            $this->TeamMember->permissionCheck($team_id, $uid);
        } catch (RuntimeException $e) {
        }
        $this->assertTrue(isset($e), "[異常]ユーザ権限チェック　チーム切換えなし");

        $this->TeamMember->myStatusWithTeam = null;
        $data = [
            'TeamMember' => [['user_id' => $uid,]],
        ];
        $this->TeamMember->Team->save($data);
        try {
            $this->TeamMember->permissionCheck("test", $uid);
        } catch (RuntimeException $e) {
        }
        $this->assertTrue(isset($e), "[異常]ユーザ権限チェック　チームなし");

        $this->TeamMember->myStatusWithTeam = null;

        $data = [
            'TeamMember' => [
                [
                    'user_id'    => $uid,
                    'active_flg' => false,
                ]
            ],
            'Team'       => [
                'name' => 'test'
            ]
        ];
        $this->TeamMember->Team->saveAll($data);
        try {
            $this->TeamMember->permissionCheck($this->TeamMember->Team->getLastInsertID(), $uid);
        } catch (RuntimeException $e) {
        }
        $this->assertTrue(isset($e), "[異常]ユーザ権限チェック　非アクティブ");

        $this->TeamMember->myStatusWithTeam = null;

        $data = [
            'TeamMember' => [
                [
                    'user_id'    => $uid,
                    'active_flg' => true,
                ]
            ],
            'Team'       => [
                'name' => 'test'
            ]
        ];
        $this->TeamMember->Team->saveAll($data);
        $res = $this->TeamMember->permissionCheck($this->TeamMember->Team->getLastInsertID(), $uid);
        $this->assertTrue($res, "[正常]ユーザ権限チェック");
    }

    function testGetWithTeam()
    {
        $this->setDefault();
        $res = $this->TeamMember->getWithTeam();
        $this->assertNotEmpty($res);
        $this->TeamMember->setMyStatusWithTeam(1, 1);
        $res = $this->TeamMember->getWithTeam();
        $this->assertNotEmpty($res);
        $this->TeamMember->myStatusWithTeam = null;
        $res = $this->TeamMember->getWithTeam();
        $this->assertNotEmpty($res);

    }

    function testAdminCheck()
    {
        $this->TeamMember->myStatusWithTeam = null;
        $uid = '1';

        $data = [
            'TeamMember' => [
                [
                    'user_id'    => $uid,
                    'active_flg' => true,
                    'admin_flg'  => false,
                ]
            ],
            'Team'       => [
                'name' => 'test'
            ]
        ];
        $this->TeamMember->Team->saveAll($data);
        try {
            $this->TeamMember->adminCheck($this->TeamMember->Team->getLastInsertID(), $uid);
        } catch (RuntimeException $e) {
        }
        $this->assertTrue(isset($e), "[異常]アドミンチェック　非アドミン");

        $this->TeamMember->myStatusWithTeam = null;
        $data = [
            'TeamMember' => [
                [
                    'user_id'    => $uid,
                    'active_flg' => true,
                    'admin_flg'  => true,
                ]
            ],
            'Team'       => [
                'name' => 'test'
            ]
        ];
        $this->TeamMember->Team->saveAll($data);
        $this->TeamMember->current_team_id = $this->TeamMember->Team->getLastInsertID();
        $this->TeamMember->my_uid = $uid;
        $res = $this->TeamMember->adminCheck();
        $this->assertTrue($res, "[正常]アドミンチェック");

    }

    function testAdd()
    {
        $uid = '1';

        $data = [
            'Team' => [
                'name' => 'test'
            ]
        ];
        $this->TeamMember->Team->save($data);
        $res = $this->TeamMember->add($uid, $this->TeamMember->Team->id);
        $this->assertTrue($res['TeamMember']['active_flg'], "[正常]メンバー追加でアクティブフラグon");
        $this->assertArrayHasKey("id", $res['TeamMember'], "[正常]メンバー追加が正常に完了");
        $res = $this->TeamMember->add($uid, $this->TeamMember->Team->id);
        $this->assertTrue($res['TeamMember']['active_flg'], "[正常]メンバー追加でアクティブフラグon");
        $this->assertArrayHasKey("id", $res['TeamMember'], "[正常]メンバー追加が正常に完了");
    }

    function testGetTeamAdminUidNotNull()
    {
        $uid = 1;
        $team_id = 1;
        $this->TeamMember->current_team_id = $team_id;
        $this->TeamMember->my_uid = $uid;
        $admin_id = $this->TeamMember->getTeamAdminUid();
        $this->assertEquals(1, $admin_id);
    }

    function testGetTeamAdminUidNull()
    {
        $uid = 1;
        $team_id = 1;
        $this->TeamMember->current_team_id = $team_id;
        $this->TeamMember->my_uid = $uid;
        $this->TeamMember->updateAll(['TeamMember.admin_flg' => false], ['TeamMember.team_id' => 1]);
        $admin_id = $this->TeamMember->getTeamAdminUid();
        $this->assertEquals(null, $admin_id);
    }

    function testGetAllMemberUserIdList()
    {
        $uid = 1;
        $team_id = 1;
        $this->TeamMember->current_team_id = $team_id;
        $this->TeamMember->my_uid = $uid;
        $this->TeamMember->getAllMemberUserIdList(false);
    }

    function testGetAllMemberUserIdListWithEval()
    {
        $uid = 1;
        $team_id = 1;
        $this->TeamMember->current_team_id = $team_id;
        $this->TeamMember->my_uid = $uid;
        $this->TeamMember->save(['id' => 1, 'evaluation_enable_flg' => false]);
        $expected = [
            (int)2 => '2',
            (int)3 => '12',
            (int)4 => '13'
        ];
        $actual = $this->TeamMember->getAllMemberUserIdList(true, true, true);
        $this->assertEquals($expected, $actual);
    }

    function testSaveNewMembersFromCsvSuccessChangeLocalName()
    {
        $this->setDefault();
        $this->TeamMember->Team->Circle->current_team_id = 1;

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = [
            'csv_test@email.com', 'aaa', 'first', 'last', 'on', 'off', null, 'jpn', 'ふぁーすと', 'ラスト'
        ];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->saveNewMembersFromCsv($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => false,
            'error_line_no' => 0,
            'error_msg'     => null,
            'success_count' => 1,
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataDifferenceTitle()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[] = $this->getEmptyRowOnCsv();
        $csv_data[0]['name'] = 'xxx';
        $csv_data[] = $this->getEmptyRowOnCsv();

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 0
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataDifferenceColumnCount()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        unset($csv_data[1][0]);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataEmpty()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[] = $this->TeamMember->_getCsvHeading();

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 0
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataEmptyEmail()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataValidateEmail()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[] = $this->TeamMember->_getCsvHeading();
        $csv_data[] = $this->getEmptyRowOnCsv();
        $csv_data[1][0] = 'aaa';

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataMemberIdEmpty()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[] = $this->TeamMember->_getCsvHeading();
        $csv_data[] = $this->getEmptyRowOnCsv();
        $csv_data[1][0] = 'aaa@aaa.com';
        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataFirstNameEmpty()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $csv_data[1][0] = 'aaa@aaa.com';
        $csv_data[1][1] = 'aaa';

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataFirstNameOnlyRoman()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[] = $this->TeamMember->_getCsvHeading();
        $csv_data[] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'aaa', 'ああああ',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataLastNameEmpty()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', '',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataLastNameOnlyRoman()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'あああ',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataAdminEmpty()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', '',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataAdminNotOnOrOff()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'aaaa',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataEvaluateEmpty()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', ''];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataEvaluateNotOnOrOff()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'aaaa'];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataLangCodeNotSupport()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'aaaaa',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataValidatePhone()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', 'aaaaaaa',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataValidateGender()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'aaaa',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataBirthDayAllOrNothing()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', '', '',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataBirthYearValidate()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', 'aaaaa', '1', '1',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataBirthMonthValidate()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', 'aaaa', '1',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataBirthDayValidate()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', '11', 'aaaa',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataGroupAlignLeft()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', '11', '11', 'group1', '', 'group3', '', '', '', '',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataGroupDuplicate()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', '11', '11', 'group1', 'group1', '', '', '', '', '',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataCoachIdIsNotMemberId()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', '11', '11', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'member_id',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataEvaluatorIdAlignLeft()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', '11', '11', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'coach_id', 'rater1', '', 'rater3', 'rater4', 'rater5', 'rater6', 'rater7',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataEvaluatorIdNotIncludeMemberId()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', '11', '11', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'coach_id', 'member_id', 'rater2', 'rater3', 'rater4', 'rater5', 'rater6', 'rater7',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataEvaluatorIdDuplicate()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', '11', '11', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'coach_id', 'rater2', 'rater2', 'rater3', 'rater4', 'rater5', 'rater6', 'rater7',];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataEmailDuplicate()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $csv_data[2] = $this->getEmptyRowOnCsv();
        $test_data_a = [
            'aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON',
        ];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data_a);

        $test_data_b = [
            'aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON',
        ];
        $csv_data[2] = Hash::merge($csv_data[2], $test_data_b);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataEmailAlreadyJoined()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();

        $test_data_a = [
            'from@email.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON',
        ];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data_a);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataMemberIdDuplicate()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $csv_data[2] = $this->getEmptyRowOnCsv();

        $test_data_a = [
            'aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON',
        ];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data_a);

        $test_data_b = [
            'bbb@bbb.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON',
        ];
        $csv_data[2] = Hash::merge($csv_data[2], $test_data_b);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataMemberIdExists()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading();
        $csv_data[1] = $this->getEmptyRowOnCsv();
        $test_data_a = [
            'aaa@aaa.com', 'member_1', 'firstname', 'lastname', 'ON', 'ON',
        ];
        $csv_data[1] = Hash::merge($csv_data[1], $test_data_a);

        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataCoachIdExists()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[] = $this->TeamMember->_getCsvHeading();
        $csv_data[] = $this->getEmptyRowOnCsv();

        $csv_data[1] = ['aaa@aaa.com', 'member_id', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', '11', '11', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'member_1', 'rater1', 'rater2', 'rater3', 'rater4', 'rater5', 'rater6', 'rater7',];
        $csv_data[2] = ['aaax@aaa.com', 'member_2', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', '11', '11', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'not_exists_coach_id', 'rater1', 'rater2', 'rater3', 'rater4', 'rater5', 'rater6', 'rater7',];
        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 3
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateNewMemberCsvDataEvaluatorIdExists()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[] = $this->TeamMember->_getCsvHeading();
        $csv_data[] = $this->getEmptyRowOnCsv();

        $csv_data[1] = ['aaa@aaa.com', 'abc', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', '11', '11', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', '', 'member_1', 'rater2', 'rater3', 'rater4', 'rater5', 'rater6', 'rater7',];
        $csv_data[2] = ['aaax@aaa.com', 'member_z', 'firstname', 'lastname', 'ON', 'ON', '', 'jpn', 'localfirstname', 'locallastname', '000-0000-0000', 'male', '1999', '11', '11', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', '', 'abc', 'rater2', 'rater3', 'rater4', 'rater5', 'rater6', 'rater7',];
        $actual = $this->TeamMember->validateNewMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataNotMatchRecordCount()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 0
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataNotMatchColumnCount()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);

        unset($csv_data[0]['email']);
        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 1
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataNotMatchTitle()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);

        $csv_data[0]['email'] = 'test';
        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 1
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataNotExistsEmail()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['aaa@aaa.com', 'firstname', 'lastname', 'member_id', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataFirstNameNotEqual()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstnamex', 'lastname', 'member_1', 'ON', 'ON', 'ON']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataLastNameNotEqual()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastnamex', 'member_1', 'ON', 'ON', 'ON']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataNotMemberId()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', '', 'ON', 'ON', 'ON']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataNotActiveFlg()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', '', 'ON', 'ON']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataActiveFlgOnOrOffError()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'aaa', 'ON', 'ON']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataNoAdminFlg()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', '', 'ON']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataAdminFlgOnOrOffError()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'aa', 'ON']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataNoEvaluate()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', '']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataEvaluateOnOrOffError()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'aaa']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataGroupAlignLeftError()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON', '', '', 'group2']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataGroupDuplicateError()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON', '', 'group1', 'group1']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataGroupDuplicateMemberId()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON',]);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataCoachIdEqualSError()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON', '', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'member_1']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataEvaluatorAlignLeftError()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON', '', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'member_2', '', 'rater2']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataEvaluatorMemberIdError()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON', '', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'member_2', 'member_1', 'rater2']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataEvaluatorDuplicateError()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON', '', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'member_2', 'rater1', 'rater1']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataRequireAdminAndActive()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'OFF', 'OFF', 'ON', '', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'member_2', 'rater1', 'rater2']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'OFF', 'OFF', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'OFF', 'OFF', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'OFF', 'OFF', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 0
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataDuplicateEmail()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON', '', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'member_2', 'rater1', 'rater2']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataDuplicateMemberId()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON', '', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'member_2', 'rater1', 'rater2']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);
        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataNotExistsCoach()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON', '', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'xxxxxxxxxx', 'rater1', 'rater2']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateMemberCsvDataNotExistsEvaluator()
    {
        $this->setDefault();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeading(false);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['from@email.com', 'firstname', 'lastname', 'member_1', 'ON', 'ON', 'ON', '', 'group1', 'group2', 'group3', 'group4', 'group5', 'group6', 'group7', 'member_2', 'rater1', 'rater2', 'xxxxxxxxxx']);
        $csv_data[2] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['test@aaa.com', 'firstname', 'lastname', 'member_2', 'ON', 'ON', 'ON']);
        $csv_data[3] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['to@email.com', 'firstname', 'lastname', 'member_3', 'ON', 'ON', 'ON']);
        $csv_data[4] = Hash::merge($this->getEmptyRowOnCsv(23),
                                   ['xxxxxxx@email.com', 'firstname', 'lastname', 'member_4', 'ON', 'ON', 'ON']);

        $actual = $this->TeamMember->validateUpdateMemberCsvData($csv_data);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateFinalEvaluationCsvDataUnMatchColumnCount()
    {
        $this->setDefault();
        $this->_saveEvaluations();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeadingEvaluation();
        unset($csv_data[0]['member_no']);
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(33), ['member_no' => 'test']);
        $actual = $this->TeamMember->validateUpdateFinalEvaluationCsvData($csv_data, 1);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 1
        ];
        $this->assertEquals($excepted, $actual);

    }

    function testValidateUpdateFinalEvaluationCsvDataUnMatchTitle()
    {
        $this->setDefault();
        $this->_saveEvaluations();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeadingEvaluation();
        $csv_data[0]['member_no'] = 'test';
        $csv_data[1] = Hash::merge($this->getEmptyRowOnCsv(33), ['member_no' => 'test']);
        $actual = $this->TeamMember->validateUpdateFinalEvaluationCsvData($csv_data, 1);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 1
        ];
        $this->assertEquals($excepted, $actual);
    }

    function testValidateUpdateFinalEvaluationCsvDataNotExistsMember()
    {
        $this->setDefault();
        $this->_saveEvaluations();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeadingEvaluation();
        $csv_data[1] = $this->getEmptyRowOnCsv(33);
        $csv_data[1] = copyKeyName($this->TeamMember->_getCsvHeadingEvaluation(), $csv_data[1]);
        $csv_data[1] = Hash::merge($csv_data[1],
                                   ['member_no' => 'test', 'total.final.score' => 'aaaaa']);
        $actual = $this->TeamMember->validateUpdateFinalEvaluationCsvData($csv_data, 1);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);

    }

    function testValidateUpdateFinalEvaluationCsvDataNotExistsScore()
    {
        $this->setDefault();
        $this->_saveEvaluations();

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeadingEvaluation();
        $csv_data[1] = $this->getEmptyRowOnCsv(33);
        $csv_data[1] = copyKeyName($this->TeamMember->_getCsvHeadingEvaluation(), $csv_data[1]);
        $csv_data[1] = Hash::merge($csv_data[1],
                                   ['member_no' => 'member_1', 'total.final.score' => 'aaaaa']);
        $actual = $this->TeamMember->validateUpdateFinalEvaluationCsvData($csv_data, 1);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);

    }

    function testValidateUpdateFinalEvaluationCsvDataMemberIdDuplicated()
    {
        $this->setDefault();
        $this->_saveEvaluations();
        $eval_data = [
            'team_id'           => 1,
            'evaluatee_user_id' => 2,
            'evaluator_user_id' => 1,
            'evaluate_term_id'  => 1,
            'comment'           => null,
            'evaluate_score_id' => null,
            'evaluate_type'     => 0,
            'goal_id'           => null,
            'index_num'         => 0,
            'status'            => 0
        ];
        $this->TeamMember->Team->Evaluation->save($eval_data);

        $csv_data = [];
        $csv_data[0] = $this->TeamMember->_getCsvHeadingEvaluation();
        $csv_data[1] = $this->getEmptyRowOnCsv(33);
        $csv_data[1] = copyKeyName($this->TeamMember->_getCsvHeadingEvaluation(), $csv_data[1]);
        $csv_data[1] = Hash::merge($csv_data[1],
                                   ['member_no' => 'member_1', 'total.final.score' => 'A']);
        $csv_data[2] = $this->getEmptyRowOnCsv(33);
        $csv_data[2] = copyKeyName($this->TeamMember->_getCsvHeadingEvaluation(), $csv_data[2]);
        $csv_data[2] = Hash::merge($csv_data[2],
                                   ['member_no' => 'member_1', 'total.final.score' => 'A']);
        $actual = $this->TeamMember->validateUpdateFinalEvaluationCsvData($csv_data, 1);

        if (viaIsSet($actual['error_msg'])) {
            unset($actual['error_msg']);
        }
        $excepted = [
            'error'         => true,
            'error_line_no' => 2
        ];
        $this->assertEquals($excepted, $actual);

    }

    function testGetAllMembersCsvDataNoUser()
    {
        $data = [
            'team_id' => 1
        ];
        $this->TeamMember->save($data);
        $this->TeamMember->getAllMembersCsvData(1);
    }

    function getEmptyRowOnCsv($colum_count = 30)
    {
        $row = [];
        for ($i = 0; $i < $colum_count; $i++) {
            $row[] = null;
        }
        return $row;
    }

    function testActivateMembers()
    {
        $res = $this->TeamMember->activateMembers('1000', 100000);
        $this->asserttrue($res);
    }

    function testIsActiveTrue()
    {
        $this->TeamMember->current_team_id = 1;
        $uid = 1;
        $this->assertTrue($this->TeamMember->isActive($uid));
    }

    function testIsActiveFalse()
    {
        $this->TeamMember->current_team_id = 1;
        $uid = 1;
        $this->assertFalse($this->TeamMember->isActive($uid, 10000));
    }

    function testIsActiveFalseCurrentTeamIdNull()
    {
        $this->TeamMember->current_team_id = null;
        $uid = 1;
        $this->assertFalse($this->TeamMember->isActive($uid));
    }

    function testIsActiveDefault()
    {
        $this->setDefault();
        $res = $this->TeamMember->isActive(1);
        $this->assertTrue($res);
        $res = $this->TeamMember->isActive(1);
        $this->assertTrue($res);
        $res = $this->TeamMember->isActive(999);
        $this->assertFalse($res);
        $res = $this->TeamMember->isActive(999);
        $this->assertFalse($res);
    }

    function setDefault()
    {
        $uid = 1;
        $team_id = 1;
        $this->TeamMember->current_team_id = $team_id;
        $this->TeamMember->my_uid = $uid;
        $this->TeamMember->User->Email->current_team_id = $team_id;
        $this->TeamMember->User->Email->my_uid = $uid;
    }

    function testSelectCoachUserIdFromTeamMembersTB()
    {
        $user_id = 777;
        $team_id = 888;
        $coach_user_id = 999;

        $params = [
            'user_id'       => $user_id,
            'team_id'       => $team_id,
            'coach_user_id' => $coach_user_id,
        ];
        $this->TeamMember->save($params);
        $res = $this->TeamMember->getCoachUserIdByMemberUserId($user_id);
        $this->assertEquals($coach_user_id, $res);

    }

    function testSelectUserIdFromTeamMembersTB()
    {
        $user_id = 777;
        $team_id = 888;
        $coach_user_id = 999;

        $params = [
            'user_id'       => $user_id,
            'team_id'       => $team_id,
            'coach_user_id' => $coach_user_id,
        ];
        $this->TeamMember->save($params);
        $res = $this->TeamMember->getMyMembersList($coach_user_id);
        $this->assertContains($user_id, $res);
    }

    function testGetEvaluationEnableFlgReturnTrue()
    {
        $user_id = 777;
        $team_id = 888;

        $params = [
            'user_id'               => $user_id,
            'team_id'               => $team_id,
            'active_flg'            => 1,
            'evaluation_enable_flg' => 1
        ];
        $this->TeamMember->save($params);
        $flg = $this->TeamMember->getEvaluationEnableFlg($user_id, $team_id);
        $this->assertTrue($flg);
    }

    function testGetEvaluationEnableFlgReturnFalsePattern1()
    {
        $user_id = 777;
        $team_id = 888;

        $params = [
            'user_id'               => $user_id,
            'team_id'               => $team_id,
            'active_flg'            => 0,
            'evaluation_enable_flg' => 1
        ];
        $this->TeamMember->save($params);
        $flg = $this->TeamMember->getEvaluationEnableFlg($user_id, $team_id);
        $this->assertFalse($flg);
    }

    function testGetEvaluationEnableFlgReturnFalsePattern2()
    {
        $user_id = 777;
        $team_id = 888;

        $params = [
            'user_id'               => $user_id,
            'team_id'               => $team_id,
            'active_flg'            => 1,
            'evaluation_enable_flg' => 0
        ];
        $this->TeamMember->save($params);
        $flg = $this->TeamMember->getEvaluationEnableFlg($user_id, $team_id);
        $this->assertFalse($flg);
    }

    function testAddDefaultSellForCsvData()
    {
        $this->TeamMember->addDefaultSellForCsvData('test');
        $this->assertEmpty($this->TeamMember->csv_datas);
    }

    function testSetTotalFinalEvaluationForCsvDataContinue()
    {
        $reflectionClass = new ReflectionClass($this->TeamMember);
        $property = $reflectionClass->getProperty('all_users');
        $property->setAccessible(true);
        $property->setValue($this->TeamMember, [['User' => ['id' => 1]]]);

        $this->TeamMember->setTotalFinalEvaluationForCsvData();
    }

    function testSetTotalFinalEvaluationForCsvDataIterator()
    {
        App::uses('Evaluation', 'Model');
        $reflectionClass = new ReflectionClass($this->TeamMember);
        $property = $reflectionClass->getProperty('all_users');
        $property->setAccessible(true);
        $property->setValue($this->TeamMember, [['User' => ['id' => 1]]]);
        $evaluations = [
            1 => [
                [
                    'Evaluation'    => [
                        'evaluate_type' => Evaluation::TYPE_FINAL_EVALUATOR,
                        'goal_id'       => null,
                        'comment'       => 'nice!'
                    ],
                    'EvaluateScore' => [
                        'name' => 'score_name',
                    ]
                ]
            ]
        ];
        $property = $reflectionClass->getProperty('evaluations');
        $property->setAccessible(true);
        $property->setValue($this->TeamMember, $evaluations);

        $this->TeamMember->setTotalFinalEvaluationForCsvData();

        $expected = [
            (int)0 => [
                'total.final.score'   => 'score_name',
                'total.final.comment' => 'nice!'
            ]
        ];
        $actual = $this->TeamMember->csv_datas;
        $this->assertEquals($expected, $actual);
    }

    function testSetTotalEvaluatorEvaluationForCsvDataContinue()
    {
        $reflectionClass = new ReflectionClass($this->TeamMember);
        $property = $reflectionClass->getProperty('all_users');
        $property->setAccessible(true);
        $property->setValue($this->TeamMember, [['User' => ['id' => 1]]]);
        $this->TeamMember->setTotalEvaluatorEvaluationForCsvData();
    }

    function testSetTotalEvaluatorEvaluationForCsvDataIterator()
    {
        App::uses('Evaluation', 'Model');
        $reflectionClass = new ReflectionClass($this->TeamMember);
        $property = $reflectionClass->getProperty('all_users');
        $property->setAccessible(true);
        $property->setValue($this->TeamMember, [['User' => ['id' => 1]]]);
        $evaluations = [
            1 => [
                [
                    'Evaluation'    => [
                        'evaluate_type' => Evaluation::TYPE_EVALUATOR,
                        'goal_id'       => null,
                        'comment'       => 'nice!'
                    ],
                    'EvaluateScore' => [
                        'name' => 'score_name',
                    ],
                    'EvaluatorUser' => [
                        'display_username' => 'test user'
                    ]
                ]
            ]
        ];
        $property = $reflectionClass->getProperty('evaluations');
        $property->setAccessible(true);
        $property->setValue($this->TeamMember, $evaluations);

        $this->TeamMember->setTotalEvaluatorEvaluationForCsvData();

        $expected = [
            (int)0 => [
                'total.evaluator.1.name'    => 'test user',
                'total.evaluator.1.score'   => 'score_name',
                'total.evaluator.1.comment' => 'nice!'
            ]
        ];
        $actual = $this->TeamMember->csv_datas;
        $this->assertEquals($expected, $actual);
    }

    function testSetTotalSelfEvaluationForCsvDataContinue()
    {
        $reflectionClass = new ReflectionClass($this->TeamMember);
        $property = $reflectionClass->getProperty('all_users');
        $property->setAccessible(true);
        $property->setValue($this->TeamMember, [['User' => ['id' => 1]]]);
        $this->TeamMember->setTotalSelfEvaluationForCsvData();
    }

    function testSetGoalEvaluationForCsvData()
    {
        $reflectionClass = new ReflectionClass($this->TeamMember);
        $property = $reflectionClass->getProperty('all_users');
        $property->setAccessible(true);
        $property->setValue($this->TeamMember, [['User' => ['id' => 1]]]);
        $this->TeamMember->setGoalEvaluationForCsvData();
        $expected = [
            (int)0 => [
                'kr_count'      => (int)0,
                'action_count'  => (int)0,
                'goal_progress' => (int)0
            ]
        ];
        $actual = $this->TeamMember->csv_datas;
        $this->assertEquals($expected, $actual);
    }

    function testSetUserInfoForCsvDataContinue()
    {
        $reflectionClass = new ReflectionClass($this->TeamMember);
        $property = $reflectionClass->getProperty('all_users');
        $property->setAccessible(true);
        $property->setValue($this->TeamMember, [['User' => ['id' => null]]]);
        $this->TeamMember->setUserInfoForCsvData();
    }

    function testSetAllMembers()
    {
        $this->TeamMember->current_team_id = 1;
        $this->TeamMember->setAllMembers(null, 'final_evaluation');
    }

    function testSetAdminUserFlagPatternON()
    {
        $member_id = 999;
        $params = [
            'id'        => $member_id,
            'admin_flg' => 0,
        ];
        $this->TeamMember->save($params);
        $this->TeamMember->setAdminUserFlag($member_id, 'ON');

        $options['conditions']['id'] = $member_id;
        $res = $this->TeamMember->find('first', $options);
        $this->assertEquals(1, $res['TeamMember']['admin_flg']);
    }

    function testSetAdminUserFlagPatternOFF()
    {
        $member_id = 999;
        $params = [
            'id'        => $member_id,
            'admin_flg' => 0,
        ];
        $this->TeamMember->save($params);
        $this->TeamMember->setAdminUserFlag($member_id, 'OFF');

        $options['conditions']['id'] = $member_id;
        $res = $this->TeamMember->find('first', $options);
        $this->assertEquals(0, $res['TeamMember']['admin_flg']);
    }

    function testSetActiveFlagPatternON()
    {
        $member_id = 999;
        $params = [
            'id'         => $member_id,
            'active_flg' => 0,
        ];
        $this->TeamMember->save($params);
        $this->TeamMember->setActiveFlag($member_id, 'ON');

        $options['conditions']['id'] = $member_id;
        $res = $this->TeamMember->find('first', $options);
        $this->assertEquals(1, $res['TeamMember']['active_flg']);
    }

    function testSetActiveFlagPatternOFF()
    {
        $member_id = 999;
        $params = [
            'id'         => $member_id,
            'active_flg' => 0,
        ];
        $this->TeamMember->save($params);
        $this->TeamMember->setActiveFlag($member_id, 'OFF');

        $options['conditions']['id'] = $member_id;
        $res = $this->TeamMember->find('first', $options);
        $this->assertEquals(0, $res['TeamMember']['active_flg']);
    }

    function testSetEvaluationEnableFlagPatternON()
    {
        $member_id = 999;
        $params = [
            'id'                    => $member_id,
            'evaluation_enable_flg' => 0,
        ];
        $this->TeamMember->save($params);
        $this->TeamMember->setEvaluationFlag($member_id, 'ON');

        $options['conditions']['id'] = $member_id;
        $res = $this->TeamMember->find('first', $options);
        $this->assertEquals(1, $res['TeamMember']['evaluation_enable_flg']);
    }

    function testSetEvaluationEnableFlagPatternOFF()
    {
        $member_id = 999;
        $params = [
            'id'                    => $member_id,
            'evaluation_enable_flg' => 0,
        ];
        $this->TeamMember->save($params);
        $this->TeamMember->setEvaluationFlag($member_id, 'OFF');

        $options['conditions']['id'] = $member_id;
        $res = $this->TeamMember->find('first', $options);
        $this->assertEquals(0, $res['TeamMember']['evaluation_enable_flg']);
    }

    function testSelect2faStepMemberInfoTypeTrue()
    {
        $user_id = 999;
        $params = [
            'id'         => $user_id,
            '2fa_secret' => null,
        ];
        $this->TeamMember->User->save($params);

        $team_id = 999;
        $params = [
            'user_id' => $user_id,
            'team_id' => $team_id,
        ];
        $this->TeamMember->save($params);

        $res = $this->TeamMember->select2faStepMemberInfo($team_id);
        $this->assertEquals($user_id, $res[0]['User']['id']);
    }

    function testSelect2faStepMemberInfoTypeFalse()
    {
        $user_id = 999;
        $params = [
            'id'         => $user_id,
            '2fa_secret' => 'test',
        ];
        $this->TeamMember->User->save($params);

        $team_id = 999;
        $params = [
            'user_id' => $user_id,
            'team_id' => $team_id,
        ];
        $this->TeamMember->save($params);

        $res = $this->TeamMember->select2faStepMemberInfo($team_id);
        $this->assertCount(0, $res);
    }

    function testSelectAdminMemberInfoTypeTrue()
    {
        $user_id = 999;
        $params = [
            'id' => $user_id,
        ];
        $this->TeamMember->User->save($params);

        $team_id = 888;
        $params = [
            'user_id'   => $user_id,
            'team_id'   => $team_id,
            'admin_flg' => 1
        ];
        $this->TeamMember->save($params);
        $res = $this->TeamMember->selectAdminMemberInfo($team_id);
        $this->assertEquals($user_id, $res[0]['User']['id']);
    }

    function testSelectAdminMemberInfoTypeFalse()
    {
        $user_id = 999;
        $params = [
            'id' => $user_id,
        ];
        $this->TeamMember->User->save($params);

        $team_id = 888;
        $params = [
            'user_id'   => $user_id,
            'team_id'   => $team_id,
            'admin_flg' => 0
        ];
        $this->TeamMember->save($params);
        $res = $this->TeamMember->selectAdminMemberInfo($team_id);
        $this->assertCount(0, $res);
    }

    function testSelectMemberInfo()
    {
        $user_id = 999;
        $params = [
            'id' => $user_id,
        ];
        $this->TeamMember->User->save($params);

        $team_id = 888;
        $params = [
            'user_id' => $user_id,
            'team_id' => $team_id,
        ];
        $this->TeamMember->save($params);
        $res = $this->TeamMember->selectMemberInfo($team_id);
        $this->assertCount(1, $res);
    }

    function testSelectGroupMemberInfo()
    {
        $user_id = 999;
        $params = [
            'id' => $user_id,
        ];
        $this->TeamMember->User->save($params);

        $team_id = 888;
        $params = [
            'user_id' => $user_id,
            'team_id' => $team_id,
        ];
        $this->TeamMember->save($params);

        $group_id = 1;
        $params = [
            'user_id'  => $user_id,
            'team_id'  => $team_id,
            'group_id' => $group_id,
        ];
        $this->TeamMember->User->MemberGroup->save($params);
        $res = $this->TeamMember->selectGroupMemberInfo($team_id, $group_id);
        $this->assertEquals($group_id, $res[0]['User']['MemberGroup'][0]['group_id']);
    }

    function testDefineTeamMemberOption()
    {
        $team_id = 999;
        $options = [
            'fields'     => ['id', 'active_flg', 'admin_flg', 'coach_user_id', 'evaluation_enable_flg', 'created'],
            'conditions' => [
                'team_id' => $team_id,
            ],
            'order'      => ['TeamMember.created' => 'DESC'],
            'contain'    => [
                'User'      => [
                    'fields'      => ['id', 'first_name', 'last_name', '2fa_secret', 'photo_file_name'],
                    'MemberGroup' => [
                        'fields' => ['group_id'],
                        'Group'  => [
                            'fields' => ['name']
                        ]
                    ],
                ],
                'CoachUser' => [
                    'fields' => $this->TeamMember->User->profileFields
                ]
            ]
        ];
        $res = $this->TeamMember->defineTeamMemberOption($team_id);
        $this->assertEquals($options, $res);
    }

    function testConvertMemberData()
    {
        // me
        $user_id = 999;
        $params = [
            'id' => $user_id,
        ];
        $this->TeamMember->User->save($params);

        // coach
        $coach_user_id = 777;
        $params = [
            'id'         => $coach_user_id,
            'first_name' => 'coach',
            'last_name'  => 'a'
        ];
        $this->TeamMember->User->save($params);

        $team_id = 888;
        $params = [
            'user_id'       => $user_id,
            'team_id'       => $team_id,
            'coach_user_id' => $coach_user_id,
        ];
        $this->TeamMember->save($params);

        $group_id = 1;
        $params = [
            'user_id'  => $user_id,
            'team_id'  => $team_id,
            'group_id' => $group_id,
        ];
        $this->TeamMember->User->MemberGroup->save($params);

        $group_name = 'SDG';
        $params = [
            'id'      => $group_id,
            'team_id' => $team_id,
            'name'    => $group_name
        ];
        $this->TeamMember->User->MemberGroup->Group->save($params);

        $res = $this->TeamMember->selectGroupMemberInfo($team_id, $group_id);
        $convert_data = $this->TeamMember->convertMemberData($res);
        $this->assertEquals($group_name, $convert_data[0]['TeamMember']['group_name']);
        $this->assertFalse($convert_data[0]['User']['two_step_flg']);
        $this->assertEquals('/img/no-image-user.jpg', $convert_data[0]['User']['img_url']);
        $this->assertArrayHasKey('coach_name', $convert_data[0]['TeamMember']);
    }

    function testGetActiveTeamMembersList()
    {
        $this->setDefault();
        $res = $this->TeamMember->getActiveTeamMembersList();
        $this->assertNotEmpty($res);
        $res = $this->TeamMember->getActiveTeamMembersList();
        $this->assertNotEmpty($res);
    }

    function testCountActiveMembersByTeamId()
    {
        $members = $this->TeamMember->find('all', [
            'fields' => [
                'TeamMember.team_id',
                'TeamMember.active_flg',
            ],
        ]);

        $counts = [];
        foreach ($members as $v) {
            if (!$v['TeamMember']['active_flg']) {
                continue;
            }
            if (!isset($counts[$v['TeamMember']['team_id']])) {
                $counts[$v['TeamMember']['team_id']] = 0;
            }
            $counts[$v['TeamMember']['team_id']]++;
        }

        foreach ($counts as $team_id => $count) {
            $res = $this->TeamMember->countActiveMembersByTeamId($team_id);
            $this->assertEquals($count, $res);
        }
    }

    function testUpdateLastLogin()
    {
        $this->setDefault();
        $this->assertNotEmpty($this->TeamMember->updateLastLogin(1, 1));
    }

    function testDeleteCacheMember()
    {
        $this->setDefault();
        $this->assertFalse($this->TeamMember->deleteCacheMember(99999));
    }

    function testGetByMemberNo()
    {
        $this->setDefault();
        $this->assertNotEmpty($this->TeamMember->getByMemberNo('member_1'));
    }

    function testGetByUserId()
    {
        $this->setDefault();
        $this->assertNotEmpty($this->TeamMember->getByUserId(1));
    }

    function testGetLoginUserAdminFlag()
    {
        $this->setDefault();
        $this->assertTrue($this->TeamMember->getLoginUserAdminFlag(1, 1));
        $this->assertFalse($this->TeamMember->getLoginUserAdminFlag(1, 2));
    }

    function testGetAdminUserCount()
    {
        $this->setDefault();
        $actual = $this->TeamMember->getAdminUserCount(1);
        $this->assertEquals(3, $actual);
    }

    function testGetCoachId()
    {
        $this->setDefault();
        $actual = $this->TeamMember->getCoachId(1, 1);
        $this->assertEquals(2, $actual);

    }

    function testGetUserIdsByMemberNo()
    {
        $this->setDefault();
        $actual = $this->TeamMember->getUserIdsByMemberNos(['member_1', 'member_2']);
        $this->assertCount(2, $actual);
    }

    function testIsAdmin()
    {
        $this->setDefault();
        $actual = $this->TeamMember->isAdmin();
        $this->assertNotEmpty($actual);
    }

    function _saveEvaluations()
    {
        $records = [
            [
                'team_id'           => 1,
                'evaluatee_user_id' => 1,
                'evaluator_user_id' => 2,
                'evaluate_term_id'  => 1,
                'evaluate_type'     => 0,
                'comment'           => 'あいうえお',
                'evaluate_score_id' => 1,
                'index_num'         => 0,
            ],
            [
                'team_id'           => 1,
                'evaluatee_user_id' => 1,
                'evaluator_user_id' => 1,
                'evaluate_term_id'  => 1,
                'evaluate_type'     => 0,
                'comment'           => 'かきくけこ',
                'evaluate_score_id' => 1,
                'index_num'         => 1,
                'goal_id'           => 1,
            ],
            [
                'team_id'           => 1,
                'evaluatee_user_id' => 1,
                'evaluator_user_id' => 1,
                'evaluate_term_id'  => 1,
                'evaluate_type'     => 0,
                'comment'           => 'さしすせそ',
                'evaluate_score_id' => 1,
                'index_num'         => 2,
                'goal_id'           => 2,
            ],
            [
                'team_id'           => 1,
                'evaluatee_user_id' => 1,
                'evaluator_user_id' => 1,
                'evaluate_term_id'  => 1,
                'evaluate_type'     => 0,
                'comment'           => 'たちつてと',
                'evaluate_score_id' => 1,
                'index_num'         => 3,
                'goal_id'           => 3,
            ],
            [
                'team_id'           => 2,
                'evaluatee_user_id' => 2,
                'evaluator_user_id' => 2,
                'evaluate_term_id'  => 2,
                'evaluate_type'     => 0,
                'comment'           => 'なにぬねの',
                'evaluate_score_id' => 1,
                'index_num'         => 0,
                'goal_id'           => 10,
            ],
            [
                'team_id'           => 2,
                'evaluatee_user_id' => 2,
                'evaluator_user_id' => 2,
                'evaluate_term_id'  => 2,
                'evaluate_type'     => 0,
                'comment'           => 'はひふへほ',
                'evaluate_score_id' => 1,
                'index_num'         => 1,
                'goal_id'           => 11,
            ],
            [
                'team_id'           => 2,
                'evaluatee_user_id' => 2,
                'evaluator_user_id' => 2,
                'evaluate_term_id'  => 2,
                'evaluate_type'     => 0,
                'comment'           => 'まみむめも',
                'evaluate_score_id' => 1,
                'index_num'         => 2,
                'goal_id'           => 12,
            ],
        ];
        
        $this->TeamMember->Team->Evaluation->saveAll($records);
    }

}
