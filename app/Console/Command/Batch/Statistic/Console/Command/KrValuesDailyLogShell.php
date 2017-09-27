<?php
App::import('Service', 'KrValuesDailyLogService');
App::uses('AppUtil', 'Util');

/**
 * KR日次進捗集計用バッチ
 * Console/cake Statistic.kr_values_daily_log -d YYYY-MM-DD
 * 説明
 * - 指定日までの最新のKR進捗から各ゴールの進捗を求める。
 * - デフォルトの指定日は前日
 *
 * @property Team             $Team
 * @property Term             $Term
 * @property Goal             $Goal
 * @property KeyResult        $KeyResult
 * @property KrProgressLog    $KrProgressLog
 * @property GoalMember       $GoalMember
 * @property KrValuesDailyLog $KrValuesDailyLog
 */
class KrValuesDailyLogShell extends AppShell
{
    public $uses = array(
        'Team',
        'Term',
        'KeyResult',
        'KrValuesDailyLog',
    );

    public function startup()
    {
        parent::startup();
    }

    /**
     * @return ConsoleOptionParser
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        $parser = parent::getOptionParser();
        $options = [
            'date'             => ['short' => 'd', 'help' => '集計日(YYYY-MM-DD)', 'required' => false,],
            'timezone'         => ['short' => 't', 'help' => '対象のチームのタイムゾーン', 'required' => false,],
            'currentTimestamp' => ['short' => 'c', 'help' => '現在のタイムスタンプ(テスト用途)', 'required' => false,],
        ];
        $parser->addOptions($options);
        return $parser;
    }

    /**
     * shellのメイン処理
     * - 日付の指定はタイムゾーンの指定がある場合のみ有効
     * - タイムゾーンの指定がない場合は、実行日時から対象のタイムゾーンを算出して、対象のチームのみ実行
     * - 対象タイムゾーンはローカルタイム0:00になるもの
     */
    public function main()
    {
        // ターゲットのタイムゾーン
        $targetTimezone = $this->params['timezone'] ?? null;
        if ($targetTimezone) {
            // 日付が指定できるのはtimezoneが指定されている場合のみ
            // デフォルトの指定日は前日
            $targetDate = $this->params['date'] ?? date('Y-m-d', strtotime('yesterday'));
            return $this->_mainProcess($targetTimezone, $targetDate);
        }

        //タイムゾーン指定が無い場合は対象タイムゾーンを自動判定
        $yesterdayDate = date('Y-m-d', strtotime('yesterday'));
        $todayDate = date('Y-m-d');
        //テストの場合のみ利用！現在日時のタイムスタンプをパラメータから取得
        $nowTimestamp = $this->params['currentTimestamp'] ?? time();
        $startTodayTimestamp = strtotime('00:00:00');
        // UTC0:00と現在日時の時差(0 - 23)
        $difHourFromUtcMidnight = AppUtil::diffHourFloorByMinute($nowTimestamp, $startTodayTimestamp);
        //時差によって対象タイムゾーンを自動判定
        if ($difHourFromUtcMidnight == 0) {
            // UTC+0:00 Western Europe Time, London
            // timezone = 0で実行、ログ対象は前日分
            $this->_mainProcess(0, $yesterdayDate);
        } elseif ($difHourFromUtcMidnight == 12) {
            // UTC+12:00(Auckland, Fiji)
            // timezone = +12で実行、ログ対象は当日分
            $this->_mainProcess(12, $todayDate);
            // UTC-12:00(Eniwetok, Kwajalein)
            // timezone = -12で実行、ログ対象は前日分
            $this->_mainProcess(-12, $yesterdayDate);
        } elseif ($difHourFromUtcMidnight < 12) {
            // UTC-11:00(Midway Island) - UTC-1:00(Cape Verde Islands)
            // timezone = -xxで実行、ログ対象は前日分
            $this->_mainProcess(-$difHourFromUtcMidnight, $yesterdayDate);
        } else {
            // $timeOffset > 12
            // UTC+1:00(Central Europe Time) - UTC+11:00(Solomon Islands)
            $targetTimezone = 24 - $difHourFromUtcMidnight;
            // ログ対象は当日分
            $this->_mainProcess($targetTimezone, $todayDate);
        }
    }

