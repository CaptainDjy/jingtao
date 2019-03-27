<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'cate' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['goods/cate']) ?>">商品分类</a>
    </li>
    <li <?= Yii::$app->controller->action->id == 'cate-edit' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['goods/cate-edit']) ?>"><?= isset($_GET['id']) && Yii::$app->controller->action->id == 'cate-edit' ? '编辑' : '添加'; ?>分类</a>
    </li>
</ul>
