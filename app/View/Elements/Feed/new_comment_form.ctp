<?php
/**
 * Created by PhpStorm.
 * User: daikihirakata
 * Date: 2014/07/19
 * Time: 22:11
 *
 * @var                    $post_id
 * @var CodeCompletionView $this
 */
?>
<!-- START app/View/Elements/Feed/new_comment_form.ctp -->
<?=
$this->Form->create('Comment', [
    'url'           => ['controller' => 'posts', 'action' => 'comment_add'],
    'inputDefaults' => [
        'div'       => 'form-group mlr_-1px',
        'label'     => false,
        'wrapInput' => '',
        'class'     => 'form-control'
    ],
    'class'         => '',
    'type'          => 'file',
    'novalidate'    => true,
]); ?>
<?=
$this->Form->input('body', [
    'id'                       => "CommentFormBody_{$post_id}",
    'label'                    => false,
    'type'                     => 'textarea',
    'wrap'                     => 'soft',
    'rows'                     => 1,
    'required'                 => true,
    'placeholder'              => __d('gl', "コメントする"),
    'class'                    => 'form-control tiny-form-text blank-disable font_12px comment-post-form box-align',
    'target-id'                => "CommentSubmit_{$post_id}",
    'required'                 => 'false'
])
?>
<div class="form-group" id="CommentFormImage_<?= $post_id ?>"
     style="display: none">
    <ul class="input-images">
        <? for ($i = 1; $i <= 5; $i++): ?>
            <li>
                <?=
                $this->element('Feed/photo_upload',
                               ['type' => 'comment', 'index' => $i, 'submit_id' => "CommentSubmit_{$post_id}", 'post_id' => $post_id]) ?>
            </li>
        <? endfor ?>
    </ul>
    <span class="help-block" id="Comment__Post_<?= $post_id ?>_Photo_ValidateMessage"></span>
</div>
<?= $this->Form->hidden('post_id', ['value' => $post_id]) ?>
<div class="comment-btn" id="Comment_<?= $post_id ?>">
    <a href="#" class="target-show-target-click font_12px comment-add-pic"
       target-id="CommentFormImage_<?= $post_id ?>"
       click-target-id="Comment__Post_<?= $post_id ?>_Photo_1">
        <button type="button" class="btn pull-left photo-up-btn">
            <i class="fa fa-camera post-camera-icon"></i>
        </button>

    </a>
    <?=
    $this->Form->submit(__d('gl', "コメントする"),
                        ['class' => 'btn btn-primary pull-right submit-btn', 'id' => "CommentSubmit_{$post_id}", 'disabled' => 'disabled']) ?>
    <div class="clearfix"></div>
</div>
<?= $this->Form->end() ?>
<!-- END app/View/Elements/Feed/new_comment_form.ctp -->

