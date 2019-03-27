<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'index' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['finance/index']) ?>">佣金列表</a>
    </li>
    <li <?= Yii::$app->controller->action->id == 'commission' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['finance/commission']) ?>">佣金设置</a>
    </li>
</ul>
