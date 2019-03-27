<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/26 16:19
 */

/**
 * @var \backend\models\DistributionConfig $config
 * @var string $name
 * @var array $config
 */

use yii\helpers\Html;

$this->title = '参数设置';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs', ['name' => $name]); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <?= Html::beginForm('', 'post', ['class' => 'form-horizontal']); ?>
            <?= $this->render('_' . $name, ['config' => $config]); ?>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                <div class="col-xs-12 col-sm-5">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
            <?= Html::endForm() ?>
        </div>

    </div>
</div>

