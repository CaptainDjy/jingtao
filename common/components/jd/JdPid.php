<?php

namespace common\components\jd;

use common\models\AdvertSpace;
use common\models\Config;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\httpclient\Client;
use fast\Http;

class JdPid extends BaseObject
{
    private $appid;
    private $unionid;
    private $cookies;

    public function init()
    {
        $this->unionid = Config::getConfig('JD_UNIONID');
        $this->appid = Config::getConfig('JD_APPID');
        $this->cookies = Config::getConfig('JD_COOKIE');
        if (empty($this->unionid)) {
            throw new Exception('京东推广位创建失败，京东联盟ID不能为空');
        }
        if (empty($this->appid)) {
            throw new Exception('京东推广位创建失败，京东APP ID不能为空');
        }
        if (empty($this->cookies)) {
            throw new Exception('京东推广位创建失败，京东COOKIE不能为空');
        }
    }

    public function create($num, $cookie = '')
    {
        if (!empty($cookie)) {
            $this->cookies = $cookie;
        }
        for ($i = 0; $i < $num; $i++) {
            $model = new AdvertSpace();
            $model->type = 2;
            $model->pid = $this->createItem();
            if (!$model->save()) {
                throw new Exception('创建失败，' . current($model->getFirstErrors()));
            }
        }

    }

    /**
     * @return string
     * @throws Exception
     */
    public function createItem()
    {
        $client = new Client();
        //$url = 'https://union.jd.com/api/promotion/queryPromotionSiteLists';//'https://media.jd.com/gotoadv/getCustomCode/1';
//        $data = [
//            'materialType' => '7', // 推广物料类型
//            'type' => '2', // 1已有 2新建
//            'wareUrl' => 'http://item.jd.com/28411405015.html',
//            'isApp' => '2',
//            'adtType' => '33', // 推广位类型 33APP
//            'protocol' => '1',
//            'unionAppId' => $this->appid,
//            'appCall' => '2',
//            'positionName' => TIMESTAMP,
//            'data' => [
//                'id' => '1704842399',//$this->appid,
//                'promotionType' => 2,//TIMESTAMP,
//                'opType'  => '2'
//            ],
//            'pageNo'=>1,
//            'pageSize'=>20,
//            'totalCount'=>100
//        ];
//        $header = [
//            'x-requested-with' => 'XMLHttpRequest',
//            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36',//'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.26 Safari/537.36 Core/1.63.5383.400 QQBrowser/10.0.1313.400',
//            'Cookie' => $this->cookies,
//        ];

        $response = $client->get('http://api.josapi.net/addpid?unionid=1001154999&authkey=ece3b6ab1c8b87a78b74343debd0f9419de6c4404d4bcabd5a51134518badf1d8b54c881b60894f7&pidname='.'京淘多返利'.'type=2&siteid=1704842399')->send();
        $data = $response->data;
        print_r($data);
        exit;
        if (empty($data) || !is_array($data) || empty($data['newSpaceName'])) {
            throw new Exception('京东推广位创建失败，请更新京东COOKIE后尝试');
        }
        return $this->unionid . '_' . $this->appid . '_' . $data['newSpaceName'];
    }

    /**
     * @param array $param
     * [
     * 'pageIndex' => 1,
     * 'pageSize' => 50,
     * ]
     * @return array|mixed|string
     * @throws Exception
     */
    public function sync($param = [])
    {
        $client = new Client();
        $url = 'https://media.jd.com/myadv/siteNew/2';
        $base = [
            'pageIndex' => 2,
            'pageSize' => 50,
            'webSiteId' => 1287311125,//所属应用ID
            // 'spaceName' => '',//推广位名称
        ];
        $params = array_merge($base, $param);
        $header = [
            'x-requested-with' => 'XMLHttpRequest',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.26 Safari/537.36 Core/1.63.5383.400 QQBrowser/10.0.1313.400',
            'Cookie' => $this->cookies,
        ];

        $response = $client->get($url, $params, $header)->send();
        if (!$response->isOk) {
            throw new Exception('京东APP推广位获取失败，请更新京东COOKIE后尝试');
        }
        $content = $response->getContent();
        if (preg_match('/error/', $content)) {
            throw new Exception(json_decode($content, true)['error']);
        }
        $data = $this->pregContent($content);

        $space_arr = [];
        $illegal = 0;
        $count = count($data);
        $type = AdvertSpace::TYPE_JD;
        foreach ($data as $key => $item) {
            if (!preg_match('/^Android-京淘尚品$/', trim($item[2]))) {
                $illegal++;
                continue;
            }
            $space_arr[$key]['title'] = $item[1];
            $space_arr[$key]['pid'] = $item[4];
            $space_arr[$key]['type'] = $type;
            $space_arr[$key]['status'] = 1;
            $space_arr[$key]['created_at'] = TIMESTAMP;
            $space_arr[$key]['updated_at'] = TIMESTAMP;
        }
        $result = AdvertSpace::batchSync($space_arr, $type);
        unset($content, $data, $space_arr);
        $result['count'] = $count;
        $result['illegal'] = $illegal;
        return $result;
    }

    /**
     * @param $content
     * @return array
     * array (size=50)
     * 0 =>
     * array (size=6)
     * 0 => string '1316411423' (length=10)
     * 1 => string '1527425750' (length=10)
     * 2 => string '
     * Android-京淘尚品
     * ' (length=136)
     * 3 => string '2018-05-27 20:55' (length=16)
     * 4 => string '1000603922_1287311125_1316411423' (length=32)
     * 5 => string '
     * <a class='opration-btn' onClick='updateSite(2,1316411423)'>修改</a>
     * <a class='opration-btn' onClick="deleteSite(2,1316411423,'1527425750')">删除</a>
     * ' (length=237)
     * 1 =>
     * array (size=6)
     * 0 => string '1316378514' (length=10)
     * 1 => string '1527425749' (length=10)
     * 2 => string '
     * Android-京淘尚品
     * ' (length=136)
     * 3 => string '2018-05-27 20:55' (length=16)
     * 4 => string '1000603922_1287311125_1316378514' (length=32)
     * 5 => string '
     * <a class='opration-btn' onClick='updateSite(2,1316378514)'>修改</a>
     * <a class='opration-btn' onClick="deleteSite(2,1316378514,'1527425749')">删除</a>
     * ' (length=237)
     * ...
     * )
     */
    private function pregContent($content)
    {
        $result = [];
        // 正则匹配网页内容
        $pattern_tbody = '/<tbody id="appSiteTbody">([\s\S]*?)<\/tbody>/ies';
        preg_match_all($pattern_tbody, $content, $tbody_matches);
        if (!isset($tbody_matches[1][0])) {
            return $result;
        }
        $pattern_tr = '/<tr>([\s\S]*?)<\/tr>/ies';
        preg_match_all($pattern_tr, $tbody_matches[1][0], $tr_matches);
        if (!isset($tr_matches[1]) || !@is_array($tr_matches[1])) {
            return $result;
        }
        foreach ($tr_matches[1] as $key => $match) {
            $pattern_td = '/<td>([\s\S]*?)<\/td>/ies';
            preg_match_all($pattern_td, $match, $td_matches);
            $result[] = $td_matches[1];
        }
        return $result;
    }

}
