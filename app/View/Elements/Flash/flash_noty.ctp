<?php
/**
 * Created by PhpStorm.
 * User: daikihirakata
 * Date: 2014/05/27
 * Time: 1:23
 *
 * @var CodeCompletionView $this
 * @var                    $message
 * @var                    $title
 * @var                    $icon
 * @var                    $type
 */
?>
<?= $this->App->viewStartComment()?>
<script type="text/javascript">
    new Noty({
        type: "<?=$type?>",
        text: '<h4>'+'<?=$title?>'+'</h4>'+'<?=$message?>',
    }).show();
</script>
<?= $this->App->viewEndComment()?>
