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
<?= $this->App->viewStartComment()?>
<?php $this->start('sidebar') ?>
<div class="sidebar-setting" role="complementary">
    <ul class="nav">
        <li class="active"><a href="#basic_setting"><?= __("Basic settings") ?></a></li>
        <li class=""><a href="#term_setting"><?= __("Term settings") ?></a></li>
        <li class=""><a href="#invite_member"><?= __("Invitation") ?></a></li>
        <li class=""><a href="#batch_registration"><?= __("Batch Registration") ?></a></li>
        <li class=""><a href="#goal_category"><?= __("Goal category settings") ?></a></li>
        <li class=""><a href="#evaluation"><?= __("Evaluation settings") ?></a></li>
        <li class=""><a href="#evaluation_score_setting"><?= __("Evaluation score settings") ?></a></li>
        <li class=""><a href="#evaluation_start"><?= __("Begin evaluation") ?></a></li>
        <li class=""><a href="#evaluation_freeze"><?= __("Pause evaluation") ?></a></li>
        <li class=""><a href="#final_evaluation"><?= __("Final evaluation") ?></a></li>
        <li class=""><a href="#progress"><?= __("Evaluation status") ?></a></li>
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
<?= $this->App->viewEndComment()?>
