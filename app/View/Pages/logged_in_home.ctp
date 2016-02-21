<?php /**
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Pages
 * @since         CakePHP(tm) v 0.10.0.1076
 * @var CodeCompletionView $this
 */
?>
<!-- START app/View/Pages/logged_in_home.ctp -->
<?php if ($this->Session->read('current_team_id')): ?>
    <?= $this->element('Feed/contents') ?>
<?php else: ?>
    <?= $this->Html->link(__d\('app', "チームを作成してください。"), ['controller' => 'teams', 'action' => 'add']) ?>
<?php endif; ?>
<!-- END app/View/Pages/logged_in_home.ctp -->
