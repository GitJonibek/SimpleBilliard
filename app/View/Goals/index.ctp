<?php /**
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Pages
 * @since         CakePHP(tm) v 0.10.0.1076
 * @var CodeCompletionView $this
 * @var                    $is_admin
 */
?>
<!-- START app/View/Goals/index.ctp -->
<?php if ($is_admin): ?>
    <div class="panel panel-default feed-share-range">
        <div class="panel-body ptb_10px plr_11px">
            <div class="col col-xxs-12 font_12px">
                <?= $this->Form
                    ->postLink("<i class='fa fa-download'></i> " . __d('gl', 'CSVの書き出し'),
                               [
                                   'action' => 'download_all_goal_csv',
                               ],
                               [
                                   'class'  => 'pull-right font_verydark',
                                   'escape' => false,
                               ]
                    );
                ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="col col-xxs-12 goals-feed-head">
            <span class="font_14px goals-column-title"><?= __d('gl', 'みんなのゴール') ?></span>
        </div>
        <div class="goal-search-menu">
            <div class="goal-term-search-menu btn-group btn-group-justified" role="group">
                <?php foreach ($search_options['term'] as $key => $val): ?>
                    <?php if ($val == $search_option['term'][1]): ?>
                        <a href="<?= $this->Html->url(array_merge($search_url, ['term' => $key])) ?>"
                           class="btn btn-default goal-search-elm selected" role="button"><?= $val ?></a>
                    <?php else: ?>
                        <a href="<?= $this->Html->url(array_merge($search_url, ['term' => $key])) ?>"
                           class="btn btn-default goal-search-elm" role="button"><?= $val ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="goal-filter-menu btn-group btn-group-justified" role="group">
                <div class=" btn-group" role="group">
                    <a href="#" class="btn btn-default goal-filter-elm dropdown-toggle" data-toggle="dropdown"
                       role="button" aria-expanded="false">
                        <span class="caret goal-menu-caret"></span>
                        <span class="goal_type_name"><?= $search_option['category'][1] ?></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <?php foreach ($search_options['category'] as $key => $val): ?>
                            <li><a href="<?= $this->Html->url(array_merge($search_url,
                                                                          ['category' => $key])) ?>"><?= $val ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="btn-group" role="group">
                    <a href="#" class="btn btn-default goal-filter-elm dropdown-toggle" data-toggle="dropdown"
                       role="button" aria-expanded="false">
                        <span class="caret goal-menu-caret"></span>
                        <span class="goal_type_name"><?= $search_option['progress'][1] ?></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <?php foreach ($search_options['progress'] as $key => $val): ?>
                            <li><a href="<?= $this->Html->url(array_merge($search_url,
                                                                          ['progress' => $key])) ?>"><?= $val ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="btn-group " role="group">
                    <a href="#" class="btn btn-default goal-filter-elm dropdown-toggle" data-toggle="dropdown"
                       role="button" aria-expanded="false">
                        <span class="caret goal-menu-caret"></span>
                        <span class="goal_type_name"><?= $search_option['order'][1] ?></span>

                    </a>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <?php foreach ($search_options['order'] as $key => $val): ?>
                            <li><a href="<?= $this->Html->url(array_merge($search_url,
                                                                          ['order' => $key])) ?>"><?= $val ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="goal-search-count">
            <p><?= __d('gl', "対象ゴール") ?><span><?= $goal_count ?><?= __d('gl', "件") ?></span></p>
        </div>
        <?php if (empty($goals)): ?>
            <div class="col col-xxs-12 mt_16px">
                <div class="alert alert-warning fade in" role="alert">
                    <?= __d('gl', "対象ゴールがありません。") ?>
                </div>
            </div>
        <?php else: ?>
            <?= $this->element('Goal/index_items') ?>
            <?php if (count($goals) == 50)://TODO 暫定的に300、いずれ20に戻す ?>
                <div class="panel-body panel-read-more-body" id="GoalMoreView">
                    <a id="FeedMoreReadLink" href="#" class="btn btn-link click-feed-read-more"
                       parent-id="GoalMoreView"
                       next-page-num="2"
                       month-index="1"
                       get-url="<?= $this->Html->url(['controller' => 'goals', 'action' => 'ajax_get_more_index_items']) ?>">
                        <?= __d('gl', "もっと見る ▼") ?></a>
                </div>
            <?php endif; ?>
        <?php endif ?>
    </div>
</div>
<!-- END app/View/Goals/index.ctp -->
