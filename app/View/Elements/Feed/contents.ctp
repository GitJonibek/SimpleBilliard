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
 * @var                       $params
 * @var                       $item_created
 */
?>
<!-- START app/View/Elements/Feed/contents.ctp -->
<?= $this->element('Feed/feed_share_range_filter',
                   compact('current_circle', 'user_status', 'circle_member_count', 'circle_status', 'feed_filter')) ?>
<?php
// 投稿単体ページでは入力フォームを表示しない
if (!isset($this->request->params['post_id'])) {
    if (isset($user_status)) {
        if (viaIsSet($params['controller']) == 'posts' && (viaIsSet($params['action']) == 'feed' || viaIsSet($params['action']) == 'ajax_circle_feed') && ($user_status == 'joined' || $user_status == 'admin')) {
            echo $this->element("Feed/common_form");
        }
    }
    else {
        echo $this->element("Feed/common_form");
    }
}
?>
<a href="" class="alert alert-info feed-notify-box" role="alert" style="margin-bottom:5px;display:none;opacity:0;">
    <span class="num"></span><?= __d\('app', "件の新しい投稿があります。") ?></a>

<?= $this->element('Feed/circle_join_button', compact('current_circle', 'user_status')) ?>
<?php
// 通知 -> 投稿単体ページ と遷移してきた場合は、通知一覧に戻るボタンを表示する
if (isset($this->request->params['post_id']) && isset($this->request->params['named']['notify_id'])): ?>
    <a href="#" get-url="<?= $this->Html->url(['controller' => 'notifications']) ?>" class="btn-back-notifications">
        <i class="fa fa-chevron-left font_18px font_lightgray lh_20px"></i>
    </a>
<?php endif ?>
<?php
// 投稿単体ページで $posts が空の場合（投稿が削除された場合）
if (isset($this->request->params['post_id']) && !$posts): ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <?= __d\('app', '存在しません。') ?>
        </div>
    </div>
<?php endif ?>
<div id="app-view-elements-feed-posts">
<?= $this->element("Feed/posts") ?>
</div>
<?php //(投稿が指定件数　もしくは　アイテム作成日から１ヶ月以上経っている)かつパーマリンクでない
if ((count($posts) == POST_FEED_PAGE_ITEMS_NUMBER || (isset($item_created) && $item_created < REQUEST_TIMESTAMP - (60 * 60 * 24 * 30))) &&
    (!isset($this->request->params['post_id']) || empty($this->request->params['post_id']))
):
    ?>
    <?php $next_page_num = 2;
    $month_index = 0;
    $more_read_text = __d\('app', "もっと読む ▼");
    $oldest_post_time = 0;
    if ((count($posts) != POST_FEED_PAGE_ITEMS_NUMBER)) {
        $next_page_num = 1;
        $month_index = 1;
        $more_read_text = __d\('app', "さらに投稿を読み込む ▼");
    }

    // １件目の投稿の更新時間
    // 次回 Ajax リクエスト時はこの投稿の更新時間より前の投稿のみを読み込む
    // （新着投稿による重複表示をふせぐため）
    // ホームフィードでは created、その他では modified を使用する
    $first_post = isset($posts[0]) ? $posts[0] : null;
    $post_time_before = null;

    // circle_feed ページの場合
    // サークル作成日以前の投稿は存在しないので読み込まない
    if (isset($current_circle) && $current_circle) {
        $oldest_post_time = $current_circle['Circle']['created'];
        if ($first_post) {
            $post_time_before = $first_post['Post']['modified'];
        }
    }
    // ホーム画面の場合
    // チーム作成日以前の投稿は存在しないので読み込まない
    elseif (isset($current_team) && $current_team) {
        $oldest_post_time = $current_team['Team']['created'];
        if ($first_post) {
            $post_time_before = $first_post['Post']['created'];
        }
    }
    ?>
    <div class="panel panel-default feed-read-more" id="FeedMoreRead">
        <div class="panel-body panel-read-more-body">
            <span class="none" id="ShowMoreNoData"><?= __d\('app', "これ以上の投稿はありませんでした。") ?></span>
            <a href="#" class="click-feed-read-more"
               parent-id="FeedMoreRead"
               no-data-text-id="ShowMoreNoData"
               next-page-num="<?= $next_page_num ?>"
               month-index="<?= $month_index ?>"
               get-url="<?=
               $this->Html->url($feed_more_read_url) ?>"
               id="FeedMoreReadLink"
               oldest-post-time="<?= $oldest_post_time ?>"
               post-time-before="<?= $post_time_before ?>"
                >
                <?= $more_read_text ?> </a>
        </div>
    </div>
<?php endif; ?>
<?php
// 通知 -> 投稿単体ページ と遷移してきた場合は、通知一覧に戻るボタンを表示する
if (isset($this->request->params['post_id']) && isset($this->request->params['named']['notify_id'])): ?>
    <a href="#" get-url="<?= $this->Html->url(['controller' => 'notifications']) ?>" class="btn-back-notifications">
        <i class="fa fa-chevron-left font_18px font_lightgray lh_20px"></i>
    </a>
<?php endif ?>
<!-- END app/View/Elements/Feed/contents.ctp -->
