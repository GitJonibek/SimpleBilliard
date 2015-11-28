<?php
/**
 * Created by PhpStorm.
 * User: t-tsunekawa
 * Date: 2015/04/3
 * Time: 3:19 PM
 *
 * @var CodeCompletionView $this
 * @var                    $circle_members
 */

// 管理者メンバー
$admin_circle_members = array_filter($circle_members, function ($v) {
    return $v['CircleMember']['admin_flg'];
});
?>
<!-- START app/View/Elements/modal_circle_members.ctp -->
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header no-border">
            <button type="button" class="close font_33px close-design" data-dismiss="modal" aria-hidden="true"><span
                    class="close-icon">&times;</span></button>
            <h4 class="modal-title font_18px font_bold"><?= __d('gl', "このサークルのメンバー") ?></h4>
        </div>
        <ul class="nav nav-tabs">
            <li class="active"><a href="#ModalCircleMemberTab1" data-toggle="tab"><?= __d('gl', "メンバー(%s)",
                                                                                          count($circle_members)) ?></a>
            </li>
            <li><a href="#ModalCircleMemberTab2" data-toggle="tab"><?= __d('gl', "管理者(%s)",
                                                                           count($admin_circle_members)) ?></a></li>
        </ul>
        <div class="modal-body modal-feed-body tab-content">
            <div class="tab-pane fade in active" id="ModalCircleMemberTab1">
                <?php if (!empty($circle_members)): ?>
                    <div class="row borderBottom">
                        <?php foreach ($circle_members as $user): ?>
                            <?=
                            $this->element('Feed/read_like_user',
                                           ['user'     => $user['User'],
                                            'created'  => $user['CircleMember']['modified'],
                                            'is_admin' => $user['CircleMember']['admin_flg'],
                                            'type'     => 'rough']) ?>
                        <?php endforeach ?>
                    </div>
                <?php else: ?>
                    <?= __d('gl', "このサークルにはメンバーがいません。") ?>
                <?php endif ?>
            </div>
            <div class="tab-pane fade in" id="ModalCircleMemberTab2">
                <div class="row borderBottom">
                    <?php foreach ($admin_circle_members as $user): ?>
                        <?=
                        $this->element('Feed/read_like_user',
                                       ['user'     => $user['User'],
                                        'created'  => $user['CircleMember']['modified'],
                                        'is_admin' => $user['CircleMember']['admin_flg'],
                                        'type'     => 'rough']) ?>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
        <div class="modal-footer modal-feed-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= __d('gl', "閉じる") ?></button>
        </div>
    </div>
</div>
<!-- END app/View/Elements/modal_circle_members.ctp -->
