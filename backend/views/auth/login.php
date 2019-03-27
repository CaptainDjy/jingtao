<?php

/**
 * @var $this \yii\web\View
 * @var $form yii\bootstrap\ActiveForm
 * @var $model backend\models\LoginForm
 */

use yii\bootstrap\Html;
use yii\captcha\Captcha;
use yii\helpers\Url;
use common\models\Config;

$this->title = '系统登录';
?>

<style>
    body {
        min-width: 1200px;
        overflow-x: scroll;
    }

    .login-body {
        background: url("<?= Yii::getAlias('@static/img/login/bg.jpg') ?>") no-repeat center;
        position: absolute;
        top: 100px;
        bottom: 100px;
        left: 0;
        right: 0;
        min-width: 1200px;
        z-index: 999;
    }

    .login-body form {
        position: absolute;
        right: 150px;
        top: 70px;
        background-color: #fff;
        width: 400px;
        border-radius: 10px;
        padding: 70px 50px;
        box-shadow: 2px 5px 5px 0 #00000030;
    }

    .login-body .login-title {
        line-height: 26px;
        font-size: 26px;
        text-align: center;
        padding-bottom: 40px;
        color: #333;
    }

    .login-body .btn-login {
        border: none;
        margin-top: 10px;
        color: #fff;
        background-color: #1690FD;
        width: 300px;
        height: 50px;
        border-radius: 25px;
        font-size: 25px;
    }

    .login-body .btn-login:hover {
        color: #fff;
        background-color: #1690FD;
    }

    .login-body .btn-login:active {
        color: #eee;
        background-color: #0687fb;
    }

    .login-body .help-block {
        margin: 5px 0;
    }

    .field-loginform-verifycode {
        overflow: hidden;
    }

    .field-loginform-verifycode p {
        clear: both;
        margin-top: 50px;
    }

    .form-login {
        width: 320px;
        padding-top: 10px;
    }

    .form-group {
        width: 90%;
        margin-bottom: 20px;
        border-bottom: 1px solid #ccc;
        margin-left: 5% !important;
    }

    .verify-code {
        position: relative;
        width: 63%;
        margin-bottom: 20px;
        border-bottom: 1px solid #ccc;
    }

    .form-control {
        /*margin-left: 15%;*/
        width: 80%;
        border: none;
        background-color: #fff !important;
    }

    .input-group-addon {
        border: none;
    }

    .username {
        background: url("<?= Yii::getAlias('@static/img/login/user.png') ?>") center center no-repeat;
    }

    .password {
        background: url("<?= Yii::getAlias('@static/img/login/pwd.png') ?>") center center no-repeat;
    }

    .code {
        background: url("<?= Yii::getAlias('@static/img/login/verify_code.png') ?>") center center no-repeat;
    }

    #loginform-verifycode-image {
        height: 26px;
        width: 38%;
        border: solid 1px #d5d5d5;
        border-radius: 3px;
        box-sizing: border-box;
        padding: 0 5px;
        position: absolute;
        top: 12px;
        right: -93px;
    }

    .input-group-addon:hover {
        cursor: pointer;
    }

    .msg {
        height: 20px;
        line-height: 20px;
    }

    .btn:active, .btn.active {
        outline: none !important;
    }

    .login-page, .register-page {
        background: #fff;
    }
</style>
<div class="login-body">
    <?= Html::beginForm('', 'post', ['class' => 'form-horizontal form-login']); ?>
    <p class="login-title"><?= Config::getConfig('WEB_SITE_TITLE');?>管理后台</p>
    <div class="input-group form-group">
        <label class="input-group-addon username" for="username"></label>
        <input type="text" class="form-control" id="username" name="LoginForm[username]" placeholder="用户名" autofocus
               onblur="checkUserName(this)">
        <span class="input-group-addon clear-val">X</span>
    </div>
    <p class="help-block help-block-error"></p>
    <div class="input-group form-group">
        <label class="input-group-addon password" for="pwd"></label>
        <input type="password" class="form-control" id="pwd" name="LoginForm[password]" placeholder="密码"
               onblur="checkPwd(this)">
        <span class="input-group-addon clear-val">X</span>
    </div>
    <p class="help-block help-block-error"></p>
    <div class="input-group form-group verify-code">
        <label class="input-group-addon code" for="loginform-verifycode"></label>
        <?= Captcha::widget([
            'model' => $model,
            'attribute' => 'verifyCode',
            'options' => [
                'class' => 'form-control',
                'placeholder' => '验证码',
                'onblur' => 'checkCode(this)'
            ],
            'imageOptions' => [
                'title' => '点击切换',
            ],
        ]) ?>
    </div>
    <p class="help-block msg" style="color: red"></p>

    <div class="row">
        <div class="col-xs-12">
            <?= Html::Button('登&nbsp;&nbsp;录', ['class' => 'btn btn-login', 'id' => 'submit']) ?>
        </div>
    </div>
    <?= Html::endForm(); ?>
</div>

<script>
    function checkUserName(obj) {
        var username = $(obj).val();
        if (!username || username === "") {
            $('.msg').text('用户名不可为空');
            $(obj).focus();
            return false;
        } else {
            $('.msg').text('');
            return true;
        }
    }

    function checkPwd(obj) {
        var pwd = $(obj).val();
        if (!pwd || pwd === "") {
            $('.msg').text('密码不可为空');
            $(obj).focus();
            return false;
        } else {
            $('.msg').text('');
            return true;
        }
    }

    function checkCode(obj) {
        var code = $(obj).val();
        if (!code || code === "") {
            $('.msg').text('验证码不可为空');
            $(obj).focus();
            return false;
        } else {
            $('.msg').text('');
            return true;
        }
    }

    function logining() {
        $('#submit').attr('disabled', 'disabled');
        $('#submit').text('正在登陆...');
    }

    function unLogining() {
        $('#submit').removeAttr('disabled');
        $('#submit').html('登&nbsp;&nbsp;录');
    }

    function login() {
        logining();
        var username = $("#username");
        var pwd = $('#pwd');
        var verifycode = $('#loginform-verifycode');
        if (!checkUserName(username)) {
            unLogining();
            return false;
        }
        if (!checkPwd(pwd)) {
            unLogining();
            return false;
        }
        if (!checkCode(verifycode)) {
            unLogining();
            return false;
        }
        $.ajax({
            url: '<?= Url::toRoute(['auth/login'])?>',
            method: 'POST',
            headers: {
                'X-CSRF-Token': '<?= Yii::$app->request->getCsrfToken() ?>'
            },
            data: {username: username.val(), password: pwd.val(), verifycode: verifycode.val()},
            success: function (res) {
                if (res.code === 0) {
                    window.location.href = res.data.url;
                }
                else {
                    $('.msg').text(res.msg);
                    unLogining();
                    $('#loginform-verifycode').focus();
                }
            }
        })
    }
    <?php $this->beginBlock('footerJs') ?>
    $('.clear-val').on('click', function () {
        $(this).prev().val('');
    });
    $("input").keydown(function () {
        if (event.keyCode === 13) {
            login();
        }
    });
    $('#submit').on('click', login);
    <?php $this->endBlock() ?>
</script>


