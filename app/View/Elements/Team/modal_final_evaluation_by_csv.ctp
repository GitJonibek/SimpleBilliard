<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 6/6/14
 * Time: 3:19 PM
 *
 * @var CodeCompletionView $this
 * @var                    $my_member_status
 * @var                    $evaluate_term_id
 * @var                    $start
 * @var                    $end
 */
?>
<!-- START app/View/Elements/modal_final_evaluation_by_csv.ctp -->
<div class="modal fade" tabindex="-1" id="ModalFinalEvaluation_<?= $evaluate_term_id ?>_ByCsv">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close font_33px close-design" data-dismiss="modal" aria-hidden="true">
                    <span class="close-icon">&times;</span></button>
                <h4 class="modal-title"><?= __d('app', "最終評価を行う (期間：%s - %s)",
                                                $this->TimeEx->date($start),
                                                $this->TimeEx->date($end)) ?></h4>
            </div>
            <div class="modal-body">
                <?=
                $this->Form->create('Team', [
                    'url'           => ['controller' => 'teams', 'action' => 'ajax_upload_final_evaluations_csv', 'evaluate_term_id' => $evaluate_term_id],
                    'inputDefaults' => [
                        'div'       => 'form-group',
                        'label'     => [
                            'class' => ''
                        ],
                        'wrapInput' => '',
                        'class'     => 'form-control'
                    ],
                    'novalidate'    => true,
                    'type'          => 'file',
                    'id'            => "FinalEvaluation_{$evaluate_term_id}_Form",
                    'loader-id'     => "FinalEvaluation_{$evaluate_term_id}_Loader",
                    'result-msg-id' => "FinalEvaluation_{$evaluate_term_id}_ResultMsg",
                    'submit-id'     => "FinalEvaluation_{$evaluate_term_id}_Submit",
                    'class'         => 'ajax-csv-upload',
                ]); ?>
                <div class="form-group">
                    <label class=""><?= __d('app', "1.評価データをダウンロード") ?></label>

                    <p><?= __d('app', "CSVフォーマットの評価データをダウンロードしてください。テンプレートのヘッダーは変更しないでください。") ?></p>

                    <div class="">
                        <?=
                        $this->Html->link(__d('app', "評価データをダウンロード"),
                                          ['action' => 'download_final_evaluations_csv', 'evaluate_term_id' => $evaluate_term_id],
                                          ['class' => 'btn btn-default', 'div' => false])
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class=""><?= __d('app', '2.ファイルのアップロード') ?></label>

                    <p><?= __d('app', "変更した評価データのファイルをアップロードしてください。") ?></p>

                    <div class="">
                        <div class="fileinput fileinput-new fileinput-enabled-submit" data-provides="fileinput"
                             submit-id="FinalEvaluation_<?= $evaluate_term_id ?>_Submit">
                            <span class="btn btn-default btn-file">
                                <span class="fileinput-new"><?= __d('app', "ファイルを選択") ?></span>
                                <span class="fileinput-exists"><?= __d('app', "別のファイルに変更する") ?></span>
                                <?=
                                $this->Form->input('csv_file',
                                                   ['type'         => 'file',
                                                    'label'        => false,
                                                    'div'          => false,
                                                    'css'          => false,
                                                    'wrapInput'    => false,
                                                    'errorMessage' => false,
                                                    'accept'       => ".csv",
                                                   ]) ?>
                            </span>
                            <span class="fileinput-filename"></span>
                            <a href="#" class="close fileinput-exists" data-dismiss="fileinput"
                               style="float: none">&times;</a>
                        </div>
                    </div>
                </div>
                <div id="FinalEvaluation_<?= $evaluate_term_id ?>_ResultMsg" class="none">
                    <div class="alert" role="alert">
                        <h4 class="alert-heading"></h4>
                        <span class="alert-msg"></span>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-sm-9 col-sm-offset-3">
                        <div id="FinalEvaluation_<?= $evaluate_term_id ?>_Loader" class="pull-right none">
                            &nbsp;<i class="fa fa-refresh fa-spin"></i>
                        </div>
                        <button type="button" class="btn btn-link design-cancel bd-radius_4px"
                                data-dismiss="modal"><?= __d('app',
                                                             "キャンセル") ?></button>
                        <?=
                        $this->Form->submit(__d('app', "更新する"),
                                            ['class' => 'btn btn-primary', 'div' => false, 'disabled' => 'disabled', 'id' => "FinalEvaluation_{$evaluate_term_id}_Submit"]) ?>

                    </div>
                </div>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
<!-- END app/View/Elements/modal_final_evaluation_by_csv.ctp -->
