<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>登录</title>
    <meta name="description" content="登录">
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <meta name="keywords" content="index">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="icon" type="image/png" href="/static/mobile/i/favicon.png">
    <link rel="apple-touch-icon-precomposed" href="/static/mobile/i/app-icon72x72@2x.png">
    <meta name="apple-mobile-web-app-title" content="登录"/>
    <link rel="stylesheet" href="/static/mobile/css/amazeui.min.css"/>
    <link rel="stylesheet" href="/static/mobile/css/amazeui.datatables.min.css"/>
    <link rel="stylesheet" href="/static/mobile/css/app.css">
    <link rel="stylesheet" href="/static/mobile/css/sign_up.css">
    <script src="/static/mobile/js/jquery.min.js"></script>
    <script src="/static/mobile/js/layer/layer.js"></script>
    <style>
        html, body {
            width: 100%;
            height: 100%;
        }
    </style>
</head>

<body data-type="login">
<div class="am-g tpl-g" style="height: 100%;width: 100%;">
    <div class="tpl-login" style="position: relative;width: 100%;">
        <div class="tpl-login-content"
             style="background: transparent;margin-top:-140px;position: absolute;top: 50%;left: 50%;margin-left: -250px;">
            <form class="am-form tpl-form-line-form">
                <div class="am-form-group">
                    <input type="text" style="background: #fff;" class="tpl-form-input" id="user-mobile" maxlength="11"
                           placeholder="请输入账号">
                </div>
                <div class="am-form-group">
                    <input type="password" style="background: #fff;" class="tpl-form-input" id="user-pass"
                           placeholder="请输入密码">
                </div>
                <div class="am-cf" style="margin-bottom: 20px;overflow: hidden;">
                    <p><a href="<?= \yii\helpers\Url::toRoute(['/auth/forgetpass']) ?>">找回密码</a></p>
                    <p><a href="<?= \yii\helpers\Url::toRoute(['/auth/sing-up']) ?>">注册新账号</a></p>
                </div>
                <div class="am-form-group">
                    <button type="button"
                            class="am-btn am-btn-primary  am-btn-block tpl-btn-bg-color-success  tpl-login-btn login">提交
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="/static/mobile/js/amazeui.min.js"></script>
<script src="/static/mobile/js/app.js"></script>
<script src="/static/mobile/js/userpass.js"></script>
<script>
    window.authForm = {
        login: function (mobile, password) {
            $.post('/auth/login', {
                mobile: mobile,
                password: password,
                "<?= Yii::$app->request->csrfParam ?>": "<?= Yii::$app->request->csrfToken ?>"
            }, function (res) {
                if (res.code == 0) {
                    $.AMUI.store.set('userInfo', res.data);
                    window.location.href = '/site/index';
                } else {
                    layer.msg(res.msg, {
                        offset: ['300px'],
                        time: 1000
                    });
                }
            })
        }
    }
    if ($(window).width() < 641) {
        $('body').css({
            'background': 'url("/static/mobile/img/11.jpg") no-repeat ',
            'background-size': 'cover',
        })
        $('.tpl-login-content').css({
            'position': 'static',
            'margin': '52% auto 0'
        })
    } else {
        $('body').css({'background': 'url("/static/mobile/img/22.jpg") no-repeat ', 'background-size': 'cover'})
        $('.tpl-login-content').css({
            'position': 'static',
            'margin': '300px auto '
        })
    }
</script>
</body>

</html>
