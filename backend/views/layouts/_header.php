<?php
/** @var $this \yii\web\View
 * @var \backend\models\SystemUser $user
 */
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\Config;

$user = Yii::$app->user->identity;
?>

<header class="main-header">
    <a href="<?= Url::home() ?>" class="logo"><b><?= Config::getConfig('WEB_SITE_TITLE');?></b>管理后台</a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- Notifications: style can be found in dropdown.less -->
<!--                <li class="dropdown notifications-menu">-->
<!--                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">-->
<!--                        <i class="fa fa-bell-o"></i>-->
<!--                        <span class="label label-warning">0</span>-->
<!--                    </a>-->
<!--                    <ul class="dropdown-menu">-->
<!--                        <li class="header">暂无最新消息</li>-->
<!--                        <li>-->
<!--                            <ul class="menu">-->
<!--                                <!--<li>-->
<!--                                    <a href="#">-->
<!--                                        <i class="fa fa-users text-aqua"></i> 5 new members joined today-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                            </ul>-->
<!--                        </li>-->
<!--                        <li class="footer"><a href="#">查看全部</a></li>-->
<!--                    </ul>-->
<!--                </li>-->

                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <?= Html::img('@web/static/img/adminlte/user2-160x160.jpg', ['class' => 'user-image', 'alt' => "头像"]) ?>
                        <span class="hidden-xs"><?= @$user->username ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <?= Html::img('@web/static/img/adminlte/user2-160x160.jpg', ['class' => 'img-circle', 'alt' => "User Image"]) ?>
                            <p>
                                <?= @$user->username ?>
                                <small><?= date('Y-m-d H:i') ?></small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a class="btn btn-default btn-flat"
                                   href="<?= Url::toRoute(['/auth/user-edit', 'id' => @$user->id]) ?>"
                                   data-toggle="modal" data-target="#ajaxModal">修改密码</a>
                            </div>
                            <div class="pull-right">
                                <?php if (!empty($user)): ?>
                                    <a href="<?= Url::toRoute(['/auth/logout']) ?>" class="btn btn-default btn-flat">安全退出</a>
                                <?php else: ?>
                                    <a href="<?= Url::toRoute(['/auth/login']) ?>"
                                       class="btn btn-default btn-flat">去登陆</a>
                                <?php endif; ?>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
