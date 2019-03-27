<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\robots;

use common\components\pdd\PddClient;
use common\components\pdd\requests\DdkCmsPromUrlGenerate;
use common\models\config;
use common\components\pdd\requests\DdkGoodsSearchRequest;
use common\components\pdd\requests\DdkGoodsUrlGenerate;
use common\components\pdd\requests\GoodsOptGetRequest;
use common\models\Goods;
use common\models\RobotDdk;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * 多多客商品采集
 * Class Ddk
 * @package common\components\robots
 */
class DdkRobot extends Robot
{
    /**
     * 采集特定分类
     * @var int
     */
    private $cid = 0;

    /**
     * 来源和入库分类对应
     * @var array
     */
    private $map;

    public function init()
    {
        parent::init();
        $this->map = RobotDdk::map();
    }

    /**
     * 主运行函数
     * @param string $type
     * @param array $params
     * @return array|bool
     * @throws UserException
     * @throws \yii\base\Exception
     */
    public function run($type = 'total', $params = [])
    {
        $data = [];
        switch ($type) {
            case 'total':
                $data = $this->getTotal($params);
                break;
        }

        return $data;
    }

    /**
     * 获取全部数据
     * @param array $params
     * @return bool
     * @throws UserException
     * @throws \yii\base\Exception
     */
    private function getTotal($params = [])
    {
        $client = new PddClient();
        $request = new DdkGoodsSearchRequest();
        $request->page_size = '10';
        $request->page = $this->pageNum;
        $request->opt_id = $params['opt_id'];
        $request->with_coupon = 'true';
        $request->sort_type = 0;
        $resultData = $client->run($request);
//        print_r($resultData);
//        exit;
        if (empty($resultData) || empty($resultData['goods_search_response']) || empty($resultData['goods_search_response']['goods_list'])) {
            return false;
        }

        foreach ($resultData['goods_search_response']['goods_list'] as $data) {
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
//拼多多商品链接
    private function getAotal($goodid)
    {
        $client = new PddClient();
        $request = new DdkGoodsUrlGenerate();

        $request->p_id = Config::getConfig('DDJB_PID');//'1748819_40169385';
        $request->goods_id_list ='['.$goodid.']';
        $resultData = $client->run($request);

        return $resultData;
    }
//拼多多商品类目
    private function getLotal()
    {
        $client = new PddClient();
        $request = new GoodsOptGetRequest();
        $request->parent_opt_id = 0;
        $resultData = $client->run($request);
//        print_r($resultData);
//        exit;
        return $resultData;
    }

    /**
     * 转化数据为商品模型
     * @param array $data
     * @return Goods
     */
    private function convertGoodsModel($data = [])
    {
        $oldModel = Goods::find()->where(['type' => [31], 'origin_id' => (string)$data['goods_id']])->one();
        if (!empty($oldModel)) {
            $model = $oldModel;
        } else {
            $model = new Goods();
            $model->loadDefaultValues();
        }

        $model->cid = ArrayHelper::getValue($this->map, $data['opt_id'], 0);
        $model->from_cid = $data['opt_id'];
        $model->type = Goods::TYPE_ID['pdd'];
        $model->origin_id = (string)$data['goods_id'];
        $model->from_id = (string)$data['goods_id'];
        $model->title = $data['goods_name'];
        $model->thumb = $data['goods_thumbnail_url'];

        $url=$this->getAotal($data['goods_id']);
//        print_r($url['goods_promotion_url_generate_response']['goods_promotion_url_list'][0]['mobile_short_url']);
//        exit;
//        $model->coupon_link=$url['goods_promotion_url_generate_response']['goods_promotion_url_list'][0]['mobile_short_url'];
        $model->coupon_link=$url['goods_promotion_url_generate_response']['goods_promotion_url_list'][0]['mobile_url'];
        $model->origin_price = !empty($data['min_group_price']) ? bcdiv($data['min_group_price'], 100, 2) : bcdiv($data['min_normal_price'], 100, 2);
        $model->coupon_money = bcdiv($data['coupon_discount'], 100, 2);
        $model->coupon_price = bcsub($model->origin_price, $model->coupon_money, 2);
        $model->coupon_rate = bcdiv($model->coupon_price * 10, $model->origin_price, 1);
        $model->coupon_total = $data['coupon_total_quantity'];
        $model->coupon_remained = $data['coupon_remain_quantity'];
        $model->coupon_start_at = $data['coupon_start_time'];
        $model->coupon_end_at = $data['coupon_end_time'];
        $model->coupon_condition = (string)bcdiv($data['coupon_min_order_amount'], 100, 2);

        $model->commission_rate = bcdiv($data['promotion_rate'], 10, 2);
        $model->commission_money = bcmul($model->coupon_price, bcdiv($model->commission_rate, 100, 2), 2);

        $model->sales_num = $data['sold_quantity'];

        $model->seller_nickname = $data['mall_name'];
        $model->start_time = $data['coupon_start_time'];
        $model->end_time = $data['coupon_end_time'];
//    print_r($model);
//    exit;
        return $model;
    }
}
