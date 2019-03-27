<?php
use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'list' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['comment/list']) ?>">评论管理</a>
    </li>
    <?php if (Yii::$app->controller->action->id == 'update'): ?>
        <li class="active">
            <a href="<?= Url::to(['channel/update']) ?>">添加菜单</a>
        </li>
    <?php endif; ?>
</ul>
