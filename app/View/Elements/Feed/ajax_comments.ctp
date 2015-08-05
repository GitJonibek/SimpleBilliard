<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 7/17/14
 * Time: 4:42 PM
 *
 * @var CodeCompletionView $this
 * @var                    $comments
 */
?>
<?php foreach ($comments as $comment): ?>
    <?=
    $this->element('Feed/comment',
                   ['comment' => $comment['Comment'], 'comment_file' => $comment['CommentFile'], 'user' => $comment['User'], 'like' => $comment['MyCommentLike'], 'long_text' => $long_text]) ?>
<?php endforeach ?>
