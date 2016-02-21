<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 6/19/14
 * Time: 2:41 PM
 *
 * @var CodeCompletionView $this
 * @var boolean            $last_first
 * @var string             $language_name
 * @var array              $me
 * @var boolean            $is_not_use_local_name
 */
?>
<!-- START app/View/Elements/User/notify_setting.ctp -->
<div id="notify_setting">
    <div class="panel panel-default">
        <div class="panel-heading"><?= __d\('app', "通知") ?></div>
        <?=
        $this->Form->create('User', [
            'inputDefaults' => [
                'div'       => 'form-group',
                'label'     => [
                    'class' => 'col col-sm-3 control-label form-label'
                ],
                'class'     => 'form-control setting_input-design'
            ],
            'class'         => 'form-horizontal',
            'novalidate'    => true,
            'type'          => 'file',
            'id'            => 'ChangeProfileForm'
        ]); ?>
        <?= $this->Form->hidden('NotifySetting.user_id', ['value' => $this->Session->read('Auth.User.id')]) ?>
        <?=
        $this->Form->hidden('NotifySetting.id',
                            ['value' => isset($this->request->data['NotifySetting']['id']) ? $this->request->data['NotifySetting']['id'] : null]) ?>
        <div class="panel-body notify-setting-panel-body">
            <div class="form-group">
                <label class="col col-sm-3 col-xxs-12 control-label form-label">
                    <i class="fa fa-globe"></i> <?= __d\('app', 'Goalousサイト') ?>
                </label>

                <div class="col col-xxs-9 col-sm-9">
                    <p class="form-control-static"><?= h(NotifySetting::$TYPE_GROUP['all']) ?></p>
                </div>
            </div>
            <div id="NotifySettingAppHelp" class="col col-sm-offset-3 help-block font_12px">
                <?= __d\('app', 'すべての通知が送信されます。') ?>
            </div>


            <hr>
            <div class="form-group">
                <label class="col col-sm-3 col-xxs-12 control-label form-label">
                    <i class="fa fa-envelope-o"></i> <?= __d\('app', 'Email') ?>
                </label>
                <?=
                $this->Form->input("NotifySetting.email", [
                    'id'      => 'NotifySettingEmail',
                    'label'   => false,
                    'div'     => false,
                    'type'    => 'select',
                    'class'   => 'form-control',
                    'options' => NotifySetting::$TYPE_GROUP,
                    'wrapInput' => 'user-setting-notify-email-select col col-xxs-5 col-sm-3',
                ])
                ?>
            </div>
            <div id="NotifySettingEmailHelp" class="col col-sm-offset-3 help-block font_12px none"></div>

            <hr>
            <div class="form-group">
                <label class="col col-sm-3 col-xxs-12 control-label form-label">
                    <i class="fa fa-mobile"></i> <?= __d\('app', 'モバイル') ?>
                </label>
                <?=
                $this->Form->input("NotifySetting.mobile", [
                    'id'      => 'NotifySettingMobile',
                    'label'   => false,
                    'div'     => false,
                    'type'    => 'select',
                    'class'   => 'form-control',
                    'options' => NotifySetting::$TYPE_GROUP,
                    'wrapInput' => 'user-setting-notify-mobile-select col col-xxs-5 col-sm-3',
                ])
                ?>
            </div>
            <div id="NotifySettingMobileHelp" class="col col-sm-offset-3 help-block font_12px none"></div>
        </div>
        <div class="panel-footer setting_pannel-footer">
            <?= $this->Form->submit(__d\('app', "変更を保存"), ['class' => 'btn btn-primary pull-right']) ?>
            <div class="clearfix"></div>
        </div>
        <?= $this->Form->end(); ?>
    </div>
</div>
<?php $this->append('script'); ?>
<script>
    $(function () {
        var notify_help_message = {
            'all': '<?= __d\('app', 'すべての通知が送信されます。') ?>',
            'primary': '<?= __d\('app', 'あなたに関わる重要な通知（サークルへの新しい投稿などを除く）が送信されます。') ?>',
            'none': '<?= __d\('app', '通知は送信されません。') ?>'
        };

        var onSelectChange =  function () {
            var $select = $(this);
            var selected = $select.val();
            var $helpMessage = $('#' + $select.attr('id') + 'Help');

            $helpMessage.hide();
            if (notify_help_message[selected]) {
                $helpMessage.text(notify_help_message[selected]).show();
            }
        };
        $('#NotifySettingEmail').on('change', onSelectChange).trigger('change');
        $('#NotifySettingMobile').on('change', onSelectChange).trigger('change');
    })
</script>
<?php $this->end(); ?>
<!-- END app/View/Elements/User/notify_setting.ctp -->
