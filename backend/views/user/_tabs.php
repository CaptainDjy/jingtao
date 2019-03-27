<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'list' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['user/list']) ?>">会员管理</a>
    </li>

    <li <?= Yii::$app->controller->action->id == 'relation-list' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['user/relation-list']) ?>">关系列表</a>
    </li>
    <li <?= Yii::$app->controller->action->id == 'lv' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['user/lv']) ?>">等级设置</a>
    </li>



    <?php if (Yii::$app->controller->action->id == 'update' && empty(Yii::$app->request->get('id'))): ?>
        <li class="active">
            <a href="<?= Url::to(['user/update']) ?>">添加会员</a>
        </li>
    <?php endif; ?>

    <?php if (Yii::$app->controller->action->id == 'update' && !empty(Yii::$app->request->get('id'))): ?>
        <li class="active">
            <a href="<?= Url::to(['user/update']) ?>">修改会员</a>
        </li>
    <?php endif; ?>

    <?php if (Yii::$app->controller->action->id == 'real-edit' && !empty(Yii::$app->request->get('id'))): ?>
        <li class="active">
            <a href="<?= Url::to(['user/real-edit']) ?>">修改实名认证</a>
        </li>
    <?php endif; ?>


</ul>
