<?php

/**
 * @var $model \common\models\BizCategory
 */

use common\widgets\kindeditor\KindeditorWidget;
use yii\helpers\Html;
use backend\widgets\MyActiveForm;

$this->title = '商家管理';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs-cate'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($model, 'title')->textInput(['placeholder' => '分类名']); ?>
                <?= $form->field($model, 'pic')->widget(KindeditorWidget::class,
                    [
                        'editorType' => 'image-dialog',
                        'params' => [
                            'remark' => '建议尺寸50*50'
                        ]
                    ]
                ) ?>
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

