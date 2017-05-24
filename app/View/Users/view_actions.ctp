<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 7/9/15
 * Time: 3:33 PM.
 *
 * @var CodeCompletionView
 * @var
 * @var
 * @var
 * @var
 * @var
 */
$namedParams = $this->request->params['named'];
$filterCommonUrl = "/users/view_actions/user_id:{$namedParams['user_id']}/page_type:{$namedParams['page_type']}"
?>
<?= $this->App->viewStartComment() ?>
<div class="user-view-actions col-sm-8 col-sm-offset-2">
    <?= $this->element('User/simplex_top_section') ?>
    <div class="user-view-actions-panel">
        <div class="view-actions-panel-wrap">
            <div class="view-actions-panel-filter">
                <a class="dropdown-toggle" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true"
                   aria-expanded="true">
                    <?= __('Term') ?>:&nbsp;<strong><?= $terms[$term_id] ?>&nbsp;<span
                            class="fa fa-angle-down ml_2px"></span></strong>
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                    <?php foreach ($terms as $id => $termText): ?>
                        <li><a href='<?= "$filterCommonUrl/term_id:$id" ?>'><?= $termText ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="view-actions-panel-filter">
                <a class="dropdown-toggle" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true"
                   aria-expanded="true">
                    <?= __('Goal') ?>:&nbsp;<strong><?= $goal_select_options[Hash::get($namedParams, 'goal_id')] ?>
                        &nbsp;<span class="fa fa-angle-down ml_2px"></span></strong>
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
                    <?php
                    foreach ($goal_select_options as $goalID => $goalName) { ?>
                        <?php if ($goalName == "_separator_"): ?>
                            <li role="separator" class="divider"></li>
                        <?php else: ?>
                            <li>
                                <a href='<?= "$filterCommonUrl/term_id:$term_id/goal_id:$goalID" ?>'><?= $goalName ?></a>
                            </li>
                        <?php endif; ?>
                    <?php }
                    unset($goalName);
                    unset($goalID);
                    ?>
                </ul>
            </div>
            <div class="view-actions-panel-btngroup-wrap">
                <div class="view-action-panel-filter-btngroup">
                    <?php if ($namedParams['page_type'] == 'list'): ?>
                        <a class="view-action-panel-filter-button"
                           href="<?= $this->Html->url(am($namedParams, ['page_type' => 'image'])) ?>">
                            <i class="fa fa-th-large link-dark-gray"></i>
                        </a>
                        <a class="view-action-panel-filter-button mod-active"
                           href="<?= $this->Html->url(am($namedParams, ['page_type' => 'list'])) ?>">
                            <i class="fa fa-reorder link-dark-gray"></i>
                        </a>
                    <?php elseif ($namedParams['page_type'] == 'image'): ?>
                        <a class="view-action-panel-filter-button mod-active"
                           href="<?= $this->Html->url(am($namedParams, ['page_type' => 'image'])) ?>">
                            <i class="fa fa-th-large link-dark-gray"></i>
                        </a>
                        <a class="view-action-panel-filter-button"
                           href="<?= $this->Html->url(am($namedParams, ['page_type' => 'list'])) ?>">
                            <i class="fa fa-reorder link-dark-gray"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        $item_num = POST_FEED_PAGE_ITEMS_NUMBER;
        if ($namedParams['page_type'] == 'image') {
            $item_num = MY_PAGE_CUBE_ACTION_IMG_NUMBER;
        }
        ?>
        <div class="profile-user-action-contents" id="UserPageContents">
            <?php if ($namedParams['page_type'] == 'list'): ?>
                <?= $this->element('Feed/posts') ?>
            <?php elseif ($namedParams['page_type'] == 'image'): ?>
        <?php if (count($posts) == 0): ?>
            <div class="cube-img-column-frame add-action mod-only">
                <h3><?= __("You haven't created any actions&hellip; yet.") ?></h3>
                <?php else: ?>
                <div class="cube-img-column-frame add-action">
                    <?php endif; ?>
                    <div class="profile-user-action-contents-add-image">
                        <span><a href="/goals/add_action/goal_id:<?= Hash::get($namedParams, 'goal_id') ?>">+</a></span>
                    </div>
                    <a href="/goals/add_action/"><?= __('Add Action') ?></a>
                </div>
                <?= $this->element('cube_img_blocks') ?>
                <?php endif; ?>
            </div>
            <?php //投稿が指定件数　もしくは　アイテム作成日から１ヶ月以上経っている場合
            if (count($posts) == $item_num || $item_created < REQUEST_TIMESTAMP - MONTH): ?>

                <div class="panel-body">
                    <?php
                    $next_page_num = 2;
                    $month_index = 0;
                    $more_read_text = __('More...');
                    if ((count($posts) != $item_num)) {
                        $next_page_num = 1;
                        $month_index = 1;
                        $more_read_text = __('View more');
                    }
                    ?>
                    <div class="panel panel-default feed-read-more" id="FeedMoreRead">
                        <div class="panel-body panel-read-more-body">
                            <span class="none" id="ShowMoreNoData"><?= __('There is no further action.') ?></span>
                            <a href="#" class="click-feed-read-more"
                               onclick="Page.action_resize()"
                               parent-id="FeedMoreRead"
                               no-data-text-id="ShowMoreNoData"
                               next-page-num="<?= $next_page_num ?>"
                               month-index="<?= $month_index ?>"
                               get-url="<?=
                               $this->Html->url([
                                   'controller'     => 'posts',
                                   'action'         => 'ajax_get_user_page_post_feed',
                                   'user_id'        => Hash::get($namedParams, 'user_id'),
                                   'author_id'      => Hash::get($namedParams, 'user_id'),
                                   'goal_id'        => Hash::get($namedParams, 'goal_id'),
                                   'type'           => Post::TYPE_ACTION,
                                   'page_type'      => Hash::get($namedParams, 'page_type'),
                                   'base_timestamp' => $apiBaseTimestamp,
                               ]) ?>"
                               id="FeedMoreReadLink"
                               append-target-id="UserPageContents"
                               oldest-post-time="<?= $oldest_post_time ?>"
                            >
                                <?= h($more_read_text) ?> </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?= $this->App->viewEndComment() ?>
