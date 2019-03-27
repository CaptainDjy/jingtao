<?php

use yii\helpers\Url;

?>
<ul class="nav nav-tabs">
    <li <?= Yii::$app->controller->action->id == 'index' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['article/index']) ?>">文章管理</a>
    </li>
    <li <?= Yii::$app->controller->action->id == 'edit' ? ' class="active"' : '' ?>>
        <a href="<?= Url::to(['article/edit']) ?>"><?= isset($_GET['id']) && Yii::$app->controller->action->id == 'edit' ? '编辑' : '添加'; ?>文章</a>
    </li>
    <!--<li <?/*= Yii::$app->controller->action->id == 'cate' ? ' class="active"' : '' */?>>
        <a href="<?/*= Url::to(['article/cate']) */?>">文章分类</a>
    </li>
    <li <?/*= Yii::$app->controller->action->id == 'cate-edit' ? ' class="active"' : '' */?>>
        <a href="<?/*= Url::to(['article/cate-edit']) */?>"><?/*= isset($_GET['id']) && Yii::$app->controller->action->id == 'cate-edit' ? '编辑' : '添加'; */?>分类</a>
    </li>-->
</ul>
