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
<link rel="alternate" hreflang="ja" href="<?= $this->Html->url('/ja/pricing') ?>"/>
<link rel="alternate" hreflang="en" href="<?= $this->Html->url('/en/pricing') ?>"/>
<link rel="alternate" hreflang="x-default" href="<?= $this->Html->url('/pricing') ?>"/>
<?php $this->end() ?>
<!-- START app/View/Pages/pricing.ctp -->
<!-- ******PRICE PLAN****** -->
<section id="price-plan" class="price-plan section">
    <div class="container text-center">
        <h2 class="title"><?= __d('lp', '今だけ、有料プランも完全無料') ?></h2>
        <p class="intro"><?= __d('lp', 'リリース記念で2016年8月までPlusをご利用いただけます。フィードバックをお待ちしております。') ?></p>
            <div class="item col-xs-12 col-md-6 col-md-offset-3">
                <h3 class="heading"><?= __d('lp', 'Plus') ?><span class="label label-custom"><?= __d('lp',
                                                                                                     'キャンペーン') ?></span>
                </h3>
                <div class="content">
                    <div class="price-figure">
                        <span class="currency"><?= __d('lp', '¥') ?><div class="pricing-line-through"></div></span><span class="number">1,980</span><span
                            class="unit"><?= __d('lp', '/月') ?></span>
                    </div>
                    <i class="fa fa-arrow-down pricing-figure-mid-icon"></i>
                    <div class="price-figure">
                        <p><?= __d('lp', '1ユーザーあたり') ?></p>
                        <span class="currency"><?= __d('lp', '¥') ?></span>
                        <span class="number">0</span>
                        <span class="unit"><?= __d('lp', '/月') ?></span>
                    </div>
                    <ul class="list-unstyled feature-list">
                        <li><?= __d('lp', '1チームのアカウント無制限') ?></li>
                        <li><?= __d('lp', '20MB/ファイルのアップロード') ?></li>
                        <li><?= __d('lp', 'ストレージ無制限のファイル共有') ?></li>
                        <li><?= __d('lp', 'チャットメッセージ') ?></li>
                        <li><?= __d('lp', 'インサイト分析') ?></li>
                        <li><?= __d('lp', 'チーム管理機能') ?></li>
                        <li><?= __d('lp', 'オンラインでのユーザーサポート') ?></li>
                    </ul>
                    <a class="pricing-signup btn btn-cta btn-cta-primary"
                       href="<?= $this->Html->url(['controller' => 'users', 'action' => 'register']) ?>">
                        <?= __d('lp', '今すぐ始める') ?>
                        <br/>
                        <span class="extra">
                            <?= __d('lp', '無料相談受付中') ?>
                        </span>
                    </a>
                </div><!--//content-->
            </div><!--//item-->
        </div><!--//row-->
    </div><!--//container-->
</section><!--//price-plan-->

<?= $this->element('Homepage/signup') ?>
<!-- END app/View/Pages/pricing.ctp -->
