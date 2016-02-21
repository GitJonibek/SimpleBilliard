<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 6/6/14
 * Time: 3:19 PM
 *
 * @var CodeCompletionView $this
 * @var                    $goal_id
 * @var                    $goal
 * @var                    $kr_list
 * @var                    $kr_value_unit_list
 * @var                    $key_result_id
 */
?>
<!-- START app/View/Elements/Goal/modal_add_action.ctp -->
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close font_33px close-design" data-dismiss="modal" aria-hidden="true"><span
                    class="close-icon">&times;</span></button>
            <h4 class="modal-title"><?= __d('app', "アクションを追加する") ?></h4>
            <ul class="add-key-result-goal-info">
                <li>
                    <i class="fa fa-flag"></i> <?= h($goal['Goal']['name']) ?>
                </li>
            </ul>
        </div>
        <?=
        $this->Form->create('ActionResult', [
            'inputDefaults' => [
                'div'       => 'form-group',
                'label'     => [
                    'class' => 'no-asterisk'
                ],
                'wrapInput' => 'goal-set-input',
                'class'     => 'form-control addteam_input-design'
            ],
            'class'         => 'form-horizontal',
            'url'           => ['controller' => 'goals', 'action' => 'add_completed_action'],
            'novalidate'    => true,
            'id'            => 'AddActionResultForm',
            'type'          => 'file',
        ]); ?>
        <?= $this->Form->hidden('goal_id', ['value' => $goal_id]) ?>
        <div class="modal-body modal-circle-body">
            <div class="row">
                <div class="form-group required">
                    <div class="set-goal">
                        <h5 class="modal-key-result-headings"><?= __d('app', '画像') ?>&nbsp;<i
                                class="fa font_brownRed font_14px">*</i>
                            <span class="modal-key-result-headings-description"><?= __d('app',
                                                                                        'アクションの結果が分かる画像を追加しよう') ?></span>
                        </h5>
                    </div>
                    <div class="goal-set-input required">
                        <div class="row form-group m_0px" id="CommonActionFormImage">
                            <ul class="col input-images post-images">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <li id="WrapPhotoForm_Action_<?= $i ?>">
                                        <?= $this->element('Feed/photo_upload',
                                                           ['type' => 'action_result', 'index' => $i, 'submit_id' => 'AddActionSubmitModal', 'id_prefix' => 'Modal']) ?>
                                    </li>
                                <?php endfor ?>
                            </ul>
                            <span class="help-block" id="ModalActionResult__Photo_ValidateMessage"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <?=
                $this->Form->input('name',
                                   ['before'                   => '<div class="set-goal">' .
                                       '<h5 class="modal-key-result-headings">' .
                                       __d('app', "説明") .
                                       '&nbsp;<i class="fa font_brownRed font_14px">*</i><span class="modal-key-result-headings-description">' .
                                       __d('app', "やった事を書こう") . '</span></h5></div>',
                                    'label'                    => false,
                                    'placeholder'              => __d('app', "例）Webサイトを完成させた"),
                                    "data-bv-notempty-message" => __d('validate', "入力必須項目です。"),
                                    'rows'                     => 1,
                                   ]) ?>
            </div>
            <div class="row">
                <div class="form-group">
                    <h5 class="modal-key-result-headings"><?= __d('app', '達成要素') ?>
                        <span class="modal-key-result-headings-description"><?= __d('app', '紐付ける成果を選択しよう。') ?></span>
                    </h5>

                    <div class="goal-set-input">
                        <?php if ($key_result_id): ?>
                            <?= $this->Form->hidden('key_result_id', ['value' => $key_result_id]) ?>
                            <p class="form-control-static"><?= $kr_list[$key_result_id] ?></p>
                        <?php else: ?>
                            <?=
                            $this->Form->input('key_result_id', [
                                'label'    => false,
                                'type'     => 'select',
                                'required' => false,
                                'div'      => false,
                                'style'    => 'width:170px',
                                'options'  => $kr_list,
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <div class="set-goal">
                        <h5 class="modal-key-result-headings"><?= __d('app', '通知先') ?>
                            <span class="modal-key-result-headings-description">
                                <?= __d('app', '通知したいサークル、ユーザを選択') ?></span>
                        </h5>
                    </div>
                    <div class="goal-set-input">
                        <div class="bbb" id="">
                            <?=
                            $this->Form->hidden('ActionResult.share',
                                                ['id' => 'select2ActionCircleMember', 'value' => "", 'style' => "width: 100%",]) ?>
                            <?php $this->Form->unlockField('ActionResult.share') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?=
            $this->Form->submit(__d('app', "アクションを登録"),
                                ['id' => 'AddActionSubmitModal', 'class' => 'btn btn-primary', 'div' => false, 'disabled' => 'disabled']) ?>

            <button type="button" class="btn btn-default" data-dismiss="modal"><?= __d('app', "閉じる") ?></button>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>
<!-- END app/View/Elements/Goal/modal_add_action.ctp -->
