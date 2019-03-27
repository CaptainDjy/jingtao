<?php
/**
 * @var $this yii\web\View
 * @var $model common\models\Config
 */

use backend\widgets\MyActiveForm;
use common\helpers\MyHtml;
use common\models\Config;
use yii\helpers\Html;

$this->title = '配置修改';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($model, 'name')->textInput(['placeholder' => '配置标识只能使用英文且不能重复,英文之间用_分割']); ?>
                <?= $form->field($model, 'title')->textInput(['placeholder' => '用于后台显示的配置标题']); ?>
                <?= $form->field($model, 'sort')->textInput(['placeholder' => '用于分组显示的顺序']); ?>
                <?= $form->field($model, 'type')->dropDownList(Config::getConfig('CONFIG_TYPE_LIST'), ['style' => 'width:200px;']); ?>

                <?= $form->field($model, 'group')->dropDownList(Config::getConfig('CONFIG_GROUP_LIST'), ['style' => 'width:200px;']); ?>
                <?= $form->field($model, 'value')->textarea(['rows' => 6, 'placeholder' => '配置值,如数组类型,需 键对应值,例如 a:b【换行】c:d']); ?>
                <?= $form->field($model, 'extra')->textarea(['rows' => 6, 'placeholder' => '如果配类型是单选按钮或下拉框需要配置该项,例如 0:开启【换行】1:关闭'])->hint('如果配类型是单选按钮或下拉框需要配置该项,例如 0:开启【换行】1:关闭'); ?>
                <?= $form->field($model, 'remark')->textarea(['rows' => 6, 'placeholder' => '配置详细说明']); ?>
                <?= $form->field($model, 'status')->radioList([1 => '显示', 0 => '隐藏'], ['item' => [MyHtml::className(), 'radioListItem']])->label('是否显示'); ?>
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
