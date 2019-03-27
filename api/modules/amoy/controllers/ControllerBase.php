<?php

namespace api\modules\amoy\controllers;

use api\components\wechat\Wechat;
use common\models\Config;
use GuzzleHttp\Client;
use yii\base\Exception;
use yii\helpers\Json;
use yii\rest\Controller;
use yii\web\Response;

// 指定允许其他域名访问   跨域
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
// 响应头设置
header('Access-Control-Allow-Headers:*');
header('Access-Control-Allow-Credentials: true');


/**
 * Class ControllerBase
 * @package api\controllers
 * @property Wechat $wechat
 * @property int $uid
 */
class ControllerBase extends Controller
{
    public $uid;
    private $_wechat = null;
    private $url = 'http://gw.api.taobao.com/router/rest';
    private $pddurl = 'http://gw-api.pinduoduo.com/api/router';
    private $jdUrl = 'https://api.jd.com/routerjson';

    /**
     * @param int $code
     * @param array $data
     * @param string $msg
     * @return array
     */
    public function responseJson($code = 0, $data = [], $msg = '')
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $data = ['code' => $code, 'data' => $data, 'msg' => $msg];
        return $data;
    }

    /**
     * 实例化微信类
     * @return Wechat
     */
    public function getWechat()
    {
        if (!$this->_wechat) {
            $config = \Yii::$app->params['WECHAT'];
            $config['guzzleClient'] = new Client();
            $config['cert'] = Config::getConfig('WECHAT_CERT');
            $config['ssl_key'] = Config::getConfig('WECHAT_SSL_KEY');
            $this->_wechat = new Wechat($config);
        }
        return $this->_wechat;
    }

    /**
     * 数据进模型
     * @param $model
     * @param $data
     * @return bool
     */
    public function arrayLoad($model, $data)
    {
        if (!empty($data) && is_array($data) && is_object($model)) {
            foreach ($data as $k => $v) {
                $model->$k = $v;
            }
            return $model;
        } else {
            return false;
        }
    }

    /**
     * 支付密码验证
     * @param $pwd -原密码
     * @param $payPwd -输入密码
     * @return array
     * @throws Exception
     */
    public function validatePayPwd($pwd, $payPwd)
    {
        if (empty($pwd)) {
            throw new Exception('请设置支付密码');
        }
        if (empty($payPwd)) {
            throw new Exception('请填写支付密码');
        }
        if ($pwd == md5($payPwd)) {
            return $this->responseJson(0, '', '验证成功');
        } else {
            throw new Exception('支付密码错误');
        }
    }


    /**
     * 获取京东接口结果
     * @param $data
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function getJdResult($data)
    {
        $data['access_token'] = "d784c8d9-2366-466a-a0f5-b01b2c0fd208";
        $data['app_key'] = "E6F61024F8E9DA177EDD9172FA136867";
        $data['appSecret'] = "d145ed1fdc5e4ae8822507bddacf654b";
        $data['timestamp'] = time();
        $data['format'] = "json";
        $data['v'] = "2.0";
        $client = new Client([
            'base_uri' => $this->jdUrl,
            'timeout' => 2.0,
            'verify' => false,
        ]);
        $response = $client->request('post', $this->jdUrl, ['query' => $data]);
        $remainingBytes = $response->getBody()->getContents();
        $result = json_decode($remainingBytes, true);
        if ($result) {
            unset($data['timestamp']);
            $strKey = $data['appSecret'];
            foreach ($data as $key => $value) {
                $strKey .= $key . $value;
            }
            $strKey .= $data['appSecret'];
            $key = md5($strKey);
            $redis = \Yii::$app->get('redis');
            if ($val = $redis->get($key)) {
                $result = json_decode($val, true);
            } else {
                $redis->set($key, $remainingBytes);
                $redis->expire($key, 60);
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 拼多多获取结果
     * @param $data
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function getPddResult($data)
    {
        $data['client_id'] = Config::getConfig('PDD_CLIENT_ID');//POP分配给应用的client_id
        $data['client_secret'] = Config::getConfig('PDD_CLIENT_SECRET');
        //$data['access_token'] = '1b6069142fda44f1a3e7eae283d5638e21dc38a2';
        $data['data_type'] = 'JSON';
        $data['timestamp'] = time();

        ksort($data);
        $str = $data['client_secret'];
        foreach ($data as $key => $value) {
            $str .= $key . $value;
        }
        $str .= $data['client_secret'];
        $str = strtoupper(md5($str));
        $data['sign'] = $str; //签名
        $client = new Client([
            'base_uri' => $this->pddurl,
            'timeout' => 2.0,
        ]);
/*        $response = $client->request('post', $this->pddurl, [
            'query' => [
                $data,
            ]
        ]);*/
        /*$response = $this->curlPost($this->pddurl,$data);
        print_r($response);die();
        $remainingBytes = $response->getBody()->getContents();*/
        $result = $this->curlPost($this->pddurl,$data);
//        print_r($result);
//        exit;
        if ($result) {
            $redis = \Yii::$app->get('redis');
            unset($data['timestamp']);
            unset($data['sign']);
            $strKey = '';
            foreach ($data as $key => $value) {
                $strKey .= $key . $value;
            }
            /*$key = md5($strKey);
            if ($val = $redis->get($key)) {
                $result = json_decode($val, true);
            } else {
                $redis->set($key, $result);
                $redis->expire($key, 60);
            }*/
            return $result;
        } else {
            return false;
        }
    }/**
 * @param $url
 * @param $params
 * @return array
 * @throws Exception
 */
    public function curlPost($url, $params)
    {
        $client = new \yii\httpclient\Client();
        $response = $client->post($url, $params)->send();
//        print_r($response);
//        exit;
        if (!$response->isOk) {
            throw new Exception('拼多多接口网络请求错误：状态码:' . $response->getStatusCode());
        }
        $data = Json::decode($response->content, true);
        if (!empty($data['error_response']) && !empty($data['error_response']['error_code'])) {
            throw new Exception('拼多多接口报错：' . $data['error_response']['error_code'] . $data['error_response']['error_msg']);
        }
        return $data;

    }

    /**
     * 获取淘宝接口结果
     * @param $data
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function getUrlResult($data)
    {
        $data['app_key'] = "24908737"; // TODO  读数据库
        $data['secretKey'] = "63d486c6ff7429f39b9e9e48d9181ff8";
        $data['sign_method'] = "md5";
        $data['timestamp'] = date("Y-m-d H:i:s", time());
        $data['format'] = "json";
        $data['v'] = "2.0";
        ksort($data);
        $str = $data['secretKey'];
        foreach ($data as $key => $value) {
            $str .= $key . $value;
        }
        $str .= $data['secretKey'];
        $str = strtoupper(md5($str));
        $data['sign'] = $str;
        $client = new Client([
            'base_uri' => $this->url,
            'timeout' => 2.0,
        ]);
        $response = $client->request('post', $this->url, ['query' => $data]);
        $remainingBytes = $response->getBody()->getContents();
        $result = json_decode($remainingBytes, true);
        if (!empty($result['error_response'])) {
            return $result['error_response'];
        } else {
            $redis = \Yii::$app->get('redis');
            unset($data['timestamp']);
            unset($data['sign']);
            $strKey = '';
            foreach ($data as $key => $value) {
                $strKey .= $key . $value;
            }
            $key = md5($strKey);
            if ($val = $redis->get($key)) {
                $result = json_decode($val, true);
            } else {
                $redis->set($key, $remainingBytes);
                $redis->expire($key, 60);
            }
//            return $result;
            return 111;
        }
    }
}
