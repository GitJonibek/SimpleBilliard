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
<!-- START app/View/Elements/Homepage/signup.ctp -->
<!-- ******SIGNUP****** -->
<section id="signup" class="signup">
    <div class="container text-center">
        <h2 class="title"><?= __('Let\'s go to Goalous!') ?></h2>
        <p class="summary"><?= __('It\'s free until 31 Dec 2016! Try it!') ?></p>
        <a href="<?= $this->Html->url(['controller' => 'users', 'action' => 'register', '?' => ['type' => 'bottom']]) ?>"
           class="col-md-6 col-md-offset-3" id="RegisterLinkBottom">
            <button type="submit" class="btn btn-cta btn-cta-primary btn-block btn-lg"><?= __('Create New Team') ?></button>
        </a>
    </div>
</section><!--//signup-->
<!-- END app/View/Elements/Homepage/signup.ctp -->
