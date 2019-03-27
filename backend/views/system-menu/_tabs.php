<?php
use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'list' ? ' class="active"' : '' ?>>
        <a href="<?= Url::toRoute(['system-menu/list']) ?>">菜单管理</a>
    </li>
    <?php if (Yii::$app->controller->action->id == 'update'): ?>
        <li class="active">
            <a href="<?= Url::toRoute(['system-menu/update']) ?>">添加菜单</a>
        </li>
    <?php endif; ?>
</ul>
