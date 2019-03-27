<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/18
 * Time: 14:01
 */

/** @var string $uploadSuccessPath */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(["options" => ["enctype" => "multipart/form-data"]]);
?>
<?= $form->field($model, "file")->fileInput() ?>

    <button>Submit</button>

<?= $uploadSuccessPath ?>
<?php ActiveForm::end(); ?>


<div class="box box-success">
    <div class="box-body">
        <?= Html::beginForm(['advert-space/import'], 'get', ['class' => 'form-horizontal']) ?>
        <div class="form-group">
            <?= Html::submitButton('提交', ['class' => 'btn btn-success']); ?>

        </div>
        <?= Html::endForm() ?>
    </div>
</div>
