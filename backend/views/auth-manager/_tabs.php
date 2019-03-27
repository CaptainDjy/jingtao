<?php
use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'role-list' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['auth-manager/role-list']) ?>">角色管理</a>
    </li>
    <?php if (Yii::$app->controller->action->id == 'role-accredit'): ?>
        <li class="active">
            <a href="<?= Url::to(['auth-manager/role-accredit']) ?>">角色授权</a>
        </li>
    <?php endif; ?>
    <li <?= Yii::$app->controller->action->id == 'item-list' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['auth-manager/item-list']) ?>">权限管理</a>
    </li>
    <li <?= Yii::$app->controller->action->id == 'auth-rule' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['auth-manager/auth-rule']) ?>">规则管理</a>
    </li>
</ul>
