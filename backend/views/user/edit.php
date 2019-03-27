<?php

/**
 * @var $model backend\models\SystemAuthItem
 * @var $parent_title string
 * @var $pid integer
 * @var $level integer
 */

use yii\widgets\ActiveForm;
use yii\helpers\Url;

?>
<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'validationUrl' => Url::toRoute(['/user/update']),
]); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <span>×</span><span class="sr-only">关闭</span>
    </button>
    <h4 class="modal-title">积分修改</h4>
</div>
<div class="modal-body">
    <?= $form->field($model, 'mobile')->textInput(['placeholder' => '请输入会员手机号']); ?>
    <?= $form->field($model, 'credit2')->textInput(['placeholder' => '请输入充值金额']); ?>
    <?= $form->field($model, 'credit6')->textInput(['placeholder' => '请输入充值金额']); ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
    <button class="btn btn-primary">保存内容</button>
</div>
<?php ActiveForm::end(); ?>



