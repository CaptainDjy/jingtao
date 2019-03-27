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
use common\models\RobotHdk;
use yii\base\UserException;
use yii\console\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;

class HdkRobot extends Robot
{
    /**
     * 好单库密匙
     * @var string
     */
    private $apiKey;
//    private $pid;
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
        $this->apiKey = Config::getConfig('HDK_API_KEY');
        $this->pid = Config::getConfig('MIAO_PID');
        $this->map = RobotHdk::map();
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
        $response = $client->get('http://v2.api.haodanku.com/itemlist/apikey/'.$this->apiKey.'/nav/3/cid/0/back/10/min_id/'.$this->pageNum)->send();//对象
//        $response=$response->content;//json字符串
        $result=$response->getData();//数组
        $num_id = $result['min_id'];
        /*print_r('http://v2.api.haodanku.com/itemlist/apikey/'.$this->apiKey.'/nav/3/cid/0/back/10/min_id/'.$this->pageNum);
        exit;*/
        foreach ($result['data'] as $data) {
//            $request_url = 'http://v2.api.haodanku.com/ratesurl';
//            $request_data['apikey'] = $this->apiKey;
//            $request_data['itemid'] = $data['itemid'];
//            $request_data['pid'] = $this->pid;
//            $request_data['tb_name'] = Config::getConfig('MIAO_TBNAME');
//            $ch = curl_init();
//            curl_setopt($ch,CURLOPT_URL,$request_url);
//            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//            curl_setopt($ch, CURLOPT_TIMEOUT,10);
//            curl_setopt($ch,CURLOPT_POST,1);
//            curl_setopt($ch,CURLOPT_POSTFIELDS,$request_data);
//            $res = curl_exec($ch);
//            curl_close($ch);
//            $arr=json_decode($res,true);
//
//            if ($arr['code']!=1){
//                Log::add($arr,'error');
//                continue;
////                throw new Exception('好单库接口报错');
//            }
//            $data['coupon_click_url']=$arr['data']['coupon_click_url'];
            $Quan_id=explode('activityId=',$data['couponurl']);
            $data['coupon_click_url']='https://uland.taobao.com/coupon/edetail?activityId=' . $Quan_id[1] . '&pid='.$this->pid.'&itemId=' . $data['itemid'] . '&src=cd_cdll';

            $model = $this->convertGoodsModel($data);

            if ($model->isNewRecord) {
                $this->num++;
            }
            if (!$model->save()) {

                throw new UserException('商品保存失败：' . $model->error);
            }
        }
        //return true;
        return $num_id;
    }

    /**
     * 转化数据为商品模型
     * @param array $data
     * @return Goods
     */
    private function convertGoodsModel($data = [])
    {
        $oldModel = Goods::find()->where(['type' => [11, 12], 'origin_id' => (string)$data['itemid']])->one();
        if (!empty($oldModel)) {
            $model = $oldModel;
        } else {
            $model = new Goods();
            $model->loadDefaultValues();
        }

        $model->cid = ArrayHelper::getValue($this->map, $data['fqcat'], 0);//入库分类
        $model->from_cid = $data['fqcat'];//来源分类
        $model->activity = 1;//活动类型 视频单
        $model->type = $data['shoptype'] == 'B' ? Goods::TYPE_ID['tmall'] : Goods::TYPE_ID['taobao'];
        $model->origin_id = (string)$data['itemid'];
        $model->origin_price = (string)$data['itemprice'];
        $model->from_id = (string)$data['itemid'];
        $model->title = $data['itemtitle'];
        $model->sub_title = $data['itemshorttitle'];
        $model->thumb = $data['itempic'];
        $model->coupon_price = $data['itemendprice'];
        $model->coupon_money = $data['couponmoney'];
        $model->coupon_total = $data['couponnum'];//总量
        $model->coupon_remained = $data['couponnum'];//券剩余
        $model->coupon_received = $data['couponreceive2'];//当天已领券数量
        $model->coupon_start_at = $data['couponstarttime'];//优惠券开始时间
        $model->coupon_end_at = $data['couponendtime'];//优惠券结束时间
        $model->coupon_link=$data['coupon_click_url'];//优惠券连接
//        $model->coupon_link='https://uland.taobao.com/coupon/edetail?activityId=' . $data['Quan_id'] . '&pid='.$this->pid.'&itemId=' . $data['itemid'] . '&src=cd_cdll';
//        $model->coupon_short_link = $data['Quan_m_link'];
        $model->coupon_condition = $data['couponexplain'];//优惠券条件
        $model->commission_money = $data['itemendprice'] * $data['tkrates'] / 100;//佣金(预计可得)
        $model->commission_rate = $data['tkrates'];//佣金比率
        $model->plan_type = empty($data['Jihua_link']) ? 0 : 1;

        $model->sales_num = $data['itemsale'];
        if ($data['itemdesc']) {
            $model->description = $data['itemdesc'];
        } else {
            $model->description = $model->title . '，现价只需要' . $model->origin_price . '元，领券后下单还可优惠' . $model->coupon_money . '元，赶紧抢购吧！';
        }

        // $model->keywords = $data['D_title'];
        // $model->info = $data['D_title'];
        $model->seller_id = $data['userid'];
        $model->seller_nickname = $data['sellernick'];
        $model->start_time = TIMESTAMP;
        $model->end_time = $data['end_time'];
//        print_r($data['end_time']);
//        exit;
//        $model->commission_money = bcmul($model->coupon_price, bcdiv($model->commission_rate, 100, 2), 2);
//        $model->save();
        return $model;
    }

}
