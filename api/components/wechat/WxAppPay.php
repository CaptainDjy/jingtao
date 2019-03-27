<?php

namespace api\components\wechat;

use common\helpers\Utils;
use common\models\Config;
use common\models\UpgradeOrder;
use yii\base\Exception;
use yii\helpers\Url;
use yii\httpclient\Client;

/**
 * 微信APP支付
 * Class WxAppPay
 * @package api\components\wechat
 * @property string $method
 */
class WxAppPay
{
    /**
     * 请求地址
     * @var string
     */
    public $method = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    /**
     * 应用唯一标识
     * @var string
     */
    public $appId;

    /**
     * 商户ID
     * @var string
     */
    public $mchId;

    /**
     * 商户key
     * @var string
     */
    public $key;

    /**
     * 签名类型
     * @var string
     */
    public $signType = 'md5';

    /**
     * cert
     * @var string
     */
    public $cert;

    /**
     * ssl_key
     * @var string
     */
    public $ssl_key;

    /**
     * 是否强制校验真实姓名 FORCE_CHECK强制
     * @var string
     */
    public $check_name = 'NO_CHECK';

    /**
     * 支付回调地址
     * @var string
     */
    public $notifyUrl;

    public function __construct()
    {
        $this->appId = Config::getConfig('WECHAT_APP_ID');
        $this->mchId = Config::getConfig('WECHAT_MCH_ID');
        $this->key = Config::getConfig('WECHAT_KEY');
        $this->notifyUrl = Url::to('notify/wx', true);
    }

    /**
     * @param $xml
     * @return mixed
     * @throws Exception
     */
    private function xml2array($xml)
    {
        if (!$xml) {
            throw new Exception("xml数据异常！");
        }
        //将XML转为array
        libxml_disable_entity_loader(true); //禁止引用外部xml实体
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     * @param $array
     * @return string
     * @throws Exception
     */
    private static function arr2xml($array)
    {
        if (!is_array($array)
            || count($array) <= 0) {
            throw new Exception("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($array as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function curlPost($data)
    {
        $client = new Client();
        $response = $client->post($this->method, $data)->send();

        if (!$response->isOk) {
            throw new Exception('接口网络请求错误：状态码' . $response->getStatusCode());
        }
        return $response->content;
    }

    /**
     * @param $xml
     * @param $url
     * @param bool $useCert
     * @param int $second
     * @return bool|mixed
     */
    private function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if ($useCert == true) {
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            //curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            //curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }

    /**
     * 统一下单
     * @param $order UpgradeOrder
     * @return string
     */
    public function pay($order)
    {
        $params = [
            'body' => '会员权益',
            'out_trade_no' => $order->trade_no,
            'total_fee' => bcmul($order->amount, 100),//单位:分
        ];
        $data = $this->getData($params);
        $response = $this->postXmlCurl($data, $this->method);
        $arr = $this->xml2array($response);
        $pack = 'Sign=WXPay';
        $prePayParams = array();
        $prePayParams['appid'] = $arr['appid'];
        $prePayParams['noncestr'] = $arr['nonce_str'];
        $prePayParams['partnerid'] = $arr['mch_id'];
        $prePayParams['prepayid'] = $arr['prepay_id'];
        $prePayParams['package'] = $pack;
        $prePayParams['timestamp'] = time();

        ksort($prePayParams);
        $prePayParams['sign'] = $this->generateSign($prePayParams, $this->key);
        return json_encode($prePayParams);
    }

    /**
     * 格式化请求参数
     * @param $params
     * @return string
     */
    private function getData($params)
    {
        $base = [
            'appid' => $this->appId,
            'mch_id' => $this->mchId,
            'nonce_str' => Utils::genderRandomStr('', 32),
            'spbill_create_ip' => Utils::getIp(),
            'notify_url' => $this->notifyUrl,
            'trade_type' => 'APP',
        ];
        $params = array_filter(array_merge($base, $params));
        ksort($params);

        $params['sign'] = $this->generateSign($params, $this->key);
        return self::arr2xml($params);
    }

    /**
     * Generate a signature.
     *
     * @param array $attributes
     * @param string $key
     * @param string $encryptMethod
     *
     * @return string
     */
    public function generateSign(array $attributes, $key, $encryptMethod = 'md5')
    {
        $attributes['key'] = $key;

        return strtoupper(call_user_func_array($encryptMethod, [urldecode(http_build_query($attributes))]));
    }

    private function checkSign($params)
    {
        return true;
        //todo 校验签名
        // $signToCheck = $params['sign'];
        // unset($params['sign']);
        // $sign = $this->generateSign($params, $this->key);
        // if ($signToCheck == $sign) {
        //     return true;
        // }
        // throw new Exception('签名校验失败!sign:' . $signToCheck . ',' . $sign);
    }

}