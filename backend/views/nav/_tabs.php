<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'index' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['nav/index']) ?>">导航管理</a>
    </li>
    <li <?= Yii::$app->controller->action->id == 'edit' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['nav/edit']) ?>"><?= isset($_GET['id']) && Yii::$app->controller->action->id == 'edit' ? '编辑' : '添加'; ?>导航</a>
    </li>
</ul>
