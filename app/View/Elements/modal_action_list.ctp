<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 6/6/14
 * Time: 3:19 PM
 *
 * @var CodeCompletionView $this
 * @var                    $goal
 * @var                    $priority_list
 * @var                    $posts
 * @var                    $feed_more_read_url
 */
?>
<!-- START app/View/Elements/modal_action_list.ctp -->
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close font_33px close-design" data-dismiss="modal" aria-hidden="true">
                <span class="close-icon">&times;</span></button>
            <h4 class="modal-title"><?= __d('app', "アクションリスト") ?></h4>
        </div>
        <div class="modal-body" id="ActionListBody">
            <?php if (!empty($posts)): ?>
                <?php if (isset($posts[0]['Goal']['id']) && $posts[0]['Goal']['id']): ?>
                    <!--START Goal Post Header -->
                    <div class="panel panel-default">
                        <div class="panel-body pt_10px plr_11px pb_8px bd-b">
                            <div class="col col-xxs-12">
                                <div class="pull-right">
                                    <a href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'ajax_get_goal_description_modal', 'goal_id' => $posts[0]['Goal']['id']]) ?>"
                                       class="no-line font_verydark modal-ajax-get">
                                        <?=
                                        $this->Html->image('ajax-loader.gif',
                                                           [
                                                               'class'         => 'lazy media-object',
                                                               'data-original' => $this->Upload->uploadUrl($posts[0],
                                                                                                           "Goal.photo",
                                                                                                           ['style' => 'small']),
                                                               'width'         => '32px',
                                                               'error-img'     => "/img/no-image-link.png",
                                                           ]
                                        )
                                        ?>
                                    </a>
                                </div>
                                <div class="ln_contain w_88per">
                                    <a href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'ajax_get_goal_description_modal', 'goal_id' => $posts[0]['Goal']['id']]) ?>"
                                       class="no-line font_verydark modal-ajax-get">
                                        <i class="fa fa-flag font_gray"></i>&nbsp;<?= h($posts[0]['Goal']['name']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--END Goal Post Header -->
                <?php endif; ?>
            <?php endif; ?>
            <?= $this->element("Feed/posts", ['without_header' => true, 'without_add_comment' => true]) ?>
            <?php if (count($posts) == POST_FEED_PAGE_ITEMS_NUMBER): ?>
                <div class="panel panel-default feed-read-more" id="ActionListMoreRead">
                    <div class="panel-body panel-read-more-body">
                        <span class="none" id="ActionShowMoreNoData"><?= __d('app', "これ以上のデータがありません。") ?></span>
                        <a href="#" class="btn btn-link click-feed-read-more"
                           parent-id="ActionListMoreRead"
                           id="ActionListMoreReadLink"
                           next-page-num="2"
                           no-data-text-id="ActionShowMoreNoData"
                           get-url="<?=
                           $this->Html->url($feed_more_read_url) ?>"
                            >
                            <?= __d('app', "もっと読む ▼") ?></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default"
                    data-dismiss="modal"><?= __d('app', "閉じる") ?></button>
        </div>
    </div>
</div>
<!-- END app/View/Elements/modal_action_list.ctp -->
