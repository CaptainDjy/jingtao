<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'cate' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['biz/cate']) ?>">分类列表</a>
    </li>
    <li <?= Yii::$app->controller->action->id == 'cate-edit' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['biz/cate-edit']) ?>"><?= isset($_GET['id']) && Yii::$app->controller->action->id == 'cate-edit' ? '编辑' : '添加'; ?>分类</a>
    </li>
</ul>
