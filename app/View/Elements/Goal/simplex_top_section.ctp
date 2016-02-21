<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 7/9/15
 * Time: 3:50 PM
 *
 * @var $goal
 * @var $action_count
 * @var $member_count
 * @var $follower_count
 * @var $is_leader
 * @var $is_coaching_goal
 */
?>
<!-- START app/View/Elements/simplex_top_section.ctp -->
<div class="panel-body goal-detail-upper-panel">
    <div class="goal-detail-avatar-wrap">
        <?=
        $this->Html->image('ajax-loader.gif',
                           [
                               'class'         => 'goal-detail-avatar lazy',
                               'data-original' => $this->Upload->uploadUrl($goal['Goal'], 'Goal.photo',
                                                                           ['style' => 'x_large']),
                           ]
        )
        ?>
    </div>

    <div class="goal-detail-numbers-wrap">
        <div class="goal-detail-numbers-action">
            <div class="goal-detail-numbers-action-counts">
                <?= h($this->NumberEx->formatHumanReadable($action_count, ['convert_start' => 10000])) ?>
            </div>
            <span class="goal-detail-numbers-category-action">
                <?= __d\('app', 'アクション') ?>
            </span>
        </div>
        <div class="goal-detail-numbers-member">
            <div class="goal-detail-numbers-member-counts">
                <?= h($this->NumberEx->formatHumanReadable($member_count, ['convert_start' => 10000])) ?>
            </div>
            <span class="goal-detail-numbers-category-member">
                <?= __d\('app', 'メンバー') ?>
            </span>
        </div>
        <div class="goal-detail-numbers-follower">
            <div class="goal-detail-numbers-follower-counts">
                <?= h($this->NumberEx->formatHumanReadable($follower_count, ['convert_start' => 10000])) ?>
            </div>
            <span class="goal-detail-numbers-category-follower">
                <?= __d\('app', 'フォロワー') ?>
            </span>
        </div>
        <?php if ($is_leader): ?>
            <?= $this->Html->link(__d\('app', 'ゴール編集'),
                                  [
                                      'controller' => 'goals',
                                      'action'     => 'add',
                                      'goal_id'    => $goal['Goal']['id'],
                                      'mode'       => 3,
                                  ],
                                  [
                                      'class' => 'btn-profile-edit'
                                  ])
            ?>
        <? else: ?>
            <div class="col col-xxs-12 mt_18px">
                <?php $follow_opt = $this->Goal->getFollowOption($goal); ?>
                <?php $collabo_opt = $this->Goal->getCollaboOption($goal); ?>
                <div class="col col-xxs-5 col-xxs-offset-1 col-xs-4 col-xs-offset-2 col-sm-offset-2">
                    <a class="btn btn-white bd-circle_22px toggle-follow p_8px width100_per
                    <?= h($follow_opt['class']) ?>
                    <?php if ($is_coaching_goal): ?>
                    follow-on
                    <?php endif ?>"

                       href="#"
                       data-class="toggle-follow"
                       goal-id="<?= $goal['Goal']['id'] ?>"
                        <?php if ($follow_opt['disabled'] || $is_coaching_goal): ?>
                            disabled="disabled"
                        <?php endif ?>
                        >
                        <span class="ml_5px"><?= __d\('app', 'フォロー') ?></span>
                    </a>
                </div>
                <div class="col col-xxs-5 col-xxs-offset-1 col-xs-4">
                    <a class="btn btn-white bd-circle_22px modal-ajax-get-collabo p_8px width100_per
                    <?= h($collabo_opt['class']) ?>"
                       data-toggle="modal"
                       data-target="#ModalCollabo_<?= $goal['Goal']['id'] ?>"
                       href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'ajax_get_collabo_change_modal', 'goal_id' => $goal['Goal']['id']]) ?>">
                        <span class=""><?= __d\('app', 'コラボ') ?></span>
                    </a>
                </div>
            </div>
        <?php endif ?>
    </div>
    <p class="goal-detail-goal-name">
        <?= h($goal['Goal']['name']) ?>
    </p>

    <p class="goal-detail-goal-purpose">
        <?= h($goal['Purpose']['name']) ?>
    </p>

</div>
<div class="goal-detail-tab-group">
    <a class="goal-detail-info-tab <?= $this->request->params['action'] == 'view_info' ? "profile-user-tab-active" : null ?>"
       href="<?= $this->Html->url(
           [
               'controller' => 'goals',
               'action'     => 'view_info',
               'goal_id'    => $goal['Goal']['id'],
           ]); ?>">
        <i class="fa fa-flag goal-detail-tab-icon"></i>

        <p class="goal-detail-tab-title">
            <?= h(__d\('app', '基本情報')) ?>
        </p>
    </a>
    <a class="goal-detail-kr-tab <?= $this->request->params['action'] == 'view_krs' ? "profile-user-tab-active" : null ?>"
       href="<?= $this->Html->url(
           [
               'controller' => 'goals',
               'action'     => 'view_krs',
               'goal_id'    => $goal['Goal']['id'],
           ]); ?>">
        <i class="fa fa-key goal-detail-tab-icon"></i>

        <p class="goal-detail-tab-title">
            <?= h(__d\('app', '成果')) ?>
        </p>
    </a>
    <a class="goal-detail-action-tab <?= $this->request->params['action'] == 'view_actions' ? "profile-user-tab-active" : null ?>"
       href="<?= $this->Html->url(
           [
               'controller' => 'goals',
               'action'     => 'view_actions',
               'goal_id'    => $goal['Goal']['id'],
               'page_type'  => 'image'
           ]); ?>">
        <i class="fa fa-check goal-detail-tab-icon"></i>

        <p class="goal-detail-tab-title">
            <?= h(__d\('app', 'アクション')) ?>
        </p>
    </a>
    <a class="goal-detail-member-tab <?= $this->request->params['action'] == 'view_members' ? "profile-user-tab-active" : null ?>"
       href="<?= $this->Html->url(
           [
               'controller' => 'goals',
               'action'     => 'view_members',
               'goal_id'    => $goal['Goal']['id'],
           ]); ?>">
        <i class="fa fa-users goal-detail-tab-icon"></i>

        <p class="goal-detail-tab-title">
            <?= h(__d\('app', 'メンバー')) ?>
        </p>
    </a>
    <a class="goal-detail-member-tab <?= $this->request->params['action'] == 'view_followers' ? "profile-user-tab-active" : null ?>"
       href="<?= $this->Html->url(
           [
               'controller' => 'goals',
               'action'     => 'view_followers',
               'goal_id'    => $goal['Goal']['id'],
           ]); ?>">
        <i class="fa fa-heart goal-detail-tab-icon"></i>

        <p class="goal-detail-tab-title">
            <?= h(__d\('app', 'フォロワー')) ?>
        </p>
    </a>
</div>
<!-- END app/View/Elements/simplex_top_section.ctp -->
