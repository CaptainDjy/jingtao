<?php

namespace common\components\miao;

use common\helpers\Http;
use common\models\Config;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\httpclient\Exception;

/**
 * Created by PHPSTORM.
 * User: Yuuuuuu
 * Date: 2018/9/27
 * Time: 10:41
 */
class MiaoClient extends BaseObject{

    /*
     * 接口地址
     */
    public $API_URL = 'https://api.open.21ds.cn/';

    public $apikey ;

    public function init()
    {
        $this->apikey = Config::getConfig('MIAO_APKEY');
    }

    /**
     * @param $request
     * @return mixed
     * @throws Exception
     */
    public function run($request){
        $url = $this->API_URL . $request->url;

        $sysParams = [
            'apkey' => $this->apikey
        ];

        $apiParams = $request->getApiParams();

        $params = ArrayHelper::merge($sysParams, $apiParams);

        return Http::get($url,$params);

    }
}