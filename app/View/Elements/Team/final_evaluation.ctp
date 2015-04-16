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
 */
?>
<!-- START app/View/Elements/Team/final_evaluation.ctp -->
<div class="panel panel-default">
    <div class="panel-heading"><?= __d('gl', "最終評価") ?></div>
    <div class="panel-body">
        <div class="form-group">
            <label for="TeamName" class="col col-sm-3 control-label form-label"></label>

            <div class="col col-sm-6">
                <p class="form-control-static"><?= __d('gl', "このセクションでは最終評価をCSVにて行う事ができます。") ?></p>

                <p class="form-control-static"><?= __d('gl',
                                                       "ファイルをダウンロードし、フォーマットに従って入力したあと、更新済みのCSVファイルをアップロードしてください。") ?></p>

                <p class="form-control-static"><?= __d('gl', "") ?></p>

                <p class="form-control-static"><?= __d('gl', "") ?></p>

                <p class="form-control-static"><?= __d('gl', "") ?></p>

                <p class="form-control-static"><?= __d('gl', "") ?></p>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-sm-9 col-sm-offset-3">
                <a href="#" class="btn btn-default" data-toggle="modal"
                   data-target="#ModalFinalEvaluationByCsv"><?= __d('gl', '最終評価を行う') ?></a>
            </div>
        </div>
    </div>
</div>
<!-- END app/View/Elements/Team/final_evaluation.ctp -->
<? $this->start('modal') ?>
<?= $this->element('modal_final_evaluation_by_csv') ?>
<? $this->end() ?>
