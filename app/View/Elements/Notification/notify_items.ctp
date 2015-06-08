<?php
/**
 * Created by PhpStorm.
 * User: saeki
 * Date: 15/04/27
 * Time: 16:57
 *
 * @var $notify_items
 * @var $location_type
 */
?>

<!-- START app/View/Elements/Notification/notify_items.ctp -->

<?php foreach ($notify_items as $notify_item): ?>
    <?=
    $this->element('Notification/notify_item',
                   ['user' => viaIsSet($notify_item['User']), 'notification' => $notify_item['Notification'], 'location_type' => $location_type]) ?>
<?php endforeach; ?>

<!-- END app/View/Elements/Notification/notify_items.ctp -->
