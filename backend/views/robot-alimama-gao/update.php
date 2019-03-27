<?php

use backend\widgets\MyActiveForm;
use common\models\GoodsCategory;
use common\models\RobotAlimamaGao;
use yii\bootstrap\Html;

/**
 * @var $this \yii\web\View
 * @var $model RobotAlimamaGao
 * @var $id int
 */

?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($model, 'title'); ?>
                <?= $form->field($model, 'from_cid')->dropDownList(RobotAlimamaGao::FROM_CATEGORY, ['prompt' => '请选择', 'style' => 'width:300px;']); ?>
                <?= $form->field($model, 'to_cid')->dropDownList(GoodsCategory::map(), ['prompt' => '请选择', 'style' => 'width:300px;']); ?>
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
