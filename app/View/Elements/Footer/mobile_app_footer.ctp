<?php
$footerMenuList = [
    [
        'name' => 'home',
        'url'  => '/',
        'icon' => '',
        'label' => 'Home'

    ],
    [
        'name' => 'messages',
        'url'  => '/topics',
        'icon' => 'send',
        'label' => 'Message'
    ],
    [
        'name' => 'circles',
        'url'  => '/circles',
        'icon' => 'group_work',
        'label' => 'Circles'
    ],
    [
        'name' => 'notifications',
        'url'  => '/notifications',
        'icon' => 'notifications',
        'label' => 'Notifications'
    ],
    [
        'name' => 'more',
        'url'  => '/others',
        'icon' => 'dehaze',
        'label' => 'More'
    ],
];

$badgeCounts = [
    'messages' => $new_notify_message_cnt,
    'notifications' => Router::url() === '/notifications' ? 0 : $new_notify_cnt,
    'circles' => 0, // TODO.Renewal: set
    'more' => 0, // TODO.Renewal: set
]
?>

<footer class="mobile-app-footer">
    <ul class="mobile-app-footer-list">
        <?php foreach ($footerMenuList as $menu): ?>
            <li class="mobile-app-footer-list-item">
                <a href="<?= $menu['url']?>" class="mobile-app-footer-list-item-link <?= Router::url() === $menu['url'] ? 'active' : '' ?>">
                    <?php if($menu['name'] === 'home') :?>
                        <div class="material-icons mod-feed"></div>
                    <?php else: ?>
                        <i class="material-icons"><?= $menu['icon']?></i>
                    <?php endif;?>
                    <span class=""><?= $menu['label']?></span>
                </a>
                <?php if(in_array($menu['name'], ['messages', 'notifications', 'circles', 'more'], false)) :?>
                    <div class="btn btn-xs notify-function-numbers <?= $menu['name'] === 'circles'? 'mod-small' : ''?> js-mbAppFooter-setBadgeCnt-<?=$menu['name']?> <?= empty($badgeCounts[$menu['name']]) ? 'hidden' : '' ?>">
                         <span class="<?= $badgeCounts[$menu['name']] > 99 ? 'oval' : ''?>">
                           <?= $badgeCounts[$menu['name']] ?>
                         </span>
                    </div>
                <?php endif;?>
            </li>
        <?php endforeach; ?>
    </ul>
</footer>
