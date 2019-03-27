<?php
/**
 * @var array $data
 */

use common\models\Config;
use common\models\Goods;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <title></title>
    <script type="text/javascript">
        window.onload = function () {
            var docEl = document.documentElement
            var docElcl = docEl.clientWidth
            if (docElcl > 1080) {
                docElcl = 1080
                docEl.style.fontSize = docElcl / 10.8 + 'px'
            } else {
                docEl.style.fontSize = docElcl / 10.8 + 'px'
            }
            console.log(docEl.style.fontSize)
        }
    </script>
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        p {
            padding: 0.2rem 0;
            margin: 0;
        }

        div, p, span, img, del, li, body {
            font-size: 16px;
        }

        .box {
            padding: 0.7rem;
        }

        .header {
            overflow: hidden;
        }

        .header p {
            font-size: 0.51rem;
        }

        .header p span {
            background: #f00;
            border-radius: 3px;
            /*font-size: 14px;*/
            padding: 2px 5px;
            color: #fff;
        }

        .banner {
            margin: 0.2rem 0;
            width: 100%;
            height: 9.3rem;
        }

        .banner img {
            width: 100%;
            height: 100%;
        }

        .content {
            border-bottom: 2px solid #f00;
            padding: 0.2rem 0;
        }

        .content_first_p {
            padding: 0.2rem 0 0 0;
            display: inline-block;
            color: #C10204;
            font-size: 0.5rem;
        }

        .content_first_p span {
            font-size: 0.82rem;
        }

        .content_last_p {
            padding: 0.15rem 0;
            font-size: 0.45rem;
            color: #888;
        }

        .content_last_p del {
            font-size: 0.6rem;
        }

        .content div {
            margin-top: 0.5rem;
            width: 3.28rem;
            height: 1.34rem;
            display: inline-block;
            float: right;
            position: relative;
        }

        .content div li {
            width: 2.51rem;
            height: 1.34rem;
            text-align: center;
            position: absolute;
            left: 0;
            bottom: 0;
            color: #fff;
            font-size: 0.54rem;
            line-height: 1.34rem;
            z-index: 999;
        }

        .content div li span {
            font-size: 0.85rem;
        }

        .content div img {
            width: 3.28rem;
            height: 1.34rem;
        }

        .footer {
            margin-top: 1.0rem;
            overflow: hidden;
            text-align: right;
        }

        .footer img {
            float: right;
            width: 3rem;
            height: 3rem;
        }

        .footer p {
            float: right;
            padding: 0rem 0 0.28rem 0;
            margin: 0.28rem 0.43rem 0.28rem 0.28rem;
            border-bottom: 1px solid #ddd;
            font-size: 0.4rem;
        }

        .footer p:last-child {
            border: none;
            padding: 0;
            margin-top: 0;
            font-size: 0.3rem;
            color: #777;
        }
    </style>
</head>

<body>
<div class="box">
    <div class="banner">
        <img src="<?= $data['pic']; ?>"/>
    </div>
    <div class="header">
        <p>
            <span><?= Goods::TYPE_LABEL[$data['type']]; ?></span>
            <?= $data['title']; ?>
        </p>
    </div>
    <div class="content" style="overflow: hidden;">
        <p class="content_first_p">劵后价￥<span><?= $data['price']; ?></span></p>
        <div>
            <img src="<?= \Yii::getAlias("@static/img/quan.png"); ?>"/>
            <li>￥ <span><?= $data['coupon_price']; ?></span></li>
        </div>
        <p class="content_last_p">原价￥
            <del><?= $data['original_price']; ?></del>
        </p>
    </div>
    <div class="footer">
        <img src="<?= $data['qrcode']; ?>?<?=rand()?>>"/>
        <p><?php Config::getConfig('WEB_SITE_TITLE') ?></p>
        <p>长按图片识别图中二维码即可前往购买</p>
    </div>
</div>
</body>

</html>