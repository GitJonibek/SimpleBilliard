<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 7/9/15
 * Time: 3:33 PM
 *
 * @var $goal
 * @var $goal_term
 */
?>
<!-- START app/View/Goals/view_info.ctp -->
<div class="col-sm-8 col-sm-offset-2">
    <div class="panel panel-default">
        <?= $this->element('Goal/simplex_top_section') ?>
        <div class="panel-body goal-detail-info-panel">
            <div class="goal-detail-info-data-wrap">
                <div class="goal-detail-info-left-icons">
                    <i class="fa fa-flag"></i>
                </div>
                <div class="goal-detail-info-data">
                    <span class="font_bold"><?= h($goal['Goal']['name']) ?></span>

                    <p class="goal-detail-info-purpose"><?= __d('gl', '目的') ?>： <?= $goal['Purpose']['name'] ?></p>

                    <p class="goal-detail-info-category"><?= __d('gl', 'カテゴリー') ?>
                        ： <?= h($goal['GoalCategory']['name']) ?></p>
                </div>
            </div>
            <div class="goal-detail-info-progress-wrap">
                <div class="goal-detail-info-left-icons">
                    <i class="fa-bullseye fa"></i>
                </div>
                <div class="goal-detail-info-progress">
                    <?= h(round($goal['Goal']['start_value'], 1)) ?> →
                    <?= h(round($goal['Goal']['target_value'], 1)) ?>
                    [<?= h(KeyResult::$UNIT[$goal['Goal']['value_unit']]) ?>]
                </div>
            </div>
            <div class="goal-detail-info-due-wrap">
                <div class="goal-detail-info-left-icons">
                    <i class="fa-calendar fa"></i>
                </div>
                <div class="goal-detail-info-due">
                    <?= $this->Time->format('Y/m/d', $goal['Goal']['start_date'] + $goal_term['timezone'] * HOUR) ?>
                    - <?= $this->Time->format('Y/m/d', $goal['Goal']['end_date'] + $goal_term['timezone'] * HOUR) ?>
                    <?php if ($this->Session->read('Auth.User.timezone') != $goal_term['timezone']): ?>
                        <?= $this->TimeEx->getTimezoneText($goal_term['timezone']); ?>
                    <?php endif ?>
                </div>
            </div>
            <div class="goal-detail-info-members">
                <p class="goal-detail-info-members-head"><?= __d('gl', 'メンバー') ?></p>
                <?php
                $member_all = array_merge($goal['Leader'], $goal['Collaborator']);
                $member_view_num = 5;
                $over_num = count($member_all) - $member_view_num;
                ?>
                <?php foreach ($member_all as $member): ?>
                    <?php
                    if ($member_view_num-- == 0) {
                        break;
                    }
                    ?>
                    <?=
                    $this->Html->link($this->Upload->uploadImage($member['User'], 'User.photo', ['style' => 'medium'],
                                                                 ['class' => 'goal-detail-info-avatar',]),
                                      ['controller' => 'users',
                                       'action'     => 'view_goals',
                                       'user_id'    => $member['User']['id']],
                                      ['escape' => false]
                    )
                    ?>
                <?php endforeach ?>
                <?php if ($over_num > 0): ?>
                    <a href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'view_members', 'goal_id' => $goal['Goal']['id']]) ?>"
                       class="goal-detail-members-remaining">
                        <?= $this->Upload->uploadImage($member_all[5]['User'], 'User.photo', ['style' => 'medium'],
                                                       ['class' => 'goal-detail-info-avatar',]) ?>
                        <span class="goal-detail-member-more-counts">
                                <i class="fa fa-plus"></i>
                            <?= $over_num ?></span>
                    </a>
                <?php endif ?>
            </div>
            <div class="goal-detail-info-description">
                <p class="goal-detail-info-description-head"><?= __d('gl', '詳細') ?></p>

                <p class="goal-detail-info-description-contents"><?= nl2br($this->TextEx->autoLink($goal['Goal']['description'])) ?></p>
            </div>
        </div>
    </div>
</div>
<!-- END app/View/Goals/view_info.ctp -->
