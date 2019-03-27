<?php

/**
 * @var $model \common\models\AdvertPosition
 */

use common\helpers\MyHtml;
use common\widgets\kindeditor\KindeditorWidget;
use yii\helpers\Html;
use backend\widgets\MyActiveForm;

?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($model, 'title')->textInput(['placeholder' => '请输入标题']); ?>
                <?= $form->field($model, 'op')->textInput(['placeholder' => '请输入标识']); ?>
                <?= $form->field($model, 'width')->textInput(['placeholder' => '请输入宽度']); ?>
                <?= $form->field($model, 'height')->textInput(['placeholder' => '请输入高度']); ?>
                <?= $form->field($model, 'img')->widget(KindeditorWidget::class,
                    [
                        'editorType' => 'image-dialog',
                        'params' => [
                            'remark' => '建议尺寸200*200'
                        ]
                    ]
                ) ?>
                <?= $form->field($model, 'remark')->textarea(['placeholder' => '备注', 'rows' => '6', 'style' => ['resize' => 'vertical']]); ?>
                <?= $form->field($model, 'status')->radioList([1 => '显示', 0 => '隐藏'], ['item' => [MyHtml::class, 'radioListItem']]); ?>
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

