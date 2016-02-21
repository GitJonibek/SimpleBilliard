<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 6/30/14
 * Time: 9:59 AM
 *
 * @var CodeCompletionView $this
 * @var array              $team
 * @var                    $from_setting
 */
?>
<!-- START app/View/Elements/Team/invite.ctp -->
<div class="panel panel-default">
    <div class="panel-heading"><?= __d\('app', "新しいメンバーを招待") ?></div>
    <?=
    $this->Form->create('Team', [
        'inputDefaults' => [
            'div'       => 'form-group',
            'label'     => [
                'class' => 'col col-sm-3 control-label form-label'
            ],
            'wrapInput' => 'col col-sm-6',
            'class'     => 'form-control'
        ],
        'class'         => 'form-horizontal',
        'novalidate'    => true,
        'id'            => 'InviteTeamForm',
        'url'           => ['action' => 'invite'],
        'method'        => 'post'
    ]); ?>
    <div class="panel-body">
        <div class="form-group">
            <label for="TeamName" class="col col-sm-3 control-label form-label"><?= __d\('app', "チーム名") ?></label>

            <div class="col col-sm-6">
                <p class="form-control-static"><?= h($team['Team']['name']) ?></p>
            </div>
        </div>
        <hr>
        <?=
        $this->Form->input('emails', [
            'label'                    => __d\('app', "招待するメンバーのメールアドレス"),
            'type'                     => 'text',
            'rows'                     => 3,
            'data-bv-stringlength'         => 'true',
            'data-bv-stringlength-max'     => 2000,
            'data-bv-stringlength-message' => __d('validate', "最大文字数(%s)を超えています。", 2000),
            "data-bv-notempty-message" => __d('validate', "入力必須項目です。"),
            'afterInput'               => '<span class="help-block">'
                . '<p class="font_11px">' . __d\('app', "メールアドレスはカンマ( , )区切り、もしくは改行区切りで複数指定可能です。") . '</p>'
                . '<ul class="example-indent font_11px"><li>' . __d\('app', "例%s",
                                                                    1) . ' aaa@example.com,bbb@example.com</li></ul>'
                . '<ul class="example-indent font_11px"><li>'
                . '' . __d\('app', "例%s", 2) . ' aaa@example.com</br>'
                . 'aaa@example.com</br>'
                . '</li></ul>'
                . '</span>'
        ]) ?>
        <hr>
        <?=
        $this->Form->input('comment', [
            'label'      => __d\('app', "コメント(オプション)"),
            'type'       => 'text',
            'rows'       => 3,
            'data-bv-stringlength'         => 'true',
            'data-bv-stringlength-max'     => 2000,
            'data-bv-stringlength-message' => __d('validate', "最大文字数(%s)を超えています。", 2000),
            'afterInput' => '<span class="help-block font_11px">' . __d\('app',
                                                                        "コメント(任意)はメールの本文に追加されます。") . '</span>'
        ]) ?>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-sm-9 col-sm-offset-3">
                <?=
                $this->Form->submit(__d\('app', "招待メールを送信"),
                                    ['class' => 'btn btn-primary', 'div' => false, 'disabled' => 'disabled']) ?>
                <?php if (isset($from_setting) && !$from_setting): ?>
                    <?=
                    $this->Html->link(__d\('app', "スキップ"), "/",
                                      ['class' => 'btn btn-default', 'div' => false]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?= $this->Form->end(); ?>
</div>
<?php $this->append('script') ?>
<script type="text/javascript">
    $(document).ready(function () {

        $('[rel="tooltip"]').tooltip();

        $('#InviteTeamForm').bootstrapValidator({
            live: 'enabled',
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            }
        });
    });
</script>
<?php $this->end() ?>
<!-- END app/View/Elements/Team/invite.ctp -->
