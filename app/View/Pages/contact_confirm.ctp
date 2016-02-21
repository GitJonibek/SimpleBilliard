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
- meta description
- og:description
- og:url
- title
*/
$meta_contact_confirm = [
    [
        "name" => "description",
        "content" => __d('lp','Goalous (ゴーラス) は、ゴール達成への最強にオープンな社内SNS。すべてのメンバーのゴールをオープンにし、ゴールへのアクションを写真でたのしく共有できます。お問い合わせ内容の確認です。'),
    ],
    [
        "name" => "keywords",
        "content" => "目標管理,目標達成,社内SNS,評価,MBO",
    ],
    [
        "property" => "og:type",
        "content" => "website",
    ],
    [
        "property" => "og:title",
        "content" => __d('lp', '入力内容確認 | Goalous(ゴーラス)'),
    ],
    [
        "property" => "og:description",
        "content" =>__d('lp', 'Goalous (ゴーラス) は、ゴール達成への最強にオープンな社内SNS。すべてのメンバーのゴールをオープンにし、ゴールへのアクションを写真でたのしく共有できます。お問い合わせ内容の確認です。'),
    ],
    [
        "property" => "og:url",
        "content" => "https://www.goalous.com/contact_confirm/",
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
$num_ogp = count($meta_contact_confirm);
for($i = 0; $i < $num_ogp; $i++){
    echo $this->Html->meta($meta_contact_confirm[$i]);
}
?>
<title><?= __d('lp', '入力内容確認 | Goalous (ゴーラス)') ?></title>
<link rel="alternate" hreflang="ja" href="<?= $this->Html->url('/ja/contact_confirm') ?>"/>
<link rel="alternate" hreflang="en" href="<?= $this->Html->url('/en/contact_confirm') ?>"/>
<link rel="alternate" hreflang="x-default" href="<?= $this->Html->url('/contact_confirm') ?>"/>
<?php $this->end() ?>
<!-- START app/View/Pages/contact_confirm.ctp -->
<!-- ******CONTACT MAIN****** -->
<section id="contact-main" class="contact-main section">
    <div class="container text-center">
        <h2 class="title"><?= __d('lp', 'お問い合わせ内容確認') ?></h2>
        <p class="intro"><?= __d('lp', '内容をご確認のうえ、問題なければ送信をクリックしてください。') ?></p>
    </div><!--//container-->
</section>

<section class="container contact-form-section">
    <div class="row">
        <div class="col-md-8 col-md-offset-2 text-left">
            <form class="form-horizontal">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?= __d('lp', 'ご希望') ?></label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><?= h($data['want_text']) ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?= __d('lp', '会社名') ?></label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><?= h($data['company']) ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?= __d('lp', 'お名前') ?></label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><?= h($data['name']) ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?= __d('lp', 'メールアドレス') ?></label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><?= h($data['email']) ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?= __d('lp', 'お問い合わせ内容') ?></label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><?= nl2br(h($data['message'])) ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?= __d('lp', 'ご希望の営業担当者') ?></label>
                    <div class="col-sm-8">
                        <p class="form-control-static"><?= h($data['sales_people_text']) ?></p>
                    </div>
                </div>

                <a href="<?= $this->Html->url(['controller' => 'pages', 'action' => 'contact', 'lang' => $top_lang, 'from_confirm' => true]) ?>"
                   class="btn btn-block btn-cta-secondary"><?= __d('lp', '戻る') ?></a>
                <a href="<?= $this->Html->url(['controller' => 'pages', 'action' => 'contact_send', 'lang' => $top_lang]) ?>"
                   class="btn btn-block btn-cta-primary contact-confirm-send" id="SendContactLink"><?= __d('lp',
                                                                                      '送信する') ?></a>
            </form><!--//form-->
        </div>
    </div><!--//row-->
</section><!--//contact--><!-- END app/View/Pages/contact_confirm.ctp -->
<?php $this->append('script'); ?>
<script type="text/javascript">
    $(function () {
        $('#SendContactLink').on('click', function () {
            if ($(this).hasClass('double_click')) {
                return false;
            }
            $(this).text("<?=__d('lp', '送信中...')?>");
            // 2重送信防止クラス
            $(this).addClass('double_click');
        });
    })
</script>
<?php $this->end(); ?>
