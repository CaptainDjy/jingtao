<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\robots;

use common\models\Config;
use common\models\Goods;
use common\models\RobotQtk;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

class QtkRobot extends Robot
{
    /**
     * 轻淘客密匙
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
        $this->apiKey = Config::getConfig('QTK_API_KEY');
        $this->pid=Config::getConfig('MIAO_PID');
        $this->map = RobotQtk::map();
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
        $response = $client->get('http://openapi.qingtaoke.com/qingsoulist?sort=1&app_key=' . $this->apiKey . '&v=1.0&cat=0&min_price=1&max_price=100&new=0&is_ju=0&is_tqg=0&is_ali=0',
            [
                'page' => $this->pageNum,
                'page_size' => $this->pageSize
            ]
        )->send();//对象
//        $response=$response->content;//json字符串
        $result = $response->getData();//数组
//        print_r($result);
//        exit;
        if (empty($result['data'])) {
            throw new UserException('商品保存失败');
        } else {

            foreach ($result['data']['list'] as $data) {

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
    }

    /**
     * 转化数据为商品模型
     * @param array $data
     * @return Goods
     */
    private function convertGoodsModel($data = [])
    {
        $oldModel = Goods::find()->where(['type' => [11, 12], 'origin_id' => (string)$data['goods_id']])->one();
        if (!empty($oldModel)) {
            $model = $oldModel;
        } else {
            $model = new Goods();
            $model->loadDefaultValues();
        }

        $model->cid = ArrayHelper::getValue($this->map, $data['goods_cat'], 0);//入库分类
        $model->from_cid = $data['goods_cat'];//来源分类
        $model->type = $data['is_tmall'] == '1' ? Goods::TYPE_ID['tmall'] : Goods::TYPE_ID['taobao'];//商品类型
        $model->origin_id = (string)$data['goods_id'];//原始ID
        $model->origin_price = (string)$data['goods_price'];//原价
        $model->from_id = (string)$data['goods_id'];//ID
        $model->title = $data['goods_title'];//标题
        $model->sub_title = $data['goods_short_title'];//短标题
        $http=substr($data['goods_pic'],0,4);
        if ($http=='http'){
            $model->thumb = $data['goods_pic'];//商品图片
        }else{
            $model->thumb = 'https:'.$data['goods_pic'];//商品图片
        }

        $model->coupon_price = $data['goods_price']-$data['coupon_price'];//优惠券后价格
//        $model->coupon_id = $data['Quan_id'];
        $model->coupon_money = $data['coupon_price'];//优惠券金额
//        $model->coupon_rate = bcdiv($model->coupon_price * 10, $model->origin_price, 1);
        $model->coupon_total = $data['coupon_number']+$data['coupon_over'];//总量
        $model->coupon_id=$data['coupon_id'];
        $model->coupon_remained = $data['coupon_number'];//券剩余
        $model->coupon_received = $data['coupon_over'];//已领券数量
        $model->coupon_start_at = strtotime($data['coupon_start_time']);//优惠券结束时间
        $model->coupon_end_at = strtotime($data['coupon_end_time']);//优惠券结束时间
//        print_r($data['coupon_id']);
//        exit;

        $model->coupon_link = 'https://uland.taobao.com/coupon/edetail?activityId=' . $data['coupon_id'] . '&pid='.$this->pid.'&itemId=' . $data['goods_id'] . '&src=cd_cdll';//$data['coupon_click_url'];
//        $model->coupon_link='https://uland.taobao.com/quan/detail?sellerId='.$data['seller_id'].'&activityId='.$data['coupon_id'];//优惠券连接
//        print_r($model->coupon_link);
//        exit;
//        $model->coupon_short_link = $data['Quan_m_link'];
        $model->coupon_condition = $data['coupon_condition'];//优惠券条件
        $model->commission_money = $data['commission'];
        $model->is_ju=$data['is_ju'];
        $model->is_tqg=$data['is_tqg'];
//        $model->commission_money = $data['itemendprice'] * ($data['tkrates'] / 100);//佣金(预计可得)
//        $model->commission_rate = $data['tkrates'];//佣金比率
//        $model->commission_rate_plan = $data['tkurl'];//佣金计划
//        $model->commission_rate_queqiao = $data['Commission_queqiao'];
//        $model->plan_link = $data['Jihua_link'];
//        $model->plan_status = $data['Jihua_shenhe'];
        $model->plan_type = empty($data['Jihua_link']) ? 0 : 1;

        $model->sales_num = $data['goods_sales'];
        if ($data['goods_introduce']) {
            $model->description = $data['goods_introduce'];
        } else {
            $model->description = $model->title . '，现价只需要' . $model->origin_price . '元，领券后下单还可优惠' . $model->coupon_money . '元，赶紧抢购吧！';
        }

        // $model->keywords = $data['D_title'];
        // $model->info = $data['D_title'];
        $model->seller_id = $data['seller_id'];
//        $model->seller_nickname = $data['sellernick'];
        $model->start_time = TIMESTAMP;
        $model->end_time = strtotime($data['coupon_end_time']);
//        print_r($data);
//        exit;
//        $model->commission_money = bcmul($model->coupon_price, bcdiv($model->commission_rate, 100, 2), 2);
//        $model->save();
        return $model;
    }
}
