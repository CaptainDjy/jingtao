<?php

/**
 * @var $model backend\models\SystemAuthItem
 * @var $parent_title string
 * @var $pid integer
 * @var $level integer
 */

use backend\models\SystemAuthItem;
use common\widgets\kindeditor\KindeditorWidget;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

?>
<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'validationUrl' => Url::toRoute(['/user/real-reject']),
]); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <span>×</span><span class="sr-only">关闭</span>
    </button>
    <h4 class="modal-title">驳回</h4>
</div>
<div class="modal-body">
    <?= $form->field($model, 'remark')->textarea(['placeholder' => '请输入驳回原因', 'rows' => 5]) ?>
    <!--    < ?= $form->field($model, 'remark')->label('驳回内容')->widget(KindEditorWidget::className(), [-->
    <!--        'clientOptions' => [-->
    <!--            'width' => '100%',-->
    <!--            'height' => '500px',-->
    <!--            'themeType' => 'simple',-->
    <!--        ],-->
    <!--    ]);-->
    <!--    ?>-->
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
    <button class="btn btn-primary">保存内容</button>
</div>
<?php ActiveForm::end(); ?>



