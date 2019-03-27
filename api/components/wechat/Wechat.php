<?php

namespace api\components\wechat;

use common\helpers\Utils;
use common\helpers\XML;
use Psr\Http\Message\ResponseInterface;
use yii\base\BaseObject;
use GuzzleHttp\Client;
use yii\base\Exception;

/**
 * 微信授权登录
 * Class Wechat
 * @package api\components\wechat
 */
class Wechat extends BaseObject
{
    /**
     * api请求地址
     * @var string
     */
    public $urlBase = 'https://api.weixin.qq.com/sns/';

    /**
     * 请求access_token地址
     * @var string
     */
    public $getAccessTokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * 刷新access_token地址
     * @var string
     */
    public $refreshAccessTokenUrl = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

    /**
     * 获取userinfo地址
     * @var string
     */
    public $userinfoUrl = 'https://api.weixin.qq.com/sns/userinfo';

    /**
     * 统一下单地址
     * @var string
     */
    public $unifiedorderUrl = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    /**
     * 企业付款
     * @var string
     */
    public $mchPayUrl = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    /**
     * guzzleClient
     * @var Client
     */
    public $guzzleClient;

    /**
     * 应用唯一标识
     * @var string
     */
    public $app_id;

    /**
     * 应用密钥AppSecret
     * @var string
     */
    public $secret;

    /**
     * 商户ID
     * @var string
     */
    public $mch_id;

    /**
     * 商户key
     * @var string
     */
    public $key = '123456';

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
    public $notify_url;

    /**
     * 认证类型
     * @var array
     */
    public $grant_type = [
        'authorization_code' => 'authorization_code',
        'refresh_token' => 'refresh_token',
    ];

    /**
     * access_token
     * @var string
     */
    public $access_token;

    /**
     * access_token有效期截止时间
     * expires_end
     * @var int
     */
    public $expires_end;

    /**
     * refresh_token
     * @var string
     */
    public $refresh_token;

    /**
     * userinfo
     * @var array
     */
    public $userinfo;

    /**
     * openid
     * @var string
     */
    public $openid;

    public function init()
    {
    }

    /**
     * 授权获取用户信息
     * @param $code
     * @return array
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    // public function auth($code)
    // {
    //     $this->accessToken($code);
    //     $this->userInfo();
    //     $userinfo = array_merge($this->userinfo,['access_token' => $this->access_token]);
    //     return $userinfo;
    // }

    /**
     * 获取access_token
     * @param $code
     * @return string
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    // public function accessToken($code)
    // {
    //     // $data = [
    //     //     'appid' => $this->app_id,
    //     //     'secret' => $this->secret,
    //     //     'code' => $code,
    //     //     'grant_type' => $this->grant_type['authorization_code'],
    //     // ];
    //     //
    //     // $content = $this->send($this->getAccessTokenUrl, $data);
    //     $content = [
    //         'access_token' => '9_2VIgRkjWqzfVWRJ7grUHxtKjPtNflItAPU8UeweNbZJn1WcVXvCgFtkKokVjJJqLo1eI1aSbiHkgFdMemDsetQ4GHqfnJA-IIJW1WAnOc7w',
    //         'openid' => 'oRrdQt9FshqfrSMxBMLFQy0PwvZ4',
    //         'refresh_token' => '9_g39QO9n6PQ5En2Puu1sWVLxa0VwGVBWgBIUpsaZtNQuYY_6XWnkgnIw3cb7Ft-6A33KWgAn6frM2fr_0uOxyky7XbpygiS161btm2iASbNY',
    //         'expires_in' => 7200
    //     ];
    //     $this->access_token = $content['access_token'];
    //     $this->openid = $content['openid'];
    //     $this->refresh_token = $content['refresh_token'];
    //     $this->expires_end = time() + $content['expires_in'];
    //     // $this->refreshAccessToken();
    //     return $this->access_token;
    // }

    /**
     * 刷新access_token
     * @return string
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    // private function refreshAccessToken()
    // {
    //     $data = [
    //         'appid' => $this->app_id,
    //         'grant_type' => $this->grant_type['refresh_token'],
    //         'refresh_token' => $this->refresh_token,
    //     ];
    //     $content = $this->send($this->refreshAccessTokenUrl, $data);
    //     $this->access_token = $content['access_token'];
    //     $this->openid = $content['openid'];
    //     $this->refresh_token = $content['refresh_token'];
    //     $this->expires_end = time() + $content['expires_in'];
    //     return $this->access_token;
    // }

    /**
     * 获取有效access_token
     * @param null $code
     * @return string
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    // public function getExpiresAccessToken($code = null)
    // {
    //     if (!empty($code)) {
    //         //未授权
    //         $this->accessToken($code);
    //     }
    //     if (time() >= $this->expires_end) {
    //         //授权过期,刷新access_token
    //         $this->refreshAccessToken();
    //     }
    //     return $this->access_token;
    // }

    /**
     * 获取用户信息
     * @param $params
     * @return mixed
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function userInfo($params)
    {
        $data = [
            'access_token' => $params['access_token'],
            'openid' => $params['openid'],
        ];
        return $this->send($this->userinfoUrl, $data);
    }

    /**
     * 统一下单
     * [
     * 'out_trade_no' => 'ORDER_ID', // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
     * 'body' => 'APP名-实际商品名',
     * 'total_fee' => 888, // 企业付款金额，单位为分
     * ];
     * @param array $params
     * @return mixed
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */

