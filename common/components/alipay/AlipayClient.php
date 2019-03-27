<?php

namespace common\components\alipay;

use common\components\alipay\requests\AlipayFundTransToaccountTransferRequest;
use common\components\alipay\requests\AlipayTradeAppPayRequest;
use common\components\alipay\requests\Request;
use common\models\Config;
use common\models\Withdraw;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\httpclient\Client;

class AlipayClient extends BaseObject
{
    /**
     * 接口地址
     */
    const API_URL = 'https://openapi.alipay.com/gateway.do';

    /**
     * 应用ID
     * @var string
     */
    private $appId;

    public $format = 'json';

    public $charset = 'utf-8';

    public $signType = 'RSA2';

    public $appAuthToken = null;

    protected $alipaySdkVersion = "alipay-sdk-php-20161101";

    /**
     * 支付私钥
     * @var string
     */
    private $privKey;

    /**
     * 支付公钥
     * @var string
     */
    private $pubKey;

    /**
     * 支付宝公钥
     * @var
     */
    private $alipayKey;

    public $apiVersion = '1.0';

    /**
     * 初始化
     */
    public function init()
    {
        $this->appId = Config::getConfig('ALIPAY_APP_ID');
        $this->privKey = Config::getConfig('ALIPAY_PRIV_KEY');
        $this->pubKey = Config::getConfig('ALIPAY_PUB_KEY');
        $this->alipayKey = Config::getConfig('ALIPAY_KEY');
    }

    /**
     * @param Request $request
     * @param null $session
     * @return mixed
     * @throws Exception
     */
    public function run($request, $session = null)
    {
        $sysParams = [
            'app_id' => $this->appId,
            'method' => $request->method,
            'format' => $this->format,
            'charset' => $this->charset,
            'sign_type' => $this->signType,
            'timestamp' => date("Y-m-d H:i:s"),
            'version' => $this->apiVersion,
            'app_auth_token' => $this->appAuthToken,
            'biz_content' => json_encode($request->getApiParams()),
        ];
        if (null != $session) {
            $sysParams["session"] = $session;
        }
        $apiParams = $request->getApiParams();

        $params = ArrayHelper::merge($sysParams, $apiParams);
        $params['sign'] = $this->sign($params);

        return $this->curlPost(self::API_URL, $params);
    }

    /**
     * @param $url
     * @param $params
     * @return mixed
     * @throws Exception
     */
    public function curlPost($url, $params)
    {
        $client = new Client();
        $response = $client->post($url, $params)->send();

        if (!$response->isOk) {
            throw new Exception('支付宝接口网络请求错误：状态码' . $response->getStatusCode());
        }
        return $response->content;
    }

    /**
     * 生成用于调用收银台SDK的字符串
     * @param $request AlipayTradeAppPayRequest
     * @return string
     */
    public function sdkExecute($request)
    {
        $params = [
            'app_id' => $this->appId,
            'method' => $request->method,
            'format' => $this->format,
            'charset' => $this->charset,
            'sign_type' => $this->signType,
            'timestamp' => date("Y-m-d H:i:s"),
            'version' => $this->apiVersion,
            'alipay_sdk' => $this->alipaySdkVersion,
        ];

        // 订单标题
        $subject = Config::getConfig('WEB_SITE_TITLE').'会员升级';
        // 订单详情
        $body = Config::getConfig('WEB_SITE_TITLE').'会员升级,获得更多权益';
        $params['biz_content'] = "{\"body\":\"" . $body . "\","
            . "\"subject\": \"" . $subject . "\","
            . "\"out_trade_no\": \"" . $request->out_trade_no . "\","
            . "\"total_amount\": \"" . $request->total_amount . "\""
            . "}";;
        $params['notify_url'] = $request->notify_url;

        ksort($params);

        $params['sign'] = $this->sign($params);

        return http_build_query($params);
    }

    /**
     * 签名 RSA2
     * @param array $params
     * @return string
     */
    public function sign($params = [])
    {
        $stringToBeSigned = $this->getSignContent($params);
        $str = chunk_split($this->privKey, 64, "\n");
        $key = "-----BEGIN RSA PRIVATE KEY-----\n$str-----END RSA PRIVATE KEY-----\n";
        openssl_sign($stringToBeSigned, $sign, $key, OPENSSL_ALGO_SHA256);
        return base64_encode($sign);
    }

    /**
     * 验签
     * @param array $params
     * @param $sign
     * @return string
     */
    public function checkSign($params, $sign)
    {
        $str = chunk_split($this->alipayKey, 64, "\n");
        $key = "-----BEGIN PUBLIC KEY-----\n$str-----END PUBLIC KEY-----\n";
        return (bool)openssl_verify(json_encode($params), base64_decode($sign), $key, OPENSSL_ALGO_SHA256);
    }

    /**
     * 签名字符串
     * @param array $params
     * @return string
     */
    public function getSignContent($params)
    {
        ksort($params);
        $stringToSign = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (empty($params[$k])) {
                continue;
            }
            if ($i == 0) {
                $stringToSign .= "$k" . "=" . "$v";
            } else {
                $stringToSign .= "&" . "$k" . "=" . "$v";
            }
            $i++;
        }
        unset ($k, $v);
        return $stringToSign;
    }

    /**
     * 提现到支付宝账户
     * @param $model Withdraw
     * @return mixed
     * @throws Exception
     */
    public static function withdraw($model)
    {
        $client = new self();
        $request = new AlipayFundTransToaccountTransferRequest();
        $request->out_biz_no = $model->trade_sn;//商户订单号
        $request->payee_type = 'ALIPAY_LOGONID';//收款方账户类型:支付宝登录号，支持邮箱和手机号格式。
        $request->payee_account = $model->pay_to;//收款账户
        $request->amount = $model->amount;//金额,单位元,最少0.1元
        $request->remark = Config::getConfig('WEB_SITE_TITLE').'余额提现';//转账备注
        $content = $client->run($request);

        $data = Json::decode($content, true);
        $response = $data['alipay_fund_trans_toaccount_transfer_response'];
        $sign = $data['sign'];
        if ($response['code'] == '10000') {
            if ($client->checkSign($response, $sign)) {
                return $response;
            }
            throw new Exception('支付宝提现验签失败!');
        } else {
            throw new Exception('支付宝提现请求失败:code:' . $response['code'] . ',msg:' . $response['msg']);
        }
    }

    /**
     * app支付接口2.0
     * @param $order
     * @return string
     */
    public function tradeAppPay($order)
    {
        $request = new AlipayTradeAppPayRequest();
        $request->out_trade_no = $order->trade_no;//商户订单号
        $request->total_amount = $order->amount;//订单金额
        $request->notify_url = Url::to(['notify/alipay'], true);
        return $this->sdkExecute($request);
    }
}
