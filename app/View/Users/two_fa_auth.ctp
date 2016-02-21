<?php
/**
 * ログイン画面
 * Created by PhpStorm.
 * User: daikihirakata
 * Date: 2014/06/01
 * Time: 0:19
 *
 * @var CodeCompletionView $this
 */
?>
<!-- START app/View/Users/two_fa_auth.ctp -->
<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading"><?= __d('app', "2段階認証") ?></div>
            <div class="panel-body login-panel-body">
                <?=
                $this->Form->create('User', [
                    'inputDefaults' => [
                        'div'       => 'form-group',
                        'label'     => [
                            'class' => 'col col-sm-3 control-label form-label'
                        ],
                        'wrapInput' => 'col col-sm-6',
                        'class'     => 'form-control login_input-design disable-change-warning'
                    ],
                    'class'         => 'form-horizontal login-form',
                    'novalidate'    => true
                ]); ?>
                <?=
                $this->Form->input('two_fa_code', [
                    'label' => __d('app', "コード")
                ]) ?>

                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3">
                        <?=
                        $this->Form->submit(__d('app', "認証"),
                                            ['class' => 'btn btn-primary']) ?>
                        <span class="help-block">
                            <?= __d('app', "認証アプリを使用できない場合は") ?>
                            <?= $this->Html->link(__d('app', "こちら"), [
                                'controller' => 'users',
                                'action'     => 'two_fa_auth_recovery',
                            ]); ?>
                        </span>
                    </div>
                </div>
                <?= $this->Form->end(); ?>
            </div>
        </div>
    </div>
</div>
<!-- END app/View/Users/two_fa_auth.ctp -->
