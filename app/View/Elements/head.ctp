<?php /**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 5/28/14
 * Time: 4:55 PM
 *
 * @var CodeCompletionView $this
 * @var                    $title_for_layout string
 * @var                    $this             View
 */
?>
<!-- START app/View/Elements/head.ctp -->
<head>
    <?= $this->Html->charset(); ?>
    <title origin-title="<?= $title_for_layout; ?>">
        <?= $title_for_layout; ?>
    </title>
    <?php echo $this->Html->meta('icon');
    echo $this->Html->meta(
        ['name'    => 'viewport',
         'content' => "width=device-width, initial-scale=1, maximum-scale=1"
        ]);
    echo $this->Html->meta(['http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge']);
    //TODO botの拒否。一般公開前に必ず外す。
    echo $this->Html->meta(['name' => 'ROBOTS', 'content' => 'NOINDEX,NOFOLLOW']);

    // クリックジャッキング対策
    echo $this->Html->meta(['name' => 'X-FRAME-OPTIONS', 'content' => 'SAMEORIGIN']);

    //    echo $this->Html->css('bw-simplex.min', array('media' => 'screen'));
    //    echo $this->Html->css('bw-simplex', array('media' => 'screen'));
    echo $this->Html->css('goalstrap', array('media' => 'screen'));
    echo $this->Html->css('jasny-bootstrap.min');
    echo $this->Html->css('font-awesome.min');
    echo $this->Html->css('jquery.nailthumb.1.1');
    echo $this->Html->css('bootstrapValidator.min');
    echo $this->Html->css('bootstrap-switch.min');
    echo $this->Html->css('pnotify.custom.min');
    echo $this->Html->css('lightbox');
    echo $this->Html->css('showmore');
    echo $this->Html->css('bootstrap-ext-col');
    echo $this->Html->css('customRadioCheck.min');
    echo $this->Html->css('select2');
    echo $this->Html->css('select2-bootstrap');
    echo $this->Html->css('bootstrap-ext-col');
    echo $this->Html->css('datepicker3');
    echo $this->Html->css('style', array('media' => 'screen'));
    echo $this->Html->css('goalous.min', array('media' => 'screen'));
    echo $this->fetch('css');
    echo $this->fetch('meta');

    echo $this->Html->script('vendor/angular/angular.min');
    echo $this->Html->script('vendor/angular/angular-ui-router.min');
    echo $this->Html->script('vendor/angular/angular-route.min');
    echo $this->Html->script('vendor/angular/angular-translate.min');
    echo $this->Html->script('vendor/angular/angular-translate-loader-static-files.min');
    echo $this->Html->script('vendor/angular/ui-bootstrap-tpls-0.13.0.min');
    echo $this->Html->script('vendor/angular/angular-pnotify');

    ?>
    <?php if ($this->request->params['action'] === 'display'): //上部のタブメニューの表示切替えの為?>
        <style>
            @media screen and (max-width: 991px) {
                .container {
                    margin-top: 50px;
                }
            }
        </style>
    <?php endif; ?>

    <!--suppress HtmlUnknownTarget -->
    <link href="/img/apple-touch-icon.png" rel="apple-touch-icon-precomposed">
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Oswald:400,300|Pathway+Gothic+One">
    <!--[if lt IE 9]>
    <?= $this->Html->script('vendor/html5shiv')?>
    <?= $this->Html->script('vendor/respond.min')?>
    <![endif]-->
    <?php //公開環境のみタグを有効化
    if (PUBLIC_ENV) {
        /** @noinspection PhpDeprecationInspection */
        echo $this->element('external_service_tags');
    }
    ?>
</head>
<!-- END app/View/Elements/head.ctp -->
