<?php

namespace common\components\taobao;

use common\helpers\Utils;
use common\models\AdvertSpace;
use common\models\Config;
use yii\base\BaseObject;
use yii\db\Exception;
use yii\httpclient\Client;

/**
 * 淘宝PID管理
 * Class TaobaoPid
 * @package common\components\taobao
 */
class TaobaoPid extends BaseObject
{
    public $cookie;

    private $aliId;
    private $siteId;

    /**
     * @return array|string|void
     * @throws Exception
     */
    public function init()
    {
        $this->aliId = Config::getConfig('ALIMAMA_ID');
        if (empty($this->aliId)) {
            throw new Exception('淘宝联盟推广位创建失败，淘宝联盟ID不能为空');
        }

        if (empty($this->cookie)) {
            $this->cookie = Config::getConfig('ALIMAMA_COOKIE');
        }
        if (empty($this->cookie)) {
            throw new Exception('淘宝联盟推广位创建失败，淘宝联盟COOKIE不能为空');
        }
    }

    /**
     * @param $siteId
     * @throws Exception
     */
    public function create($siteId)
    {
        if (!empty($siteId)) {
            $this->siteId = Config::getConfig('ALIMAMA_GID');
        }
        if (empty($this->siteId)) {
            throw new Exception('淘宝联盟推广位创建失败，导购ID不能为空');
        }

        $model = new AdvertSpace();
        $model->type = 1;
        $model->pid = $this->createItem();
        if (!$model->save()) {
            throw new Exception('创建失败，' . current($model->getFirstErrors()));
        }

    }

    /**
     * @return string
     * @throws Exception
     */
    public function createItem()
    {
        preg_match('/_tb_token_=(.*?);/', $this->cookie, $match);
        $token = $match[1];
        $client = new Client();
        $url = 'https://pub.alimama.com/common/adzone/selfAdzoneCreate.json';
        $data = [
            'tag' => '29',
            'gcid' => '7',
            'siteid' => $this->siteId,
            'selectact' => 'add',
            'newadzonename' => 'AT_' . date('Ymd_His'),
            '_tb_token_' => $token,
        ];
        $header = [
            'x-requested-with' => 'XMLHttpRequest',
            'Cookie' => $this->cookie,
        ];

        $response = $client->post($url, $data, $header)->send();
        if (!$response->isOk) {
            throw new Exception('淘宝推广位创建失败，请更新阿里妈妈COOKIE后尝试');
        }
        $data = $response->getData();
        if (!is_array($data) || $data['ok'] != 'true' || empty($data['data'])) {
            throw new Exception('淘宝推广位创建失败，请更新阿里妈妈COOKIE后尝试');
        }

        return 'mm_' . $this->aliId . '_' . $this->siteId . '_' . $data['data']['adzoneId'];
    }

    /**
     * @param array $param
     * [
     * 'toPage' => 1,
     * ]
     * @return array|mixed|string
     * @throws Exception
     */
    public function sync($param = [])
    {
        preg_match('/_tb_token_=(.*?);/', $this->cookie, $match);
        $token = $match[1];
        $client = new Client();
        $url = 'https://pub.alimama.com/common/adzone/adzoneManage.json';
        $base = [
            'tab' => 3,
            'toPage' => 1,
            'gcid' => '8',
            'perPageSize' => 40,
            't' => Utils::getMsectime(),
            '_tb_token_' => $token,
        ];
        $params = array_merge($base, $param);
        $header = [
            'x-requested-with' => 'XMLHttpRequest',
            'Cookie' => $this->cookie,
        ];

        $response = $client->get($url, $params, $header)->send();
        if (!$response->isOk) {
            throw new Exception('淘宝导购推广位获取失败，请更新阿里妈妈COOKIE后尝试');
        }
        $data = $response->getData();
        if (!is_array($data) || $data['ok'] != 'true' || empty($data['data'])) {
            throw new Exception('淘宝导购推广位获取失败，请更新阿里妈妈COOKIE后尝试');
        }
        $space_arr = [];
        $illegal = 0;
        $type = AdvertSpace::TYPE_TB;
        $count = count($data['data']['pagelist']);
        foreach ($data['data']['pagelist'] as $key => $item) {
            if (!preg_match('/^AT_.*/', $item['name'])) {
                $illegal++;
                continue;
            }
            $space_arr[$key]['title'] = $item['name'];
            $space_arr[$key]['pid'] = $item['adzonePid'];
            $space_arr[$key]['type'] = $type;
            $space_arr[$key]['status'] = 1;
            $space_arr[$key]['created_at'] = TIMESTAMP;
            $space_arr[$key]['updated_at'] = TIMESTAMP;
        }
        $result = AdvertSpace::batchSync($space_arr, $type);
        unset($data, $space_arr);
        $result['count'] = $count;
        $result['illegal'] = $illegal;
        return $result;
    }

}
