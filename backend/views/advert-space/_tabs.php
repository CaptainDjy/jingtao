<?php
/**
 * @author pine
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * createtime: 2018/05/26 17:00
 */

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'index' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['advert-space/index']) ?>">推广位列表</a>
    </li>

    <?php if (Yii::$app->controller->action->id == 'update'): ?>
        <li class="active">
            <a href="javascript:void(0)">编辑推广位</a>
        </li>
    <?php endif; ?>

    <li <?= Yii::$app->controller->action->id == 'create' && Yii::$app->request->get('type') == '1' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['advert-space/create', 'type' => 1]) ?>">创建淘宝推广位</a>
    </li>
    <li <?= Yii::$app->controller->action->id == 'create' && Yii::$app->request->get('type') == '2' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['advert-space/create', 'type' => 2]) ?>">创建京东推广位</a>
    </li>
    <li <?= Yii::$app->controller->action->id == 'create' && Yii::$app->request->get('type') == '3' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['advert-space/create', 'type' => 3]) ?>">创建拼多多推广位</a>
    </li>
</ul>
