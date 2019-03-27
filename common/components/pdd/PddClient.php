<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\pdd;

use common\components\pdd\requests\Request;
use common\models\Config;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;

class PddClient extends BaseObject
{
    /**
     * 接口地址
     */
    const API_URL = 'http://gw-api.pinduoduo.com/api/router';

    /**
     * 接口key
     * @var string
     */
    private $client_id;

    /**
     * 接口密匙
     * @var string
     */
    private $client_secret;

    private $access_token;

    public $data_type = 'JSON';

    public $version = 'V1';

    /**
     * 初始化
     */
    public function init()
    {
        $this->client_id = Config::getConfig('PDD_CLIENT_ID');
        $this->client_secret = Config::getConfig('PDD_CLIENT_SECRET');
        $this->access_token = '79b294b085944e95899a77f8964a151a9fd14d01';
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function run($request)
    {
        $sysParams = [
            'type' => $request->type,
            'client_id' => $this->client_id,
            'timestamp' => time(),
            'data_type' => $this->data_type,
            'version' => $this->version,
        ];

        if ($request->isNeedAuth) {
            $sysParams['access_token'] = $this->access_token;
        }

        $apiParams = $request->getApiParams();
        $params = ArrayHelper::merge($sysParams, $apiParams);
        $params['sign'] = $this->sign($params);
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
            throw new Exception('拼多多接口网络请求错误：状态码:' . $response->getStatusCode());
        }
        $data = Json::decode($response->content, true);
        if (!empty($data['error_response']) && !empty($data['error_response']['error_code'])) {
            throw new Exception('拼多多接口报错：' . $data['error_response']['error_code'] . $data['error_response']['error_msg']);
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
        $stringToBeSigned = $this->client_secret;
        foreach ($params as $k => $v) {
            if (!is_array($v) && "@" != substr($v, 0, 1)) {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->client_secret;
        return strtoupper(md5($stringToBeSigned));
    }

}
