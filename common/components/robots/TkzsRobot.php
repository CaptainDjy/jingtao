<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\robots;

use common\models\Config;
use common\models\Goods;
use common\models\RobotTkzs;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

class TkzsRobot extends Robot
{
    /**
     * 淘客助手密匙
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
        $this->apiKey = Config::getConfig('TKZS_API_KEY');
        $this->pid=Config::getConfig('MIAO_PID');
        $this->map = RobotTkzs::map();
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
        $response = $client->get('http://api.taokezhushou.com/api/v1/all?app_key='.$this->apiKey,
            [
                'page'=>$this->pageNum
            ])->send();//对象
        $result = $response->getData();//数组

        if (empty($result['data'])) {
            throw new UserException('商品保存失败');
        } else {

            foreach ($result['data'] as $data) {
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

        $model->cid = ArrayHelper::getValue($this->map, $data['goods_cate_id'], 0);//入库分类
        $model->from_cid = $data['goods_cate_id'];//来源分类
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
        $model->coupon_price = $data['goods_price']-$data['coupon_amount'];//优惠券后价格
        $model->coupon_money = $data['coupon_amount'];//优惠券金额
        $model->coupon_id=$data['coupon_id'];
        $model->coupon_start_at = strtotime($data['coupon_start_time']);//优惠券开始时间
        $model->coupon_end_at = strtotime($data['coupon_end_time']);//优惠券结束时间

        $model->coupon_link = 'https://uland.taobao.com/coupon/edetail?activityId=' . $data['coupon_id'] . '&pid='.$this->pid.'&itemId=' . $data['goods_id'] . '&src=cd_cdll';//$data['coupon_click_url'];

        $model->coupon_condition = $data['coupon_apply_amount'];//优惠券条件
        $model->commission_money = $model->coupon_price * $data['commission_rate']/100;//佣金
        $model->is_ju=$data['juhuasuan'];
        $model->is_tqg=$data['taoqianggou'];
        $model->plan_type = empty($data['Jihua_link']) ? 0 : 1;

        $model->sales_num = $data['goods_sale_num'];//商品销量

        //商品描述
        if (!empty($data['goods_introduce'])) {
            $model->description = $data['goods_introduce'];
        } else {
            $model->description = $model->title . '，现价只需要' . $model->origin_price . '元，领券后下单还可优惠' . $model->coupon_money . '元，赶紧抢购吧！';
        }

        $model->seller_id = $data['seller_id'];//卖家ID/店铺ID
        $model->start_time = TIMESTAMP;
        $model->end_time = strtotime($data['coupon_end_time']);

        return $model;
    }
}
