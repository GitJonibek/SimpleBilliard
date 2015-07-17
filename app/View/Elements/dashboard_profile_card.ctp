<?php
/**
 * Created by PhpStorm.
 * User: kubotanaruhito
 * Date: 12/4/14
 * Time: 19:58
 *
 * @var CodeCompletionView $this
 */
?>
<!-- START app/View/Elements/dashboard_profile_card.ctp -->
<div class="dashboardProfileCard" xmlns="http://www.w3.org/1999/html">
    <a class="dashboardProfileCard-bg col-xxs-12" tabindex="-1" href="/#"></a>

    <div class="dashboardProfileCard-content">
        <a class="dashboardProfileCard-avatorLink"
           href="<?= $this->Html->url(['controller' => 'users', 'action' => 'view_goals', 'user_id' => $this->Session->read('Auth.User.id')]) ?>">
            <?= $this->Upload->uploadImage($this->Session->read('Auth.User'), 'User.photo', ['style' => 'medium'],
                                           ['class' => 'dashboardProfileCard-avatarImage inline-block']) ?>
        </a>
        <a href="<?= $this->Html->url(['controller' => 'users', 'action' => 'view_goals', 'user_id' => $this->Session->read('Auth.User.id')]) ?>">
        <span class="dashboardProfileCard-userField font_bold font_verydark ln_1-f">
            <?= h($this->Session->read('Auth.User.display_first_name')) ?>
        </span>
        </a>

        <div class="dashboardProfileCard-stats font_10px">
            <div class="dashboardProfileCard-point">
                <div class="ml_8px"><?= __d('gl', "今期のポイント") ?></div>
                <div class="text-align_c">
                    <div class="inline-block">
                        <span class="dashboardProfileCard-score font_bold font_33px ml_8px">?</span>
                        <span class="ml_2px">pt</span>
                    </div>
                    <div class="inline-block">
                        <div class="ml_2px"><?= __d('gl', "先週比") ?></div>
                        <span>
                            (<span class="font_seagreen font_bold plr_1px">?<i class="fa fa-level-up"></i></span>)
                        </span>
                    </div>
                </div>
            </div>
            <div class="dashboardProfileCard-activities bd-t mt_8px">
                <div class="ml_8px mt_5px"><?= __d('gl', "今期のアクティビティ") ?></div>
                <ul class="dashboardProfileCard-activityList text-align_c col-xxs-12 p_8px mb_0px">
                    <li class="dashboardProfileCard-activity inline-block col-xxs-4">
                        <div class="ls_title"><?= __d('gl', "アクション") ?></div>
                        <i class="fa fa-check-circle mr_1px"></i><span class="ls_number" id="CountActionByMe"></span>
                    </li>
                    <li class="dashboardProfileCard-activity inline-block col-xxs-4">
                        <div class="ls_title"><?= __d('gl', "成果") ?></div>
                        <i class="fa fa-key mr_1px"></i><span class="ls_number">?</span>
                    </li>
                    <li class="dashboardProfileCard-activity inline-block col-xxs-4">
                        <div class="ls_title"><?= __d('gl', "投稿") ?></div>
                        <i class="fa fa-comment-o mr_1px"></i><span class="ls_number" id="CountPostByMe"></span>
                    </li>
                </ul>
                <div class="dashboardProfileCard-moreRead text-align_c mtb_8px"><a class="font_lightGray-gray"
                                                                                   href="/#"><i
                            class="fa fa-eye mr_5px"></i><?= __d('gl', "もっと見る") ?></a></div>
            </div>
        </div>
    </div>
</div>
<?php $this->append('script') ?>
<script type="text/javascript">
    $(document).ready(function () {
        ajaxAppendCount('CountPostByMe', "<?=$this->Html->url(['controller'=>'users','action'=>'ajax_get_post_count'])?>");
        ajaxAppendCount('CountActionByMe', "<?=$this->Html->url(['controller'=>'users','action'=>'ajax_get_action_count'])?>");
    });
</script>
<?php $this->end() ?>

<!-- END app/View/Elements/dashboard_profile_card.ctp -->
