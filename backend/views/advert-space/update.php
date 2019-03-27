<?php
/**
 * @author 河南邦耀网络科技
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * createtime: 2018/05/26 17:00
 */

/**
 * 前端会员添加编辑
 * @var $this yii\web\View
 * @var $model common\models\User
 */

$this->title = '更新推广位';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <?= $this->render('_form', [
                'model' => $model,
            ]); ?>
        </div>
    </div>
</div>