    public function unify(array $params)
    {
        $data = $this->getData($params);
        return $this->send($this->unifiedorderUrl, $data, 'post');
    }

    /**
     * 企业付款
     *
     * [
     * 'partner_trade_no' => '商户订单号',
     * 'openid' => 'openid',
     * 'amount' => 888,
     * 'desc' => '理赔',
     * ]
     * @param array $params
     * @return mixed
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function toBalance(array $params)
    {
        $data = $this->getMchData($params);
        return $this->send($this->unifiedorderUrl, $data, 'post');
    }

    /**
     * 发送请求
     * @param $url
     * @param array $data
     * @param string $method
     * @return mixed
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function send($url, $data = [], $method = 'GET')
    {
        $response = $this->guzzleClient->request($method, $url, ['verify' => false, 'query' => $data]);
        $status_code = $response->getStatusCode();
        if ($this->isOk($response)) {
            $content = json_decode($response->getBody()->getContents(), true);
            if (!isset($content['errcode'])) {
                return $content;
            } else {
                throw new Exception('微信请求失败,errcode:' . $content['errcode'] . ',errmsg' . $content['errmsg']);
            }
        } else {
            throw new Exception('请求失败:' . $status_code . ',' . $response->getReasonPhrase());
        }
    }

    /**
     * Checks if response status code is OK (status code = 20x)
     * @param  $response ResponseInterface
     * @return bool whether response is OK.
     */
    public function isOk($response)
    {
        return strncmp('20', $response->getStatusCode(), 2) === 0;
    }

    /**
     * 格式化统一下单请求参数
     * @param $params
     * @return string
     */
    private function getData($params)
    {
        $base = [
            'appid' => $this->app_id,
            'mch_id' => $this->mch_id,
            'nonce_str' => Utils::genderRandomStr('', 32),
            'spbill_create_ip' => Utils::getIp(),
            'notify_url' => $this->notify_url,
            'trade_type' => 'APP',
        ];
        $params = array_filter(array_merge($base, $params));
        ksort($params);

        $params['sign'] = $this->generateSign($params, $this->key);
        $data['body'] = XML::build($params);
        return $data;
    }

    /**
     * 格式化企业付款请求参数
     * @param $params
     * @return string
     */
    private function getMchData($params)
    {
        $base = [
            'mch_appid' => $this->app_id,
            'mchid' => $this->mch_id,
            'nonce_str' => Utils::genderRandomStr('', 32),
            'check_name' => $this->check_name,
            'spbill_create_ip' => Utils::getIp(),
        ];
        $params = array_filter(array_merge($base, $params));
        ksort($params);

        $params['sign'] = $this->generateSign($params, $this->key);
        $data = array_merge([
            'cert' => $this->cert,
            'ssl_key' => $this->ssl_key,
        ], XML::build($params));
        return $data;
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
}