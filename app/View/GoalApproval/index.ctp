<?php /**
 * @var CodeCompletionView $this
 * @var                    $unapproved_cnt
 * @var                    $done_cnt
 * @var                    $goal_info
 * @var                    $value_unit_list
 */
?>
<!-- START app/View/GoalApproval/index.ctp -->
<style type="text/css">
    .approval_body_text {
        font-size: 14px
    }

    .sp-feed-alt-sub {
        background: #f5f5f5;
        position: fixed;
        top: 50px;
        z-index: 1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, .15);
        width: 100%;
        left: 0;
    }

    .approval_body_start_area {
        margin-top: 40px;
    }

    .approval_botton_area {
        text-align: center;
    }

    .approval_button {
        color: #ffffff;
        width: 97%;
        margin-bottom: 10px;
    }

    .approval_status_box {
        text-align: center;
        background-color: #696969;
        color: #ffffff;
        font-size: 12px;
        padding-top: 5px;
        padding-bottom: 5px;
        letter-spacing: 0.2em;
    }

    .approval_badge {
        margin: -20px 0 0 -4px;
        color: #fff;
        font-size: 10px;
        background-color: red;
    }

</style>

<div class="col col-md-12 sp-feed-alt-sub" style="top: 50px;" id="SpFeedAltSub">
    <div class="col col-xxs-6 text-align_r">
        <a class="font_lightGray-veryDark no-line plr_18px sp-feed-link inline-block pt_12px height_40px sp-feed-active"
           id="SubHeaderMenuFeed">
            <?= __("処理待ち") ?>
            <?php if ($unapproved_cnt > 0) { ?>
            <span class="btn btn-danger btn-xs approval_badge">
            <?php echo $unapproved_cnt; ?>
            <?php } ?>
            </span>
        </a>
    </div>
    <div class="col col-xxs-6">
        <a class="font_lightGray-veryDark no-line plr_18px sp-feed-link inline-block pt_12px height_40px"
           id="SubHeaderMenuGoal" href="<?= $this->Html->url(['controller' => 'goal_approval', 'action' => 'done']) ?>">
            <?= __("処理済み") ?><?php if ($done_cnt > 0) {
                echo '(' . $done_cnt . ')';
            } ?></a>
    </div>
</div>

