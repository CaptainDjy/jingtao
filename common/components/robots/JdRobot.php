<?php
/**
 * @author pine
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * createtime: 2018/05/26 17:00
 */

namespace common\components\robots;

use common\components\jd\JdClient;
use common\components\jd\requests\Query;
use common\components\jd\requests\UnionSearchQueryCouponGoods;
use common\models\Config;
use common\models\Goods;
use common\models\RobotJd;
use yii\base\Exception;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class JdRobot extends Robot
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

    private $apiKey;

    public function init()
    {
        parent::init();
//        $this->apiKey = Config::getConfig('DATAOKE_API_KEY');
        $this->apiKey = Config::getConfig('JD_APP_KEY');
        $this->map = RobotJd::map();
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
        $client = new JdClient();
        $request = new Query();
        $request->goodsReq = [
            'eliteId'=>3,//频道
            'pageIndex'=>1,//$this->pageNum,//页数
            'pageSize'=>20,//每页数量
            'sortName'=>'price',//排序字段
            'sort'=>'desc'//排序方式
        ];
//        print_r($request);exit;
        $resultData = $client->run($request);
print_r($resultData);
exit;
        if (empty($resultData) || empty($resultData['jd_union_open_goods_jingfen_query_response']) || empty($resultData['jd_union_open_goods_jingfen_query_response']['result'])) {
            return false;
        }
        $tmp = json_decode($resultData['jd_union_open_goods_jingfen_query_response']['result'], true);

        try{
            foreach ($tmp['data'] as $data) {
                $model = $this->convertGoodsModel($data);
                if ($model->isNewRecord) {
                    $this->num++;
                }
                if (!$model->save()) {
                    throw new UserException('商品保存失败：' . $model->error);
                }
            }
        }catch (\Exception $e){
            echo '$tmp的值：';
            var_dump($tmp);
            die('代码：'.$e->getFile().' 第 '.$e->getLine().' 行出错：'.$e->getMessage());
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
        $oldModel = Goods::find()->where(['type' => [21], 'origin_id' => (string)$data['skuId']])->one();
        if (!empty($oldModel)) {
            $model = $oldModel;
        } else {
            $model = new Goods();
            $model->loadDefaultValues();
        }

        $model->from_cid = $data['categoryInfo']['cid1'];//来源分类
        $model->cid = ArrayHelper::getValue($this->map, $data['categoryInfo']['cid1'] , 0);//入库分类
        $model->type = Goods::TYPE_ID['jd'];//商品类型
        $model->origin_id = (string)$data['skuId'];//商品ID
        $model->from_id = (string)$data['skuId'];//商品ID
        $model->title = $data['skuName'];//标题
        $model->thumb = $data['imageInfo']['imageList'][0]['url'];//商品图片
        $model->origin_price = $data['priceInfo']['price'];//原价

        if(isset($data['couponInfo']['couponList'][0]['discount'])){
            $model->coupon_price = $model->origin_price - $data['couponInfo']['couponList'][0]['discount'];//券后价
            $model->coupon_money = $data['couponInfo']['couponList'][0]['discount'];//优惠券金额
            $model->coupon_link = $data['couponInfo']['couponList'][0]['link'];//优惠券地址
            $model->coupon_start_at = bcdiv($data['couponInfo']['couponList'][0]['getStartTime'], 1000, 0);//券开始时间
            $model->coupon_end_at = bcdiv($data['couponInfo']['couponList'][0]['getEndTime'], 1000, 0);//券结束时间
            $model->coupon_condition = (string)$data['couponInfo']['couponList'][0]['quota'];//优惠券条件
        }else{
            $model->coupon_price = $data['priceInfo']['price'];//券后价
            $model->coupon_money = 0;//优惠券金额
            $model->coupon_link = $data['materialUrl'];//商品地址
            $model->coupon_start_at =time(); //券开始时间
            $model->coupon_end_at = time()+604800;//券结束时间
        }

        $model->commission_rate = $data['commissionInfo']['commissionShare'];//佣金比率
        $model->seller_id = (string)$data['shopInfo']['shopId'];//卖家ID
        $model->commission_money = $data['commissionInfo']['commission'];//佣金
        $model->start_time = $model->coupon_start_at;//开始时间
        $model->end_time = $model->coupon_end_at ?: TIMESTAMP + 604800;//无结束时间默认一周

        return $model;
    }

}