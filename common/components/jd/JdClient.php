<?php
/**
 * @author 
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\jd;

use common\components\jd\requests\Request;
use common\models\Config;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;

class JdClient extends BaseObject
{
    /**
     * 接口地址
     */
//    const API_URL = 'https://api.jd.com/routerjson';//宙斯
    const API_URL = 'https://router.jd.com/api';//京东联盟
//    const API_URL = 'http://japi.jingtuitui.com/api/get_goods_list';

    /**
     * 接口key
     * @var string
     */
    private $app_key;

    /**
     * 接口密匙
     * @var string
     */
    private $app_secret;

    private $access_token;

    public $data_type = 'json';

    public $version = '1.0';

    /**
     * 初始化
     */
    public function init()
    {
        $this->app_key = Config::getConfig('JD_APP_KEY');
        $this->app_secret = Config::getConfig('JD_APPSECRET');
//        $this->access_token = Config::getConfig('JD_ACCESS_TOKEN');
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function run($request)
    {
        $sysParams = [
            'method' => $request->method,
//            'access_token' => $this->access_token,
            'app_key' => $this->app_key,
            'timestamp' => date("Y-m-d H:i:s"),
            'format' => $this->data_type,
            'sign_method'   => 'md5',
            'v' => $this->version,
        ];

        $apiParams=$request->getApiParams();

//        $params = ArrayHelper::merge($sysParams, $apiParams);
        $params = $sysParams;
        $params['param_json'] =json_encode($apiParams);
        $params['sign'] = $this->sign($params);
//        print_r($params);
//        exit;

        return $this->curlPost(self::API_URL, $params);
    }

    /**
     * @param $url
     * @param $params
     * @return array
     * @throws Exception
     */
    public function curlPost($url, $params)
    {
        $client = new Client();
        $response = $client->post($url, $params)->send();

        if (!$response->isOk) {
            throw new Exception('京东接口网络请求错误：状态码' . $response->getStatusCode());
        }
        $data = Json::decode($response->content, true);

        if (!empty($data['error_response']) && !empty($data['error_response']['code'])) {
            throw new Exception('京东接口报错：' . $data['error_response']['code'] . $data['error_response']['zh_desc']);
        }
        return $data;
    }

    /**
     * 签名
     * @param array $params
     * @return string
     */
    public function sign($params = [])
    {

        ksort($params);
        $stringToBeSigned ='';//$this->access_token;
        foreach ($params as $k => $v) {
            if (!is_array($v) && "@" != substr($v, 0, 1)) {
                $stringToBeSigned .= "$k$v";
            }
        }

        unset($k, $v);
        $sec = Config::getConfig('JD_APPSECRET');

//        print_r($sec.$stringToBeSigned.$sec);
//        exit;
        return strtoupper(md5($sec.$stringToBeSigned.$sec));
    }

    public function getAccessToken()
    {

    }

    public function getCode()
    {

    }

}
