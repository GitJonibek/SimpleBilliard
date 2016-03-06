<?php /**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 6/11/14
 * Time: 11:40 AM
 *
 * @var CodeCompletionView $this
 * @var                    $border_months_options
 * @var                    $start_term_month_options
 */
?>
<!-- START app/View/Elements/Team/edit_basic_setting.ctp -->
<div class="panel panel-default">
    <div class="panel-heading"><?= __("Basic settings") ?></div>
    <?=
    $this->Form->create('Team', [
        'inputDefaults' => [
            'div'       => 'form-group',
            'label'     => [
                'class' => 'col col-sm-3 control-label form-label'
            ],
            'wrapInput' => 'col col-sm-6',
            'class'     => 'form-control addteam_input-design'
        ],
        'class'         => 'form-horizontal',
        'novalidate'    => true,
        'type'          => 'file',
        'id'            => 'AddTeamForm',
        'url'           => ['action' => 'edit_team']
    ]); ?>
    <div class="panel-body add-team-panel-body">
        <?=
        $this->Form->input('name',
                           ['label'                        => __("Team Name"),
                            'placeholder'                  => __("eg. Team Goalous"),
                            "data-bv-notempty-message"     => __("入力必須項目です。"),
                            'data-bv-stringlength'         => 'true',
                            'data-bv-stringlength-max'     => 128,
                            'data-bv-stringlength-message' => __("最大文字数(%s)を超えています。", 128),
                           ]) ?>
        <hr>
        <div class="form-group">
            <label for="" class="col col-sm-3 control-label form-label"><?= __("Team Image") ?></label>

            <div class="col col-sm-6">
                <div class="fileinput_small fileinput-new" data-provides="fileinput">
                    <div class="fileinput-preview thumbnail nailthumb-container photo-design"
                         data-trigger="fileinput"
                         style="width: 96px; height: 96px; line-height:96px;">
                        <?
                        $team_img_data = $this->request->data;
                        $add_team_id_data = ['id' => $this->Session->read('current_team_id')];
                        $team_img_data['Team'] = array_merge($team_img_data['Team'], $add_team_id_data);
                        ?>
                        <?=
                        $this->Upload->uploadImage($team_img_data, 'Team.photo',
                                                   ['style' => 'medium_large']) ?>
                    </div>
                    <div>
                        <span class="btn btn-default btn-file">
                            <span class="fileinput-new">
                                <?= __("Select an image") ?>
                            </span>
                            <span class="fileinput-exists"><?= __("Reselect an image") ?></span>
                            <?=
                            $this->Form->input('photo',
                                               ['type'         => 'file',
                                                'label'        => false,
                                                'div'          => false,
                                                'css'          => false,
                                                'wrapInput'    => false,
                                                'errorMessage' => false,
                                                'required'     => false
                                               ]) ?>
                        </span>
                        <span class="help-block font_11px inline-block"><?= __('Smaller than 10MB') ?></span>
                    </div>
                </div>

                <div class="has-error">
                    <?=
                    $this->Form->error('photo', null,
                                       ['class' => 'help-block text-danger',
                                        'wrap'  => 'span'
                                       ]) ?>
                </div>
            </div>

        </div>
        <hr>
        <?=
        $this->Form->input('type', [
            'label'      => __("Plan"),
            'type'       => 'select',
            'options'    => Team::$TYPE,
            'wrapInput'  => 'team-setting-basic-plan',
            'afterInput' => '<span class="help-block font_11px">'
                .__("You can use Goalous free of charge until 31 Aug 2016.") // 同様の文言がteam/add.ctp
                // . __("フリープランは、５人までのチームで使えます。また、複数の機能制限がございます。")
                // . '<br>'
                // . __("このプランはチーム作成後にいつでも変更できます。")
                . '</span>'
        ]) ?>
    </div>

    <div class="panel-footer addteam_pannel-footer">
        <div class="row">
            <div class="col-xxs-4 col-sm-offset-3">
                <?=
                $this->Form->submit(__("Change basic settings"),
                                    ['class' => 'btn btn-primary display-inline', 'div' => false, 'disabled' => 'disabled']) ?>
            </div>
            <div class="col-xxs-8 col-sm-5 text-align_r">
                <a id="TeamDeleteButton" class="team-delete-button" href="#"><?= __('Delete the team') ?></a>
            </div>
        </div>
    </div>
    <?= $this->Form->end(); ?>
    <?=
    $this->Form->create('Team', [
        'class'      => 'none',
        'novalidate' => true,
        'id'         => 'TeamDeleteForm',
        'url'        => ['action' => 'delete_team']
    ]); ?>
    <?= $this->Form->end(); ?>

</div>
<?php $this->append('script') ?>
<script type="text/javascript">
    $(document).ready(function () {

        $('[rel="tooltip"]').tooltip();

        $('#AddTeamForm').bootstrapValidator({
            live: 'enabled',
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            },
            fields: {
                "data[Team][photo]": {
                    feedbackIcons: 'false',
                    validators: {
                        file: {
                            extension: 'jpeg,jpg,png,gif',
                            type: 'image/jpeg,image/png,image/gif',
                            maxSize: 10485760,   // 10mb
                            message: "<?=__("10MB以下かつJPG、PNG、GIFのいずれかの形式を選択して下さい。")?>"
                        }
                    }
                }
            }
        });

        $('#TeamDeleteButton').on('click', function (e) {
            e.preventDefault();

            if (confirm('<?=__('If the team is deleted, everything such as posts, actions and goals will be deleted. Do you really want to delete the team?')?>')) {
                $('#TeamDeleteForm').submit();
            }
        });
    });
</script>
<?php $this->end() ?>
<!-- END app/View/Elements/Team/edit_basic_setting.ctp -->
