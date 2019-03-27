<?php

namespace common\components\wechat;

use common\helpers\Utils;
use common\models\Config;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Url;

/**
 * 微信APP支付
 * Class WxAppPay
 * @package common\components\wechat
 */
class WxAppPay extends Model
{
    const API_Url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    public $app_id;
    public $mch_id;
    public $key;
    public $out_trade_no;
    public $amount;
    public $body = '会员权益';

    public function init()
    {
        $this->app_id = Config::getConfig('WECHAT_APP_ID');
        $this->mch_id = Config::getConfig('WECHAT_MCH_ID');
        $this->key = Config::getConfig('WECHAT_KEY');
    }

    public function rules()
    {
        return [
            [['app_id', 'mch_id', 'key', 'out_trade_no', 'amount'], 'required'],
            [['amount'], 'number', 'min' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'app_id' => 'appid',
            'mch_id' => '商户号',
            'key' => '商户key',
            'out_trade_no' => '订单号',
            'amount' => '订单金额',
        ];
    }

    public function run()
    {
        if (!$this->validate()) {
            throw new Exception(current($this->getFirstErrors()));
        }
        $data = $this->getData();
        $xml = $this->postXmlCurl($data, self::API_Url);
        $arrayFromXml = $this->xml2array($xml);
        if ($arrayFromXml['return_code'] !== "SUCCESS" || $arrayFromXml['result_code'] !== "SUCCESS") {
            throw new Exception('微信请求失败:code:' . $arrayFromXml['return_code'] . ',msg:' . $arrayFromXml['return_msg']);
        }
        return $this->getPrePayData($arrayFromXml);
    }

    /**
     * 格式化请求参数
     * @return string
     */
    private function getData()
    {
        $data = [
            'appid' => $this->app_id,
            'mch_id' => $this->mch_id,
            'nonce_str' => Utils::genderRandomStr('', 32),
            'spbill_create_ip' => Utils::getIp(),
            'notify_url' => Url::to('/api/notify/pay/wxpay', true),
            'trade_type' => 'APP',
            'body' => $this->body,
            'out_trade_no' => $this->out_trade_no,
            'total_fee' => $this->amount,//单位:分
        ];
        $data = array_filter($data);
        ksort($data);

        $data['sign'] = $this->sign($data);
        return self::arr2xml($data);
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
     * @param $xml
     * @param $url
     * @param int $second
     * @return bool|mixed
     */
    private function postXmlCurl($xml, $url, $second = 30)
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
            curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }

    /**
     * Generate a signature.
     *
     * @param array $attributes
     * @param string $encryptMethod
     *
     * @return string
     */
    public function sign(array $attributes, $encryptMethod = 'md5')
    {
        $attributes['key'] = $this->key;

        return strtoupper(call_user_func_array($encryptMethod, [urldecode(http_build_query($attributes))]));
    }

    public function checkSign($data)
    {
        $signToCheck = $data['sign'];
        unset($data['sign']);
        ksort($data);
        $sign = $this->sign($data);
        if ($signToCheck != $sign) {
            return false;
        }
        return true;
    }

    private function getPrePayData($params)
    {
        $data = [
            'appid' => $params['appid'],
            'noncestr' => $params['nonce_str'],
            'partnerid' => $params['mch_id'],
            'prepayid' => $params['prepay_id'],
            'package' => 'Sign=WXPay',
            'timestamp' => TIMESTAMP,
        ];

        ksort($data);
        $data['sign'] = $this->sign($data);
        return $data;
    }

}