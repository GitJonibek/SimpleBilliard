<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 6/6/14
 * Time: 3:19 PM
 *
 * @var CodeCompletionView $this
 * @var                    $goal
 */
?>
<!-- START app/View/Elements/Goal/modal_goal_description.ctp -->
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close font_33px close-design" data-dismiss="modal" aria-hidden="true">
                <span class="close-icon">&times;</span></button>
            <h4 class="modal-title"><?= __d('gl', "ゴール概要") ?>&nbsp;&nbsp;
                <a class=""
                   href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'view_info', 'goal_id' => $goal['Goal']['id']]) ?>">
                    <?= __d('gl', 'ゴールページへ') ?>
                </a>
            </h4>
        </div>
        <div class="modal-body modal-circle-body">
            <div class="col col-xxs-12">
                <div class="col col-xxs-6">
                    <a href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'view_info', 'goal_id' => $goal['Goal']['id']]) ?>">
                        <img src="<?= $this->Upload->uploadUrl($goal, 'Goal.photo', ['style' => 'large']) ?>"
                             width="128"
                             height="128">
                    </a>

                </div>
                <?php if ($goal['Goal']['user_id'] != $this->Session->read('Auth.User.id') && isset($goal['Goal']) && !empty($goal['Goal'])): ?>
                    <div class="col col-xxs-6">
                        <? $follow_opt = $this->Goal->getFollowOption($goal) ?>
                        <? $collabo_opt = $this->Goal->getCollaboOption($goal) ?>
                        <div>
                            <a class="btn btn-white bd-circle_22px mt_16px toggle-follow font_verydark <?= $follow_opt['class'] ?>"
                               href="#" <?= $follow_opt['disabled'] ?>="<?= $follow_opt['disabled'] ?>"
                            data-class="toggle-follow"
                            goal-id="<?= $goal['Goal']['id'] ?>">
                            <i class="fa fa-heart font_rougeOrange" style="<?= $follow_opt['style'] ?>"></i>
                            <span class="ml_5px"><?= $follow_opt['text'] ?></span>
                            </a>
                        </div>
                        <div>
                            <a class="btn btn-white bd-circle_22px mt_16px font_verydark modal-ajax-get-collabo <?= $collabo_opt['class'] ?>"
                               data-toggle="modal"
                               data-target="#ModalCollabo_<?= $goal['Goal']['id'] ?>"
                               href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'ajax_get_collabo_change_modal', 'goal_id' => $goal['Goal']['id']]) ?>">
                                <i class="fa fa-child font_rougeOrange font_18px"
                                   style="<?= $collabo_opt['style'] ?>"></i>
                                <span class="ml_5px font_14px"><?= $collabo_opt['text'] ?></span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (isset($goal['Goal']) && !empty($goal['Goal'])): ?>
                <div class="col col-xxs-12 font_11px">
                    <i class="fa fa-folder"></i><span class="pl_2px"><?= h($goal['GoalCategory']['name']) ?></span>
                </div>
                <div class="col col-xxs-12">
                    <p class="font_18px">
                        <a class="font_verydark"
                           href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'view_info', 'goal_id' => $goal['Goal']['id']]) ?>">
                            <?= h($goal['Goal']['name']) ?>
                        </a>
                    </p>
                </div>
                <div class="col col-xxs-12 bd-b mb-pb_5px">
                    <?= h($goal['Purpose']['name']) ?>
                </div>
                <div class="col col-xxs-12 bd-b mb-pb_5px">
                    <i class="fa fa-bullseye"></i><span class="pl_2px"><?= __d('gl', '程度') ?></span>

                    <div><?= __d('gl', '単位: %s', KeyResult::$UNIT[$goal['Goal']['value_unit']]) ?></div>
                    <?php if ($goal['Goal']['value_unit'] != KeyResult::UNIT_BINARY): ?>
                        <div><?= __d('gl', '達成時: %s', (double)$goal['Goal']['target_value']) ?></div>
                        <div><?= __d('gl', '開始時: %s', (double)$goal['Goal']['start_value']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col col-xxs-12">
                    <!-- アクション、フォロワー -->
                </div>
                <div class="col col-xxs-12 bd-b mb-pb_5px">
                    <div><i class="fa fa-sun-o"></i><span class="pl_2px"><?= __d('gl', "リーダー") ?></span></div>
                    <?php if (isset($goal['Leader'][0]['User'])): ?>
                        <img src="<?=
                        $this->Upload->uploadUrl($goal['Leader'][0]['User'],
                                                 'User.photo', ['style' => 'small']) ?>"
                             style="width:32px;height: 32px;">
                        <?= h($goal['Leader'][0]['User']['display_username']) ?>
                    <?php endif; ?>
                </div>
                <div class="col col-xxs-12 bd-b mb-pb_5px">
                    <div><i class="fa fa-child"></i><span class="pl_2px"><?= __d('gl', "コラボレータ") ?>
                            &nbsp;(<?= count($goal['Collaborator']) ?>)</span></div>
                    <?php if (isset($goal['Collaborator']) && !empty($goal['Collaborator'])): ?>
                        <?php foreach ($goal['Collaborator'] as $collabo): ?>
                            <img src="<?=
                            $this->Upload->uploadUrl($collabo['User'],
                                                     'User.photo', ['style' => 'small']) ?>"
                                 style="width:32px;height: 32px;" alt="<?= h($collabo['User']['display_username']) ?>"
                                 title="<?= h($collabo['User']['display_username']) ?>">
                        <?php endforeach ?>
                    <?php else: ?>
                        <?= __d('gl', "なし") ?>
                    <?php endif; ?>
                </div>
                <div class="col col-xxs-12 bd-b mb-pb_5px">
                    <div><i class="fa fa-heart"></i><span class="pl_2px"><?= __d('gl', "フォロワー") ?>
                            &nbsp;(<?= count($goal['Follower']) ?>)</span></div>
                    <?php if (isset($goal['Follower']) && !empty($goal['Follower'])): ?>
                        <?php foreach ($goal['Follower'] as $follower): ?>
                            <img src="<?=
                            $this->Upload->uploadUrl($follower['User'],
                                                     'User.photo', ['style' => 'small']) ?>"
                                 style="width:32px;height: 32px;" alt="<?= h($follower['User']['display_username']) ?>"
                                 title="<?= h($follower['User']['display_username']) ?>">
                        <?php endforeach ?>
                    <?php else: ?>
                        <?= __d('gl', "なし") ?>
                    <?php endif; ?>
                </div>
                <div class="col col-xxs-12 bd-b mb-pb_5px">
                    <div><i class="fa fa-ellipsis-h"></i><span class="pl_2px"><?= __d('gl', '詳細') ?></span></div>
                    <div>
                        <?= nl2br($this->TextEx->autoLink($goal['Goal']['description'])) ?>
                    </div>
                </div>
                <div class="col col-xxs-12">
                    <div><i class="fa fa-key"></i><span class="pl_2px"><?= __d('gl', "達成要素") ?>
                            &nbsp;(<?= count($goal['KeyResult']) ?>)</span></div>
                    <?php if (isset($goal['KeyResult']) && !empty($goal['KeyResult'])): ?>
                        <?php foreach ($goal['KeyResult'] as $key_result): ?>
                            <div class="col col-xxs-12 dot-omission">
                                <?php if ($key_result['completed']): ?>
                                    <span class="fin-kr tag-sm tag-info"><?= __d('gl', "完了") ?></span>
                                <?php else: ?>
                                    <span class="unfin-kr tag-sm tag-danger"><?= __d('gl', "未完了") ?></span>
                                <?php endif; ?>
                                <?= h($key_result['name']) ?>
                            </div>
                        <?php endforeach ?>
                    <?php else: ?>
                        <?= __d('gl', "なし") ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= __d('gl', "閉じる") ?></button>
        </div>
    </div>
</div>
<!-- END app/View/Elements/Goal/modal_goal_description.ctp -->
