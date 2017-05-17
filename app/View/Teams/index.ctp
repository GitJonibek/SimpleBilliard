<?php
/**
 * @var $team_list
 * @var $group_list
 * @var $prev_week
 * @var $prev_month
 */
?>
<?php $this->start('sidebar'); ?>
<?= $this->element('Team/side_menu', ['active' => 'index']); ?>
<?php $this->end(); ?>
<?= $this->App->viewStartComment() ?>
<?php if ($changed_term_flg): ?>
    <div>
        <div class="panel panel-default mod-notice">
            <div class="panel-block">
                <?php $message = __("The term of your team has been changed. All goals/KRs schedules were automatically updated as follows.

1.The goals/KRs that beings in the current term and ends in the next term were updated to end on the last day of the current term.
2.The goals/KRs that begins in the current term and ends beyond the next term were updated to end on the last day of the current term.
3.The goals/KRs that beings in the next term and ends beyond the next term were updated to end on the last day of the next term.
4.If the start date and end date of a goals/KRs is both within the current term, or both within the next term, were not changed.
5.The goals/KRs that begins and ends beyond the the next term were updated to match the start and end date of the next term."); ?>
                <?= nl2br($message) ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<div>
    <div class="panel panel-default">
        <div class="panel-heading"><?= __("Basic info") ?></div>
        <div class="panel-body add-team-panel-body form-horizontal">
            <div class="form-group">
                <label class="col col-sm-3 control-label form-label"><?= __("Team Name") ?></label>
                <div class="col col-sm-6">
                    <p class="form-control-static">
                        <?= $team['name'] ?>
                    </p>
                </div>
            </div>
            <hr>
            <div class="form-group">
                <label for="" class="col col-sm-3 control-label form-label"><?= __("Team Image") ?></label>

                <div class="col col-sm-6">
                    <?=
                    $this->Upload->uploadImage(['Team' => $team], 'Team.photo',
                        ['style' => 'medium_large']) ?>
                </div>
            </div>
            <hr>
            <div class="form-group">
                <label class="col col-sm-3 control-label form-label"><?= __("Timezone") ?></label>
                <div class="col col-sm-6">
                    <p class="form-control-static">
                        <?= $timezone_label ?>
                    </p>
                </div>
            </div>
            <hr>
            <div class="form-group">
                <label class="col col-sm-3 control-label form-label"><?= __("Plan") ?></label>
                <div class="col col-sm-6">
                    <p class="form-control-static">
                        <?= __("Free Campaign") ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<div>
    <div class="panel panel-default">
        <div class="panel-heading"><?= __("Term settings") ?></div>
        <div class="panel-body add-team-panel-body form-horizontal">
            <?php if ($current_term_start_date && $current_term_end_date): ?>
                <div class="form-group">
                    <label class="col col-sm-3 control-label form-label"><?= __("Current Term") ?></label>
                    <div class="col col-sm-6">
                        <p class="form-control-static" id="">
                            <?= $this->TimeEx->date($current_term_start_date, $current_term_timezone) ?>
                            - <?= $this->TimeEx->date($current_term_end_date, $current_term_timezone) ?>
                            <?= $this->TimeEx->getTimezoneText($current_term_timezone) ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($next_term_start_date && $next_term_end_date): ?>
                <div class="form-group">
                    <label class="col col-sm-3 control-label form-label"><?= __("Next Term") ?></label>
                    <div class="col col-sm-6">
                        <p class="form-control-static" id="">
                            <?= $this->TimeEx->date($next_term_start_date, $next_term_timezone) ?>
                            - <?= $this->TimeEx->date($next_term_end_date, $next_term_timezone) ?>
                            <?= $this->TimeEx->getTimezoneText($next_term_timezone) ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->App->viewEndComment() ?>
