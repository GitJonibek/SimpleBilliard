<?php /**
 * PHP 5
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Pages
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @var CodeCompletionView $this
 * @var                    $user_count
 * @var                    $top_lang
 */
?>
<?php $this->append('meta') ?>
<?php
/*
Page毎に要素が変わるもの
- url
- title
*/
$ogp_law = [
    [
        "property" => "og:type",
        "content" => "website",
    ],
    [
        "property" => "og:title",
        "content" => __d('gl', '特定商取引法に基づく表記 | Goalous(ゴーラス)'),
    ],
    [
        "property" => "og:description",
        "content" =>__d('gl', 'Goalous(ゴーラス)は、チーム力向上のためのSNSです。Goalousを利用すれば、オープンでクリアな目標設定をしたり、ゴールへの活動内容を写真で共有したり、サークルやメッセンジャーで仲間たちとコミュニケーションをとったりできます。'),
    ],
    [
        "property" => "og:url",
        "content" => "https://www.goalous.com/law/",
    ],
    [
        "property" => "og:image",
        "content" => "https://www.goalous.com/img/homepage/background/promo-bg.jpg",
    ],
    [
        "property" => "og:site_name",
        "content" => __d('lp', 'Goalous (ゴーラス) │ゴール達成への最強にオープンな社内SNS'),
    ],
    [
        "property" => "fb:app_id",
        "content" => "966242223397117",
    ],
    [
        "name" => "twitter_card",
        "content" => "summary",
    ],
    [
        "name" => "twitter:site",
        "content" => "@goalous",
    ]
];
$num_ogp = count($ogp_law);
for($i = 0; $i < $num_ogp; $i++){
    echo $this->Html->meta($ogp_law[$i]);
}
?>
<title><?= __d('lp', '特定商取引法に基づく表記 | Goalous (ゴーラス)') ?></title>
<link rel="alternate" hreflang="ja" href="<?= $this->Html->url('/ja/law') ?>"/>
<link rel="alternate" hreflang="en" href="<?= $this->Html->url('/en/law') ?>"/>
<link rel="alternate" hreflang="x-default" href="<?= $this->Html->url('/law') ?>"/>
<?php $this->end() ?>
<!-- START app/View/Pages/law.ctp -->
<div id="markdown" src="../../composition/markdowns/<?=$short_lang?>_law.md" class="markdown-wrap"></div>
<!-- END app/View/Pages/law.ctp -->
