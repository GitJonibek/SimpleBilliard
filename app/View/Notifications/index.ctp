<?php
/**
 * Created by PhpStorm.
 * User: saeki
 * Date: 15/04/27
 * Time: 14:22
 *
 * @var                    $notify_items
 * @var                    $isExistMoreNotify
 * @var CodeCompletionView $this
 */
?>

<!-- START app/View/Notifications/index.ctp -->

<div class="panel panel-default">
    <div class="panel-heading">
        <div class="noitify-mark-allread-wrap">
            <i class="fa fa-check btn-link notify-mark-allread" id="mark_all_read" rel="tooltip" title="<?= __d('gl','Mark All as Read')?>" style='color:#d2d4d5'></i>
        </div>
        <?= __d('gl', "すべてのお知らせ") ?>
    </div>
    <div class="panel-body panel-body-notify-page">
        <ul class="notify-page-cards" role="menu">
            <?=
            $this->element('Notification/notify_items', ['user' => $notify_items, 'location_type' => 'page']) ?>
        </ul>
        <?php if ($isExistMoreNotify): ?>
            <div class="panel-read-more-body">
                <span class="none" id="ShowMoreNoData"><?= __d('gl', "これ以上のデータがありません。") ?></span>
                <a id="FeedMoreReadLink" href="#" class="btn btn-link font_bold click-notify-read-more-page"
                   get-url="<?=
                   $this->Html->url(['controller' => 'notifications', 'action' => 'ajax_get_old_notify_more']) ?>"
                    >
                    <?= __d('gl', "もっと見る ▼") ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- END app/View/Notifications/index.ctp -->
