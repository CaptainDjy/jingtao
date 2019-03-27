<?php
use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'list' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['system-user/list']) ?>">用户管理</a>
    </li>
    <?php if (Yii::$app->controller->action->id == 'update'): ?>
        <li class="active">
            <a href="<?= Url::to(['system-user/update']) ?>">添加用户</a>
        </li>
    <?php endif; ?>
</ul>
