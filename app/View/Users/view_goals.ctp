<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 7/9/15
 * Time: 3:33 PM
 *
 * @var CodeCompletionView $this
 * @var                    $goals
 * @var                    $display_action_count
 * @var                    $is_mine
 * @var                    $page_type
 * @var                    $my_goals_count
 * @var                    $follow_goals_count
 */
?>
<!-- START app/View/Users/view_goals.ctp -->
<div class="col-sm-8 col-sm-offset-2">
    <div class="panel panel-default">
        <?= $this->element('User/simplex_top_section') ?>
        <div class="panel-body view-goals-panel">
            <div class="input-group profile-user-goals-terms">
                    <span class="input-group-addon profile-user-goals-terms-icon-wrap" id="">
                        <i class="profile-user-goals-terms-icon fa fa-calendar-o"></i>
                    </span>
                <?=
                $this->Form->input('term_id', [
                    'label'                    => false,
                    'div'                      => false,
                    'required'                 => true,
                    'class'                    => 'form-control disable-change-warning profile-user-goals-terms-select',
                    'id'                       => 'LoadTermGoal',
                    'options'                  => $term,
                    'default'                  => $term_id,
                    'redirect-url'             => $term_base_url,
                    'wrapInput'         => 'profile-user-goals-terms-select-wrap'
                ])
                ?>
            </div>
            <br>
            <div class="profile-goals-select-wrap btn-group" role="group">
                <a href="<?= $this->Html->url(['controller' => 'users', 'action' => 'view_goals', 'user_id' => $user['User']['id'], 'term_id'=>$term_id]) ?>"
                   class="profile-goals-select btn <?= $page_type == "following" ? "btn-unselected" : "btn-selected" ?>">
                    <?= __("マイゴール(%s)", $my_goals_count) ?></a>
                <a href="<?= $this->Html->url(['controller' => 'users', 'action' => 'view_goals', 'user_id' => $user['User']['id'],'term_id'=>$term_id, 'page_type' => 'following']) ?>"
                   class="profile-goals-select btn <?= $page_type == "following" ? "btn-selected" : "btn-unselected" ?>">
                    <?= __("フォロー中(%s)", $follow_goals_count) ?></a>
            </div>
            <?php foreach ($goals as $goal): ?>
                <div class="col col-xxs-12 my-goals-item">
                    <div class="col col-xxs-2 col-xs-2">
                        <a href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'view_info', 'goal_id' => $goal['Goal']['id']]) ?>">
                            <?=
                            $this->Html->image('ajax-loader.gif',
                                               [
                                                   'class'         => 'lazy img-rounded profile-goals-img',
                                                   'data-original' => $this->Upload->uploadUrl($goal, 'Goal.photo',
                                                                                               ['style' => 'x_large'])
                                               ]
                            )
                            ?></a>
                    </div>
                    <div class="col col-xxs-10 col-xs-10 pl_5px">
                        <div class="col col-md-11 col-xs-10 col-xxs-9 profile-goals-card-title-wrapper">
                            <a href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'view_info', 'goal_id' => $goal['Goal']['id']]) ?>"
                               class="profile-goals-card-title">
                                <p class="font_verydark profile-goals-card-title-text">
                                    <span><?= h($goal['Goal']['name']) ?></span>
                                </p>
                            </a>
                        </div>
                        <?php if ($is_mine && $page_type != "following"): ?>
                            <?= $this->element('Goal/goal_menu_on_my_page', ['goal' => $goal]) ?>
                        <?php endif; ?>
                        <div class="col col-xxs-12 font_lightgray font_12px">
                            <?= __("目的: %s", $goal['Purpose']['name']) ?>
                        </div>
                        <div class="col col-xxs-12 font_lightgray font_12px">
                            <?= __("認定ステータス: %s",
                                    Collaborator::$STATUS[$goal['Collaborator']['valued_flg']]) ?>
                        </div>
                        <div class="col col-xxs-12">
                            <div class="progress mb_0px goals-column-progress-bar">
                                <div class="progress-bar progress-bar-info" role="progressbar"
                                     aria-valuenow="<?= h($goal['Goal']['progress']) ?>" aria-valuemin="0"
                                     aria-valuemax="100" style="width: <?= h($goal['Goal']['progress']) ?>%;">
                                    <span class="ml_12px"><?= h($goal['Goal']['progress']) ?>%</span>
                                </div>
                            </div>
                        </div>

                        <?php if ($page_type != "following"): ?>
                            <?php if ($goal['Goal']['user_id'] != $this->Session->read('Auth.User.id') && isset($goal['Goal'])): //ゴールのリーダが自分以外の場合に表示?>
                                <div class="col col-xxs-12 mt_5px">
                                    <? $follow_opt = $this->Goal->getFollowOption($goal) ?>
                                    <? $collabo_opt = $this->Goal->getCollaboOption($goal) ?>
                                    <div class="col col-xxs-6 col-xs-4 mr_5px">
                                        <a class="btn btn-white font_verydark bd-circle_22px toggle-follow p_8px <?= h($follow_opt['class']) ?>"
                                           href="#"
                                           data-class="toggle-follow"
                                           goal-id="<?= $goal['Goal']['id'] ?>"
                                        <?= h($follow_opt['disabled']) ?>="<?= h($follow_opt['disabled']) ?>">
                                        <i class="fa fa-heart font_rougeOrange" style="<?= h($follow_opt['style']) ?>"></i>
                                        <span class="ml_5px"><?= h($follow_opt['text']) ?></span>
                                        </a>
                                    </div>
                                    <div class="col col-xxs-5 col-xs-4">
                                        <a class="btn btn-white bd-circle_22px font_verydark modal-ajax-get-collabo p_8px <?= h($collabo_opt['class']) ?>"
                                           data-toggle="modal"
                                           data-target="#ModalCollabo_<?= $goal['Goal']['id'] ?>"
                                           href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'ajax_get_collabo_change_modal', 'goal_id' => $goal['Goal']['id']]) ?>">
                                            <i class="fa fa-child font_rougeOrange font_18px"
                                               style="<?= h($collabo_opt['style']) ?>"></i>
                                            <span class="ml_5px font_14px"><?= h($collabo_opt['text']) ?></span>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="col col-xxs-12 mt_5px">
                                <ul class="profile-user-actions">
                                    <?php if ($is_mine && $goal['Goal']['is_current_term']): ?>
                                        <li class="profile-user-action-list">
                                            <a class="profile-user-add-action"
                                               href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'add_action', 'goal_id' => $goal['Goal']['id']]) ?>"><i
                                                    class="fa fa-plus"></i>

                                                <p class="profile-user-add-action-text "><?= __("Action") ?></p>

                                                <p class="profile-user-add-action-text "><?= __("Add") ?></p>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php foreach ($goal['ActionResult'] as $key => $action): ?>
                                        <?php
                                        $last_many = false;
                                        //urlはアクション単体ページ
                                        $url = ['controller' => 'posts', 'action' => 'feed', 'post_id' => $action['Post'][0]['id']];
                                        //最後の場合かつアクション件数合計が表示件数以上の場合
                                        if ($key == count($goal['ActionResult']) - 1 && count($goal['ActionResultCount']) > $display_action_count) {
                                            $last_many = true;
                                            //urlはゴールページの全アクションリスト
                                            $url = ['controller' => 'users', 'action' => 'view_actions', 'user_id' => $user['User']['id'], 'page_type' => 'image', 'goal_id' => $goal['Goal']['id']];//TODO urlはマイページのアクションリストが完成したら差し替え
                                        }
                                        ?>
                                        <li class="profile-user-action-list">
                                            <a href="<?= $this->Html->url($url) ?>" class="profile-user-action-pic">
                                                <?php if (viaIsSet($action['ActionResultFile'][0]['AttachedFile'])): ?>
                                                    <?= $this->Html->image('ajax-loader.gif',
                                                                           [
                                                                               'class'         => 'lazy',
                                                                               'width'         => 48,
                                                                               'height'        => 48,
                                                                               'data-original' => $this->Upload->uploadUrl($action['ActionResultFile'][0]['AttachedFile'],
                                                                                                                           "AttachedFile.attached",
                                                                                                                           ['style' => 'x_small']),
                                                                           ]
                                                    );
                                                    ?>

                                                <?php else: ?>

                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php
                                                        if (!empty($action["photo{$i}_file_name"]) || $i == 5) {
                                                            echo $this->Html->image('ajax-loader.gif',
                                                                                    [
                                                                                        'class'         => 'lazy',
                                                                                        'width'         => 48,
                                                                                        'height'        => 48,
                                                                                        'data-original' => $this->Upload->uploadUrl($action,
                                                                                                                                    "ActionResult.photo$i",
                                                                                                                                    ['style' => 'x_small']),
                                                                                    ]);
                                                            break;
                                                        }
                                                        ?>
                                                    <?php endfor; ?>
                                                <?php endif; ?>
                                                <?php if ($last_many): ?>
                                                    <span class="action-more-counts">
                                                        <i class="fa fa-plus"></i>
                                                        <?= count($goal['ActionResultCount']) - $display_action_count + 1 ?>
                                                    </span>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                    <? endforeach ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
<!-- END app/View/Users/view_goals.ctp -->
