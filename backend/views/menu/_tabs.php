<?php
use yii\helpers\Url;

?>
<ul class="nav nav-tabs">

    <li <?= Yii::$app->controller->action->id == 'item-list' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['menu/item-list']) ?>">前台菜单</a>
    </li>

    <?php if (Yii::$app->controller->action->id == '_item-edit'): ?>
        <li class="active">
            <a href="<?= Url::to(['menu/_item-edit']) ?>">添加菜单</a>
        </li>
    <?php endif; ?>

</ul>
