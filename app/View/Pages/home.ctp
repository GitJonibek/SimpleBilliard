<?php
/**
 * PHP 5
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @var CodeCompletionView
 * @var
 * @var
 */
?>
<?= $this->App->viewStartComment()?>
<?php $this->append('meta') ?>
<?php
/*
- meta description
- og:description
- og:url
- title
 */
$meta_lp = [
    [
        "name"    => "description",
        "content" => __('Goalous is one of the best team communication tools. Let your team open. Your action will be share with your collegues. %s',__("You can use Goalous on Web and on Mobile App.")),
    ],
    [
        "name"    => "keywords",
        "content" => __("goal management, achieve a goal, sns app, evaluation, mbo"),
    ],
    [
        "property" => "og:type",
        "content"  => "website",
    ],
    [
        "property" => "og:title",
        "content"  => __('Goalous | Enjoy your work. Achieve your Goal.'),
    ],
    [
        "property" => "og:description",
        "content"  => __('Goalous is one of the best team communication tools. Let your team open. Your action will be share with your collegues. %s',__("You can use Goalous on Web and on Mobile App.")),
    ],
    [
        "property" => "og:url",
        "content"  => "https://www.goalous.com/",
    ],
    [
        "property" => "og:image",
        "content"  => "https://www.goalous.com/img/homepage/background/promo-bg.jpg",
    ],
    [
        "property" => "og:site_name",
        "content"  => __('Goalous │ Enterprise SNS the most ever open for Goal'),
    ],
    [
        "property" => "fb:app_id",
        "content"  => "966242223397117",
    ],
    [
        "name"    => "twitter_card",
        "content" => "summary",
    ],
    [
        "name"    => "twitter:site",
        "content" => "@goalous",
    ]
];
$num_ogp = count($meta_lp);
for ($i = 0; $i < $num_ogp; $i++) {
    echo $this->Html->meta($meta_lp[$i]);
}
?>
<title><?= __('Goalous | Enjoy your work. Achieve your Goal.') ?></title>
<link rel="alternate" hreflang="ja" href="<?= $this->Html->url('/ja/') ?>"/>
<link rel="alternate" hreflang="en" href="<?= $this->Html->url('/en/') ?>"/>
<link rel="alternate" hreflang="x-default" href="<?= $this->Html->url('/') ?>"/>
<?php $this->end() ?>

<!-- ******PROMO****** -->
<section id="promo" class="promo section">
    <div class="section-container">
        <h1><?= __("Enjoy your work. Achieve your goals.") ?></h1>
        <div class="create-team">
            <p class="create-team-guide"><strong><?= __("Easy set-up ･ Free 15 day Trial") ?></strong></p>
            <a href="/signup/email?type=header" class="btn btn-cta-primary btn-lg"><?= __('Create New Team');?></a>
            <ul>
                <li>
                    <a href="/users/login"><?=__('Login')?></a>
                </li>
                <li>
                    <a href="/contact"><?=__('Contact&nbsp;us')?></a>
                </li>
            </ul>
        </div>
    </div>
</section><!--//promo-->

<div class="seminar">
    <div class="container">
        <div class="row text-left">
            <h2 class="title"><strong><?= __('New Event');?>:</strong> <?= __('Free Goalous Seminar');?></h2>
            <p><?= __('Learn all of the ways you can improve your organization using Goalous.');?></p>
            <a href="http://peatix.com/event/319633/view" target="_blank" class="btn btn-cta-primary btn-lg"><?= __('Get Ticket');?></a>
        </div>
    </div>
</div>

<!-- ******WHY****** -->
<section id="why" class="why section">
    <div class="container">
        <h2 class="title text-center"><?= __('Your organization can be changed by Goalous!') ?></h2>
        <p class="intro text-center"><?= __('Achieve Goals, make your team open, let your job become joyful!') ?></p>
        <div class="item row flex from-left">
            <div
                class="content col-md-5 col-sm-5 col-xs-12 pull-right col-md-offset-1 col-sm-offset-1 col-xs-offset-0 col-right">
                <h3 class="title"><?= __("Move closer to your company's vision.") ?></h3>
                <div class="details">
                    <p><?= __(
                            'Everyone can know the company vision and can make their Goals. Goalous lets them see their improvement and how close they are to the vision. The more they use Goalous the closer they can get to that vision.') ?></p>
                </div>
            </div><!--//content-->
            <div class="figure col-md-6 col-sm-6 col-xs-12 col-left">
                <?= $this->Html->image('homepage/top/top-1.jpg',
                    array(
                        'alt'   => __(
                            'You get to know your team grow by your achievement of goals.'),
                        'class' => 'img-responsive'
                    )); ?>
            </div><!--//figure-->
        </div><!--//item-->

        <hr/>

        <div class="item row flex from-right">
            <div class="content col-md-5 col-sm-5 col-xs-12 col-left">
                <h3 class="title"><?= __('The phrase "I don know what that is, what they do" goes away.') ?></h3>
                <div class="details">
                    <p><?= __(
                            'What did your colleagues did today? Share on Goalous and get to know more and more.') ?></p>
                </div>
            </div><!--//content-->
            <div class="figure col-md-6 col-sm-6 col-xs-12 col-md-offset-1 col-sm-offset-1 col-xs-offset-0 col-right">
                <?= $this->Html->image('homepage/top/top-2.jpg',
                    array(
                        'alt'   => __(
                            '仕事で大変な事も嬉しい事もオープンにしてお互いを理解しましょう！すべてはそこから始まります。'),
                        'class' => 'img-responsive'
                    )); ?>
            </div><!--//figure-->
        </div><!--//item-->

        <hr/>

        <div class="item row flex from-left">
            <div
                class="content col-md-5 col-sm-5 col-xs-12 pull-right col-md-offset-1 col-sm-offset-1 col-xs-offset-0 col-right">
                <h3 class="title"><?= __('Cooperate, get results.') ?></h3>
                <div class="details">
                    <p><?= __(
                            'To achieve the team vision, the team must know what each other is doing. By using Goalous to know clearly what each other is doing, the team can succeeded efficiently.') ?></p>
                </div>
            </div><!--//content-->
            <div class="figure col-md-6 col-sm-6 col-xs-12 col-left">
                <?= $this->Html->image('homepage/top/top-3.jpg',
                    array(
                        'alt'   => __(
                            'Help each other, enjoy with them. How wonderful?'),
                        'class' => 'img-responsive'
                    )); ?>
            </div><!--//figure-->
        </div><!--//item-->
    </div><!--//container-->
