<?php

/**
 * @var $model \common\models\Nav
 */

use common\helpers\MyHtml;
use common\models\Nav;
use common\widgets\kindeditor\KindeditorWidget;
use yii\helpers\Html;
use backend\widgets\MyActiveForm;

$this->title = '导航管理';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($model, 'title')->textInput(['placeholder' => '请输入标题']); ?>
<!--                <?//= $form->field($model, 'type')->radioList(Nav::TYPE_LABEL, ['item' => [MyHtml::class, 'radioListItem']]); ?>-->
                <?= $form->field($model, 'url')->textInput(['placeholder' => '请输入链接']); ?>
                <?= $form->field($model, 'img')->widget(KindeditorWidget::class,
                    [
                        'editorType' => 'image-dialog',
                        'params' => [
                            'remark' => '建议尺寸200*200'
                        ]
                    ]
                ) ?>
                <?= $form->field($model, 'sort')->textInput(['placeholder' => '排序']); ?>
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

