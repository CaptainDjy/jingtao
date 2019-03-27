<?php

/**
 * @var $model backend\models\SystemAuthItem
 * @var $parent_title string
 * @var $pid integer
 * @var $level integer
 */
/** @var  $item */
/** @var array $model */

use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

?>
<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'validationUrl' => Url::toRoute(['system-user/update']),
]); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <span>×</span><span class="sr-only">关闭</span>
    </button>
    <h4 class="modal-title">授权角色</h4>
</div>
<div class="modal-body">
    <?= $form->field($item, 'role_id')->dropDownList(ArrayHelper::map($model, 'id', 'name'), ['value' => isset($item['id']) ? $item['id'] : 0])->label(false); ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
    <button class="btn btn-primary">保存内容</button>
</div>
<?php ActiveForm::end(); ?>



