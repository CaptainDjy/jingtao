<?php
/**
 * @var $item backend\models\SystemMenu
 * @var $list array
 */
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\kindeditor\KindeditorWidget;
use backend\widgets\MyActiveForm;
use common\helpers\MyHtml;

?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($item, 'uid')->textInput(['placeholder' => '用户ID']); ?>
                <?= $form->field($item, 'order_id')->textInput(['placeholder' => '订单号']); ?>
                <?= $form->field($item, 'consumption')->radioList([1 => '省级', 2 => '市级', 3 => '区县级'], ['item' => [MyHtml::class, 'radioListItem']]); ?>
                <?= $form->field($item, 'level')->radioList([1 => '一级', 2 => '二级', 3 => '三级'], ['item' => [MyHtml::class, 'radioListItem']]); ?>
                <?= $form->field($item, 'province')->textInput(); ?>
                <?= $form->field($item, 'city')->textInput(); ?>
                <?= $form->field($item, 'county')->textInput(); ?>
                <?= $form->field($item, 'price')->textInput(); ?>
                <?= $form->field($item, 'pay_type')->radioList([1 => '微信支付', 2 => '支付宝支付', 3 => '手动打款'], ['item' => [MyHtml::class, 'radioListItem']]); ?>

                <?= $form->field($item, 'remark')->textarea(['rows' => 5]); ?>
                <?= $form->field($item, 'status')->radioList([0 => '未审核', 1 => '审核中', 2 => '已审核', 4 => "取消合伙人"], ['item' => [MyHtml::class, 'radioListItem']])->label('是否审核');; ?>
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

