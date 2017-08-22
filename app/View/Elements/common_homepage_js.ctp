<?php
/**
 * @var CodeCompletionView $this
 */
?>
<?= $this->App->viewStartComment()?>
<?= $this->Html->script('homepage/jquery-1.12.4.min') ?>
<?= $this->Html->script('homepage/bootstrap.min') ?>
<?= $this->Html->script('vendor/bootstrapValidator') ?>
<?= $this->Html->script('homepage/bootstrap-hover-dropdown.min') ?>
<?= $this->Html->script('homepage/jquery.inview.min') ?>
<?= $this->Html->script('homepage/isMobile') ?>
<?= $this->Html->script('homepage/back-to-top') ?>
<?= $this->Html->script('homepage/jquery.placeholder') ?>
<?= $this->Html->script('homepage/jquery.fitvids') ?>
<?= $this->Html->script('homepage/jquery.flexslider-min') ?>
<?= $this->Html->script('homepage/marked.min') ?>
<?= $this->Html->script('homepage/main') ?>
<?= $this->Html->script('homepage/froogaloop2.min') ?>
<?= $this->Html->script('homepage/vimeo') ?>
<?= $this->Html->script('homepage/require') ?>

<!--[if !IE]>-->
<?= $this->Html->script('homepage/animations') ?>
<!--<![endif]-->
<?= $this->element('cake_variables') ?>
<script>
    $(function () {
        if ($("#markdown")[0]) {
            $.ajax({
                url: $('#markdown')[0].attributes['src'].value
            }).success(function (data) {
                $('#markdown').append(marked(data));
            }).error(function (data) {
                $('#markdown').append("This content failed to load.");
            });
        }
    });
</script>

<?= $this->App->viewEndComment()?>
