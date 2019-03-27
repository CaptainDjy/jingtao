<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'order' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['finance/order']) ?>">订单列表</a>
    </li>
</ul>
