<?php
/**
 * Created by PhpStorm.
 * User: daikihirakata
 * Date: 2014/07/19
 * Time: 23:28
 *
 * @var CodeCompletionView $this
 * @var                    $user
 * @var                    $created
 * @var                    $type
 */
?>
<!-- START app/View/Elements/Feed/read_like_user.ctp -->
<div class="col col-xxs-12 mpTB0">
    <?=
    $this->Upload->uploadImage($user, 'User.photo', ['style' => 'small'],
                               ['class' => 'comment-img'])
    ?>
    <div class="comment-body modal-comment">
        <div class="font_12px font_bold modalFeedTextPadding">
            <?= h($user['display_username']) ?>&nbsp;
            <?php if (viaIsSet($is_admin)): ?>
                <i class="fa fa-adn team-members-card-admin-icon"></i>
            <?php endif; ?>
        </div>

        <?php if ($created): ?>
            <div class="font_12px font_lightgray modalFeedTextPaddingSmall">
                <?= $this->TimeEx->elapsedTime(h($created), viaIsSet($type)) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- END app/View/Elements/Feed/read_like_user.ctp -->
