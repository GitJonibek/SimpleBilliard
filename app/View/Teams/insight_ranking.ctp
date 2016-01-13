<?php
/**
 * @var $group_list
 * @var $team_list
 * @var $text_list
 * @var $url_list
 */
?>
<?php $this->start('sidebar'); ?>
<?= $this->element('Team/side_menu', ['active' => 'insight_ranking']); ?>
<?php $this->end(); ?>
<!-- START app/View/Teams/insight_ranking.ctp -->
<div>
    <div class="panel panel-default">
        <div class="panel-body">
            <?= $this->Form->create('TeamInsight', [
                'url'           => ['controller' => 'teams',
                                    'action'     => 'insight_ranking'],
                'inputDefaults' => [
                    'div'       => 'form-group',
                    'label'     => false,
                    'wrapInput' => '',
                    'class'     => 'form-control disable-change-warning',
                ],
                'id'            => 'InsightForm',
                'type'          => 'get',
            ]); ?>
            <?= $this->element('Team/insight_form_input',
                               ['use' => ['date_range', 'group', 'ranking_type', 'timezone']]) ?>
            <?= $this->Form->end() ?>

            <div id="InsightRankingResult" class="mt_18px"></div>
        </div>
    </div>
</div>
<!-- END app/View/Teams/insight_ranking.ctp -->
