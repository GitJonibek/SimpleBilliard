<?php /**
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @var string             $title_for_layout
 * @var CodeCompletionView $this
 * @var                    $action_name
 */
?>
<!-- START app/View/Layouts/default.ctp -->
<!DOCTYPE html>
<!--suppress ALL -->
<html lang="ja">
<?= $this->element('head') ?>
<body class="body">
<?php if (extension_loaded('newrelic')) {
    /** @noinspection PhpUndefinedFunctionInspection */
    echo newrelic_get_browser_timing_header();
} ?>
<?= $this->element('google_tag_manager') ?>
<?php if ($this->Session->read('Auth.User.id')) {
    echo $this->element('header_logged_in');
}
else {
    echo $this->element('header_not_logged_in');
}
?>
<?php if ($this->request->params['action'] === 'display') {
    echo $this->element('header_sp_feeds_alt');
} ?>
<div id="container" class="container">
    <div class="row">
        <div class="col-md-2 col-sm-4 col-xs-4 hidden-xxs layout-sub">
            <div class="left-side-container" id="js-left-side-container">
                <?= $this->element('dashboard_profile_card') ?>
                <?= $this->element('circle_list') ?>
            </div>
        </div>
        <div class="col-md-6 col-xs-8 col-xxs-12 layout-main" role="main">
            <?= $this->Session->flash(); ?>
            <!-- Remark -->
            <?= $this->fetch('content'); ?>
            <!-- /Remark -->
        </div>
        <div class="col-md-4 visible-md visible-lg col-xs-8 col-xxs-12 layout-goal" role="goal_area">
            <?= $this->element('my_goals_area') ?>
        </div>
    </div>
</div>
<?= $this->element('common_modules') ?>

<?= $this->element('modals') ?>
<!-- START fetch modal -->
<?= $this->fetch('modal') ?>
<!-- END fetch modal -->
<?= $this->element('gl_common_js') ?>
<!-- START fetch script -->
<?= $this->fetch('script') ?>
<!-- END fetch script -->
<?php if (extension_loaded('newrelic')) {
    /** @noinspection PhpUndefinedFunctionInspection */
    echo newrelic_get_browser_timing_footer();
} ?>
</body>
</html>
<!-- END app/View/Layouts/default.ctp -->
