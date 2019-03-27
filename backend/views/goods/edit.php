<?php

/**
 * @var $model \common\models\Goods
 */

use common\helpers\MyHtml;
use yii\helpers\Html;
use backend\widgets\MyActiveForm;
use common\widgets\kindeditor\KindeditorWidget;
use common\widgets\daterangepicker\DateRangePicker;
$this->title = '商品管理';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($model, 'title')->textInput(['placeholder' => '商品标题']); ?>
                <?= $form->field($model, 'sub_title')->textInput(['placeholder' => '商品副标题']); ?>

                <?= $form->field($model, 'thumb')->widget(KindeditorWidget::class,
                    [
                        'editorType' => 'image-dialog',
                        'params' => [
                            'remark' => '建议尺寸50*50'
                        ]
                    ]
                ) ?>

                <?= $form->field($model, 'cid')->textInput(['placeholder' => '所属分类']); ?>
                <?= $form->field($model, 'coupon_link')->textInput(['placeholder' => '优惠券地址']); ?>
                <?= $form->field($model, 'commission_money')->textInput(['placeholder' => '佣金金额']); ?>
                <?= $form->field($model, 'origin_price')->textInput(['placeholder' => '原价']); ?>
                <?= $form->field($model, 'coupon_price')->textInput(['placeholder' => '优惠券后价格']); ?>
                <?= $form->field($model, 'coupon_money')->textInput(['placeholder' => '优惠券金额']); ?>
                <?= $form->field($model, 'coupon_remained')->textInput(['placeholder' => '优惠券数量']); ?>
                <?= $form->field($model, 'end_time')->textInput(['placeholder' => '过期时间']); ?>
<!--                <div class="form-group">-->
<!--                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">时间范围</label>-->
<!--                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-4">-->
<!--                        <?//= DateRangePicker::widget([
//                            'name' => 'date',
//                            'clientOptions' => [
//                               'startDate' =>$searchArr['date']['start'],
//                               'endDate' => $searchArr['date']['end'],
//                            ],
//
//                        ]) ?>-->
<!--                    </div>-->
<!--                </div>-->
                <?= $form->field($model, 'description')->textarea(['placeholder' => '商品描述', 'rows' => '2', 'style' => ['resize' => 'vertical']]); ?>
<!--                <?//= $form->field($model, 'settop')->radioList([1 => '是', 0 => '否'], ['item' => [MyHtml::class, 'radioListItem']]); ?>-->
                <?= $form->field($model, 'choice')->radioList([1 => '是', 0 => '否'], ['item' => [MyHtml::class, 'radioListItem']]); ?>
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

