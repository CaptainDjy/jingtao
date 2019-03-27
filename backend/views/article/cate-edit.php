<?php

/**
 * @var $model \common\models\GoodsCategory
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
                <?= $form->field($model, 'title')->textInput(['placeholder' => '分类标题']); ?>
                <?= $form->field($model, 'img')->widget(KindeditorWidget::class,
                    [
                        'editorType' => 'image-dialog',
                        'params' => [
                            'remark' => '建议尺寸200*200'
                        ]
                    ]
                ) ?>
                <?= $form->field($model, 'status')->radioList([0 => '显示', 9 => '隐藏'], ['item' => [MyHtml::class, 'radioListItem']]); ?>
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

