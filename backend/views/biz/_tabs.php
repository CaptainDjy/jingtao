<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'index' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['biz/index']) ?>">商家列表</a>
    </li>
    <li <?= Yii::$app->controller->action->id == 'edit' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['biz/edit']) ?>"><?= isset($_GET['id']) && Yii::$app->controller->action->id == 'edit' ? '编辑' : '添加'; ?>商家</a>
    </li>
</ul>
