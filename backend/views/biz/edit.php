<?php

/**
 * @var $model \common\models\Biz
 */

use common\models\BizCategory;
use common\widgets\kindeditor\KindeditorWidget;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use backend\widgets\MyActiveForm;

$biz_cate = ArrayHelper::map(BizCategory::find()->select(['id', 'title'])->asArray()->all(), 'id', 'title');
$this->title = '商家管理';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($model, 'cid')->widget(Select2::class, [
                    'data' => $biz_cate,
                    'theme' => Select2::THEME_DEFAULT,
                    'options' => [
                        'placeholder' => '请选择',
                    ],
                ]); ?>
                <?= $form->field($model, 'title')->textInput(['placeholder' => '商家名称']); ?>
                <?= $form->field($model, 'img')->widget(KindeditorWidget::class,
                    [
                        'editorType' => 'image-dialog',
                        'params' => [
                            'remark' => '建议尺寸200*200'
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

