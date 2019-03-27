<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\User
 * @var $form backend\widgets\MyActiveForm
 */


use backend\widgets\MyActiveForm;
use common\helpers\MyHtml;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$data = [
//    ['lv' => 0, 'name' => '粉丝'],
    ['lv' => 1, 'name' => '会员'],
    ['lv' => 2, 'name' => '代理'],
    ['lv' => 3, 'name' => '总代'],
];
$relation['superior'] = rtrim($model['superior'], '_0');
$rela = explode('_', $relation['superior']);

?>

<div class="box-body">
    <?php $form = MyActiveForm::begin(); ?>
<!--  <?//= $form->field($model, 'realname')->textInput(['placeholder' => '请输入会员真实姓名', 'maxlength' => 15]); ?>  -->
    <?= $form->field($model, 'nickname')->textInput(['placeholder' => '用户昵称', 'maxlength' => 15]); ?>
    <?= $form->field($model, 'mobile')->textInput(['placeholder' => '请输入11位手机号', 'maxlength' => 11]); ?>
    <?= $form->field($model, 'password_hash')->passwordInput(['placeholder' => '请输入8-16位字母加数字的密码', 'maxlength' => 16, 'minlength' => 6]); ?>
    <?= $form->field($model, 'lv')->dropDownList(ArrayHelper::map($data, 'lv', 'name')); ?>
<!--    <?//= $form->field($model, 'superior')->textInput(['placeholder' => '请输入推荐人手机号', 'maxlength' => 11, 'value' => $rela[0]]); ?>-->
<!--    <?//= $form->field($model, 'status')->radioList([0 => '正常', 9 => '禁用'], ['item' => [MyHtml::class, 'radioListItem']])->label('状态'); ?>-->
    <!--    < ?= $form->field($model, 'report_center')->radioList([0 => '否', 1 => '是'], ['item' => [MyHtml::class, 'radioListItem']])->label('是否报单中心'); ?>-->
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
        <div class="col-xs-12 col-sm-5">
            <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <?php MyActiveForm::end(); ?>
</div>
