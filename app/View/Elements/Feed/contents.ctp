<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 7/4/14
 * Time: 10:33 AM
 *
 * @var CodeCompletionView    $this
 * @var                       $posts
 * @var                       $current_circle
 * @var                       $circle_member_count
 * @var                       $feed_more_read_url
 * @var                       $feed_filter
 * @var                       $circle_status
 * @var                       $user_status
 */
?>
<!-- START app/View/Elements/Feed/contents.ctp -->

<?php
if (isset($user_status)) {
    if (isset($params['action']) && $params['controller'] == 'posts' && $params['action'] == 'feed' && ($user_status == 'joined' || $user_status == 'admin')) {
        ?>
        <?= $this->element("Feed/common_form") ?>
    <?php }
}
else { ?>
    <?= $this->element("Feed/common_form") ?>
<?php } ?>
<div class="feed-share-range">
    <div class="panel-body ptb_10px plr_11px">
        <div class="col col-xxs-12 font_12px">
            <?php if ($feed_filter == "all"): ?>
                <span class="feed-current-filter"><?= __d('gl', 'すべて') ?></span>
            <?php else: ?>
                <?= $this->Html->link(__d('gl', 'すべて'), "/", ['class' => 'font_lightgray']) ?>
            <?php endif; ?>
            <span> ･ </span>
            <?php if ($feed_filter == "goal"): ?>
                <span class="feed-current-filter"><?= __d('gl', 'ゴール') ?></span>
            <?php else: ?>
                <?= $this->Html->link(__d('gl', 'ゴール'),
                                      ['controller' => 'posts', 'action' => 'feed', 'filter_goal' => true],
                                      ['class' => 'font_lightgray']) ?>
            <?php endif; ?>
            <?php if ($current_circle): ?>
                <span> ･ </span>
                <span class="feed-current-filter"><?= mb_strimwidth(h($current_circle['Circle']['name']), 0, 29,
                                                                    '...') ?></span>
                <a href="<?= $this->Html->url(['controller' => 'circles', 'action' => 'ajax_get_circle_members', 'circle_id' => $current_circle['Circle']['id']]) ?>"
                     class="modal-ajax-get"> <span class="feed-circle-user-number"><i
                            class="fa fa-user"></i>&nbsp;<?= $circle_member_count ?>
                    </span></a>
                <?php if ($user_status != 'admin') { ?>
                    <div class="pull-right header-function dropdown">
                        <a id="download" data-toggle="dropdown"
                           class="font_lightGray-gray"
                           href="#" style="opacity: 0.54;">
                            <i class="fa fa-cog header-function-icon"
                               style="color: rgb(80, 80, 80); opacity: 0.88;"></i>
                            <i class="fa fa-caret-down goals-column-fa-caret-down header-function-icon"
                               style="color: rgb(80, 80, 80); opacity: 0.88;"></i>
                        </a>
                        <ul aria-labelledby="dropdownMenu1" role="menu"
                            class="dropdown-menu dropdown-menu-right frame-arrow-icon">
                            <?php if (!$current_circle['Circle']['team_all_flg']): ?>
                                <li>
                                    <?php if ($user_status != 'joined') { ?>
                                        <a href="<?= $this->Html->url(['controller' => 'posts', 'action' => 'join_circle', 'circle_id' => $current_circle['Circle']['id']]) ?>">
                                            <?= __d('gl', 'Join Circle') ?></a>
                                    <?php }
                                    else { ?>
                                        <a href="<?= $this->Html->url(['controller' => 'posts', 'action' => 'unjoin_circle', 'circle_id' => $current_circle['Circle']['id']]) ?>">
                                            <?= __d('gl', 'Leave Circle') ?></a>
                                    <?php } ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($user_status == 'joined'): ?>
                                <li>
                                    <?php if ($circle_status == '1') {
                                        echo $this->Html->link(__d('gl', 'Hide'),
                                                               ['controller' => 'posts', 'action' => 'circle_toggle_status', 'circle_id' => $current_circle['Circle']['id'], 0]);
                                    }
                                    else {
                                        echo $this->Html->link(__d('gl', 'Show'),
                                                               ['controller' => 'posts', 'action' => 'circle_toggle_status', 'circle_id' => $current_circle['Circle']['id'], 1]);
                                    } ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php } ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<a href="" class="alert alert-info feed-notify-box" role="alert" style="margin-bottom:5px;display:none;opacity:0;">
    <span class="num"></span><?= __d('gl', "件の新しい投稿があります。") ?></a>
