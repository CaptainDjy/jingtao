<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\alipay;

use common\models\Config;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Url;

class Pay extends Model
{
    const API_Url = 'https://openapi.alipay.com/gateway.do';
    public $app_id;
    public $merchant_private_key;
    public $alipay_public_key;
    public $return_url;
    public $orderId;
    public $total_fee;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->app_id = Config::getConfig('ALIPAY_APP_ID');
        $this->merchant_private_key = Config::getConfig('ALIPAY_PRIV_KEY');
        $this->alipay_public_key = Config::getConfig('ALIPAY_PRIV_KEY');
        //$this->return_url = Url::to();
    }

    public function run()
    {
        if (!$this->validate()) {
            throw new Exception(current($this->getFirstErrors()));
        }
        $data = $this->getData();
        $params = $this->buildParams($data);
        $sign = $this->getSign($params);
        $url = self::API_Url . "?" . $params . "&sign=" . urlencode($sign);
        return $url;
    }

    private function getData()
    {
        $data = [
            'app_id' => $this->app_id,
            'method' => 'alipay.trade.wap.pay',
            'format' => 'json',
            'return_url' => $this->return_url,
            'charset' => 'UTF-8',
            'sign_type' => 'RSA',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => Url::to('/api/notify/pay/alipay', true),
            'biz_content' => [
                'subject' => '在线支付',
                'out_trade_no' => $this->orderId,
                'total_amount' => $this->total_fee,
                'product_code' => 'QUICK_WAP_WAY',
            ]
        ];
        $data['biz_content'] = json_encode($data['biz_content'], JSON_UNESCAPED_UNICODE);
        return $data;
    }

    private function getSign($str)
    {
        if (empty($str)) {
            throw new Exception('签名失败，缺少参数');
        }
        // 签名
        $priKey = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($this->merchant_private_key, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
        $signature = '';
        $res = openssl_get_privatekey($priKey);
        openssl_sign($str, $signature, $res);
        openssl_free_key($res);
        $signature = base64_encode($signature);
        return $signature;
    }

    private function buildParams($data)
    {
        if (!is_array($data)) {
            throw new Exception('拼装失败，参数格式错误');
        }
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            if (empty($v)) {
                continue;
            }
            $str .= "{$k}={$v}&";
        }
        return trim($str, '&');
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_id', 'merchant_private_key', 'alipay_public_key', 'return_url', 'orderId', 'total_fee'], 'required'],
            [['total_fee'], 'number', 'min' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'app_id' => 'appid',
            'merchant_private_key' => '私钥',
            'alipay_public_key' => '公钥',
            'return_url' => '回调地址',
            'orderId' => '订单号',
            'total_fee' => '支付金额',
        ];
    }

}
