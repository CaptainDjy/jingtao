<?php

/* @var $this \yii\web\View */

/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed">
<?php $this->beginBody() ?>
<div class="wrap">
    <?= $this->render('_header') ?>
    <?= $this->render('_left') ?>
    <div class="content-wrapper" style="min-height: 1068px;">
        <section class="content">
            <?= Alert::widget() ?>
            <?= $content ?>
        </section>
    </div>
</div>

<footer class="main-footer fixed">
    <div class="pull-right hidden-xs">
        <b>Version</b> 2.0
    </div>
    <strong>Copyright © <?= date('Y') ?> <a href="http://www.weiyuntop.com"></a></strong> All rights reserved.
</footer>

<?php $this->endBody() ?>

<?php $this->registerJs($this->blocks['footerJs']) ?>

<!--ajax模拟框加载-->
<div class="modal fade" id="ajaxModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <img src="<?= Yii::getAlias('@static/img/loading.gif') ?>" alt="" class="loading">
                <span>&nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        <!--ajax模拟框消除-->
        $('#ajaxModal').on('hidden.bs.modal', function () {
            var tpl = '<div class="modal-body"><img src="<?= Yii::getAlias('@static/img/loading.gif') ?>" alt="" class="loading"><span>&nbsp;&nbsp;Loading... </span></div>';
            $(this).removeData("bs.modal");
            $(".modal-content").html(tpl);
        });
        setTimeout(function () {
            $('.alert').slideUp(500);
        }, 3000);
    });

    window.AdminLTEOptions = {
        animationSpeed: 200
    };
</script>
<style>
    .btn-dh {
        margin-left: 10px;
        background-color: #3C8DBC;
        color: #fff;
    }

    .btn-dh:hover {
        background-color: #6FC4F5;
        color: #fff;
    }

    .table > thead > tr > th {
        border-bottom: 1px solid #e2e2e2 !important;
    }

    .table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
        border-top: 1px solid #e2e2e2;
    }
</style>
</body>
</html>
<?php $this->endPage() ?>
