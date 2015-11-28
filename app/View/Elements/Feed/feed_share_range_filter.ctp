<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 8/3/15
 * Time: 12:07 PM
 *
 * @var CodeCompletionView $this
 * @var                    $current_circle
 * @var                    $user_status
 * @var                    $circle_member_count
 * @var                    $circle_status
 * @var                    $feed_filter
 */
?>
<div class="feed-share-range">
    <div class="panel-body ptb_10px plr_11px">
        <?php if ($current_circle): ?>
            <div class="col col-xxs-12 font_12px">
                <?= $this->element('Feed/circle_filter_menu',
                                   compact('current_circle', 'user_status', 'circle_member_count', 'circle_status')
                ) ?>
                <span class="font_verydark">
                    <?= __d('gl', '・') ?>
                    <?php if ($current_circle['Circle']['public_flg']): ?>
                        <i class="fa fa-unlock font_14px"></i>
                    <?php else: ?>
                        <i class="fa fa-lock font_14px"></i>
                    <?php endif ?>
                </span>
            </div>
            <div class="col col-xxs-12 font_14px mtb_3px">
                <i class="fa fa-th-list"></i>
                <?php if ($this->request->params['action'] == "feed"): ?>
                    <span class="feed-current-filter"><?= __d('gl', 'フィード') ?></span>
                <?php else: ?>
                    <?= $this->Html->link(__d('gl', 'フィード'),
                                          ['action' => 'feed', 'circle_id' => $current_circle['Circle']['id']],
                                          ['class' => 'font_lightgray']) ?>
                <?php endif; ?>
                <span>&nbsp;|&nbsp;</span>
                <i class="fa fa-file-o"></i>
                <?php if ($this->request->params['action'] == "attached_file_list"): ?>
                    <span class="feed-current-filter"><?= __d('gl', 'ファイル') ?></span>
                <?php else: ?>
                    <?= $this->Html->link(__d('gl', 'ファイル'),
                                          ['action' => 'attached_file_list', 'circle_id' => $current_circle['Circle']['id']],
                                          ['class' => 'font_lightgray']) ?>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </div>
</div>
