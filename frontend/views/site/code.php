<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title></title>
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <!--<link rel="stylesheet" type="text/css" href="css/bootstrap.css" />-->
    <!--<script type="text/javascript" src="js/bootstrap.js"></script>-->
    <!--<script type="text/javascript" src="js/clipboard.js"></script>-->
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            list-style: none;
            -webkit-tap-highlight-color: rgba(255, 0, 0, 0);
        }

        p {
            display: block;
            -webkit-margin-before: 1em;
            -webkit-margin-after: 1em;
            -webkit-margin-start: 0px;
            -webkit-margin-end: 0px;
        }

        p {
            margin: 0;
            padding: 0;
        }

        .couponWrap {
            background: url(img/5b0791b2Ne5a9b5e7.png);
            background-size: 100% 100%;
            height: 291px;
        }

        .couponWrap .dog {
            margin: 0 auto;
            padding-top: 25px;
            display: block;
            width: 118px;
            height: auto;
        }

        .couponWrap .dog-hands {
            margin: 0 auto;
            width: 87px;
            height: 12px;
            background: url(img/xiazai2.png) no-repeat;
            background-size: 87px 12px;
            z-index: 3;
            position: relative;
            top: -10px;
        }

        .couponWrap .coupon-star {
            background: url(img/xiazai.png);
            background-size: 100% 100%;
            color: #fff;
        }

        .couponWrap .coupon-star .tag {
            background: url(img/xiazai3.png) no-repeat;
            background-size: 100% 100%;
        }

        .couponWrap .coupon-star .price span {
            text-shadow: 2px 2px 1px #000000;
        }

        .couponWrap .coupon-star .timelimit {
            color: #f9f8ea;
        }

        .couponWrap .coupon-star .btnget {
            background: #f23030;
        }

        .couponWrap .coupon {
            width: 350px;
            height: 120px;
            margin: -14px auto;
            position: absolute;
            left: 13px;
            cursor: pointer;
        }

        .couponWrap .coupon .tag {
            width: 55px;
            height: 55px;
            margin-left: -2px;
            margin-top: -2px;
        }

        .couponWrap .coupon .tag span {
            display: inline-block;
            -webkit-transform: rotate(-45deg);
            -ms-transform: rotate(-45deg);
            transform: rotate(-45deg);
            font-size: 12px;
            margin-top: 10px;
            margin-left: 3.5px;
        }

        .couponWrap .coupon .shopName {
            position: absolute;
            top: 8px;
            left: 52px;
            font-size: 14px;
        }

        .couponWrap .coupon .price {
            height: 64px;
            line-height: 64px;
            position: absolute;
            top: 30px;
            left: 11px;
            font-size: 10px;
            color: #f9f8ea;
        }

        .couponWrap .coupon .price span {
            font-size: 48px;
            font-weight: 700;
        }

        .couponWrap .coupon .price span span {
            font-size: 44px;
        }

        .couponWrap .coupon .timelimit {
            position: absolute;
            left: 11px;
            bottom: 11px;
            font-size: 10px;
        }

        .couponWrap .coupon .btnget {
            cursor: pointer;
            width: 192px;
            height: 64px;
            line-height: 64px;
            -webkit-transform: scale(0.5);
            -ms-transform: scale(0.5);
            transform: scale(0.5);
            position: absolute;
            top: 27px;
            right: -40.6px;
            border-radius: 40px;
            text-align: center;
        }

        .couponWrap .coupon .btnget span {
            font-size: 28px;
            margin-top: 1px;
            display: block;
        }

        .skuWrap {
            cursor: pointer;
            background: #2347f5;
            height: 190px;
            border-radius: 0 0 6px 6px;
            position: relative;
            top: -30px;
        }

        .skuWrap .goods {
            width: 375px;
            height: auto;
            margin-top: -45px;
        }

        .skuWrap .mask {
            border-radius: 6px;
            width: 362px;
            height: 160px;
            margin: -5px 7px;
            background: #f6f6f6;
        }

        .skuWrap .mask .pic {
            margin: 5px;
            width: 125px;
            height: 125px;
        }

        .skuWrap .mask .right {
            width: 223px;
            height: 105px;
            float: right;
            margin-top: 11px;
        }

        .skuWrap .mask .right .content {
            width: 210px;
            height: 38px;
            font-size: 14px;
            color: #3e3936;
            text-align: justify;
            -o-text-overflow: ellipsis;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .skuWrap .mask .right .comment {
            width: 226px;
            height: 14px;
            font-size: 10px;
            text-align: right;
            color: #827b77;
            padding-right: 0;
            margin-top: 3px;
        }

        .skuWrap .mask .right .original-price {
            width: 100%;
            height: 17px;
            font-size: 12px;
            text-align: left;
            color: #827b77;
            margin-top: 6px;
        }

        .skuWrap .mask .right .original-price span {
            text-decoration: line-through;
        }

        .skuWrap .mask .right .discount {
            width: 100%;
            height: 21px;
            font-size: 16px;
            font-family: Arial-MT;
            font-weight: bold;
            text-align: left;
            color: #f23030;
            margin-top: 06px;
        }

        .skuWrap .mask .layer {
            cursor: pointer;
            width: 100%;
            height: 32px;
            line-height: 32px;
            background: #f23030;
            text-align: center;
            border-radius: 0 0 06px 06px;
            margin-top: -4px;
        }

        .skuWrap .mask .layer a {
            color: #ffffff;
            text-decoration: none;
            font-family: MicrosoftYaHei;
            font-size: 12px;
            letter-spacing: 0.7px;
        }

        .ruleWrap {
            width: 342px;
            margin: 0 auto;
        }

        .ruleWrap .title {
            width: 92px;
            height: 21px;
            line-height: 21px;
            margin: 0 auto;
            text-align: center;
            background: url(img/xiazai1.png) no-repeat;
            background-size: 92px 21px;
        }

        .ruleWrap .title span {
            font-size: 14px;
            font-family: PingFangSC;
            font-weight: 600;
            color: #ffffff;
        }

        .ruleWrap .line {
            margin-top: 10px;
            width: 341px;
            height: 2px;
            border-top: solid 0.4px #f6f6f6;
        }

        .ruleWrap article {
            margin-top: 10px;
            width: 342px;
            font-family: PingFangSC;
            font-size: 12px;
            font-weight: 300;
            line-height: 1.83;
            text-align: left;
            color: #9b9b9b;
        }


    </style>
    <script type="text/javascript">
    </script>
</head>

<body>
<div id="content">
    <div data-reactroot="" class="wrap">
        <div class="topTip"><span></span></div>
        <div class="couponWrap"><img class="dog"
                                     src="https://m.360buyimg.com/babel/jfs/t17260/331/2606888558/7125/65e58f66/5b0791b2N220e28de.png">
            <div class="dog-hands"></div>
            <div class="coupon coupon-star">
                <div class="tag"><span>东券</span></div>
                <p class="shopName">自然旋律旗舰店</p>
                <p class="price">满69元减&nbsp;<span>45<span>元</span></span>
                </p>
                <p class="timelimit">限店铺限商品：2018/05/21-2018/05/27</p>
                <div class="btnget"><span>立即领取</span></div>
            </div>
        </div>
        <div class="skuWrap"><img class="goods"
                                  src="https://m.360buyimg.com/babel/jfs/t19816/97/704807915/6153/b8884d62/5b0791b2Nd8b1fdf1.png"
                                  alt="">
            <div class="mask"><img class="pic"
                                   src="https://img12.360buyimg.com/cms/s300x300_jfs/t18103/276/939980445/157556/e6b07157/5ab37a8cN67211ba9.jpg"
                                   alt="">
                <div class="right">
                    <p class="content">自然旋律水杨酸爽肤水男珍贵水女祛痘淡化痘印控油去闭口粉刺收...</p>
                    <p class="comment">234条评论&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                    <p class="original-price">京东价：<span id="price">￥69.00</span></p>
                    <p class="discount">用券后：<span id="price_usecoupon">￥24.00</span></p>
                </div>
                <div class="layer">
                    <a href="#">查看商品详情</a>
                </div>
            </div>
        </div>
        <div class="ruleWrap">
            <div class="title"><span>活动说明</span></div>
            <div class="line"></div>
            <article>
                <p>1. 点击“立即领取”按钮，领取成功后在我的京东-资产中心-优惠券，可查询已发放到账户的优惠券；</p>
                <p>2. 因优惠券领取用户级别、数量、时间等均不同，可能存在无法领取的情况，请遵守京东商城优惠券领取规则，先领先得；</p>
                <p>3. 优惠券领取成功后，请遵守京东商城优惠券使用规则，以订单结算页中的优惠券使用提示为准；</p>
                <p>4. 获取、使用优惠券时如存在违规行为（作弊领取、恶意套现、虚假交易等），将取消用户领取资格、撤销违规交易且收回全部优惠券（含已使用及未使用的），必要时将追究法律责任；</p>
                <p>规则声明：</p>
                <p>上传至该页面的商品与优惠券信息，会被京东集团官方收录并给其他用户使用。上传者上传此类信息的，表明上传者已与商家达成一致，即
                    商家同意官方收录且给其他用户使用。如商家不同意，请上传者不要上传，如否导致的纠纷由上传者解决并承担责任。</p><br></article>
        </div>
    </div>
</div>
</body>

</html>