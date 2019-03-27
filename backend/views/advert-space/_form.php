<?php
/**
 * @author pine
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * createtime: 2018/05/26 17:00
 */

/**
 * @var $this yii\web\View
 * @var $model common\models\User
 * @var $form backend\widgets\MyActiveForm
 */

use backend\widgets\MyActiveForm;
use common\helpers\MyHtml;
use yii\helpers\Html;

?>

<div class="box-body">
    <?php $form = MyActiveForm::begin(); ?>
    <?= $form->field($model, 'pid')->textInput(['readonly' => 'true',]); ?>
    <?= $form->field($model, 'uid')->textInput(['placeholder' => '请输入会员ID',]); ?>
    <?= $form->field($model, 'title')->textInput(['placeholder' => '请输入名称',]); ?>
    <?= $form->field($model, 'status')->radioList([0 => '未启用', 1 => '已启用'], ['item' => [MyHtml::class, 'radioListItem']])->label('状态'); ?>
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
        <div class="col-xs-12 col-sm-5">
            <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <?php MyActiveForm::end(); ?>
</div>
