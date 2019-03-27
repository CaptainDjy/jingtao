<?php

/**
 * @var $model \common\models\Advert
 */

use common\helpers\MyHtml;
use common\models\Advert;
use common\models\AdvertPosition;
use common\widgets\kindeditor\KindeditorWidget;
use kartik\datetime\DateTimePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use backend\widgets\MyActiveForm;

$advert_position = \yii\helpers\ArrayHelper::map(AdvertPosition::find()->select(['id', 'title'])->asArray()->all(), 'id', 'title');
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($model, 'position_id')->widget(Select2::class, [
                    'data' => $advert_position,
                    'theme' => Select2::THEME_DEFAULT,
                    'options' => [
                        'placeholder' => '请选择',
                    ],
                ]); ?>
                <?= $form->field($model, 'title')->textInput(['placeholder' => '请输入标题']); ?>
                <?= $form->field($model, 'url')->textInput(['placeholder' => '请输入链接']); ?>
                <?= $form->field($model, 'img')->widget(KindeditorWidget::class,
                    [
                        'editorType' => 'image-dialog',
                        'params' => [
                            'remark' => '建议尺寸200*200'
                        ]
                    ]
                ) ?>
                <?= $form->field($model, 'deadline')->widget(DateTimePicker::class, [
                    'options' => ['placeholder' => '截止日期'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd hh:00',
                        'minView' => 'day',
                        'todayBtn' => true,
                    ]
                ]); ?>
                <?= $form->field($model, 'status')->radioList(Advert::STATUS_LABEL, ['item' => [MyHtml::class, 'radioListItem']]); ?>
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

