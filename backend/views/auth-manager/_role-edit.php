<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/14 16:08
 */

/**
 * @var $model backend\models\SystemAuthItem
 */

use backend\models\SystemAuthItem;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'validationUrl' => Url::toRoute(['auth-manager/role-edit', 'name' => $model['name']]),
]); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <span>×</span><span class="sr-only">关闭</span>
    </button>
    <h4 class="modal-title">角色编辑</h4>
</div>
<div class="modal-body">
    <?= $form->field($model, 'name')->textInput()->label('角色名称') ?>
    <?= $form->field($model, 'type')->hiddenInput(['value' => SystemAuthItem::ROLE])->label(false) ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
    <button class="btn btn-primary">保存内容</button>
</div>
<?php ActiveForm::end(); ?>
