<?php /**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 6/11/14
 * Time: 11:40 AM
 *
 * @var CodeCompletionView $this
 * @var                    $border_months_options
 * @var                    $start_term_month_options
 * @var                    $timezones
 */
?>
<!-- START app/View/Teams/add.ctp -->
<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading"><?= __d('app', "チームを作成してください") ?></div>
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
            ]); ?>
            <div class="panel-body add-team-panel-body">
                <?=
                $this->Form->input('name',
                                   ['label'                        => __d('app', "チーム名"),
                                    'placeholder'                  => __d('app', "例) チームGoalous"),
                                    "data-bv-notempty-message"     => __d('validate', "入力必須項目です。"),
                                    'data-bv-stringlength'         => 'true',
                                    'data-bv-stringlength-max'     => 128,
                                    'data-bv-stringlength-message' => __d('validate', "最大文字数(%s)を超えています。", 128),
                                   ]) ?>
                <hr>
                <div class="form-group">
                    <label for="" class="col col-sm-3 control-label form-label"><?= __d('app', "チーム画像") ?></label>

                    <div class="col col-sm-6">
                        <div class="fileinput_small fileinput-new" data-provides="fileinput">
                            <div class="fileinput-preview thumbnail nailthumb-container photo-design"
                                 data-trigger="fileinput"
                                 style="width: 96px; height: 96px; line-height:96px;">
                                <i class="fa fa-plus photo-plus-large"></i>
                            </div>
                            <div>
                        <span class="btn btn-default btn-file">
                            <span class="fileinput-new">
                                <?=
                                __d('app',
                                    "画像を選択") ?>
                            </span>
                            <span class="fileinput-exists"><?= __d('app', "画像を再選択") ?></span>
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
                                <span class="help-block font_11px inline-block"><?= __d('app', '10MB以下') ?></span>
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
                    'label'      => __d('app', "プラン"),
                    'type'       => 'select',
                    'options'    => Team::$TYPE,
                    'afterInput' => '<span class="help-block font_11px">'
                        .__d('app', "2016年8月31日まで無料でご利用いただけます。") // 同様の文言がteam/edit_basic_setting.ctp
                        // . __d('app', "フリープランは、５人までのチームで使えます。また、複数の機能制限がございます。")
                        // . '<br>'
                        // . __d('app', "このプランはチーム作成後にいつでも変更できます。")
                        . '</span>'
                ]) ?>
                <hr>
                <?=
                $this->Form->input('timezone', [
                    'label'   => __d('app', "タイムゾーン"),
                    'type'    => 'select',
                    'options' => $timezones,
                    'value'   => $this->Session->read('Auth.User.timezone')
                ])
                ?>
                <?=
                $this->Form->input('start_term_month', [
                    'label'                    => __d('app', "開始月"),
                    'type'                     => 'select',
                    // help-block の文言があるので、エラーメッセージは表示しない
                    "data-bv-notempty-message" => __d('validate', " "),
                    'options'                  => $start_term_month_options,
                    'afterInput'               => '<span class="help-block font_11px">'
                        . __d('app', "基準となる期の開始月を選択して下さい。")
                        . '</span>'
                ]) ?>
                <?=
                $this->Form->input('border_months', [
                    'label'                    => __d('app', "期間"),
                    'type'                     => 'select',
                    "data-bv-notempty-message" => __d('validate', "選択してください。"),
                    'options'                  => $border_months_options
                ]) ?>
                <div class="form-group">
                    <label class="col col-sm-3 control-label form-label"><?= __d('app', "現在の期間") ?></label>

                    <div class="col col-sm-6">
                        <p class="form-control-static" id="CurrentTermStr">
                        </p>
                    </div>
                </div>
            </div>

            <div class="panel-footer addteam_pannel-footer">
                <div class="row">
                    <div class="team-button pull-right">
                        <?=
                        $this->Form->submit(__d('app', "チームを作成"),
                                            ['class' => 'btn btn-primary display-inline', 'div' => false, 'disabled' => 'disabled']) ?>
                    </div>
                </div>
            </div>
            <?= $this->Form->end(); ?>
        </div>
    </div>
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
                            message: "<?=__d('validate', "10MB以下かつJPG、PNG、GIFのいずれかの形式を選択して下さい。")?>"
                        }
                    }
                }
            }
        });
    });
</script>
<?php $this->end() ?>
<!-- END app/View/Teams/add.ctp -->
