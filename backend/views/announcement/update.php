<?php

use yii\helpers\Html;
use common\widgets\kindeditor\KindeditorWidget;
use backend\widgets\MyActiveForm;
use common\helpers\MyHtml;

/**
 * @var $item \common\models\Announcement
 */
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($item, 'title')->textInput(['placeholder' => '文章标题（必填）']); ?>
                <?= $form->field($item, 'remark')->textInput(['placeholder' => '文章摘要']); ?>
                <?= $form->field($item, 'status')->radioList([1 => '启用', 9 => '禁用'], ['item' => [MyHtml::class, 'radioListItem']]); ?>
                <?= $form->field($item, 'content')->label('文章内容')->widget(KindEditorWidget::class, [
                    'clientOptions' => [
                        'width' => '100%',
                        'height' => '500px',
                        'themeType' => 'simple'
                    ],
                ]) ?>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                    <div class="col-xs-12 col-sm-5">
                        <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
                    </div>
                </div>
                <?php MyActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>

