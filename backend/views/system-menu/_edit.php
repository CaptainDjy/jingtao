<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/14 16:08
 */

/**
 * @var $model backend\models\SystemMenu
 * @var $parent_title string
 * @var $pid integer
 * @var $level integer
 */

use common\helpers\MyHtml;
use yii\widgets\ActiveForm;
use yii\helpers\Url;


?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'validationUrl' => Url::toRoute(['system-menu/edit']),
]); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <span>×</span><span class="sr-only">关闭</span>
    </button>
    <h4 class="modal-title">上级菜单:<?= $parent_title ?></h4>
</div>

<div class="modal-body">
    <?= $form->field($model, 'title')->textInput(['placeholder' => '请输入标题']); ?>
    <?= $form->field($model, 'link')->textInput(['placeholder' => '完整的Url 比如：site/index&type=1，可为空']); ?>
    <?= $form->field($model, 'group')->textInput(['placeholder' => '顶级菜单有效，子菜单请和上级一致']); ?>
    <?= $form->field($model, 'icon')->textInput(['placeholder' => 'Font Awesome 字体图标 例如:fa fa-home']); ?>
    <?= $form->field($model, 'sort')->textInput()->label('排序'); ?>
    <?= $form->field($model, 'remark')->textarea(); ?>
    <?= $form->field($model, 'isShow')->radioList([1 => '是', 0 => '否'], ['item' => [MyHtml::class, 'radioListItem']]); ?>
    <?= $form->field($model, 'pid')->hiddenInput(['value' => $pid])->label(false); ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
    <button class="btn btn-primary">保存内容</button>
</div>
<?php ActiveForm::end(); ?>
