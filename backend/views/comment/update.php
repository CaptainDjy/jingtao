<?php
/**
 * @var $model backend\models\SystemMenu
 * @var $list array
 */

use backend\widgets\MyActiveForm;
use yii\helpers\Html;

?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($model, 'uid')->textInput(['placeholder' => '完整的Url 比如：site/index&type=1，可为空']); ?>
                <?= $form->field($model, 'gid')->textInput(['placeholder' => '完整的Url 比如：site/index&type=1，可为空']); ?>
                <?= $form->field($model, 'type')->radioList(['1' => '商家', '0' => '会员']); ?>
                <?= $form->field($model, 'sort')->textInput()->label('排序'); ?>
                <?= $form->field($model, 'status')->checkbox([1 => '是', 'style' => 'margin: 11px 0 0;'], false); ?>
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
