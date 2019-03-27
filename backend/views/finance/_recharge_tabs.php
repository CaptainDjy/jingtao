<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'recharge' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['finance/recharge']) ?>">会员充值</a>
    </li>
</ul>
