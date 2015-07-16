<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 5/28/14
 * Time: 5:04 PM
 *
 * @var CodeCompletionView $this
 * @var                    $title_for_layout string
 * @var                    $nav_disable
 * @var array              $my_teams
 * @var                    $current_global_menu
 * @var                    $my_member_status
 * @var                    $is_evaluation_available
 * @var                    $evaluable_cnt
 * @var                    $unapproved_cnt
 * @var                    $all_alert_cnt
 */
?>
<!-- START app/View/Elements/header_logged_in.ctp -->
<header class="header">
    <div class="navbar navbar-fixed-top navbar-default gl-navbar" id="header">
        <button type="button" class="navbar-toggle hamburger header-toggle-icon" data-toggle="offcanvas" data-target=".navbar-offcanvas">
            <i class="fa fa-navicon toggle-icon"></i>
        </button>
        <div class="nav-container header-container">
            <div class="navbar-offcanvas offcanvas navmenu-fixed-left top_50px">
                <ul class="nav navbar-nav">
                    <li class="mtb_5px mtb-sm_0">
                        <a class="header-logo header_l-icons hoverPic <?= $current_global_menu == "home" ? "activeColumn" : null ?>"
                           href="<?= $this->Html->url('/') ?>"><!--suppress HtmlUnknownTarget -->
                            <div class="ta-sm_c">
                                <img src="<?= $this->Html->url('/img/logo_off.png') ?>" class="header-logo-img"
                                     alt="Goalous2.0" width="20px" height="20px">
                                <p class="font_11px font_heavyGray header_icon-text hidden-xs header-link">
                                    <?= __d('gl', "ホーム") ?>
                                </p>
                                <span class="visible-xs-inline va_bl ml_5px"><?= __d('gl', "ホーム") ?></span>
                            </div>
                        </a>
                    </li>
                    <li class="mtb_5px mtb-sm_0">
                        <a class="header-goal header_l-icons <?= $current_global_menu == "goal" ? "activeColumn" : null ?>"
                           href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'index']) ?>">
                            <div class="ta-sm_c">
                                <i class="fa fa-flag header-link header-icon nav-xxs-icon"></i>
                                <p class="font_11px header_icon-text hidden-xs header-link">
                                    <?= __d('gl', "ゴール") ?>
                                </p>
                                <span class="visible-xs-inline ml_5px"><?= __d('gl', "ゴール") ?></span>
                            </div>
                        </a>
                    </li>
                    <li class="mtb_5px mtb-sm_0">
                        <a class="header-team header_l-icons <?= $current_global_menu == "team" ? "activeColumn" : null ?>"
                           href="<?= $this->Html->url(['controller' => 'teams', 'action' => 'main']) ?>">
                            <div class="ta-sm_c">
                                <i class="fa fa-users header-link header-icon nav-xxs-icon"></i>
                                <p class="font_11px header_icon-text hidden-xs header-link">
                                    <?= __d('gl', "チーム") ?>
                                </p>
                                <span class="visible-xs-inline ml_5px"><?= __d('gl', "チーム") ?></span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <form class="nav-form-group" role="search">
                            <?php echo $this->Form->input('current_team',
                                                          array(
                                                              'type'      => 'select',
                                                              'options'   => !empty($my_teams) ? $my_teams : [__d('gl',
                                                                                                                  'チームがありません')],
                                                              'value'     => $this->Session->read('current_team_id'),
                                                              'id'        => 'SwitchTeam',
                                                              'label'     => false,
                                                              'div'       => false,
                                                              'class'     => 'form-control nav-team-select font_12px disable-change-warning',
                                                              'wrapInput' => false,
                                                          ))
                            ?>
                        </form>
                    </li>
                    <li class="header-search-group">
                        <form class="nav-form-group" role="search">
                            <i class="fa fa-search nav-form-icon"></i>
                            <input type="text"
                                   class="form-control nav-search font_12px disable-change-warning develop--search"
                                   placeholder="Search">
                        </form>
                    </li>
                    <li class="circle-list-in-hamburger visible-xxs hidden-xs">
                        <?= $this->element('circle_list_in_hamburger') ?>
                    </li>
                </ul>
            </div>
            <div class="navbar-header navbar-right">
                <div class="pull-right nav-icons">
                    <div class="header-dropdown-user">
                        <a href="#"
                           class="dropdown-toggle me-menu-image font_verydark no-line header-user-profile pull-right"
                           data-toggle="dropdown"
                           id="download">
                            <?=
                            $this->Upload->uploadImage($this->Session->read('Auth'), 'User.photo', ['style' => 'small'],
                                                       ['width' => '26px', 'height' => '26px', 'alt' => 'icon', 'class' => 'header-nav-avator pull-left img-circle mtb_3px']) ?>
                            <span
                                class="header-user-name font_11px hidden-xxs header-home header-link pr_5px mlr_5px ptb_5px bd-r"><?= $this->Session->read('Auth.User.display_first_name') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right frame-arrow-pic" aria-labelledby="download">
                            <li class="text-align_c"><?= __d('gl', "準備中") ?></li>

                        </ul>
                    </div>
                    <a href="<?= $this->Html->url('/') ?>" class="header-home header-link">
                        <?= __d('gl', "ホーム") ?>
                    </a>
                    <div class="header-dropdown-add">
                        <a href="#" data-toggle="dropdown" id="download" class="btn-addition-header">
                            <i class="header-dropdown-icon-add fa fa-plus-circle header-link"></i>
                        </a>
                        <ul class="header-nav-add-contents dropdown-menu dropdown-menu-right" aria-labelledby="download">
                            <?php if ($this->Session->read('current_team_id')): ?>
                                <li><a href="<?= $this->Html->url(['controller' => 'goals', 'action' => 'add']) ?>">
                                        <i class="fa fa-flag header-drop-icons"></i>
                                        <span class="font_verydark"><?= __d('gl', 'ゴールを作成') ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" data-toggle="modal" data-target="#modal_add_circle">
                                        <i class="fa fa-circle-o header-drop-icons"></i>
                                        <span class="font_verydark"><?= __d('gl', "サークルを作成") ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $this->Html->url(['controller' => 'teams', 'action' => 'add_group_vision']) ?>">
                                        <i class="fa fa-plane header-drop-icons"></i>
                                        <span class="font_verydark"><?= __d('gl', "グループビジョンを作成") ?></span>
                                    </a>
                                </li>
                                <?php if ($my_member_status['TeamMember']['admin_flg']): ?>
                                    <li>
                                        <a href="<?= $this->Html->url(['controller' => 'teams', 'action' => 'add_team_vision']) ?>">
                                            <i class="fa fa-rocket header-drop-icons"></i>
                                            <span class="font_verydark"><?= __d('gl', "チームビジョンを作成") ?></span>
                                        </a>
                                    </li>
                                <? endif; ?>
                            <?php endif; ?>
                            <li>
                                <a href="<?= $this->Html->url(['controller' => 'teams', 'action' => 'add']) ?>">
                                    <i class=" fa fa-users header-drop-icons"></i>
                                    <span class="font_verydark"><?= __d('gl', 'チームを作成') ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="header-dropdown-message dropdown-menu-right develop--forbiddenLink">
                      <a class="btn-message-header" href="#">
                          <i class="header-dropdown-icon-message fa fa-paper-plane-o header-link"></i>
                      </a>
                    </div>
                    <div class="header-dropdown-notify dropdown-menu-right">
                        <a id="click-header-bell" class="btn-notify-header" data-toggle="dropdown" href="#">
                            <i class="header-dropdown-icon-notify fa fa-flag fa-bell-o header-drop-icons header-link"></i>
                            <div class="btn btn-xs bell-notify-box notify-bell-numbers"
                                 id="bellNum" style="opacity: 0;">
                                <span>0</span><sup class="notify-plus none">+</sup>
                            </div>
                        </a>
                        <div class="frame-arrow-notify dropdown-menu dropdown-menu-right notify-dropdown-area">
                            <ul class="header-nav-notify-contents" id="bell-dropdown" role="menu">
                                <li class="notify-card-empty" id="notifyCardEmpty">
                                    <i class="fa fa-smile-o font_33px mr_8px"></i><span
                                        class="notify-empty-text"><?= __d('gl', "未読の通知はありません。") ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="header-dropdown-functions dropdown-menu-right header-function nav-icons">
                        <a href="#"
                           class="font_lightGray-gray btn-function-header"
                           data-toggle="dropdown"
                           id="download">
                            <i class="header-dropdown-icon-functions fa fa-cog header-function-icon header-drop-icons header-link"></i>
                            <?php if ($all_alert_cnt > 0): ?>
                                <div class="btn btn-xs notify-function-numbers">
                                 <span>
                                   <?= $all_alert_cnt ?>
                                 </span>
                                </div>
                            <?php endif; ?>
                        </a>
                        <ul class="header-nav-function-contents dropdown-menu dropdown-menu-right" role="menu"
                            aria-labelledby="dropdownMenu1">
                            <li>
                                <?= $this->Html->link(__d('gl', "ユーザ設定"),
                                                      ['controller' => 'users', 'action' => 'settings']) ?>
                            </li>
                            <li>
                                <a href="#" data-toggle="modal" data-target="#modal_tutorial">
                                    <?= __d('gl', "チュートリアル") ?>
                                </a>
                            </li>
                            <li>
                                <?php if (isset($unapproved_cnt) === true && $unapproved_cnt > 0) { ?>
                                    <div class="btn btn-danger btn-xs sub_cnt_alert">
                                        <?php echo $unapproved_cnt; ?>
                                    </div>
                                <?php } ?>
                                <?= $this->Html->link(__d('gl', "ゴール認定"),
                                                      ['controller' => 'goal_approval', 'action' => 'index']) ?>
                            </li>
                            <li><?=
                                $this->Html->link(__d('gl', "ログアウト"),
                                                  ['controller' => 'users', 'action' => 'logout']) ?></li>
                            <li class="divider"></li>
                            <?php if ($is_evaluation_available): ?>
                                <li>
                                    <?php if (viaIsSet($evaluable_cnt) && $evaluable_cnt > 0): ?>
                                        <div class="btn btn-danger btn-xs sub_cnt_alert"><?= $evaluable_cnt ?></div>
                                    <?php endif; ?>

                                    <?=
                                    $this->Html->link(__d('gl', '評価'),
                                                      ['controller' => 'evaluations', 'action' => 'index']) ?>
                                </li>
                            <?php endif; ?>
                            <?php //TODO 一時的にチーム管理者はチーム招待リンクを表示
                            if (viaIsSet($my_member_status['TeamMember']['admin_flg']) && $my_member_status['TeamMember']['admin_flg']):?>
                                <li>
                                    <?=
                                    $this->Html->link(__d('gl', 'チーム設定'),
                                                      ['controller' => 'teams', 'action' => 'settings']) ?>
                                </li>
                            <?php endif; ?>
                            <li><?=
                                $this->Html->link(__d('home', 'Blog'), 'http://blog.goalous.com/',
                                                  ['target' => '_blank']) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col col-xxs-12 hidden-md hidden-lg sp-feed-alt height_40px" id="SubHeaderMenu">
        <div class="col col-xxs-6 text-align_r">
            <a class="font_lightGray-veryDark no-line plr_18px sp-feed-link inline-block pt_12px height_40px sp-feed-active"
               id="SubHeaderMenuFeed">
                <?= __d('gl', "ニュースフィード") ?>
            </a>
        </div>
        <div class="col col-xxs-6">
            <a class="font_lightGray-veryDark no-line plr_18px sp-feed-link inline-block pt_12px height_40px"
               id="SubHeaderMenuGoal">
                <?= __d('gl', "関連ゴール") ?>
            </a>
        </div>
    </div>
</header>
<!-- END app/View/Elements/header_logged_in.ctp -->
