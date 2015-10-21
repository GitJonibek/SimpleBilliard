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
 * @var                    $kr_id
 * @var                    $goal_category_list
 * @var                    $priority_list
 * @var                    $kr_priority_list
 * @var                    $kr_value_unit_list
 * @var                    $kr_start_date_format
 * @var                    $kr_end_date_format
 * @var                    $limit_end_date
 * @var                    $limit_start_date
 * @var                    $goal_term
 */
?>
<!-- START app/View/Elements/Goal/modal_edit_key_result.ctp -->
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close font_33px close-design" data-dismiss="modal" aria-hidden="true"><span
                    class="close-icon">&times;</span></button>
            <h4 class="modal-title"><?= __d('gl', "成果を更新") ?></h4>
            <ul class="add-key-result-goal-info">
                <li>
                    <i class="fa fa-flag"></i><?= h($goal['Goal']['name']) ?>
                </li>
                <li>
                    <i class="fa fa-bullseye"></i>
                    <?= h($goal['Goal']['target_value']) ?>
                    (← <?= h($goal['Goal']['start_value']) ?>)<?= $kr_value_unit_list[$goal['Goal']['value_unit']] ?>
                </li>
                <li>
                    <i class="fa fa-calendar"></i>
                    <?= date('Y/m/d', $goal['Goal']['end_date'] + $goal_term['timezone'] * HOUR) ?>
                    (← <?= date('Y/m/d', $goal['Goal']['start_date'] + $goal_term['timezone'] * HOUR) ?> - )
                    <?php if ($this->Session->read('Auth.User.timezone') != $goal_term['timezone']): ?>
                        <?= $this->TimeEx->getTimezoneText($goal_term['timezone']); ?>
                    <?php endif ?>
                </li>
            </ul>
        </div>
        <?=
        $this->Form->create('KeyResult', [
            'inputDefaults' => [
                'div'       => 'form-group',
                'label'     => [
                    'class' => 'control-label no-asterisk text-align_left'
                ],
                'wrapInput' => 'goal-set-input',
                'class'     => 'form-control addteam_input-design'
            ],
            'class'         => 'foooo',
            'url'           => ['controller' => 'goals', 'action' => 'edit_key_result', 'key_result_id' => $kr_id],
            'novalidate'    => true,
            'id'            => 'AddGoalFormKeyResult',
        ]); ?>
        <?= $this->Form->hidden('KeyResult.id', ['value' => $kr_id]) ?>
        <?= $this->Form->hidden('KeyResult.goal_id', ['value' => $this->request->data['KeyResult']['goal_id']]) ?>
        <div class="modal-body modal-circle-body">
            <div class="aaa">
                <div class="row">
                                    <?=
                                      $this->Form->input('KeyResult.name',
                                       ['before'                   => '<div class="set-goal">' .
                                           '<h5 class="modal-key-result-headings">' . __d('gl', "成果名") .'<span class="modal-key-result-headings-description">' . __d('gl',
                                                                                 "達成の指標として『なに』をどうするか？") .  '</span></label></div>' ,
                                        'label'                    => false,
                                        'placeholder'              => __d('gl', "具体的に絞り込んで書く"),
                                        "data-bv-notempty-message" => __d('validate', "入力必須項目です。"),
                                        'rows'                     => 1,
                                        'afterInput'               => '<span class="help-block font_12px">' . __d('gl',
                                                                                                                  "例）サービスAの国内市場シェアを増加させる") . '</span>'
                                       ]) ?>
                </div>
                <div class="row">
                    <?php //TODO 一旦、使わないのでコメントアウト
                    //                echo $this->Form->input('KeyResult.current_value',
                    //                                        [
                    //                                            'before'                       => '<div class="control-label set-importance">' .
                    //                                                '<label>' . __d('gl', "現在値") . '</label>' .
                    //                                                '<div class="label-addiction">' . '</div></div>',
                    //                                            'label'                        => false,
                    //                                            'wrapInput'                    => 'goal-set-input',
                    //                                            'type'                         => 'number',
                    //                                            'step'                         => '0.1',
                    //                                            'default'                      => 100,
                    //                                            'required'                     => true,
                    //                                            'maxlength'                    => 14,
                    //                                            'data-bv-stringlength-message' => __d('validate', "文字数がオーバーしています。"),
                    //                                            "data-bv-notempty-message"     => __d('validate', "入力必須項目です。"),
                    //                                            'data-bv-numeric-message'      => __d('validate', "数字を入力してください。"),
                    //                                        ])
                    ?>
                </div>
                <div class="row">
                    <div class="ddd">
                        <h5 class="modal-key-result-headings"><?= __d('gl', "程度") ?><span class="modal-key-result-headings-description"><?= __d('gl', "どのくらい？") ?></span></h5>
                    </div>
                    <div class="goal-set-input">
                        <div class="eee">
                            <?=
                            $this->Form->input('KeyResult.value_unit',
                                               ['label'               => __d('gl', "単位"),
                                                'wrapInput'           => 'fff',
                                                'type'                => 'select',
                                                'class'               => 'change-select-target-hidden form-control addteam_input-design',
                                                'target-id'           => 'KeyResult0ValueInputWrap_' . $kr_id,
                                                'required'            => true,
                                                'hidden-option-value' => KeyResult::UNIT_BINARY,
                                                'options'             => $kr_value_unit_list
                                               ]) ?>
                        </div>
                        <div id="KeyResult0ValueInputWrap_<?= $kr_id ?>"
                             style="<?= $this->request->data['KeyResult']['value_unit'] == KeyResult::UNIT_BINARY ? "display:none;" : null ?>">
                            <div class="ggg">
                                <?=
                                $this->Form->input('KeyResult.target_value',
                                                   ['label'                        => __d('gl', "達成時"),
                                                    'wrapInput'                    => 'hhh',
                                                    'type'                         => 'number',
                                                    'step'                         => '0.1',
                                                    'default'                      => 100,
                                                    'required'                     => true,
                                                    'maxlength'                    => 14,
                                                    'data-bv-stringlength-message' => __d('validate', "文字数がオーバーしています。"),
                                                    "data-bv-notempty-message"     => __d('validate', "入力必須項目です。"),
                                                    'data-bv-numeric-message'      => __d('validate', "数字を入力してください。"),
                                                   ]) ?>
                            </div>
                            <div class="iii">
                                <?=
                                $this->Form->input('KeyResult.start_value',
                                                   ['label'                        => __d('gl', "開始時"),
                                                    'wrapInput'                    => 'jjj',
                                                    'type'                         => 'number',
                                                    'step'                         => '0.1',
                                                    'default'                      => 0,
                                                    'required'                     => true,
                                                    'maxlength'                    => 14,
                                                    'data-bv-stringlength-message' => __d('validate', "文字���がオーバーしています。"),
                                                    "data-bv-notempty-message"     => __d('validate', "入力必須項目です。"),
                                                    'data-bv-numeric-message'      => __d('validate', "数字を入力してください。"),
                                                   ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <h5 class="modal-key-result-headings"><?= __d('gl', "期間") ?>
                        <?php if ($this->Session->read('Auth.User.timezone') != $goal_term['timezone']): ?>
                            <span class="modal-key-result-headings-description">
                            <?= $this->TimeEx->getTimezoneText($goal_term['timezone']); ?>
                            </span>
                        <?php endif ?>
                    </h5>

                    <div class="goal-set-input">
                        <div class="form-group" id="KeyResult0EndDateContainer">
                            <label for="KeyResult0EndDate" class="control-label goal-set-mid-label"><?=
                                __d('gl',
                                    "期限") ?></label>

                            <div class="input-group date goal-set-date"
                                 data-date-end-date="<?= $limit_end_date ?>"
                                 data-date-start-date="<?= $limit_start_date ?>">
                                <?=
                                $this->Form->input('KeyResult.end_date',
                                                   [
                                                       'value'                    => $kr_end_date_format,
                                                       'default'                  => $kr_end_date_format,
                                                       'label'                    => false,
                                                       'div'                      => false,
                                                       'class'                    => "form-control",
                                                       'required'                 => true,
                                                       "data-bv-notempty-message" => __d('validate', "入力必須項目です。"),
                                                       'type'                     => 'text',
                                                       'wrapInput'                => null,
                                                   ]) ?>
                                <span class="input-group-addon bd-r-radius_4px"><i class="fa fa-th"></i></span>
                            </div>
                        </div>
                        <div class="form-group" id="KeyResult0StartDateContainer">
                            <label for="KeyResult0StartDate" class="control-label goal-set-mid-label"><?=
                                __d('gl', "開始") ?></label>

                            <p class="form-control-static"
                               id="KeyResult0StartDateDefault_<?= $kr_id ?>">
                                    <span
                                        class="pull-left"><?= $kr_start_date_format ?>
                                        &nbsp;&nbsp;<a href="#" class="target-show-target-del"
                                                       show-target-id="KeyResult0StartDateInputWrap_<?= $kr_id ?>"
                                                       delete-target-id="KeyResult0StartDateDefault_<?= $kr_id ?>">
                                            <?= __d('gl', "変更") ?></a>
                                    </span>
                            </p>

                            <div class="input-group date plr_5px goal-set-date none"
                                 data-date-end-date="<?= $limit_end_date ?>"
                                 data-date-start-date="<?= $limit_start_date ?>"
                                 id="KeyResult0StartDateInputWrap_<?= $kr_id ?>">
                                <?=
                                $this->Form->input('KeyResult.start_date',
                                                   [
                                                       'value'                    => $kr_start_date_format,
                                                       'label'                    => false,
                                                       'div'                      => false,
                                                       'class'                    => "form-control",
                                                       'required'                 => true,
                                                       "data-bv-notempty-message" => __d('validate', "入力必須項目です。"),
                                                       'type'                     => 'text',
                                                       'wrapInput'                => null
                                                   ]) ?>
                                <span class="input-group-addon bd-r-radius_4px"><i class="fa fa-th"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <?=
                $this->Form->input('priority', [
                    'before'                   =>'<h5 class="modal-key-result-headings">' . __d('gl', "重要度") .'<span class="modal-key-result-headings-description">' . __d('gl', "ゴールにとってこの成果の重要度") . '</span></h5>' ,
                    'label'                    => false,
                    'type'                     => 'select',
                    'default'                  => 1,
                    'required'                 => true,
                    "data-bv-notempty-message" => __d('validate', "入力必須項目です。"),
                    'style'                    => 'width:170px',
                    'options'                  => $kr_priority_list,
                ]) ?>
            </div>
        </div>
        <div class="modal-footer">
            <?=
            $this->Form->submit(__d('gl', "成果を更新"),
                                ['class' => 'btn btn-primary', 'div' => false]) ?>

            <button type="button" class="btn btn-default" data-dismiss="modal"><?= __d('gl', "閉じる") ?></button>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>
<!-- END app/View/Elements/Goal/modal_edit_key_result.ctp -->
