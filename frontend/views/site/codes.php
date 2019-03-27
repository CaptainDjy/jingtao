<?php
/**
 * @var $info array
 * @var $kouling string
 * @var $version array
 * @var $coupon_money float
 * @var $coupon_price float
 */

use common\models\Config;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php Config::getConfig('WEB_SITE_TITLE') ?></title>
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <link rel="stylesheet" href="/static/mobile/css/mui.min.css">
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            list-style: none;
            font-family: "微软雅黑";
        }

        i {
            font-style: normal;
        }

        .bar_img {
            width: 100%;
            height: 400px;
            margin-top: 62px;
        }

        .bar_img img {
            width: 100%;
            height: 100%;
        }

        .main {
            margin: 5px 15px 0px 15px;
        }

        .main p {
            margin-bottom: 35px;
            line-height: 25px;
        }

        .main li span:first-child {
            color: red;
            font-size: 16px;
        }

        .main li span:first-child i {
            font-size: 14px;
        }

        .main li del {
            font-size: 14px;
            color: #8C8C8C;
        }

        .main li span:last-child {
            width: 80px;
            height: 30px;
            line-height: 30px;
            display: block;
            background: url(/static/mobile/img/quan.png) no-repeat;
            float: right;
            font-size: 10px;
            color: #fff;
            margin-top: -4px;
            background-size: 100%;
            text-align: center;
            padding-right: 20px;
        }

        .main li span:last-child i {
            font-size: 14px;

        }

        .main ul {
            margin-top: 20px;
        }

        .main ul li {
            width: 100%;
            padding: 15px 0 20px 0;
            border: 1px dashed #FB6028;
            text-align: center;
            border-radius: 5px 5px 0 0;
            box-sizing: border-box;
            background: #FFF1EE;
        }

        .main ul li p:first-child {
            line-height: 14px;
            font-size: 16px;
            width: 100%;
        }

        .main ul li p:last-child {
            line-height: 14px;
            font-size: 12px;
            width: 100%;
        }

        .main ul div button {
            width: 100%;
            height: 35px;
            border-radius: 0 0 5px 5px;
            background: linear-gradient(to right, #F8B831, #FE8608);
            border: none;
            line-height: 35px;
            color: #fff;
            outline: none;
        }

        .foo button {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40px;
            border: none;
            background: linear-gradient(to right, #F28E1C, #FD5C2B);
            color: #fff;
            outline: none;
            border-radius: 0 0 5px 5px;
        }

        .top {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #fff;
            padding: 5px 10px;
            box-sizing: border-box;
        }

        .top img {
            width: 30px;
            float: left;
            margin-right: 5px;
            margin-top: 5px;
        }

        .top .top_tit {
            float: left;
            width: 200px;
            font-size: 12px;
        }

        .top .top_tit p:first-child {
            font-size: 14px;
        }

        .top .btn {
            float: right;
            background: red;
            color: #fff;
            font-size: 14px;
            padding: 3px 5px;
            margin-top: 12px;
            -webkit-border-radius: 15px;
            -moz-border-radius: 15px;
            border-radius: 15px;
        }
    </style>
</head>
<body>

<div class="top">
    <img src="/static/mobile/img/80x80.png" alt="">
    <div class="top_tit">
        <p style="margin-bottom: 0;"><?php Config::getConfig('WEB_SITE_TITLE') ?></p>
        <p style="font-size: 12px;">一款购物不剁手，躺着赚钱的神器。</p>
    </div>
    <div class="btn">APP下载</div>
</div>
<div class="bar_img">
    <img src="<?= $info['pict_url'] ?>"/>
</div>

<div class="main">
    <p><?= $info['title'] ?></p>
    <li>
        <span>劵后价￥<i><?= $coupon_price ?></i></span>&nbsp;<del><?= $info['reserve_price'] ?></del>
        <span>￥<i><?= $coupon_money ?></i></span>
    </li>
    <ul>
        <li>
            <p class="kouling"><?= $kouling ?></p>
            <p>长按复制框内文字，启动手机【淘宝】即可领劵购买</p>
        </li>
        <div>
            <button class="copy">一键复制淘口令</button>
        </div>
    </ul>
</div>
<div style="height: 70px;"></div>
<div class="foo">
    <button class="copy">一键复制淘口令</button>
</div>
<script src="/static/mobile/js/mui.min.js"></script>
<script type="text/javascript">
    var version;
    var downloadurl;
    mui.init();
    mui('body').on('tap', '.copy', function () {
        var text = mui('.kouling')[0].innerText;
        var oInput = document.createElement('input');
        oInput.value = text;
        document.body.appendChild(oInput);
        oInput.select(); // 选择对象
        document.execCommand("Copy"); // 执行浏览器复制命令
        oInput.className = 'oInput';
        oInput.style.display = 'none';
        mui.toast('复制成功');
    });
    mui('body').on('tap', '.btn', function () {
        if (mui.os.android) {
            version = '<?=$version['version']?>';
            downloadurl = '<?=$version['url']?>';
        } else {
            version = '<?=$version['iosVersion']?>';
            downloadurl = '<?=$version['iosUrl']?>';
        }
        window.location.href = downloadurl;
    });


</script>
</body>
</html>
