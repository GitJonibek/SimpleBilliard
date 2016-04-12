<?php /**
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Errors
 * @since         CakePHP(tm) v 0.10.0.1076
 * @var CodeCompletionView $this
 * @var                    $name string
 * @var                    $url  string
 */
?>
<div class="jumbotron jumbotron-icon text-center">
    <i class="fa-ban fa fa-5"></i>

    <h1>404</h1>

    <p><?= __("Ooops, Not Found.") ?></p>
</div>
<?php if (Configure::read('debug') > 0):
    echo $this->element('exception_stack_trace');
endif;
?>