<?php if ($current_circle && $user_status != 'admin'): ?>
    <?php if ($user_status != 'joined') { ?>
        <div class="panel panel-default">
            <div class="panel-body ptb_10px plr_11px ">
                <div class="col col-xxs-12">

                    Join this Circle to post or comment.
                    <a href="<?= $this->Html->url(['controller' => 'posts', 'action' => 'join_circle', 'circle_id' => $current_circle['Circle']['id']]) ?>"
                       class='btn btn-primary pull-right'>
                        <i class="fa fa-user-plus"></i> <?= __d('gl', 'Join Circle') ?>
                    </a>
                </div>
            </div>
        </div>
        <?php if (!empty($current_circle['Circle']['description'])) : ?>
            <div class="panel panel-default">
                <h4 style='margin-left:15px;font-weight:bold'>About </h4>

                <div class="panel-body ptb_10px plr_11px ">
                    <?= $current_circle['Circle']['description']; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php } ?>
<?php endif; ?>
<?= $this->element("Feed/posts") ?>
<?php //ポストが存在する　かつ　パーマリンクでない
if (!isset($this->request->params['post_id']) || empty($this->request->params['post_id'])):
    ?>
    <?php $next_page_num = 2;
    $month_index = 0;
    $more_read_text = __d('gl', "もっと読む ▼");
    $oldest_post_time = 0;
    if ((count($posts) != POST_FEED_PAGE_ITEMS_NUMBER)) {
        $next_page_num = 1;
        $month_index = 1;
        $more_read_text = __d('gl', "さらに投稿を読み込む ▼");
    }

    // 読み込んだ最後の投稿
    // 次回 Ajax リクエストの際はこの投稿の更新時間より前の投稿のみを読み込む
    // ホームフィードでは created、その他では modified を使用する
    $last_post = end($posts);
    $loaded_post_time = null;
    $loaded_post_time_type = null;

    // circle_feed ページの場合
    // サークル作成日以前の投稿は存在しないので読み込まない
    if (isset($current_circle) && $current_circle) {
        $oldest_post_time = $current_circle['Circle']['created'];
        if ($last_post) {
            $loaded_post_time = $last_post['Post']['modified'];
            $loaded_post_time_type = 'modified';
        }
    }
    // ホーム画面の場合
    // チーム作成日以前の投稿は存在しないので読み込まない
    elseif (isset($current_team) && $current_team) {
        $oldest_post_time = $current_team['Team']['created'];
        if ($last_post) {
            $loaded_post_time = $last_post['Post']['created'];
            $loaded_post_time_type = 'created';
        }
    }
    ?>
    <div class="panel panel-default feed-read-more" id="FeedMoreRead">
        <div class="panel-body panel-read-more-body">
            <span class="none" id="ShowMoreNoData"><?= __d('gl', "これ以上の投稿はありませんでした。") ?></span>
            <a href="#" class="btn btn-link click-feed-read-more"
               parent-id="FeedMoreRead"
               no-data-text-id="ShowMoreNoData"
               next-page-num="<?= $next_page_num ?>"
               month-index="<?= $month_index ?>"
               get-url="<?=
               $this->Html->url($feed_more_read_url) ?>"
               id="FeedMoreReadLink"
               oldest-post-time="<?= $oldest_post_time ?>"
               loaded-post-time="<?= $loaded_post_time ?>"
               loaded-post-time-type="<?= $loaded_post_time_type ?>"
                >
                <?= $more_read_text ?></a>
        </div>
    </div>
<?php endif; ?>
<!-- END app/View/Elements/Feed/contents.ctp -->