</section><!--//why-->

<!-- ******VIDEO****** -->
<section id="video" class="video section">
    <div class="container">
        <div class="control text-center">
            <button type="button" id="play-trigger" class="play-trigger" data-toggle="modal" data-target="#tour-video">
                <i class="fa fa-play"></i></button>
            <p><?= __('Watch Video') ?></p>

            <!-- Video Modal -->
            <div class="modal modal-video" id="tour-video" tabindex="-1" role="dialog" aria-labelledby="videoModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 id="videoModalLabel" class="modal-title"><?= __('About Goalous') ?></h4>
                        </div>
                        <div class="modal-body">
                            <div class="video-container">
                                <iframe id="promoVideo"
                                    data-src="<?= $this->Lang->getLangCode() == LangHelper::LANG_CODE_JP ? 'jwG1Lsq3Wyw' : 'dArw8d4uh00'?>"
                                    width="720"
                                    height="405"
                                    frameborder="0"
                                    webkitallowfullscreen
                                    mozallowfullscreen
                                    allowfullscreen>
                                </iframe>
                            </div><!--//video-container-->
                        </div><!--//modal-body-->
                    </div><!--//modal-content-->
                </div><!--//modal-dialog-->
            </div><!--//modal-->
        </div><!--//control-->
    </div>
</section><!--//video-->

<div class="store section">
    <div class="container">
        <div class="row flex">
            <div class="col-md-6 col-sm-6 col-xs-12 from-left col-left text-center">
                <h3><?= __('Wherever, Whenever, from your smartphone.') ?></h3>
                <p class="lead-text"><?= __('iOS and Android apps avaliable.') ?></p>
                <?= $this->Html->link(
                    $this->Html->image('https://linkmaker.itunes.apple.com/images/badges/en-us/badge_appstore-lrg.svg'),
                    'https://itunes.apple.com/us/app/goalous-chimu-li-xiang-shangsns/id1060474459?ls=1&mt=8',
                    array(
                        'escape' => false,
                        'alt'    => 'iPhoneアプリもご利用いただけます',
                        'class'  => 'app-dl-btn'
                    ))
                ?>
                <?= $this->Html->link(
                    $this->Html->image(
                        'https://play.google.com/intl/en_us/badges/images/apps/en-play-badge.png',
                        [
                            'alt'    => 'Get it on Google Play',
                            'height' => '40'
                        ]),
                    'https://play.google.com/store/apps/details?id=jp.co.isao.android.goalous2',
                    [
                        'escape' => false,
                        'class'  => 'app-dl-btn'
                    ])
                ?>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 from-right col-right">
                <?= $this->Html->image('homepage/top/devices.png', array('alt' => '', 'class' => 'img-responsive')); ?>
            </div>
        </div>
    </div>
</div>

<?= $this->element('Homepage/faq') ?>
<?= $this->element('Homepage/signup') ?>

<section class="document">
    <div class="container text-center">
        <dl class="media col-md-6 from-bottom">
            <div class="media-left media-middle">
                <i class="fa fa fa-file-pdf-o document-fa"></i>
            </div>
            <div class="media-body">
                <dt class="bold-text">
                    <?= __('What is Goalous? (jp, pdf)') ?>
                </dt>
                <dd>
                    <?= __('This file is written in Japanese.') ?>
                    <br>
                    <a href="../composition/pdf/jp_GoalousIntroduction_100.pdf" target="_blank"><i
                            class="fa fa-arrow-down document-download-icon"></i>
                        <span class="document-download-text">
                            <?= __('Download the file') ?>
                        </span>
                    </a>
                </dd>
            </div>
        </dl>
        <dl class="media col-md-6 from-bottom">
            <div class="media-left media-middle">
                <i class="fa fa fa-file-pdf-o document-fa"></i>
            </div>
            <div class="media-body">
                <dt class="bold-text">
                    <?= __('Flyer (pdf)') ?>
                </dt>
                <dd>
                    <?= __('Goalous Flyer (Japanese)') ?>
                    <br>
                    <a href="../composition/pdf/jp_goalous_flier_campaign.pdf" target="_blank"><i
                            class="fa fa-arrow-down document-download-icon"></i>
                        <span class="document-download-text">
                            <?= __('Download the file') ?>
                        </span>
                    </a>
                </dd>
            </div>
        </dl>
    </div>
</section>
<?php $this->append('script'); ?>
<script type="text/javascript">
    require.config({
        baseUrl: '/js/modules/'
    });
    $(document).ready(function () {
        // 登録可能な email の validate
        require(['validate'], function (validate) {
            window.bvCallbackAvailableEmailNotVerified = validate.bvCallbackAvailableEmailNotVerified;
        });
        $('#HomeSignupEmail').bootstrapValidator({
            container: "#HomeEmailErrorContainer"
        });
    });
</script>
<?php $this->end(); ?>

<?= $this->App->viewEndComment()?>
