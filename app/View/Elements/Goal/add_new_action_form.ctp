<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 1/20/15
 * Time: 6:34 PM
 *
 * @var                    $goal_id
 * @var                    $ar_count
 * @var CodeCompletionView $this
 */
?>
<!-- START app/View/Elements/Goal/add_new_action_form.ctp -->
<?= $this->Form->create('ActionResult', [
    'inputDefaults' => [
        'div'       => 'form-group mb_5px develop--font_normal',
        'wrapInput' => false,
        'class'     => 'form-control',
    ],
    'url'           => ['controller' => 'goals', 'action' => 'add_completed_action', 'goal_id' => $goal_id],
    'type'          => 'file',
    'class'         => 'form-feed-notify'
]); ?>
<?=
$this->Form->input('ActionResult.name', [
                                          'label'          => false,
                                          'rows'           => 1,
                                          'placeholder'    => __("今日やったアクションを共有しよう！"),
                                          'class'          => 'form-control tiny-form-text blank-disable-and-undisable col-xxs-10 goalsCard-actionInput mb_12px add-select-options change-warning',
                                          'id'             => "ActionFormName_" . $goal_id,
                                          'target-id'      => "ActionFormSubmit_" . $goal_id,
                                          'select-id'      => "ActionKeyResultId_" . $goal_id,
                                          'add-select-url' => $this->Html->url(['controller' => 'goals', 'action' => 'ajax_get_kr_list', 'goal_id' => $goal_id])
                                      ]
)
?>
<div class="goalsCard-activity inline-block col-xxs-2">
    <?php if ($ar_count > 0): ?>
        <a class="click-show-post-modal font_gray-brownRed pointer"
           id="ActionListOpen_<?= $goal_id ?>"
           href="<?= $this->Html->url(['controller' => 'posts', 'action' => 'ajax_get_goal_action_feed', 'goal_id' => $goal_id, 'type' => Post::TYPE_ACTION]) ?>">
            <i class="fa fa-check-circle mr_1px"></i><span
                class="ls_number"><?= $ar_count ?></span>
        </a>
    <?php else: ?>
        <i class="fa fa-check-circle mr_1px"></i><span
            class="ls_number">0</span>
    <?php endif; ?>
</div>
<div id="ActionFormDetail_<?= $goal_id ?>">
    <div class="form-group">
        <label class="font_normal col-xxs-4 lh_40px" for="ActionPhotos">
            <i class="fa fa-camera mr_2px"></i><?= __("画像") ?>
        </label>

        <div class="col-xxs-8">
            <ul class="col input-images post-images">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <li>
                        <?= $this->element('Feed/photo_upload_mini',
                                           ['type' => 'action_result', 'index' => $i, 'submit_id' => 'PostSubmit', 'has_many' => false, 'id_prefix' => 'Goal_' . $goal_id . '_']) ?>
                    </li>
                <?php endfor ?>
            </ul>
            <span class="help-block" id="Goal_<?= $goal_id ?>_ActionResult__Photo_ValidateMessage">
        </div>
    </div>
    <label class="font_normal col-xxs-4 lh_40px" for="KeyResults_<?= $goal_id ?>">
        <i class="fa fa-key mr_2px"></i><?= __("成果") ?>
    </label>
    <?=
    $this->Form->input('ActionResult.key_result_id', [
                                                       'label'   => false, //__("紐付ける達成要素を選択(オプション)"),
                                                       'options' => [null => __("選択なし")],
                                                       'class'   => 'form-control col-xxs-8 selectKrForAction',
                                                       'id'      => 'ActionKeyResultId_' . $goal_id,
                                                   ]
    )
    ?>
    <?php $this->Form->unlockField('socket_id') ?>
    <div class="form-group col-xxs-12 mt_12px">
        <a href="#" target-id="ActionFormName_<?= $goal_id ?>"
           class="btn btn-white tiny-form-text-close font_verydark"><?= __(
                                                                            "キャンセル") ?></a>
        <?= $this->Form->submit(__("アクション登録"), [
            'div'      => false,
            'id'       => "ActionFormSubmit_" . $goal_id,
            'class'    => 'btn btn-info pull-right',
            'disabled' => 'disabled',
        ]); ?>
    </div>
</div>
<?= $this->Form->end() ?>
<!-- END app/View/Elements/Goal/add_new_action_form.ctp -->
