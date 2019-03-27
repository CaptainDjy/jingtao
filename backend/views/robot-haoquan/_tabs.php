<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

use yii\helpers\Url;

$id = \Yii::$app->request->get('id');
?>
<ul class="nav nav-tabs">
    <li class="<?= Yii::$app->controller->action->id == 'index' ? 'active' : '' ?>">
        <a href="<?= Url::to(['robot-haoquan/index']) ?>">好券清单采集</a>
    </li>
    <li class="<?= Yii::$app->controller->action->id == 'update' ? 'active' : '' ?>">
        <?php if (!empty($id)): ?>
            <a href="<?= Url::to(['robot-haoquan/update', 'id' => $id]) ?>">编辑采集</a>
        <?php else: ?>
            <a href="<?= Url::to(['robot-haoquan/update']) ?>">添加采集</a>
        <?php endif; ?>
    </li>
</ul>
