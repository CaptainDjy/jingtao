<?php

namespace common\components\alipay;

use common\models\Config;
use yii\base\BaseObject;
use yii\base\Exception;

/**
 * 支付异步通知检查
 * Class Notify
 * @package common\components\alipay
 */
class NotifyHandle extends BaseObject
{
    public $app_id;
    public $merchant_private_key;
    public $alipay_public_key;

    public function init()
    {
        $this->app_id = Config::getConfig('ALIPAY_APP_ID');
        $this->merchant_private_key = Config::getConfig('	ALIPAY_PRIV_KEY');
        $this->alipay_public_key = Config::getConfig('ALIPAY_KEY');
    }

    /**
     * @param $data
     * @param string $type
     * @return bool
     * @throws Exception
     */
    public function check($data, $type = 'RSA2')
    {
        $sign = !empty($data['sign']) ? $data['sign'] : '';
        $string = $this->buildParams($data);
        return $type == "RSA2" ? $this->rsa2SignCheck($string, $sign) : $this->rsaSignCheck($string, $sign);
    }

    /**
     * RSA 验签
     * @param $data
     * @param $sign
     * @return bool
     */
    private function rsaSignCheck($data, $sign)
    {
        $pubKey = $this->alipay_public_key;
        $pubKey = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($pubKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $res = openssl_get_publickey($pubKey);
        $result = openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return (bool)$result;
    }

    /**
     * @param $data
     * @return string
     * @throws Exception
     */
    private function buildParams($data)
    {
        if (!is_array($data)) {
            throw new Exception('拼装失败，参数格式错误');
        }
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            if (empty($v) || $k == "sign" || $k == "sign_type" || $k == "type" || "@" == substr($v, 0, 1)) {
                continue;
            } else {
                $str .= "&" . $k . "=" . urldecode($v);
            }
        }
        unset ($k, $v);
        return trim($str, '&');
    }

    private function rsa2SignCheck($string, $sign)
    {
        $pubKey = $this->alipay_public_key;
        $pubKey = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($pubKey, 64, "\n") . "-----END PUBLIC KEY-----";
        $res = openssl_get_publickey($pubKey);
        $result = openssl_verify($string, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        openssl_free_key($res);
        return (bool)$result;
    }

}
