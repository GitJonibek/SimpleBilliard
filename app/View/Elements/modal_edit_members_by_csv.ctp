<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 6/6/14
 * Time: 3:19 PM
 *
 * @var CodeCompletionView $this
 * @var                    $my_member_status
 */
?>
<!-- START app/View/Elements/modal_edit_members_by_csv.ctp -->
<div class="modal fade" tabindex="-1" id="ModalEditMembersByCsv">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close font_33px close-design" data-dismiss="modal" aria-hidden="true">
                    <span class="close-icon">&times;</span></button>
                <h4 class="modal-title"><?= __("メンバーの情報を変更") ?></h4>
            </div>
            <div class="modal-body">
                <?=
                $this->Form->create('Team', [
                    'url'           => ['controller' => 'teams', 'action' => 'ajax_upload_update_members_csv'],
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
                    'id'            => 'EditMembersForm',
                    'loader-id'     => 'EditMembersLoader',
                    'result-msg-id' => 'EditMembersResultMsg',
                    'submit-id'     => 'EditMembersSubmit',
                    'class'         => 'ajax-csv-upload',
                ]); ?>
                <div class="form-group">
                    <label class=""><?= __("1.ユーザ情報をダウンロード") ?></label>

                    <p><?= __("CSVフォーマットのユーザ情報をダウンロードしてください。テンプレートのヘッダーは変更しないでください。") ?></p>

                    <div class="">
                        <?=
                        $this->Html->link(__("ユーザ情報をダウンロード"), ['action' => 'download_team_members_csv'],
                                          ['class' => 'btn btn-default', 'div' => false])
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class=""><?= __('2.ファイルのアップロード') ?></label>

                    <p><?= __("変更したユーザ情報のファイルをアップロードしてください。") ?></p>

                    <div class="">
                        <div class="fileinput fileinput-new fileinput-enabled-submit" data-provides="fileinput"
                             submit-id="EditMembersSubmit">
                            <span class="btn btn-default btn-file">
                                <span class="fileinput-new"><?= __("ファイルを選択") ?></span>
                                <span class="fileinput-exists"><?= __("別のファイルに変更する") ?></span>
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
                <div id="EditMembersResultMsg" class="none">
                    <div class="alert" role="alert">
                        <h4 class="alert-heading"></h4>
                        <span class="alert-msg"></span>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-sm-9 col-sm-offset-3">
                        <div id="EditMembersLoader" class="pull-right none">
                            &nbsp;<i class="fa fa-refresh fa-spin"></i>
                        </div>
                        <button type="button" class="btn btn-link design-cancel bd-radius_4px"
                                data-dismiss="modal"><?= __(
                                                             "キャンセル") ?></button>
                        <?=
                        $this->Form->submit(__("変更する"),
                                            ['class' => 'btn btn-primary', 'div' => false, 'disabled' => 'disabled', 'id' => 'EditMembersSubmit']) ?>

                    </div>
                </div>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
<!-- END app/View/Elements/modal_edit_members_by_csv.ctp -->
