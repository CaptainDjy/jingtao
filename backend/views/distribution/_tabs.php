<?php

use yii\helpers\Url;

/**
 * @var string $name
 */
?>
<ul class="nav nav-tabs">
    <li <?= $name == 'index' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['/distribution/index', 'name' => 'index']) ?>">总设置</a>
    </li>

    <li <?= $name == 'partner' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['/distribution/index', 'name' => 'partner']) ?>">其他设置</a>
    </li>

</ul>



