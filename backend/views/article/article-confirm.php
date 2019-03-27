<?php
use yii\helpers\Html;
?>
<p>You have entered the following information:</p>

<ul>
    <li><label>Name</label>: <?= Html::encode($model->title) ?></li>
    <li><label>Email</label>: <?= Html::encode($model->content) ?></li>
</ul>