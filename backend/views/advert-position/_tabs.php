<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'index' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['advert-position/index']) ?>">广告位列表</a>
    </li>
    <li <?= Yii::$app->controller->action->id == 'edit' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['advert-position/edit']) ?>"><?= isset($_GET['id']) && Yii::$app->controller->action->id == 'edit' ? '编辑' : '添加'; ?>广告位</a>
    </li>
</ul>
