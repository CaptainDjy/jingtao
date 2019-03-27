<?php

/**
 * @var $model \common\models\Article
 */

use common\helpers\MyHtml;
use common\models\Article;
use common\models\ArticleCategory;
use common\widgets\kindeditor\KindeditorWidget;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use backend\widgets\MyActiveForm;

$category = ArrayHelper::map(ArticleCategory::find()->select(['id', 'title'])->asArray()->all(), 'id', 'title');
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?php $form = MyActiveForm::begin(); ?>
                <?= $form->field($model, 'cid')->widget(Select2::class, [
                    'data' => $category,
                    'theme' => Select2::THEME_DEFAULT,
                    'options' => [
                        'placeholder' => '请选择',
                    ],
                ]); ?>
                <?= $form->field($model, 'title')->textInput(['placeholder' => '请输入文章标题']); ?>
<!--                <?//= $form->field($model, 'description')->textarea(['placeholder' => '文章描述', 'rows' => '2', 'style' => ['resize' => 'vertical']]); ?>-->
<!--                <?//= $form->field($model, 'url')->textInput(['placeholder' => '跳转链接'])->hint('若填写跳转链接则点击文章跳转到该链接', ['style' => 'color:red']); ?>-->
<!--                <?//= $form->field($model, 'small_img')->widget(KindeditorWidget::class,
//                    [
//                        'editorType' => 'image-dialog',
//                        'params' => [
//                            'remark' => '建议尺寸200*200'
//                        ]
//                    ]
//                ) ?>-->
<!--                <?//= $form->field($model, 'img')->widget(KindeditorWidget::class,
//                    [
//                        'editorType' => 'image-dialog',
//                        'params' => [
//                            'remark' => '建议尺寸200*200'
//                        ]
//                    ]
//                ) ?>-->
                <?= $form->field($model, 'content')->label('文章内容')->widget(KindEditorWidget::class, [
                    'clientOptions' => [
                        'width' => '100%',
                        'height' => '500px',
                        'themeType' => 'simple'
                    ],
                ]) ?>

                <?= $form->field($model, 'status')->radioList(Article::STATUS_LABEL, ['item' => [MyHtml::class, 'radioListItem']]); ?>
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

