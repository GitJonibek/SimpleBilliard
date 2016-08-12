<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 5/22/14
 * Time: 6:47 PM
 *
 * @var CodeCompletionView $this
 * @var                    $current_circle
 * @var                    $my_member_status
 * @var                    $is_mb_app
 * @var                    $notify_setting
 * @var                    $unread_msg_post_ids
 */
?>
<!-- START app/View/Elements/gl_common_js.ctp -->
<?php
echo $this->Html->script('ng_vendors.min');
echo $this->Html->script('vendors.min');
echo $this->Html->script('goalous.min');
echo $this->Html->script('ng_app.min');
?>

<!--suppress JSDuplicatedDeclaration -->
<?= $this->element('cake_variables') ?>

<?= $this->Session->flash('click_event') ?>
<?php echo $this->Session->flash('pnotify');
//環境を識別できるようにリボンを表示
?>
<?php if (ENV_NAME == "stg"): ?>
    <p class="ribbon ribbon-staging">Staging</p>
<?php elseif (ENV_NAME == "hotfix"): ?>
    <p class="ribbon ribbon-hotfix">Hotfix</p>
<?php elseif (ENV_NAME == "local"): ?>
    <p class="ribbon ribbon-local">Local</p>
<?php endif; ?>
<!-- END app/View/Elements/gl_common_js.ctp -->
