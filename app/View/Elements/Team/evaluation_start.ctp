<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 6/30/14
 * Time: 9:59 AM
 *
 * @var CodeCompletionView $this
 * @var array              $my_teams
 * @var                    $term_start_date
 * @var                    $term_end_date
 * @var                    $eval_enabled
 * @var                    $eval_start_button_enabled
 * @var                    $current_eval_is_available
 * @var                    $current_term_start_date
 * @var                    $current_term_end_date
 * @var                    $current_eval_is_frozen
 * @var                    $current_term_id
 * @var                    $previous_eval_is_available
 * @var                    $previous_term_start_date
 * @var                    $previous_term_end_date
 * @var                    $previous_eval_is_frozen
 * @var                    $previous_term_id
 */
?>
<!-- START app/View/Elements/Team/evaluation_start.ctp -->
<div class="panel panel-default">
    <div class="panel-heading"><?= __("Begin evaluation") ?></div>
    <div class="panel-body form-horizontal">
        <div class="form-group">
            <label for="TeamName" class="col col-sm-3 control-label form-label"></label>

            <div class="col col-sm-6">
                <p class="form-control-static">
                    <?= __("You can start evaluation with the current settings.") ?>
                </p>

                <p class="form-control-static">
                    <?= __("Notice - These settings can't be canceled.") ?>
                </p>
            </div>
        </div>
        <div class="form-group">
            <label for="TeamName" class="col col-sm-3 control-label form-label"><?= __("Current term") ?></label>

            <div class="col col-sm-6">
                <p class="form-control-static"><b><?= $this->TimeEx->date($current_term_start_date) ?>
                        - <?= $this->TimeEx->date($current_term_end_date) ?></b></p>
            </div>
        </div>
        <?php if (!$eval_enabled): ?>
            <div class="alert alert-danger" role="alert">
                <?= __("You need to active Evaluation settings before starting Evaluation.") ?>
            </div>
        <?php elseif (!$eval_start_button_enabled): ?>
            <div class="alert alert-info" role="alert">
                <?= __("In evaluation term") ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($eval_enabled && $eval_start_button_enabled): ?>
        <div class="panel-footer">
            <div class="row">
                <div class="col-sm-9 col-sm-offset-3">
                    <?=
                    $this->Form->postLink(__("Start current term evaluations"),
                        ['controller' => 'teams', 'action' => 'start_evaluation',],
                        ['class' => 'btn btn-primary'],
                        __("Unable to cancel. Do you really want to start evaluations?")) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<!-- END app/View/Elements/Team/evaluation_start.ctp -->
