<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 7/17/14
 * Time: 4:23 PM
 *
 * @var                    $comment
 * @var                    $my_member_status
 * @var                    $user
 * @var                    $like
 * @var                    $id_prefix
 * @var CodeCompletionView $this
 * @var                    $long_text
 * @var                    $comment_file
 */
?>
<?php if (!isset($id_prefix)) {
    $id_prefix = null;
}
?>
<!-- START app/View/Elements/Feed/comment.ctp -->
<div class="font_12px comment-box" comment-id="<?= $comment['id'] ?>">
    <div class="col col-xxs-12 pt_4px">
        <a href="<?= $this->Html->url(['controller' => 'users', 'action' => 'view_goals', 'user_id' => $user['id']]) ?>">
            <?=
            $this->Html->image('ajax-loader.gif',
                               [
                                   'class'         => 'lazy comment-img',
                                   'data-original' => $this->Upload->uploadUrl($user, 'User.photo',
                                                                               ['style' => 'small']),
                               ]
            )
            ?>
        </a>

        <div class="comment-body">
            <div class="col col-xxs-12 comment-text comment-user">
                <?php if ($user['id'] === $this->Session->read('Auth.User.id')): ?>
                    <div class="dropdown pull-right">
                        <a href="#" class="font_lightGray-gray font_11px" data-toggle="dropdown" id="download">
                            <i class="fa fa-chevron-down comment-arrow"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="download">
                            <li><a href="#" class="target-toggle-click"
                                   target-id="<?= $id_prefix ?>CommentEditForm_<?= $comment['id'] ?>"
                                   opend-text="<?= __d('gl', "編集をやめる") ?>"
                                   closed-text="<?= __d('gl', "コメントを編集") ?>"
                                   ajax-url="<?= $this->Html->url(['controller' => 'posts', 'action' => 'ajax_get_edit_comment_form', 'comment_id' => $comment['id'], $id_prefix]) ?>"
                                   click-target-id="<?= $id_prefix ?>CommentEditFormBody_<?= $comment['id'] ?>"
                                   hidden-target-id="<?= $id_prefix ?>CommentTextBody_<?= $comment['id'] ?>"

                                    ><?= __d('gl', "コメントを編集") ?></a></li>
                            <li><?=
                                $this->Form->postLink(__d('gl', "コメントを削除"),
                                                      ['controller' => 'posts', 'action' => 'comment_delete', 'comment_id' => $comment['id']],
                                                      null, __d('gl', "本当にこのコメントを削除しますか？")) ?></li>
                        </ul>
                    </div>
                <?php elseif ($my_member_status['TeamMember']['admin_flg']): ?>
                    <div class="pull-right develop--link-gray">
                        <?=
                        $this->Form->postLink('<i class="fa fa-times comment-cross"></i>',
                                              ['controller' => 'posts', 'action' => 'comment_delete', 'comment_id' => $comment['id']],
                                              ['escape' => false], __d('gl', "本当にこのコメントを削除しますか？")) ?>
                    </div>
                <?php endif; ?>
                <div class="mb_2px lh_12px">
                    <a class="font_bold font_verydark"
                       href="<?= $this->Html->url(['controller' => 'users', 'action' => 'view_goals', 'user_id' => $user['id']]) ?>">
                        <?= h($user['display_username']) ?>
                    </a>
                </div>
            </div>
            <div
                class="col col-xxs-12 <?= $long_text ? "showmore-comment-circle" : "showmore-comment" ?> comment-text feed-contents comment-contents font_verydark box-align"
                id="<?= $id_prefix ?>CommentTextBody_<?= $comment['id'] ?>"><?= $this->TextEx->autoLink($comment['body']) ?></div>

            <?php $photo_count = 0;
            for ($i = 1; $i <= 5; $i++) {
                if ($comment["photo{$i}_file_name"]) {
                    $photo_count++;
                }
            }
            ?>
            <?php if ($photo_count): ?>
                <div class="col col-xxs-12 comment-photo">

                    <div id="<?= $id_prefix ?>CarouselComment_<?= $comment['id'] ?>" class="carousel slide"
                         data-ride="carousel">
                        <!-- Indicators -->
                        <?php if ($photo_count >= 2): ?>
                            <ol class="carousel-indicators">
                                <?php $index = 0 ?>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($comment["photo{$i}_file_name"]): ?>
                                        <li data-target="#<?= $id_prefix ?>CarouselComment_<?= $comment['id'] ?>"
                                            data-slide-to="<?= $index ?>"
                                            class="<?= ($index === 0) ? "active" : null ?>"></li>
                                        <?php $index++ ?>
                                    <?php endif ?>
                                <?php endfor ?>
                            </ol>
                        <?php endif; ?>
                        <!-- Wrapper for slides -->
                        <div class="carousel-inner">
                            <?php $index = 0 ?>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($comment["photo{$i}_file_name"]): ?>
                                    <div class="item <?= ($index === 0) ? "active" : null ?>">
                                        <a href="<?=
                                        $this->Upload->uploadUrl($comment, "Comment.photo" . $i,
                                                                 ['style' => 'large']) ?>" rel="lightbox"
                                           data-lightbox="<?= $id_prefix ?>LightBoxComment_<?= $comment['id'] ?>">
                                            <?=
                                            $this->Html->image('ajax-loader.gif',
                                                               [
                                                                   'class'         => 'lazy bd-s',
                                                                   //'style'         => 'width: 50px; height: 50px;',
                                                                   'data-original' => $this->Upload->uploadUrl($comment,
                                                                                                               "Comment.photo" . $i,
                                                                                                               ['style' => 'small'])
                                                               ]
                                            )
                                            ?>
                                        </a>
                                        <?php $index++ ?>
                                    </div>
                                <?php endif ?>
                            <?php endfor ?>
                        </div>

                        <!-- Controls -->
                        <?php if ($photo_count >= 2): ?>
                            <a class="left carousel-control"
                               href="#<?= $id_prefix ?>CarouselComment_<?= $comment['id'] ?>"
                               data-slide="prev">
                                <span class="glyphicon glyphicon-chevron-left"></span>
                            </a>
                            <a class="right carousel-control"
                               href="#<?= $id_prefix ?>CarouselComment_<?= $comment['id'] ?>"
                               data-slide="next">
                                <span class="glyphicon glyphicon-chevron-right"></span>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endif; ?>

            <?php if ($comment['site_info']): ?>
                <?php $site_info = json_decode($comment['site_info'], true) ?>
                <div class="col col-xxs-12">
                    <a href="<?= isset($site_info['url']) ? $site_info['url'] : null ?>" target="_blank"
                       class="no-line font_verydark">
                        <div class="site-info bd-radius_4px">
                            <div class="media">
                                <div class="pull-left">
                                    <?=
                                    $this->Html->image('ajax-loader.gif',
                                                       [
                                                           'class'         => 'lazy media-object',
                                                           'data-original' => $this->Upload->uploadUrl($comment,
                                                                                                       "Comment.site_photo",
                                                                                                       ['style' => 'small']),
                                                           'width'         => '80px',
                                                       ]
                                    )
                                    ?>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading font_18px  media-url"><?= isset($site_info['title']) ? mb_strimwidth(h($site_info['title']),
                                                                                                                                  0,
                                                                                                                                  40,
                                                                                                                                  "...") : null ?></h4>

                                    <p class="font_11px  media-url"><?= isset($site_info['url']) ? h($site_info['url']) : null ?></p>
                                    <?php if (isset($site_info['description'])): ?>
                                        <div class="font_12px">
                                            <?= mb_strimwidth(h($site_info['description']), 0, 95, "...") ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
            <div class="col col-xxs-12">
                <?php foreach ($comment_file as $file): ?>
                    <div class="panel panel-default file-wrap-on-post">
                        <div class="panel-body pt_10px plr_11px pb_8px">
                            <?= $this->element('Feed/attached_file_item',
                                               ['data' => $file, 'page_type' => 'feed', 'post_id' => $comment['post_id'], 'comment_id' => $comment['id']]) ?>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
            <div class="lh_15px">
                <?= $this->TimeEx->elapsedTime(h($comment['created'])) ?><span class="font_lightgray"> ･ </span>
                <a href="#" class="click-like font_lightgray <?= empty($like) ? null : "liked" ?>"
                   like_count_id="<?= $id_prefix ?>CommentLikeCount_<?= $comment['id'] ?>"
                   model_id="<?= $comment['id'] ?>"
                   like_type="comment">
                    <?= __d('gl', "いいね！") ?></a><span
                    class="font_lightgray"> ･ </span>
            <span>
                            <a href="<?= $this->Html->url(['controller' => 'posts', 'action' => 'ajax_get_comment_liked_users', 'comment_id' => $comment['id']]) ?>"
                               class="modal-ajax-get font_lightgray">
                                <i class="fa fa-thumbs-o-up"></i>&nbsp;<span
                                    id="<?= $id_prefix ?>CommentLikeCount_<?= $comment['id'] ?>"><?= $comment['comment_like_count'] ?></span></a><span
                    class="font_lightgray"> ･ </span>
            <a href="<?= $this->Html->url(['controller' => 'posts', 'action' => 'ajax_get_comment_red_users', 'comment_id' => $comment['id']]) ?>"
               class="modal-ajax-get font_lightgray"><i
                    class="fa fa-check"></i>&nbsp;<span><?= $comment['comment_read_count'] ?></span></a>
            </span>
            </div>
        </div>
    </div>
</div>
<!-- END app/View/Elements/Feed/comment.ctp -->
