<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 *
 * @var CodeCompletionView $this
 * @var                    $index
 */
if (!viaIsSet($index) || !is_numeric($index)) {
    $index = 0;
}
?>

<!-- START app/View/Elements/Team/eval_score_form_elm.ctp -->
<tr class="evaluation_select"
    index="<?php echo $index ?>" id="AddScore_<?= $index ?>">
    <td>
        <div class="form-group">

            <?= $this->Form
                ->input("EvaluateScore.$index.name",
                        array(
                            'wrapInput'                => false,
                            'type'                     => 'text',
                            'div'                      => false,
                            'label'                    => false,
                            'class'                    => 'form-control',
                            'placeholder'              => __d('team', '名前'),
                            'data-bv-notempty-message' => __d('gl', "入力必須項目です。"),
                            'required'                 => true,
                        ))
            ?>
        </div>
    </td>
    <td>
        <div class="form-group">

            <?= $this->Form
                ->input("EvaluateScore.$index.index_num",
                        array(
                            'wrapInput'                => false,
                            'type'                     => 'number',
                            'div'                      => false,
                            'label'                    => false,
                            'class'                    => 'form-control',
                            'placeholder'              => __d('team', '表示順'),
                            'data-bv-notempty-message' => __d('gl', "入力必須項目です。"),
                            'data-bv-integer-message'  => __d('gl', "数字を入力してください。"),
                            'required'                 => true,
                        ))
            ?>
        </div>
    </td>
    <td>
        <div class="form-group">

            <?= $this->Form
                ->input("EvaluateScore.$index.description",
                        array(
                            'wrapInput'                => false,
                            'type'                     => 'textarea',
                            'rows'                     => 3,
                            'div'                      => false,
                            'label'                    => false,
                            'class'                    => 'form-control',
                            'placeholder'              => __d('team', '定義の説明を書きましょう'),
                            'data-bv-notempty-message' => __d('gl', "入力必須項目です。"),
                            'required'                 => true,
                        ))
            ?>
        </div>
    </td>
    <td>
        <a href="#" class="click-target-remove" target-selector="#AddScore_<?= $index ?>"><i
                class="fa fa-times font_lightGray-gray"></i></a>
    </td>
</tr>
<!-- END app/View/Elements/Team/eval_score_form_elm.ctp -->
