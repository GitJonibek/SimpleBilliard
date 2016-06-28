<?php
/**
 * Created by PhpStorm.
 * User: bigplants
 * Date: 7/16/15
 * Time: 1:45 PM
 *
 * @var $posts
 * @var $model
 */
?>
<!-- START app/View/Elements/cube_img_blocks.ctp -->
<?php if (isset($posts) && !empty($posts)): ?>
    <div class="cube-img-column">
        <?php foreach ($posts as $post): ?>
            <div class="cube-img-column-frame">
                    <?php if (viaIsSet($post['ActionResult']['ActionResultFile'][0]['AttachedFile'])): ?>
                        <!-- アクション画像がある場合 -->
                        <?= $this->Html->image('ajax-loader.gif',
                                               [
                                                   'class'         => 'cube-img-blocks-img lazy',
                                                   'data-original' => $this->Upload->uploadUrl($post['ActionResult']['ActionResultFile'][0]['AttachedFile'],
                                                                                               "AttachedFile.attached",
                                                                                               ['style' => 'small']),
                                               ]
                        );
                        ?>

                    <?php else: ?>
                        <!-- アクション画像がない場合 -->
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php
                            if (!empty($post['ActionResult']["photo{$i}_file_name"]) || $i == 5) {
                                echo $this->Html->image('ajax-loader.gif',
                                                        [
                                                            'class'         => 'lazy img-responsive',
                                                            'width'         => '186',
                                                            'height'        => '186',
                                                            'data-original' => $this->Upload->uploadUrl($post,
                                                                                                        "ActionResult.photo$i",
                                                                                                        ['style' => 'small']),
                                                        ]
                                );
                                break;
                            }
                            ?>
                        <?php endfor; ?>
                    <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<!-- END app/View/Elements/cube_img_blocks.ctp -->
