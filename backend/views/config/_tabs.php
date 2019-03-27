<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'index' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['config/index']) ?>">配置管理</a>
    </li>
    <?php
    $id = Yii::$app->request->get('id');
    if (Yii::$app->controller->action->id == 'update' && !empty($id)) { ?>
        <li class="active">
            <a href="<?= Url::to(['config/update']) ?>">修改配置</a>
        </li>
    <?php } else { ?>
        <li <?= Yii::$app->controller->action->id == 'update' && empty($id) ? ' class="active"' : '' ?>>
            <a href="<?= Url::to(['config/update']) ?>">添加配置</a>
        </li>
    <?php }; ?>
</ul>


