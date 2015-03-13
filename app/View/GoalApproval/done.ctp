<style type="text/css">
    .approval_body_text {
        font-size: 14px
    }

    .sp-feed-alt-sub {
        background: #f5f5f5;
        position: fixed;
        top: 50px;
        z-index: 1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, .15);
        width: 100%;
        left: 0;
    }

    .approval_body_start_area {
        margin-top: 40px;
    }
</style>

<div class="col col-md-12 sp-feed-alt-sub" style="top: 50px;" id="SubHeaderMenu">
    <div class="col col-xxs-6 text-align_r">
        <a class="font_lightGray-veryDark no-line plr_18px sp-feed-link inline-block pt_12px height_40px"
           id="SubHeaderMenuFeed" href="/goalapproval">
            <?= __d('gl', "処理待ち") ?> <? if ($unapproved_cnt > 0) {
                echo $unapproved_cnt;
            } ?></a>
    </div>
    <div class="col col-xxs-6">
        <a class="font_lightGray-veryDark no-line plr_18px sp-feed-link inline-block pt_12px height_40px sp-feed-active"
           id="SubHeaderMenuGoal">
            <?= __d('gl', "処理済み") ?> <? if ($done_cnt > 0) {
                echo $done_cnt;
            } ?></a>
    </div>
</div>

<div class="approval_body_start_area">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <? if (isset($goal_info) === true && count($goal_info) > 0) { ?>
                <? foreach ($goal_info as $goal) { ?>
                    <div class="panel panel-default" id="AddGoalFormPurposeWrap">
                        <div class="panel-heading goal-set-heading clearfix">
                            <p class="approval_body_text"><?= __d('gl', "名前") ?>
                                : <?= $goal['User']['local_username']; ?></p>

                            <p class="approval_body_text"><?= __d('gl', "カテゴリ") ?>
                                : <?= $goal['Goal']['goal_category_id'] === '3' ? __d('gl', "職務") : __d('gl',
                                                                                                        "成長"); ?></p>

                            <p class="approval_body_text"><?= __d('gl', "ゴール名") ?>: <?= $goal['Goal']['name']; ?></p>

                            <p class="approval_body_text"><?= $goal['Collaborator']['type'] === '1' ?
                                    __d('gl', "リーダー") : __d('gl', "コラボレーター"); ?></p>

                            <p class="approval_body_text"><?= __d('gl', "単位") ?>
                                : <?= $goal['Goal']['value_unit']; ?></p>

                            <p class="approval_body_text"><?= __d('gl', "達成時") ?>
                                : <?= $goal['Goal']['target_value']; ?></p>

                            <p class="approval_body_text"><?= __d('gl', "開始時") ?>
                                : <?= $goal['Goal']['start_value']; ?></p>

                            <p class="approval_body_text"><?= __d('gl', "期限日") ?>: <?= $goal['Goal']['end_date']; ?></p>

                            <p class="approval_body_text"><?= __d('gl', "重要度") ?>
                                : <?= $goal['Collaborator']['priority']; ?></p>

                            <p class="approval_body_text"><?= __d('gl', "目的") ?>
                                : <?= $goal['Goal']['Purpose']['name']; ?></p>

                            <p class="approval_body_text"><?= __d('gl', "詳細") ?>: <?= $goal['Goal']['name']; ?></p>

                            <p class="approval_body_text"><?= __d('gl', "ゴールイメージ") ?>
                                : <?= $goal['Goal']['photo_file_name']; ?></p>
                        </div>
                    </div>
                <? } ?>
            <? } ?>
        </div>
    </div>
</div>
