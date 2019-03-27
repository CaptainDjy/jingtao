<?php

namespace common\components\alipay\requests;

/**
 * app支付接口2.0
 * Class AlipayTradeAppPayRequest
 * @package common\components\alipay\requests
 * @property string $method
 * @property string $out_trade_no
 * @property String $total_amount 订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]
 * @property string $seller_id 收款支付宝用户ID。 如果该值为空，则默认为商户签约账号对应的支付宝用户ID
 * @property string $notify_url
 */
class AlipayTradeAppPayRequest extends Request
{
    public $method = 'alipay.trade.app.pay';

    public $params = [
        'out_trade_no' => '',
        'total_amount' => '',
        'seller_id' => '',
        'notify_url' => '',
    ];
}
