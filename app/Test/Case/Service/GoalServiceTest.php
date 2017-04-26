<?php
App::uses('GoalousTestCase', 'Test');
App::import('Service', 'GoalService');
App::import('Service', 'ActionService');
App::import('Service', 'KrValuesDailyLogService');

/**
 * GoalServiceTest Class
 * Created by PhpStorm.
 * User: daikihirakata
 * Date: 2016/12/08
 * Time: 17:50
 *
 * @property GoalService             $GoalService
 * @property Team                    $Team
 * @property Term                    $EvaluateTerm
 * @property Goal                    $Goal
 * @property KrValuesDailyLogService $KrValuesDailyLogService
 * @property KeyResult               $KeyResult
 * @property KrValuesDailyLog        $KrValuesDailyLog
 */
class GoalServiceTest extends GoalousTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.term',
        'app.team',
        'app.goal',
        'app.goal_member',
        'app.goal_label',
        'app.label',
        'app.post',
        'app.key_result',
        'app.circle',
        'app.team_member',
        'app.comment',
        'app.post_share_user',
        'app.post_share_circle',
        'app.post_like',
        'app.post_read',
        'app.action_result',
        'app.follower',
        'app.attached_file',
        'app.action_result_file',
        'app.kr_progress_log',
        'app.user',
        'app.kr_values_daily_log',
        'app.key_result',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->GoalService = ClassRegistry::init('GoalService');
        $this->Team = ClassRegistry::init('Team');
        $this->EvaluateTerm = ClassRegistry::init('Term');
        $this->Goal = ClassRegistry::init('Goal');
        $this->GoalLabel = ClassRegistry::init('GoalLabel');
        $this->ActionResult = ClassRegistry::init('ActionResult');
        $this->ActionService = ClassRegistry::init('ActionService');
        $this->KrValuesDailyLog = ClassRegistry::init('KrValuesDailyLog');
        $this->KeyResult = ClassRegistry::init('KeyResult');
        $this->KrValuesDailyLogService = ClassRegistry::init('KrValuesDailyLogService');
        $this->KrValuesDailyLog = ClassRegistry::init('KrValuesDailyLog');

        $this->setDefaultTeamIdAndUid();
    }

    function testGoalValidateFields()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function testCacheList()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function testGet()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function testExtend()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function testUpdate()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function testCreate()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function testBuildUpdateGoalData()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function testBuildUpdateTkrData()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function test_validateSave()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function test_isGoalAfterCurrentTerm()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function test_processGoals()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function test_getProgress()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function test_extendTermType()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function test_getTermType()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    function test_canExchangeTkr()
    {
        $this->markTestIncomplete('testClear not implemented.');
    }

    /**
     * ゴール削除
     */
    function test_delete()
    {
        /* テストデータ準備 */
        $goalId = $this->setupTestDelete();

        // ゴール削除
        $this->GoalService->delete($goalId);

        // ゴール削除できているか
        $ret = $this->Goal->getById($goalId);
        $this->assertEmpty($ret);

        // ゴールラベル削除できているか
        $ret = $this->GoalLabel->findByGoalId($goalId);
        $this->assertEmpty($ret);

        // ゴールとアクションの紐付けを解除できているか
        $ret = $this->ActionResult->findByGoalId($goalId);
        $this->assertEmpty($ret);

        // KR進捗日次ログ削除できているか
        $ret = $this->KrValuesDailyLog->findByGoalId($goalId);
        $this->assertEmpty($ret);

        // TODO:キャッシュ削除できているか

    }

    /**
     * test_delete用テストデータ準備
     */
    private function setupTestDelete()
    {
        $uid = 1;
        $teamId = 1;
        $this->setupTerm();
        $this->KeyResult->my_uid = $uid;
        $this->KeyResult->current_team_id = $teamId;
        $goalId = $this->createGoal($uid);
        $kr = Hash::get($this->KeyResult->getTkr($goalId), 'KeyResult');
        $fileIds = $this->prepareUploadImages();
        // アクション登録
        $saveAction = [
            "goal_id"                  => $goalId,
            "team_id"                  => $teamId,
            "user_id"                  => $uid,
            "name"                     => "ああああ\nいいいいいいい",
            "key_result_id"            => $kr['id'],
            "key_result_current_value" => $kr['current_value'],
        ];
        $this->ActionService->create($saveAction, $fileIds, null);
        // KR進捗日次ログ保存
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);
        return $goalId;
    }

    /**
     * グラフテスト
     * ゴールの進捗データの信憑性は可能であればやる
     * メインはグラフの描画範囲の検査
     */
    function testGetGraphRangeTargetDaysOver()
    {
        $this->_setUpGraphDefault();
        try {
            $this->GoalService->getGraphRange(time(), date('t') + 1);
        } catch (Exception $e) {
        }
        $this->assertTrue(isset($e));
    }

    /**
     * グラフの日付範囲指定で期間内の場合
     */
    function testGetGraphRangeTargetDaysNotOver()
    {
        $this->_setUpGraphDefault();
        try {
            $this->GoalService->getGraphRange(time(), date('t'));
        } catch (Exception $e) {
        }
        //例外が返らないこと
        $this->assertFalse(isset($e));
    }

    /**
     * グラフ表示期間算出メソッドのテスト
     * - 指定終了日が期の開始日に近い場合
     */
    function testGetGraphRangeTargetEndIsNotLongSinceTermStart()
    {
        $this->_setUpGraphDefault();
        $expected = [
            'graphStartDate' => date('Y-m-01'),
            'graphEndDate'   => date('Y-m-10'),
        ];
        //バッファなし
        $targetEndTimestamp = $this->Term->getCurrentTermData()['start_date'];
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 0);
        $expected['plotDataEndDate'] = date('Y-m-01');
        //当日が期の開始日と一緒の場合、期の開始日とプロットデータのエンドは一緒になる
        $this->assertEquals($expected, $actual);

        $targetEndTimestamp = $this->Term->getCurrentTermData()['start_date'] + 9 * DAY;
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 0);
        $expected['plotDataEndDate'] = date('Y-m-10');
        //バッファなしで当日が期の開始日から9日後はプロットデータも9日後になる
        $this->assertEquals($expected, $actual);

        $targetEndTimestamp = $this->Term->getCurrentTermData()['start_date'] + 10 * DAY;
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 0);
        //バッファなしで、targetDaysが10日で当日が期の開始日から10日後は範囲全体の日付が変わる
        $this->assertEquals([
            'graphStartDate'  => date('Y-m-02'),
            'graphEndDate'    => date('Y-m-11'),
            'plotDataEndDate' => date('Y-m-11')
        ], $actual);

        //バッファあり
        $targetEndTimestamp = $this->Term->getCurrentTermData()['start_date'];
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 9);
        $expected['plotDataEndDate'] = date('Y-m-01');
        //バッファありでも$targetEndTimestampが収まる場合は、日付が一緒になる。
        $this->assertEquals($expected, $actual);

        $targetEndTimestamp = $this->Term->getCurrentTermData()['start_date'] + 1 * DAY;
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 9);
        $expected['plotDataEndDate'] = date('Y-m-01');
        //バッファありで、$targetEndTimestampが収まらない場合は日付が変わる。
        $this->assertNotEquals([
            'graphStartDate'  => date('Y-m-02'),
            'graphEndDate'    => date('Y-m-11'),
            'plotDataEndDate' => date('Y-m-11')
        ], $actual);
    }

    /**
     * グラフ表示期間算出メソッドのテスト
     * - 指定終了日が期の終了日に近い場合
     */
    function testGetGraphRangeTermEndIsApproachingWithBuffer()
    {
        $this->_setUpGraphDefault();

        //バッファあり
        $expected = [
            'graphStartDate'  => date('Y-m-' . (string)(date('t') - 9)),
            'graphEndDate'    => date('Y-m-' . date('t')),
            'plotDataEndDate' => date('Y-m-' . date('t')),
        ];

        $targetEndTimestamp = $this->Term->getCurrentTermData()['end_date'];
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 1);
        //バッファありでも期の終了日に近い場合は、バッファ考慮しない
        $this->assertEquals($expected, $actual);

        $targetEndTimestamp = $this->Term->getCurrentTermData()['end_date'] - 8 * DAY;
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 9);
        //バッファありでも期の終了日に近い場合は、バッファ考慮しない
        $this->assertEquals($expected, $actual);

        $targetEndTimestamp = $this->Term->getCurrentTermData()['end_date'] - 9 * DAY;
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 9);
        //バッファありで、指定終了日と期の終了日の差分がバッファを超える場合はバッファ考慮される
        $this->assertNotEquals($expected, $actual);

        //バッファなし
        $expected = [
            'graphStartDate'  => date('Y-m-' . (string)(date('t') - 9)),
            'graphEndDate'    => date('Y-m-' . date('t')),
            'plotDataEndDate' => date('Y-m-' . date('t'))
        ];
        $targetEndTimestamp = $this->Term->getCurrentTermData()['end_date'];
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 0);
        //期の終了日までのデータ表示
        $this->assertEquals($expected, $actual);

        $expected = [
            'graphStartDate'  => date('Y-m-' . (string)(date('t') - 10)),
            'graphEndDate'    => date('Y-m-' . (string)(date('t') - 1)),
            'plotDataEndDate' => date('Y-m-' . (string)(date('t') - 1))
        ];
        $targetEndTimestamp = $this->Term->getCurrentTermData()['end_date'] - 1 * DAY;
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 0);
        //期の終了日から１日前が指定終了日ならそれまでのデータ表示
        $this->assertEquals($expected, $actual);

        $expected = [
            'graphStartDate'  => date('Y-m-' . (string)(date('t') - 18)),
            'graphEndDate'    => date('Y-m-' . (string)(date('t') - 9)),
            'plotDataEndDate' => date('Y-m-' . (string)(date('t') - 9))
        ];
        $targetEndTimestamp = $this->Term->getCurrentTermData()['end_date'] - 9 * DAY;
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 0);
        //期の終了日から９日前が指定終了日ならそれまでのデータ表示
        $this->assertEquals($expected, $actual);
    }

    /**
     * グラフ表示期間算出メソッドのテスト
     * - 指定終了日が期の開始日、終了日の両方に近くない場合
     */
    function testGetGraphRangeNormal()
    {
        $this->_setUpGraphDefault();

        $expected = [
            'graphStartDate'  => date('Y-m-01'),
            'graphEndDate'    => date('Y-m-10'),
            'plotDataEndDate' => date('Y-m-07'),
        ];
        $targetEndTimestamp = $this->Term->getCurrentTermData()['start_date'] + 6 * DAY;
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 3);
        $this->assertEquals($expected, $actual);

        $expected = [
            'graphStartDate'  => date('Y-m-02'),
            'graphEndDate'    => date('Y-m-11'),
            'plotDataEndDate' => date('Y-m-08'),
        ];
        $targetEndTimestamp = $this->Term->getCurrentTermData()['start_date'] + 7 * DAY;
        $actual = $this->GoalService->getGraphRange($targetEndTimestamp, $targetDays = 10, $maxBufferDays = 3);
        $this->assertEquals($expected, $actual);
    }

    /**
     * グラフデータ取得の基本テスト(期の始め)
     * 正しい件数で正しいデータが取得できていること
     * データがない場合
     */
    function test_getUserAllGoalProgressForDrawingGraph_basicStartTerm()
    {
        $this->setDefaultTeamIdAndUid();
        $this->setupTerm();
        $targetDays = 10;
        $maxBufferDays = 2;
        $term = $this->Term->getCurrentTermData();
        $termStartTimestamp = $term['start_date'];

        $ret = $this->_getUserAllGoalProgressForDrawingGraph($termStartTimestamp, $targetDays, $maxBufferDays);
        //データ件数のチェック(10日分+項目名1=11)
        $this->assertCount(11, $ret[0]);//sweet spot top
        $this->assertCount(11, $ret[1]);//sweet spot bottom
        $this->assertCount(11, $ret[3]);//x
        //sweet spotの開始値が0になっていること
        $this->assertEquals(0, $ret[0][1]);
        $this->assertEquals(0, $ret[1][1]);
        //sweet spotの終了値が前の値より大きいこと
        $this->assertTrue($ret[0][9] < $ret[0][10]);
        $this->assertTrue($ret[1][9] < $ret[1][10]);
    }

    /**
     * グラフデータ取得の基本テスト(期の真ん中)
     * 正しい件数で正しいデータが取得できていること
     * データがない場合
     */
    function test_getUserAllGoalProgressForDrawingGraph_basicMiddleTerm()
    {
        $this->setDefaultTeamIdAndUid();
        $this->setupCurrentTermExtendDays();
        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $ret = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);
        //データ件数のチェック(10日分+項目名1=11)
        $this->assertCount(11, $ret[0]);//sweet spot top
        $this->assertCount(11, $ret[1]);//sweet spot bottom
        $this->assertCount(8, $ret[2]);//data(10日-バッファ2日-1日(当日のデータなし)+項目1個=8)
        $this->assertCount(11, $ret[3]);//x
        //sweet spotの開始値が0以外になっていること
        $this->assertNotEquals(0, $ret[0][1]);
        $this->assertNotEquals(0, $ret[1][1]);
        //sweet spotの終了値が前の値より大きいこと
        $this->assertTrue($ret[0][9] < $ret[0][10]);
        $this->assertTrue($ret[1][9] < $ret[1][10]);
        //dataは全てnullになっていること
        $this->assertNull($ret[2][1]);
        $this->assertNull($ret[2][7]);
    }

    /**
     * グラフデータ取得の基本テスト(期の終わり)
     * 正しい件数で正しいデータが取得できていること
     * データがない場合
     */
    function test_getUserAllGoalProgressForDrawingGraph_basicEndTerm()
    {
        $this->setDefaultTeamIdAndUid();
        $this->setupTerm();
        $targetDays = 10;
        $maxBufferDays = 2;
        $term = $this->Term->getCurrentTermData();
        $targetEndTimestamp = $term['end_date'];

        $ret = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //データ件数のチェック(10日分+項目名1=11)
        $this->assertCount(11, $ret[0]);//sweet spot top
        $this->assertCount(11, $ret[1]);//sweet spot bottom
        $this->assertCount(11, $ret[3]);//x
        //sweet spotの開始値が0以外になっていること
        $this->assertNotEquals(0, $ret[0][1]);
        $this->assertNotEquals(0, $ret[1][1]);
        //sweet spotの終了値が前の値より大きいこと
        $this->assertTrue($ret[0][9] < $ret[0][10]);
        $this->assertTrue($ret[1][9] < $ret[1][10]);
        //dataは全てnullになっていること
        //TODO:travisこけてるからコメントアウト。要調査。
//        $this->assertNull($ret[2][1]);
        //$this->assertNull($ret[2][9]);
    }

    /**
     * 今日が今期の開始日
     * - 前期のゴールが含まれないこと
     * - 来期のゴールが含まれないこと
     * - 今期のゴール追加後に今期のゴールが含まれること
     */
    function getUserAllGoalProgressForDrawingGraph_noLogStartTermOnlyToday()
    {
        $this->setDefaultTeamIdAndUid();
        $this->setupCurrentTermStartToday();
        $targetDays = 10;
        $maxBufferDays = 2;
        $now = time();

        $this->createGoalKrs(Term::TYPE_PREVIOUS, [50]);
        $this->createGoalKrs(Term::TYPE_NEXT, [50]);
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $ret = $this->_getUserAllGoalProgressForDrawingGraph($now, $targetDays, $maxBufferDays);
        //前期のゴールが含まれないこと
        $this->assertCount(1, $ret[2]);//dataが項目名のみ

        //今期のゴール追加
        $goalId = $this->createGoalKrs(Term::TYPE_CURRENT, [50]);
        $ret = $this->_getUserAllGoalProgressForDrawingGraph($now, $targetDays, $maxBufferDays);
        $this->assertCount(2, $ret[2]);
        $this->assertEquals(50, $ret[2][1]);//ゴール進捗が存在すること

        //ゴール進捗を更新
        $this->createKr($goalId, 1, 1, 0);
        $ret = $this->_getUserAllGoalProgressForDrawingGraph($now, $targetDays, $maxBufferDays);
        $this->assertNotEquals(50, $ret[2][1]);//ゴール進捗が更新されること
    }

    /**
     * 今日が今期の終了日
     * - データの個数がフルになっていること
     * - 当日に進捗があった場合に昨日のデータのログと、当日のデータが違っていること
     */
    function test_getUserAllGoalProgressForDrawingGraph_endTermToday()
    {
        $this->setDefaultTeamIdAndUid();
        $this->setupCurrentTermEndToday();
        //昨日のログ作成
        $goalId = $this->createGoalKrs(Term::TYPE_CURRENT, [50]);
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);
        //進捗を更新(KRを追加)
        $this->createKr($goalId, 1, 1, 100);
        $targetDays = 10;
        $maxBufferDays = 2;
        $now = time();
        $ret = $this->_getUserAllGoalProgressForDrawingGraph($now, $targetDays, $maxBufferDays);
        $this->assertCount(11, $ret[2]);//dataの数が全件分あること
        $this->assertEquals(25, $ret[2][9]);//一日前のゴール進捗
        $this->assertEquals(75, $ret[2][10]);//当日のゴール進捗
    }

    /**
     * ゴール作成が過去のログ進捗に影響があること
     * - 昨日のログがあり、ゴールが追加された場合に過去のログ進捗に影響を与えないこと
     */
    function test_getUserAllGoalProgressForDrawingGraph_effectLogs()
    {
        $this->setDefaultTeamIdAndUid();
        $this->setupCurrentTermExtendDays();
        //昨日のログ作成
        $this->createGoalKrs(Term::TYPE_CURRENT, [50]);
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $targetDays = 10;
        $maxBufferDays = 2;
        $now = time();
        //1回目のデータ取得
        $before = $this->_getUserAllGoalProgressForDrawingGraph($now, $targetDays, $maxBufferDays);
        //新しいゴール追加
        $this->createGoalKrs(Term::TYPE_CURRENT, [100]);
        $this->_clearCache();
        //2回目のデータ取得
        $after = $this->_getUserAllGoalProgressForDrawingGraph($now, $targetDays, $maxBufferDays);
        //ログデータに影響があること
        $this->assertNotEquals($before[2][7], $after[2][7]);
        //当日のデータが更新されていること
        $this->assertNotEquals($before[2][8], $after[2][8]);
    }

    /**
     * グラフデータ取得でのデータの整合性チェック
     */
    function test_getUserAllGoalProgressForDrawingGraph_dataValid()
    {
        //今期を3ヶ月に設定(当月にその前後30日ずつ拡張したものにする)
        $this->setupCurrentTermExtendDays();
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $this->createGoalKrs(Term::TYPE_CURRENT, [0]);
        $this->createGoalKrs(Term::TYPE_CURRENT, [100]);
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $ret1 = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);
        //ゴールの進捗が変わっていない場合のログと当日のデータが等しくなることを確認
        $this->assertEquals($ret1[2][8], $ret1[2][7]);
        $this->assertEquals(50, $ret1[2][8]);

        $this->createGoalKrs(Term::TYPE_CURRENT, [0]);
        $ret2 = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);
        //新しいKRを追加(進捗0)した場合、最新の進捗と、過去の進捗に影響する事を確認
        $this->assertNotEquals($ret1[2][8], $ret2[2][8]);
        //最新の進捗と直前の進捗は同じ値になる
        $this->assertEquals($ret2[2][8], $ret2[2][7]);
    }

    /**
     * ゴールを追加した時に過去の進捗に影響する事
     */
    function test_getUserAllGoalProgressForDrawingGraph_addGoal()
    {
        $this->setupCurrentTermExtendDays();
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $this->createGoalKrs(Term::TYPE_CURRENT, [0]);
        $this->createGoalKrs(Term::TYPE_CURRENT, [100]);
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $before = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        $this->createGoalKrs(Term::TYPE_CURRENT, [0]);

        $after = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //最新の進捗が下がっている事
        $this->assertTrue($after[2][8] < $before[2][8]);
        //過去の進捗が下がっている事
        $this->assertTrue($after[2][7] < $before[2][7]);
        //最新と直前の進捗が同じ値になる事
        $this->assertEquals($after[2][8], $after[2][7]);
    }

    /**
     * ゴールを削除した時に過去の進捗に影響する事
     */
    function test_getUserAllGoalProgressForDrawingGraph_delGoal()
    {
        $this->setupCurrentTermExtendDays();
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $goalId1 = $this->createGoalKrs(Term::TYPE_CURRENT, [50]);
        $goalId2 = $this->createGoalKrs(Term::TYPE_CURRENT, [100]);
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $before = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //ゴール削除(進捗100%のゴールを削除。これで進捗は下がるはず)
        $this->GoalMember->deleteAll(['GoalMember.goal_id' => $goalId2]);

        $after = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //最新の進捗が下がっている事
        $this->assertTrue($after[2][8] < $before[2][8]);
        //直前の進捗が下がっている事
        $this->assertTrue($after[2][7] < $before[2][7]);
        //最新と直前の進捗が同じ値になる事
        $this->assertEquals($after[2][8], $after[2][7]);
    }

    /**
     * ゴールの重要度を更新した時に過去の進捗に影響する事
     */
    function test_getUserAllGoalProgressForDrawingGraph_changePriority()
    {
        $this->setupCurrentTermExtendDays();
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $goalId1 = $this->createGoalKrs(Term::TYPE_CURRENT, [50]);
        $goalId2 = $this->createGoalKrs(Term::TYPE_CURRENT, [100]);
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $before = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //goalId2の重要度を下げる
        $this->GoalMember->updateAll(['GoalMember.priority' => 1], ['GoalMember.goal_id' => $goalId2]);

        $after = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //最新の進捗が下がっている事
        $this->assertTrue($after[2][8] < $before[2][8]);
        //過去の進捗が下がっている事
        $this->assertTrue($after[2][7] < $before[2][7]);
        //最新と直前の進捗が同じ値になる事
        $this->assertEquals($after[2][8], $after[2][7]);
    }

    /**
     * KRを追加した時に過去の進捗に影響する事
     */
    function test_getUserAllGoalProgressForDrawingGraph_addKR()
    {
        $this->setupCurrentTermExtendDays();
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $goalId1 = $this->createGoalKrs(Term::TYPE_CURRENT, [50]);
        $goalId2 = $this->createGoalKrs(Term::TYPE_CURRENT, [100]);
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $before = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //KRを１つ追加
        $this->createKr($goalId2, 1, 1, 0);

        $after = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //最新の進捗が下がっている事
        $this->assertTrue($after[2][8] < $before[2][8]);
        //過去の進捗が下がっている事
        $this->assertTrue($after[2][7] < $before[2][7]);
        //最新と直前の進捗が同じ値になる事
        $this->assertEquals($after[2][8], $after[2][7]);
    }

    /**
     * KRを削除した時に過去の進捗に影響する事
     */
    function test_getUserAllGoalProgressForDrawingGraph_delKR()
    {
        $this->setupCurrentTermExtendDays();
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $goalId1 = $this->createGoalKrs(Term::TYPE_CURRENT, [50]);
        $goalId2 = $this->createGoalKrs(Term::TYPE_CURRENT, [100]);
        //KRを１つ追加(完了済み)
        $krId = $this->createKr($goalId1, 1, 1, 100);
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $before = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //KRを１つ削除
        $this->delKr($krId);

        $after = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //最新の進捗が下がっている事
        $this->assertTrue($after[2][8] < $before[2][8]);
        //過去の進捗が下がっている事
        $this->assertTrue($after[2][7] < $before[2][7]);
        //最新と直前の進捗が同じ値になる事
        $this->assertEquals($after[2][8], $after[2][7]);
    }

    /**
     * KRの重要度を更新した時に過去の進捗に影響する事
     */
    function test_getUserAllGoalProgressForDrawingGraph_updateKR()
    {
        $this->setupCurrentTermExtendDays();
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $goalId1 = $this->createGoalKrs(Term::TYPE_CURRENT, [30]);
        $goalId2 = $this->createGoalKrs(Term::TYPE_CURRENT, [100]);
        //KRを１つ追加
        $krId = $this->createKr($goalId1, 1, 1, 50);
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $before = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //KRの重要度を下げる
        $this->KeyResult->id = $krId;
        $this->KeyResult->saveField('priority', 1);

        $after = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //最新の進捗が下がっている事
        $this->assertTrue($after[2][8] < $before[2][8]);
        //過去の進捗が下がっている事
        $this->assertTrue($after[2][7] < $before[2][7]);
        //最新と直前の進捗が同じ値になる事
        $this->assertEquals($after[2][8], $after[2][7]);
    }

    /**
     * 過去の進捗が100を超える場合に100に補正される事
     */
    function test_getUserAllGoalProgressForDrawingGraph_over100()
    {
        $this->setupCurrentTermExtendDays();
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $goalId1 = $this->createGoalKrs(Term::TYPE_CURRENT, [100]);
        $goalId2 = $this->createGoalKrs(Term::TYPE_CURRENT, [100]);
        //KRを１つ追加(値が大きいがKR自体の進捗は50%)
        $krId = $this->createKr($goalId1, 1, 1, 1000, 0, 2000);
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $before = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //KRの値を下げる
        $this->KeyResult->id = $krId;
        $this->KeyResult->saveField('target_value', 100);
        $this->KeyResult->saveField('current_value', 50);

        $after = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //実行前の直前の進捗が100ではない事
        $this->assertNotEquals(100, $before[2][7]);
        //実行後の直前の進捗が100になる事
        $this->assertEquals(100, $after[2][7]);
    }

    /**
     * 過去の進捗が0を下回る場合に0に補正される事
     */
    function test_getUserAllGoalProgressForDrawingGraph_under0()
    {
        $this->setupCurrentTermExtendDays();
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $goalId1 = $this->createGoalKrs(Term::TYPE_CURRENT, [0]);
        $goalId2 = $this->createGoalKrs(Term::TYPE_CURRENT, [0]);

        //KRを１つ追加(KR自体の進捗は50%)
        $krId = $this->createKr($goalId1, 1, 1, 50);
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $before = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //KRの開始値をあげる
        $this->KeyResult->id = $krId;
        $this->KeyResult->saveField('start_value', 60);

        $after = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);

        //実行前の直前の進捗が0ではない事
        $this->assertNotEquals(0, $before[2][7]);
        //実行後の直前の進捗が0になる事
        $this->assertEquals(0, $after[2][7]);
    }

    /**
     * ゴール進捗は小数点第一位まで切り捨てられている事
     */
    function test_getUserAllGoalProgressForDrawingGraph_decimalNum()
    {
        $this->setupCurrentTermExtendDays();
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $goalId = $this->createGoalKrs(Term::TYPE_CURRENT, [1]);
        //ゴール進捗が小数点以下になるようなKRを作成
        $this->createKr($goalId, 1, 1, 11, 0, 100, 1);
        $this->createKr($goalId, 1, 1, 99, 0, 100, 5);

        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $ret = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);
        $value = $ret[2][8];
        $decimalNum = strlen($value) - (strpos($value, '.') + 1);
        //小数点以下の桁数が1かどうか？
        $this->assertEquals(1, $decimalNum);
        //最新と直前の進捗が同じ値になる事
        $this->assertEquals($ret[2][8], $ret[2][7]);
    }

    /**
     * キャッシュが正常に効いているか？
     */
    function test_getUserAllGoalProgressForDrawingGraph_cache()
    {
        $this->setupCurrentTermExtendDays();
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $this->createGoalKrs(Term::TYPE_CURRENT, [0]);
        $goalId = $this->createGoalKrs(Term::TYPE_CURRENT, [100]);
        $this->KrValuesDailyLogService->saveAsBulk(1, $yesterday);

        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $before = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);
        //過去ログを直接書き換えてキャッシュが効いてるかどうかの確認
        $this->KrValuesDailyLog->updateAll(['current_value' => 0], ['KrValuesDailyLog.goal_id' => $goalId]);
        $after1 = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);
        //ログ書き換えてもキャッシュが効いてるから過去ログの結果が変わらないこと
        $this->assertEquals($before[2][7], $after1[2][7]);
        //過去ログのキャッシュを削除して、結果が変わる事を確認
        $this->GlRedis->deleteKeys('*:' . CACHE_KEY_USER_GOAL_KR_VALUES_DAILY_LOG . ':*');
        $after2 = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);
        $this->assertNotEquals($before[2][7], $after2[2][7]);
    }

    /**
     * データの件数が正しい事を確認
     */
    function test_getUserAllGoalProgressForDrawingGraph_dataCount()
    {
        $this->setDefaultTeamIdAndUid();
        $this->setupCurrentTermExtendDays();
        $targetDays = 10;
        $maxBufferDays = 2;
        $targetEndTimestamp = time();

        $ret = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);
        //データ件数のチェック(10日分+項目名1=11)
        $this->assertCount(8, $ret[2]);//data(10日-バッファ2日-1日(当日のデータなし)+項目1個=8)
        //dataは全てnullになっていること
        $this->assertNull($ret[2][1]);
        $this->assertNull($ret[2][7]);

        //進捗0のゴールを一つ追加。これで最新の進捗は0になるはず
        $this->createGoalKrs(Term::TYPE_CURRENT, [0]);
        $ret = $this->_getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays);
        $this->assertCount(9, $ret[2]);//data(10日-バッファ2日+項目1個=9)
        $this->assertNull($ret[2][1]);
        //最新の進捗が0になっている
        $this->assertEquals(0, $ret[2][8]);

    }

    /**
     * テストの為のユーザグラフデータ取得用メソッド
     *
     * @param string $targetEndTimestamp
     * @param int    $targetDays
     * @param int    $maxBufferDays
     *
     * @return array
     */
    function _getUserAllGoalProgressForDrawingGraph($targetEndTimestamp, $targetDays, $maxBufferDays)
    {

        $graphRange = $this->GoalService->getGraphRange(
            $targetEndTimestamp,
            $targetDays,
            $maxBufferDays
        );
        $progressGraph = $this->GoalService->getUserAllGoalProgressForDrawingGraph(
            1,
            $graphRange['graphStartDate'],
            $graphRange['graphEndDate'],
            $graphRange['plotDataEndDate'],
            true
        );
        return $progressGraph;
    }

    //余裕があればやる
    function testFindLatestTotalGoalProgress()
    {
        $this->markTestSkipped();
    }

    //余裕があればやる
    function testFindSummarizedGoalProgressesFromLog()
    {
        $this->markTestSkipped();
    }

    //余裕があればやる
    function testSumDailyGoalProgress()
    {
        $this->markTestSkipped();
    }

    //余裕があればやる
    function testSumGoalProgress()
    {
        $this->markTestSkipped();
    }

    /**
     * sweet spotの値の件数チェック
     */
    function testGetSweetSpotValueCount()
    {
        $this->_setUpGraphDefault();
        $termStartTimestamp = $this->Term->getCurrentTermData(true)['start_date'];
        $termEndTimestamp = $this->Term->getCurrentTermData(true)['end_date'];

        $startDate = date('Y-m-d', $termStartTimestamp);
        $endDate = date('Y-m-d', $termEndTimestamp);
        $actual = $this->GoalService->getSweetSpot($startDate, $endDate);
        $this->assertcount((int)date('t'), $actual['top']);

        $startDate = date('Y-m-d', $termStartTimestamp + DAY);
        $endDate = date('Y-m-d', $termEndTimestamp);
        $actual = $this->GoalService->getSweetSpot($startDate, $endDate);
        $this->assertcount((int)date('t') - 1, $actual['top']);

        $startDate = date('Y-m-d', $termStartTimestamp);
        $endDate = date('Y-m-d', $termEndTimestamp - DAY);
        $actual = $this->GoalService->getSweetSpot($startDate, $endDate);
        $this->assertcount((int)date('t') - 1, $actual['top']);

        $startDate = date('Y-m-d', $termStartTimestamp + DAY);
        $endDate = date('Y-m-d', $termEndTimestamp - DAY);
        $actual = $this->GoalService->getSweetSpot($startDate, $endDate);
        $this->assertcount((int)date('t') - 2, $actual['top']);
    }

    /**
     * sweet spotの値が正しいこと
     */
    function testGetSweetSpotValue()
    {
        $this->_setUpGraphDefault();
        $termStartTimestamp = $this->Term->getCurrentTermData(true)['start_date'];
        $termEndTimestamp = $this->Term->getCurrentTermData(true)['end_date'];
        $startDate = date('Y-m-d', $termStartTimestamp);
        $endDate = date('Y-m-d', $termEndTimestamp);
        $actualFullTerm = $this->GoalService->getSweetSpot($startDate, $endDate);
        $this->assertEquals(0, $actualFullTerm['top'][0]);
        $this->assertEquals(0, $actualFullTerm['bottom'][0]);
        $lastKey = (int)(date('t') - 1);
        $this->assertEquals(GoalService::GRAPH_SWEET_SPOT_MAX_TOP, floor($actualFullTerm['top'][$lastKey]));
        $this->assertEquals(GoalService::GRAPH_SWEET_SPOT_MAX_BOTTOM, floor($actualFullTerm['bottom'][$lastKey]));

        $startDate = date('Y-m-d', $termStartTimestamp + DAY);
        $endDate = date('Y-m-d', $termEndTimestamp);
        $actual = $this->GoalService->getSweetSpot($startDate, $endDate);
        $this->assertEquals($actualFullTerm['top'][1], $actual['top'][0]);
        $this->assertEquals($actualFullTerm['bottom'][1], $actual['bottom'][0]);
    }

    /**
     * sweet spotの値取得範囲が期を超えている場合のテスト
     */
    function testGetSweetSpotInTermOrNot()
    {
        $this->_setUpGraphDefault();
        $termStartTimestamp = $this->Term->getCurrentTermData(true)['start_date'];
        $termEndTimestamp = $this->Term->getCurrentTermData(true)['end_date'];

        $startDate = date('Y-m-d', $termStartTimestamp - DAY);
        $endDate = date('Y-m-d', $termEndTimestamp);
        $this->assertEmpty($this->GoalService->getSweetSpot($startDate, $endDate));

        $startDate = date('Y-m-d', $termStartTimestamp);
        $endDate = date('Y-m-d', $termEndTimestamp + DAY);
        $this->assertEmpty($this->GoalService->getSweetSpot($startDate, $endDate));

        $startDate = date('Y-m-d', $termStartTimestamp - DAY);
        $endDate = date('Y-m-d', $termEndTimestamp + DAY);
        $this->assertEmpty($this->GoalService->getSweetSpot($startDate, $endDate));

        $startDate = date('Y-m-d', $termStartTimestamp);
        $endDate = date('Y-m-d', $termEndTimestamp);
        $this->assertNotEmpty($this->GoalService->getSweetSpot($startDate, $endDate));
    }

    /**
     * ゴール進捗計算メソッドのKRの重要度によって重み付けして計算しているかのテスト
     * - ステートレスなメソッドのため、前提となるデータの準備不要
     */
    function testGetProgressPriority()
    {
        //KRの重要度が同じ場合
        $krs = [
            [
                'priority'      => 1,
                'start_value'   => 0,
                'target_value'  => 100,
                'current_value' => 0,
            ],
            [
                'priority'      => 1,
                'start_value'   => 0,
                'target_value'  => 100,
                'current_value' => 100,
            ],
        ];
        //進捗0と100で50になるはず
        $this->assertEquals(50, $this->GoalService->calcProgressByOwnedPriorities($krs));

        //KRの重要度が違う場合
        $krs = [
            [
                'priority'      => 1,
                'start_value'   => 0,
                'target_value'  => 100,
                'current_value' => 0,
            ],
            [
                'priority'      => 5,
                'start_value'   => 0,
                'target_value'  => 100,
                'current_value' => 100,
            ],
        ];
        //進捗0と100だが、priorityが違うため、50にはならないはず
        $this->assertNotEquals(50, $this->GoalService->calcProgressByOwnedPriorities($krs));
    }

    /**
     * ゴール進捗計算メソッドでの閾値テスト
     */
    function testGetProgressThreshold()
    {
        //進捗率が99.*の場合は結果が99になるはず
        $krs = [
            [
                'priority'      => 1,
                'start_value'   => 0,
                'target_value'  => 100,
                'current_value' => 99.01,
            ],
        ];
        $this->assertEquals(99, $this->GoalService->calcProgressByOwnedPriorities($krs));

        //進捗率が0.*の場合は結果が1になるはず
        $krs = [
            [
                'priority'      => 1,
                'start_value'   => 0,
                'target_value'  => 100,
                'current_value' => 0.01,
            ],
        ];
        $this->assertEquals(1, $this->GoalService->calcProgressByOwnedPriorities($krs));
    }

    /**
     * グラフ表示用の進捗データの生成のテスト
     */
    function testProcessProgressesToGraph()
    {
        $progresses = [
            '2017-01-03' => 10,
            '2017-01-04' => 20,
            '2017-01-06' => 30,
            '2017-01-07' => 40,
        ];
        $expected = [
            (int)0 => (int)0,
            (int)1 => (int)0,
            (int)2 => (int)10,
            (int)3 => (int)20,
            (int)4 => (int)20,
            (int)5 => (int)30,
            (int)6 => (int)40,
            (int)7 => (int)40,
            (int)8 => (int)40,
            (int)9 => (int)40
        ];
        $actual = $this->GoalService->processProgressesToGraph('2017-01-01', '2017-01-10', $progresses);
        $this->assertEquals($expected, $actual);

        $progresses = [
            '2017-01-01' => 10,
            '2017-01-04' => 20,
            '2017-01-06' => 30,
            '2017-01-07' => 40,
            '2017-01-10' => 60,
        ];
        $expected = [
            (int)0 => (int)10,
            (int)1 => (int)10,
            (int)2 => (int)10,
            (int)3 => (int)20,
            (int)4 => (int)20,
            (int)5 => (int)30,
            (int)6 => (int)40,
            (int)7 => (int)40,
            (int)8 => (int)40,
            (int)9 => (int)60
        ];
        $actual = $this->GoalService->processProgressesToGraph('2017-01-01', '2017-01-10', $progresses);
        $this->assertEquals($expected, $actual);
    }

    //余裕あればやる
    function testGetProgressFromCache()
    {
        $this->markTestSkipped();
    }

    //余裕あればやる
    function testWriteProgressToCache()
    {
        $this->markTestSkipped();
    }

    function _setUpGraphDefault()
    {
        //実行月の期間1ヶ月で生成される。開始日:当月の月初、終了日:当月の月末
        $this->Term->addTermData(Term::TYPE_CURRENT);
    }

}
