<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'stale' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['goods/stale']) ?>">过期商品</a>
    </li>
</ul>
