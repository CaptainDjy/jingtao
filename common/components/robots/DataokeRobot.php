<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\robots;

use common\components\miao\MiaoClient;
use common\components\miao\taobao\requests\GetGoodsCouponUrl;
use common\models\Config;
use common\models\Goods;
use common\models\Log;
use common\models\RobotDataoke;
use yii\base\UserException;
use yii\console\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;

class DataokeRobot extends Robot
{
    /**
     * 大淘客密匙
     * @var string
     */
    private $apiKey;

    /**
     * 采集特定分类
     * @var int
     */
    private $cid = 0;

    private $pid;
    /**
     * 来源和入库分类对应
     * @var array
     */
    private $map;

    public function init()
    {
        parent::init();
        $this->apiKey = Config::getConfig('DATAOKE_API_KEY');
        $this->pid=Config::getConfig('MIAO_PID');
        $this->map = RobotDataoke::map();
    }

    /**
     * 主运行函数
     * @param string $type
     * @return array|bool
     * @throws UserException
     * @throws \yii\httpclient\Exception
     */
    public function run($type = 'total')
    {
        $data = [];
        switch ($type) {
            case 'total':
                $data = $this->getTotal();
                break;
        }
        return $data;
    }

    /**
     * 获取全部数据
     * @return bool
     * @throws UserException
     * @throws \yii\httpclient\Exception
     */
    private function getTotal()
    {
        $client = new Client();
        $response = $client->get('http://api.dataoke.com/index.php', [
            'r' => 'Port/index',
            'type' => 'total',
            'appkey' => $this->apiKey,
            'v' => 2,
            'page' => $this->pageNum
        ])->send();

        if (!$response->isOk) {
            throw new UserException('大淘客网络请求错误：状态码' . $response->getStatusCode());
        }
        $resultData = Json::decode($response->content, true);
//        print_r($resultData);
//        exit();
        if (empty($resultData) || empty($resultData['result'])) {
            return false;
        }

        foreach ($resultData['result'] as $data) {

            $model = $this->convertGoodsModel($data);

            if ($model->isNewRecord) {
                $this->num++;
            }
            if (!$model->save()) {
                throw new UserException('商品保存失败：' . $model->error);
            }
        }
        return true;
    }

    /**
     * 转化数据为商品模型
     * @param array $data
     * @return Goods
     */
    private function convertGoodsModel($data = [])
    {
        $oldModel = Goods::find()->where(['type' => [11, 12], 'origin_id' => (string)$data['GoodsID']])->one();
        if (!empty($oldModel)) {
            $model = $oldModel;
        } else {
            $model = new Goods();
            $model->loadDefaultValues();
        }

        $model->cid = ArrayHelper::getValue($this->map, $data['Cid'], 0);
        $model->from_cid = $data['Cid'];
        $model->type = $data['IsTmall'] == '1' ? Goods::TYPE_ID['tmall'] : Goods::TYPE_ID['taobao'];
        $model->origin_id = (string)$data['GoodsID'];
        $model->from_id = (string)$data['ID'];
        $model->title = $data['Title'];
        $model->sub_title = $data['D_title'];

        $http=substr($data['Pic'],0,4);
        if ($http=='http'){
            $model->thumb = $data['Pic'];//商品图片
        }else{
            $model->thumb = 'https:'.$data['Pic'];//商品图片
        }
//        $model->thumb = $data['Pic'];
        $model->origin_price = $data['Org_Price'];
        $model->coupon_price = $data['Price'];

        $model->coupon_id = $data['Quan_id'];
        $model->coupon_money = $data['Quan_price'];
        $model->coupon_rate = bcdiv($model->coupon_price * 10, $model->origin_price, 1);
        $model->coupon_total = 0;
        $model->coupon_remained = $data['Quan_surplus'];
        $model->coupon_received = $data['Quan_receive'];
        $model->coupon_end_at = strtotime($data['Quan_time']);
//        $model->coupon_link = $data['Quan_link'];
        $model->coupon_link = 'https://uland.taobao.com/coupon/edetail?activityId=' . $data['Quan_id'] . '&pid='.$this->pid.'&itemId=' . $data['GoodsID'] . '&src=cd_cdll';
        $model->coupon_short_link = $data['Quan_m_link'];
        $model->coupon_condition = $data['Quan_condition'];

//        $model->commission_money = $data['Org_Price'] * ($data['Commission'] / 100);
        $model->commission_rate = $data['Commission'];
        $model->commission_rate_plan = $data['Commission_jihua'];
        $model->commission_rate_queqiao = $data['Commission_queqiao'];
        $model->plan_link = $data['Jihua_link'];
        $model->plan_status = $data['Jihua_shenhe'];
        $model->plan_type = empty($data['Jihua_link']) ? 0 : 1;

        $model->sales_num = $data['Sales_num'];
        if ($data['Introduce']) {
            $model->description = $data['Introduce'];
        } else {
            $model->description = $model->title . '，现价只需要' . $model->origin_price . '元，领券后下单还可优惠' . $model->coupon_money . '元，赶紧抢购吧！';
        }

        // $model->keywords = $data['D_title'];
        // $model->info = $data['D_title'];
        $model->seller_id = $data['SellerID'];
        $model->seller_nickname = $data['SellerID'];
        $model->start_time = TIMESTAMP;
        $model->end_time = strtotime($data['Quan_time']);
        $model->commission_money = bcmul($model->coupon_price, bcdiv($model->commission_rate, 100, 2), 2);

        return $model;
    }

}
