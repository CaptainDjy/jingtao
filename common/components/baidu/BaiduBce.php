<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/14 19:55
 */

namespace common\components\baidu;

use BaiduBce\Auth\BceV1Signer;
use BaiduBce\Http\BceHttpClient;
use BaiduBce\Services\Bos\BosClient;
use BaiduBce\Services\Sts\StsClient;
use common\models\Config;
use yii\base\Exception;
use yii\base\Object;

include 'bce-php-sdk/BaiduBce.phar';

/**
 * 百度 BCE
 * @property array $config
 * @property BosClient bosClient
 * @property StsClient stsClient
 * @property BceHttpClient bceHttpClient
 * @property array $sessionToken
 */
class BaiduBce extends Object
{
    private $_config;
    private $_stsClient;
    private $_bosClient;
    private $_bceHttpClient;

    /**
     * 上传文件
     * @param string $name 存储到bos的文件名，文件夹形式a/1.gif
     * @param string $filePath 要上传的文件绝对路径
     * @return mixed
     */
    public function uploadObject($name, $filePath)
    {
        return $this->bosClient->putObjectFromFile($this->config['bucketName'], $name, $filePath);
    }

    /**
     * 阅读DOC文档
     * @param string $documentId 文档ID
     * @param int $expireInSeconds 文档阅读过期时间
     * @return array [
     *  'documentId' => string
     *  'token' => string
     *  ……
     * ]
     * @link https://cloud.baidu.com/doc/DOC/API.html#.E9.98.85.E8.AF.BB.E6.96.87.E6.A1.A3
     */
    public function readDoc($documentId, $expireInSeconds = 3600)
    {
        // TODO 需要解决每次在根目录生成null的问题
        $config = [
            'credentials' => [
                'accessKeyId' => Config::getConfig('BAIDU_BCE_AK'),
                'secretAccessKey' => Config::getConfig('BAIDU_BCE_SK'),
            ],
            'endpoint' => 'http://doc.bj.baidubce.com',
        ];
        $httpMethod = 'GET';
        $path = '/v2/document/' . $documentId;
        $signer = new BceV1Signer();
        $args = [
            'body' => 'null',
            'headers' => [],
            'params' => [
                'read' => '',
                'expireInSeconds' => $expireInSeconds,
            ],
            'outputStream' => null,
        ];
        $result = $this->bceHttpClient->sendRequest(
            $config,
            $httpMethod,
            $path,
            $args['body'],
            $args['headers'],
            $args['params'],
            $signer,
            $args['outputStream']
        );

        return json_decode($result['body'], true);
    }

    /**
     * 获取sts临时会话凭证
     * @return object
     */
    public function getSessionToken()
    {
        $request = [
            'acl' => '', //用户定义的acl
            'durationSeconds' => 3600, //STS凭证有效时间
        ];
        return $this->stsClient->getSessionToken($request);
    }

    /**
     * 初始化配置
     * @return array
     * @throws Exception
     */
    protected function getConfig()
    {
        if ($this->_config === null) {
            $this->_config = [
                'bucketName' => Config::getConfig('BAIDU_BOS_BUCKET'),
                'credentials' => [
                    'accessKeyId' => Config::getConfig('BAIDU_BCE_AK'),
                    'secretAccessKey' => Config::getConfig('BAIDU_BCE_SK'),
                    'sessionToken' => ''
                ],
                'endpoint' => Config::getConfig('BAIDU_BOS_ENDPOINT'),
                'stsEndpoint' => Config::getConfig('BAIDU_STS_ENDPOINT'),
            ];
        }
        foreach ($this->_config as $item) {
            if (empty($item)) {
                throw new Exception('参数缺失: 百度云存储尚未配置！');
            }
        }
        return $this->_config;
    }

    /**
     * 初始化 StsClient
     * @return StsClient
     */
    protected function getStsClient()
    {
        if ($this->_stsClient === null) {
            $this->_stsClient = new StsClient($this->config);
        }
        return $this->_stsClient;
    }

    /**
     * 初始化 BosClient
     * @return BosClient
     */
    protected function getBosClient()
    {
        if ($this->_bosClient === null) {
            $this->_bosClient = new BosClient($this->config);
        }
        return $this->_bosClient;
    }

    /**
     * 初始化 BceHttpClient
     * @return BceHttpClient
     */
    protected function getBceHttpClient()
    {
        if ($this->_bceHttpClient === null) {
            $this->_bceHttpClient = new BceHttpClient();
        }
        return $this->_bceHttpClient;
    }

}
