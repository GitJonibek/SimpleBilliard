<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 6/18/14
 * Time: 5:40 PM
 *
 * @var CodeCompletionView $this
 * @var                    $team_id
 * @var                    $unvalued
 */
// two_column レイアウトで、xxs サイズの時にサイドバーを隠す
$this->set('hidden_sidebar_xxs', true);
?>
<!-- START app/View/Teams/settings.ctp -->
<?php $this->start('sidebar') ?>
<div class="sidebar-setting" role="complementary">
    <ul class="nav">
        <li class="active"><a href="#basic_setting"><?= __("Basic settings") ?></a></li>
        <li class=""><a href="#term_setting"><?= __("Term settings") ?></a></li>
        <li class=""><a href="#invite_member"><?= __("メンバー招待") ?></a></li>
        <li class=""><a href="#batch_registration"><?= __("Batch Registration") ?></a></li>
        <li class=""><a href="#goal_category"><?= __("ゴールカテゴリ設定") ?></a></li>
        <li class=""><a href="#evaluation"><?= __("評価設定") ?></a></li>
        <li class=""><a href="#evaluation_score_setting"><?= __("評価スコア設定") ?></a></li>
        <li class=""><a href="#evaluation_start"><?= __("評価開始") ?></a></li>
        <li class=""><a href="#evaluation_freeze"><?= __("評価凍結") ?></a></li>
        <li class=""><a href="#final_evaluation"><?= __("最終評価") ?></a></li>
        <li class=""><a href="#progress"><?= __("評価状況") ?></a></li>
    </ul>
</div>
<?php $this->end() ?>
<div id="basic_setting">
    <?= $this->element('Team/edit_basic_setting') ?>
</div>
<div id="term_setting">
    <?= $this->element('Team/edit_term_setting') ?>
</div>
<div id="invite_member">
    <?= $this->element('Team/invite') ?>
</div>
<div id="batch_registration">
    <?= $this->element('Team/batch_setup') ?>
</div>
<div id="goal_category">
    <?= $this->element('Team/goal_category_setting') ?>
</div>
<div id="evaluation">
    <?= $this->element('Team/evaluation_setup') ?>
</div>
<div id="evaluation_score_setting">
    <?= $this->element('Team/evaluation_score_setting') ?>
</div>
<div id="evaluation_start">
    <?= $this->element('Team/evaluation_start') ?>
</div>
<div id="evaluation_freeze">
    <?= $this->element('Team/evaluation_freeze') ?>
</div>
<div id="final_evaluation">
    <?= $this->element('Team/final_evaluation') ?>
</div>
<div id="progress">
    <?= $this->element('Team/evaluation_progress') ?>
</div>
<?php $this->append('script'); ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('body').scrollspy({target: '.sidebar-setting'});
    });
</script>
<?php $this->end(); ?>
<!-- END app/View/Teams/settings.ctp -->
