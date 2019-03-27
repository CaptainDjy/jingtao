<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/14 16:08
 */

/**
 * @var $model backend\models\SystemAuthItem
 */
/** @var string $parent_title */

use yii\widgets\ActiveForm;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'validationUrl' => Url::toRoute(['system-user/user-edit', 'id' => $model['id']]),
]); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <span>×</span><span class="sr-only">关闭</span>
    </button>
    <h4 class="modal-title">用户管理:<?= $parent_title ?></h4>
</div>
<div class="modal-body">
    <?= $form->field($model, 'username')->textInput()->label('登陆名称') ?>
    <?= $form->field($model, 'password_hash')->passwordInput()->label('登陆密码') ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
    <button class="btn btn-primary">保存内容</button>
</div>
<?php ActiveForm::end(); ?>