<div class="approval_body_start_area">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <?php if (isset($goal_info) === true && count($goal_info) > 0) { ?>

                <?php foreach ($goal_info as $key => $goal) { ?>

                    <div class="panel panel-default" id="AddGoalFormPurposeWrap">
                        <?php if (isset($goal['status']) === true) { ?>
                            <div class="approval_status_box"><?= $goal['status']; ?></div>
                        <?php } ?>

                        <div class="panel-body goal-set-heading clearfix">

                            <p class="approval_body_text">
                                <?= $this->Html->image('ajax-loader.gif', ['class'         => 'lazy comment-img',
                                                                           'data-original' => $this->Upload->uploadUrl($goal['User'],
                                                                                                                       'User.photo',
                                                                                                                       ['style' => 'small'])]) ?></p>

                            <p class="approval_body_text"><?= __("名前") ?>
                                : <?= h($goal['User']['display_username']); ?></p>

                            <p class="approval_body_text"><?= __("カテゴリ") ?>
                                : <?= h($goal['Goal']['GoalCategory']['name']); ?></p>

                            <p class="approval_body_text"><?= __("ゴール名") ?>
                                : <?= h($goal['Goal']['name']); ?></p>

                            <p class="approval_body_text"><?= $goal['Collaborator']['type'] === (string)Collaborator::TYPE_OWNER ?
                                    __("リーダー") : __("コラボレーター"); ?></p>

                            <p class="approval_body_text"><?= __("役割") ?>
                                : <?= h($goal['Collaborator']['role']); ?></p>

                            <p class="approval_body_text"><?= __("単位") ?>
                                : <?= h($value_unit_list[$goal['Goal']['value_unit']]); ?></p>

                            <p class="approval_body_text"><?= __("達成時") ?>
                                : <?= (double)$goal['Goal']['target_value']; ?></p>

                            <p class="approval_body_text"><?= __("開始時") ?>
                                : <?= (double)$goal['Goal']['start_value']; ?></p>

                            <p class="approval_body_text"><?= __("期限日") ?>
                                : <?= $this->TimeEx->date(h($goal['Goal']['end_date'])) ?></p>

                            <p class="approval_body_text"><?= __("重要度") ?>
                                : <?= $goal['Collaborator']['priority']; ?></p>

                            <p class="approval_body_text"><?= __("目的") ?>
                                : <?= h($goal['Goal']['Purpose']['name']); ?></p>

                            <p class="approval_body_text"><?= __("詳細") ?>
                                : <?= nl2br($this->TextEx->autoLink($goal['Goal']['description'])); ?></p>
                            <?=
                            $this->Html->image('ajax-loader.gif',
                                               [
                                                   'class'         => 'lazy',
                                                   'data-original' => $this->Upload->uploadUrl($goal,
                                                                                               "Goal.photo",
                                                                                               ['style' => 'medium']),
                                                   'width'         => '48px',
                                                   'error-img'     => "/img/no-image-link.png",
                                               ]
                            )
                            ?>
                        </div>


                        <div class="panel-body comment-block">
                            <?= $this->Form->create('GoalApproval',
                                                    ['id' => 'GoalApprovalIndexForm_' . $goal['Collaborator']['id'], 'url' => ['controller' => 'goal_approval', 'action' => 'index'], 'type' => 'post', 'novalidate' => true]); ?>
                            <?= $this->Form->hidden('collaborator_id', ['value' => $goal['Collaborator']['id']]); ?>

                            <div class="row">
                                <div class="approval_botton_area">
                                    <?php if ($goal['my_goal'] === false) { ?>
                                        <?= $this->Form->button(__("評価対象にする"),
                                                                ['name' => 'approval_btn', 'class' => 'btn btn-primary approval_button', 'div' => false]) ?>
                                        <?= $this->Form->button(__("評価対象にしない"),
                                                                ['name' => 'wait_btn', 'id' => 'reject_btn_' . $goal['Collaborator']['id'], 'class' => 'btn btn-Gray approval_button', 'div' => false, 'disabled' => 'disabled']) ?>
                                    <?php }
                                    elseif ($goal['my_goal'] === true && $goal['Collaborator']['type'] === (string)Collaborator::TYPE_OWNER && $goal['Collaborator']['valued_flg'] === (string)Collaborator::STATUS_MODIFY) { ?>
                                        <a class="btn btn-primary approval_button"
                                           href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'add', 'goal_id' => $goal['Goal']['id'], 'mode' => 3]) ?>"><?= __(
                                                                                                                                                                               "ゴールを修正する") ?>
                                            <i class="fa fa-chevron-right"></i></a>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <?= $this->Form->textarea('comment',
                                                          ['label'                        => false,
                                                           'class'                        => 'form-control addteam_input-design blank-disable',
                                                           'target-id'                    => 'modify_btn_' . $goal['Collaborator']['id'] . ',' . 'reject_btn_' . $goal['Collaborator']['id'],
                                                           'rows'                         => 3, 'cols' => 30,
                                                           'style'                        => 'margin-top: 10px; margin-bottom: 10px;',
                                                           'placeholder'                  => 'コメントを書く',
                                                           'data-bv-stringlength'         => 'true',
                                                           'data-bv-stringlength-max'     => 5000,
                                                           'data-bv-stringlength-message' => __(
                                                                                                 "最大文字数(%s)を超えています。",
                                                                                                 5000),
                                                          ])
                                ?>
                            </div>

                            <div class="row">
                                <div class="approval_botton_area">
                                    <?php if ($goal['my_goal'] === true || ($goal['my_goal'] === false && $goal['Collaborator']['type'] === (string)Collaborator::TYPE_COLLABORATOR)) { ?>
                                        <?= $this->Form->button(__("コメントする"),
                                                                ['name' => 'comment_btn', 'class' => 'btn btn-primary approval_button', 'div' => false]) ?>
                                    <?php }
                                    else { ?>
                                        <?= $this->Form->button(__("修正を依頼"),
                                                                ['id' => 'modify_btn_' . $goal['Collaborator']['id'], 'name' => 'modify_btn', 'class' => 'btn btn-Gray approval_button', 'div' => false, 'disabled' => 'disabled']) ?>
                                    <?php } ?>
                                </div>
                            </div>

                            <?php if (isset($goal['ApprovalHistory']) === true && empty($goal['ApprovalHistory']) === false) { ?>
                                <?php foreach ($goal['ApprovalHistory'] as $history) { ?>
                                    <div class="font_12px comment-box">
                                        <div class="col col-xxs-12">
                                            <?= $this->Html->image('ajax-loader.gif', ['class'         => 'lazy comment-img',
                                                                                       'data-original' => $this->Upload->uploadUrl($history['User'],
                                                                                                                                   'User.photo',
                                                                                                                                   ['style' => 'small'])]) ?>
                                            <div class="comment-body">

                                                <div class="col col-xxs-12 mb_8px">
                                                    <div
                                                        class="mb_2px lh_12px font_bold font_verydark comment-user"><?= h($history['User']['local_username']); ?></div>
                                                    <div
                                                        class="col col-xxs-12 showmore-comment comment-text feed-contents comment-contents font_verydark box-align"><?= nl2br($history['comment']); ?></div>
                                                    <div
                                                        class="lh_15px"><?= $this->TimeEx->elapsedTime(h($history['created'])) ?></div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                            <?= $this->Form->end(); ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
<!-- END app/View/GoalApproval/index.ctp -->
<?php $this->append('script') ?>
<script type="text/javascript">
    $(document).ready(function () {
        <?php foreach ($goal_info as $goal):?>
        <?php if(isset($goal['Collaborator']['id'])):?>
        $('#GoalApprovalIndexForm_<?= $goal['Collaborator']['id']?>').bootstrapValidator({
            live: 'enabled',
            feedbackIcons: {},
            fields: {}
        });
        <?php endif;?>
        <?php endforeach;?>
    });
</script>
<?php $this->end() ?>
