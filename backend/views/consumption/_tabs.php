<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'list' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['/consumption/list']) ?>">合伙人列表</a>
    </li>
    <!--    <li < ?= Yii::$app->controller->action->id == 'partner-setting' && empty(Yii::$app->request->get('id')) ? ' class="active"' : '' ?>>-->
    <!--        <a href="--><? //= Url::to(['/consumption/partner-setting']) ?><!--">合伙人设置</a>-->
    <!--    </li>-->
    <?php if (Yii::$app->controller->action->id == 'update' && !empty(Yii::$app->request->get('id'))): ?>
        <li class="active">
            <a href="<?= Url::to(['/consumption/update']) ?>">合伙人修改</a>
        </li>
    <?php endif; ?>
</ul>
