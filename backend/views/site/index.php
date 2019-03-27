<?php

/* @var $this yii\web\View */
use yii\helpers\Url;
$this->title = \common\models\Config::getConfig('WEB_SITE_TITLE').'管理后台';
?>
<div class="site-index">

    <div class="body-content">
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?=$order?></h3>

                        <p>订单数</p>
                    </div>
                    <div class="icon">
                        <i class="glyphicon glyphicon-lock" style="margin-top: 20px"></i>
                    </div>
                    <a href="<?= Url::toRoute(['finance/order']) ?>" class="small-box-footer">更多信息 <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?=$goods?><sup style="font-size: 20px">W</sup></h3>
                        <p>商品数</p>
                    </div>
                    <div class="icon">
                        <i class="glyphicon glyphicon-stats" style="margin-top: 20px"></i>
                    </div>
                    <a href="<?= Url::toRoute(['goods/index']) ?>" class="small-box-footer">更多信息 <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?=$user?></h3>

                        <p>用户注册</p>
                    </div>
                    <div class="icon">
                        <i class="glyphicon glyphicon-user" style="margin-top: 20px"></i>
                    </div>
                    <a href="<?=Url::toRoute(['user/list'])?>" class="small-box-footer">更多信息 <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->

            <!-- ./col -->
        </div>

    </div>
</div>
