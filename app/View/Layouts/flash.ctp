<?php /**
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @var string             $page_title
 * @var string             $url
 * @var string             $pause
 * @var string             $message
 * @var CodeCompletionView $this
 */
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset(); ?>
    <title><?= h($page_title); ?></title>

    <?php if (Configure::read('debug') == 0): ?>
        <meta http-equiv="Refresh" content="<?= h($pause); ?>;url=<?= h($url); ?>"/>
    <?php endif ?>
    <style><!--
        P {
            text-align: center;
            font: bold 1.1em sans-serif
        }

        A {
            color: #444;
            text-decoration: none
        }

        A:HOVER {
            text-decoration: underline;
            color: #44E
        }

        --></style>
</head>
<body>
<p><a href="<?= h($url); ?>"><?= h($message); ?></a></p>
</body>
</html>
