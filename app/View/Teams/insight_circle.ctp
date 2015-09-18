<?php
/**
 * @var $circle_insights
 * @var $team_list
 */
?>
<!-- START app/View/Teams/insight_circle.ctp -->
<div class="col-sm-8 col-sm-offset-2">
    <div class="panel panel-default" style="padding:20px;">
        <?= $this->Form->create('TeamInsight', [
            'url'           => ['controller' => 'teams',
                                'action'     => 'insight_circle'],
            'inputDefaults' => [
                'div'       => 'form-group',
                'label'     => false,
                'wrapInput' => '',
                'class'     => 'form-control',
            ],
            'id'            => 'InsightForm',
            'type'          => 'get',
        ]); ?>
        <?= $this->element('Team/insight_form_input', ['use' => ['team', 'date_range', 'timezone']]) ?>
        <?= $this->Form->end() ?>

        <div id="InsightCircleResult" class="mt_18px"></div>

    </div>
</div>
<!-- END app/View/Teams/insight_circle.ctp -->
