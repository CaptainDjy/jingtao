<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'index' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['advert/index']) ?>">广告管理</a>
    </li>
    <li <?= Yii::$app->controller->action->id == 'edit' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['advert/edit']) ?>"><?= isset($_GET['id']) && Yii::$app->controller->action->id == 'edit' ? '编辑' : '添加'; ?>广告</a>
    </li>
</ul>
