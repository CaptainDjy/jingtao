<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\taobao;

use common\components\taobao\requests\Request;
use common\models\Config;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;

class TaobaoClient extends BaseObject
{
    /**
     * 接口地址
     */
    const API_URL = 'http://gw.api.taobao.com/router/rest';

    /**
     * 接口key
     * @var string
     */
    public $appkey;

    /**
     * 接口密匙
     * @var string
     */
    public $secretKey;

    public $signMethod = 'md5';

    public $format = 'json';

    public $apiVersion = '2.0';

    /**
     * 初始化
     */
    public function init()
    {
        $this->appkey = Config::getConfig('TAOBAO_API_KEY');
        $this->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
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
            'method' => $request->method,
            'app_key' => $this->appkey,
            'sign_method' => $this->signMethod,
            'timestamp' => date("Y-m-d H:i:s"),
            'format' => $this->format,
            'v' => $this->apiVersion,
        ];
        if (null != $session) {
            $sysParams["session"] = $session;
        }
        $apiParams = $request->getApiParams();

        $params = ArrayHelper::merge($sysParams, $apiParams);
        $params['sign'] = $this->sign($params);
//        print_r($params);
//        exit;

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
//        print_r($response);
//        exit;
        if (!$response->isOk) {
            throw new Exception('淘宝接口网络请求错误：状态码' . $response->getStatusCode());
        }
        $data = Json::decode($response->content, true);
//        print_r($data);
//        exit;

        if (!empty($data['error_response']) && !empty($data['error_response']['code'])) {
            throw new Exception('淘宝接口报错：code:' . $data['error_response']['code'] . '.msg:' . $data['error_response']['msg']);
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

        $stringToBeSigned = $this->secretKey;
        foreach ($params as $k => $v) {
            if (!is_array($v) && "@" != substr($v, 0, 1)) {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->secretKey;

        return strtoupper(md5($stringToBeSigned));
    }

}
