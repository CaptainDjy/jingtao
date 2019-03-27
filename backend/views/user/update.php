<?php
/**
 * 前端会员添加编辑
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * @createtime: 2017/8/17 18:42
 */

/**
 * @var $model common\models\User
 */

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
