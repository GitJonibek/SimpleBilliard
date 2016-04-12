<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 *
 * @var CodeCompletionView $this
 * @var                    $index
 * @var                    $id
 */
?>
<!-- START app/View/Elements/Team/confirm_to_inactive_score_modal.ctp -->
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close font_33px close-design" data-dismiss="modal" aria-hidden="true"><span
                    class="close-icon">&times;</span></button>
            <h4 class="modal-title"><?= __("Confirm to delete the score") ?></h4>
        </div>
        <div class="modal-body">
            <div class="col col-xxs-12">
                <p><?= __("Even if you delete a score, there is no affection to the past data.") ?></p>

                <p><?= __("After deleting, you can't select it.") ?></p>

                <p><?= __("Do you really want to delete this score definition?") ?></p>
            </div>
        </div>
        <div class="modal-footer">
            <?=
            $this->Form->postLink(__("Delete"),
                                  ['controller' => 'teams', 'action' => 'to_inactive_score', 'team_id' => $id],
                                  ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
</div>
<!-- END app/View/Elements/Team/confirm_to_inactive_score_modal.ctp -->