    /**
     * メインの保存処理
     *
     * @param float  $targetTimezone
     * @param string $targetDate
     */
    protected function _mainProcess(float $targetTimezone, string $targetDate)
    {
        // validate
        if (!$this->_validateTargetDate($targetDate)) {
            $this->error('Invalid parameter', $this->_usageString());
        }
        if (!$this->_validateTimezone($targetTimezone)) {
            $timezones = array_keys(AppUtil::getTimezoneList());
            $this->error('Invalid parameter. Timezone should be in following values.', $timezones);
        }

        // 該当日のデータを削除(ハードデリート)
        // TODO: 現時点では、この処理は行わない。過去のKR値ログは書き換えることができないため。詳しくは、 https://github.com/IsaoCorp/goalous/pull/5486
        //       レアケースだが、timezoneの変更によって同日のデータが存在する場合がある。その際に既存データを削除する以下の処理は必要。
        // $this->KrValuesDailyLog->deleteAll(['KrValuesDailyLog.target_date' => $targetDate]);

        // 今期のチームの期間設定が対象タイムゾーンと一致するチーム
        $teamIds = $this->Team->findIdsByTimezone($targetTimezone);

        $this->_saveKrValuesDailyLogsAsBulk($teamIds, $targetDate, $targetTimezone);
    }

    /**
     * 今期KR一覧の値をバルクで保存する
     * Modelのcurrent_team_idを初期化
     * - 実行が失敗した場合、一度だけ失敗したチームのみ再実行を走らせる
     *
     * @param array  $teamIds
     * @param string $targetDate
     * @param float  $targetTimezone
     * @param bool   $isRerunning
     *
     * @return bool
     */
    protected function _saveKrValuesDailyLogsAsBulk(
        array $teamIds,
        string $targetDate,
        float $targetTimezone,
        bool $isRerunning = false
    ) {
        /** @var KrValuesDailyLogService $KrValuesDailyLogService */
        $KrValuesDailyLogService = ClassRegistry::init('KrValuesDailyLogService');

        //メモリ消費を抑えるためにチーム毎に集計し保存する。
        $successCount = 0;
        $failureTeams = [];
        foreach ($teamIds as $teamId) {
            // モデルに current_team_id をセット
            $this->_setupModels($teamId);
            //バルクで保存
            if ($KrValuesDailyLogService->saveAsBulk($teamId, $targetDate)) {
                $successCount++;
            } else {
                $failureTeams[] = $teamId;
            }
        }

        //失敗した場合のみログ出力
        //TODO: 成功の場合(Infoレベル)も、slackにチャンネル分けて出力すべき
        if (count($failureTeams) > 0) {
            $this->log(sprintf('[targetDate:%s, targetTimezone:%s][success:%d failure:%d] Done kr_values_daily_log shell.',
                    $targetDate,
                    $targetTimezone,
                    $successCount,
                    count($failureTeams))
            );

            // 保存に失敗したチームは一度だけ再実行する
            if (!$isRerunning) {
                $this->log(sprintf("Rerun batch for only failure teams. failureTeamIds: %s",
                    implode(",", $failureTeams)));
                $this->_saveKrValuesDailyLogsAsBulk($failureTeams, $targetDate, true);
            }
        }

        return;
    }

    /**
     * @return string
     */
    protected function _usageString(): string
    {
        return 'Usage: date format should be YYYY-MM-DD.';
    }

    /**
     * @param int $teamId
     */
    protected function _setupModels(int $teamId)
    {
        foreach ($this->uses as $model) {
            $this->{$model}->current_team_id = $teamId;
        }
    }

    /**
     * @param string $date
     *
     * @return bool
     */
    protected function _validateTargetDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        list($y, $m, $d) = explode('-', $date);
        if (strlen($date) != 10 || !checkdate(intval($m), intval($d), intval($y))) {
            return false;
        }
        return true;
    }

    /**
     * タイムゾーンの値が正しいかチェック
     *
     * @param float $timezone
     *
     * @return bool
     */
    protected function _validateTimezone(float $timezone): bool
    {
        $timezones = array_keys(AppUtil::getTimezoneList());
        return in_array($timezone, $timezones);
    }

}
