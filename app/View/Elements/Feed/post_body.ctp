<?php
/**
 * Created by PhpStorm.
 * User: daikihirakata
 * Date: 5/19/15
 * Time: 02:04
 *
 * @var                    $post
 * @var CodeCompletionView $this
 */
?>
<!-- START app/View/Elements/Feed/post_body.ctp -->
<div
    class="col col-xxs-12 feed-contents post-contents <?= h($long_text) ? "showmore-circle" : "showmore" ?> font_14px font_verydark box-align"
    id="PostTextBody_<?= $post['Post']['id'] ?>">
    <?php if (($post['Post']['type'] == Post::TYPE_NORMAL) || ($post['Post']['type'] == Post::TYPE_MESSAGE)): ?>
        <?= nl2br($this->TextEx->autoLink($post['Post']['body'])) ?>
    <?php elseif ($post['Post']['type'] == Post::TYPE_ACTION): ?>
        <i class="fa fa-check-circle disp_i"></i>&nbsp;<?= nl2br($this->TextEx->autoLink($post['ActionResult']['name'])) ?>
    <?php elseif ($post['Post']['type'] == Post::TYPE_KR_COMPLETE): ?>
        <i class="fa fa-key disp_i"></i>&nbsp;<?= __("%s を達成しました！",
                                                      h($post['KeyResult']['name'])) ?>
    <?php elseif ($post['Post']['type'] == Post::TYPE_GOAL_COMPLETE): ?>
        <i class="fa fa-flag disp_i"></i>&nbsp;<?= __("%s を達成しました！", h($post['Goal']['name'])) ?>
    <?php else: ?>
        <?= Post::$TYPE_MESSAGE[$post['Post']['type']] ?>
    <?php endif; ?>
</div>
<!-- END app/View/Elements/Feed/post_body.ctp -->