<?php
/**
 * @var $model backend\models\SystemMenu
 * @var $list array
 */

use backend\widgets\MyActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($model, 'title')->textInput(['placeholder' => '请输入标题']); ?>
                <?= $form->field($model, 'link')->textInput(['placeholder' => '完整的Url 比如：site/index&type=1，可为空']); ?>
                <?= $form->field($model, 'pid')->dropDownList(['0' => '顶级菜单'] + ArrayHelper::map($list, 'id', 'title'), ['value' => isset($model['pid']) ? $model['pid'] : 0, 'style' => 'width:200px;']); ?>
                <?= $form->field($model, 'group')->textInput(['placeholder' => '顶级菜单有效，子菜单请和上级一致']); ?>
                <?= $form->field($model, 'icon')->textInput(['placeholder' => 'Font Awesome 字体图标 例如:fa fa-home']); ?>
                <?= $form->field($model, 'sort')->textInput()->label('排序'); ?>
                <?= $form->field($model, 'remark')->textarea(); ?>
                <?= $form->field($model, 'isShow')->checkbox([1 => '是', 'style' => 'margin: 11px 0 0;'], false); ?>
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
