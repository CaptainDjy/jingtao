<?php
/**
 * 前端会员添加编辑
 * @author
 */

/**
 * @var $model common\models\User
 */
use common\widgets\kindeditor\KindeditorWidget;
use backend\widgets\MyActiveForm;
use common\helpers\MyHtml;
use yii\helpers\Html;

?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <?php $form = MyActiveForm::begin(); ?>
            <?= $form->field($model, 'realname')->textInput(['placeholder' => '请输入会员真实姓名', 'maxlength' => 15]); ?>
            <?= $form->field($model, 'mobile')->textInput(['placeholder' => '请输入11位手机号', 'maxlength' => 11]); ?>
            <?= $form->field($model, 'idcard')->textInput(['placeholder' => '请输入会员身份证号', 'maxlength' => 18]); ?>
            <?php if (empty($model->gender)) {
                $model->gender = 1;
            } ?>
            <?= $form->field($model, 'gender')->radioList([1 => '男', 2 => '女'], ['item' => [MyHtml::class, 'radioListItem']]); ?>
            <?= $form->field($model, 'identity0')->widget(KindeditorWidget::class, ['editorType' => 'image-dialog', 'params' => ['remark' => '建议尺寸200*200']]) ?>
            <?= $form->field($model, 'identity1')->widget(KindeditorWidget::class, ['editorType' => 'image-dialog', 'params' => ['remark' => '建议尺寸200*200']]) ?>
            <?= $form->field($model, 'identity2')->widget(KindeditorWidget::class, ['editorType' => 'image-dialog', 'params' => ['remark' => '建议尺寸200*200']]) ?>
            <!--            < ?php if(empty($model->real_status)){$model->real_status = 1;} ?>-->
            <?= $form->field($model, 'real_status')->radioList([0 => '未认证', 1 => '认证中', 2 => '已认证'], ['item' => [MyHtml::class, 'radioListItem']]); ?>
            <!--            < ?php-->
            <!--                if($model->real_status == 0):-->
            <!--                    echo $form->field($model,'remark')->textInput(['placeholder'=>'请输入驳回原因']);-->
            <!--                endif;-->
            <!--            ?>-->
            <?php if (empty($model->status)) {
                $model->status = 0;
            } ?>
            <?= $form->field($model, 'status')->radioList([0 => '正常', 9 => '禁用'], ['item' => [MyHtml::class, 'radioListItem']]); ?>
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
