<?php
/**
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Pages
 * @since         CakePHP(tm) v 0.10.0.1076
 * @var CodeCompletionView $this
 * @var                    $params
 * @var                    $current_circle
 * @var                    $user_status
 * @var                    $circle_member_count
 * @var                    $circle_status
 * @var                    $file_type_options
 * @var                    $circle_file_list_base_url
 * @var                    $files
 */
?>
<!-- START app/View/Posts/attached_file_list.ctp -->
<?php if ($this->Session->read('current_team_id')): ?>
    <?= $this->element('Feed/feed_share_range_filter',
                       compact('current_circle', 'user_status', 'circle_member_count', 'circle_status',
                               'feed_filter')) ?>
    <?php
    if (isset($user_status)) {
        if (viaIsSet($params['controller']) == 'posts' && viaIsSet($params['action']) == 'attached_file_list' && ($user_status == 'joined' || $user_status == 'admin')) {
            echo $this->element("Feed/common_form");
        }
    }
    else {
        echo $this->element("Feed/common_form");
    }
    ?>
    <a href="" class="alert alert-info feed-notify-box" role="alert" style="margin-bottom:5px;display:none;opacity:0;">
        <span class="num"></span><?= __d('gl', "件の新しい投稿があります。") ?></a>
    <div class="panel panel-default" id="CircleFiles">
        <?=
        $this->Form->input('file_type', [
            'label'        => false,
            'div'          => false,
            'required'     => true,
            'class'        => 'form-control disable-change-warning file-type-select font_12px',
            'id'           => 'SwitchFileType',
            'options'      => $file_type_options,
            'default'      => viaisset($this->request->params['named']['file_type']),
            'redirect-url' => $circle_file_list_base_url,
            'wrapInput' => 'circle-uploaded-files-type-select-wrap'
        ])
        ?>
        <?= $this->element('Feed/attached_files', ['files' => $files]) ?>
    </div>
    <?php
    $next_page_num = 2;
    $month_index = 0;
    $more_read_text = __d('gl', "もっとファイルを読み込む ▼");
    $oldest_post_time = 0;
    $item_num = FILE_LIST_PAGE_NUMBER;
    if ((count($files) != $item_num)) {
        $next_page_num = 1;
        $month_index = 1;
        $more_read_text = __d('gl', "さらにファイルを読み込む ▼");
    }

    // サークルの登録日以前の投稿は存在しないので読み込まないようにする
    if (isset($current_circle['Circle']['created']) && $current_circle['Circle']['created']) {
        $oldest_post_time = $current_circle['Circle']['created'];
    }
    ?>
    <div class="panel panel-default feed-read-more" id="FeedMoreRead">
        <div class="panel-body panel-read-more-body">
            <span class="none" id="ShowMoreNoData"><?= __d('gl', "これ以上のファイルはありませんでした。") ?></span>
            <a href="#" class="btn btn-link click-feed-read-more"
               parent-id="FeedMoreRead"
               no-data-text-id="ShowMoreNoData"
               next-page-num="<?= $next_page_num ?>"
               month-index="<?= $month_index ?>"
               get-url="<?=
               $this->Html->url(['controller' => 'posts',
                                 'action'     => 'ajax_get_circle_files',
                                 'circle_id'  => viaIsSet($this->request->params['named']['circle_id']),
                                 'file_type'  => viaIsSet($this->request->params['named']['file_type']),
                                ]) ?>"
               id="FeedMoreReadLink"
               append-target-id="CircleFiles"
               oldest-post-time="<?= $oldest_post_time ?>"
                >
                <?= $more_read_text ?></a>
        </div>
    </div>

    <?= $this->element('Feed/circle_join_button', compact('current_circle', 'user_status')) ?>
<?php else: ?>
    <?= $this->Html->link(__d('gl', "チームを作成してください。"), ['controller' => 'teams', 'action' => 'add']) ?>
<?php endif; ?>
<!-- END app/View/Posts/attached_file_list.ctp -->
