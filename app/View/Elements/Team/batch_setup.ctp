<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 6/30/14
 * Time: 9:59 AM
 *
 * @var CodeCompletionView $this
 * @var array              $my_teams
 */
?>
<!-- START app/View/Elements/Team/batch_setup.ctp -->
<div class="panel panel-default">
    <div class="panel-heading"><?= __d('gl', "一括登録") ?></div>
    <div class="panel-body">
        <div class="form-group">
            <label for="TeamName" class="col col-sm-3 control-label form-label"></label>

            <div class="col col-sm-6">
                <?php if ($this->Session->read('ua.device_type') == 'Desktop'): ?>
                    <p class="form-control-static"><?= __d('gl', "チームメンバーの登録や更新をCSVで一括処理します。") ?></p>

                    <p class="form-control-static"><?= __d('gl',
                                                           "ファイルをダウンロードし、フォーマットに従って入力したあと、更新済みのCSVファイルをアップロードしてください。") ?></p>

                    <p class="form-control-static"><?= __d('gl',
                                                           "すでに登録済のメンバーはデータ更新され、新規のメンバーは追加で登録され、招待メールが送信されます。") ?></p>

                    <p class="form-control-static"><?= __d('gl', "") ?></p>

                    <p class="form-control-static"><?= __d('gl', "") ?></p>

                    <p class="form-control-static"><?= __d('gl', "") ?></p>

                    <p class="form-control-static"><?= __d('gl', "") ?></p>
                <?php else: ?>
                    <p class="form-control-static"><?= __d('gl', "本機能はPCにてご利用できます。") ?></p>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <?php if ($this->Session->read('ua.device_type') == 'Desktop'): ?>
        <div class="panel-footer">
            <div class="row">
                <div class="col-sm-9 col-sm-offset-3">
                    <a href="#" class="btn btn-default" data-toggle="modal"
                       data-target="#ModalAddMembersByCsv"><?= __d('gl', '新しいメンバーを追加') ?></a>
                    <a href="#" class="btn btn-default" data-toggle="modal"
                       data-target="#ModalEditMembersByCsv"><?= __d('gl', 'メンバーの情報を更新') ?></a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<!-- END app/View/Elements/Team/batch_setup.ctp -->
<?php $this->start('modal') ?>
<?= $this->element('modal_add_members_by_csv') ?>
<?= $this->element('modal_edit_members_by_csv') ?>
<?php $this->end() ?>
