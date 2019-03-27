<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'system' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['announcement/system']) ?>">系统公告</a>
    </li>
    <?php if (Yii::$app->controller->action->id == 'update' && empty(Yii::$app->request->get('id'))): ?>
        <li class="active">
            <a href="<?= Url::to(['announcement/update']) ?>">添加系统公告</a>
        </li>
    <?php endif; ?>
    <?php if (Yii::$app->controller->action->id == 'update' && !empty(Yii::$app->request->get('id'))): ?>
        <li class="active">
            <a href="<?= Url::to(['announcement/update']) ?>">修改系统公告</a>
        </li>
    <?php endif; ?>
</ul>
