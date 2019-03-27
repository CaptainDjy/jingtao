<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\robots;

use common\models\Config;
use common\models\Goods;
use common\models\RobotMjd;
use common\models\RobotTkzs;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

class MjdRobot extends Robot
{
    /**
     * 京东密匙
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
        $this->apiKey = Config::getConfig('MIAO_APKEY');
//        $this->pid=Config::getConfig('MIAO_PID');
        $this->pageSize = 100;
        $this->map = RobotMjd::map();
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
                foreach (RobotMjd::FROM_CATEGORY as $k => $v) {
                    $data = $this->getTotal($k);
                }
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
    private function getTotal($k)
    {
        print_r('========================================================='.PHP_EOL);
        print_r('cid:'.$k.PHP_EOL);
        $totalPage = 10;
        $client = new Client();
        for($this->pageNum=1;$this->pageNum<=$totalPage;$this->pageNum++){
            $post = [
                'cid1' => $k,
                'isCoupon' => 1,
                'pageIndex' => $this->pageNum,
                'pageSize' => $this->pageSize,
                'apkey' => $this->apiKey,
            ];
            $response = $client->get('https://api.open.21ds.cn/jd_api_v1/getjdunionitems?', $post)->send();//对象
            $result = $response->getData();//数组
            try{
                $totalCount = $result['data']['totalCount'];    //  总数
                $totalPage = intval($totalCount / $this->pageSize);  //  总页数
            }catch (\Exception $e){
                print_r($response.PHP_EOL);
                print_r($result);
                return true;
            }
            echo '哪一步停的';
            print_r('page:'.$this->pageNum.',');
            print_r('totalPage:'.$totalPage.PHP_EOL.',');


            if (empty($result['data']) || $result['code'] == -1) {
                echo 'null'.PHP_EOL;
                print_r($result);
//                throw new UserException('商品保存失败');
            } else {

                foreach ($result['data']['lists'] as $data) {
                    $model = $this->convertGoodsModel($data);
                    if ($model->isNewRecord) {
                        $this->num++;
                    }
                    if (!$model->save()) {

                        throw new UserException('商品保存失败：' . $model->error);
                    }
                }
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
//        print_r($data);
//        exit;
        $oldModel = Goods::find()->where(['type' => [21], 'origin_id' => (string)$data['skuId']])->one();
        if (!empty($oldModel)) {
            $model = $oldModel;
        } else {
            $model = new Goods();
            $model->loadDefaultValues();
        }

        $model->cid = ArrayHelper::getValue($this->map, $data['categoryInfo']['cid1'], 0);//入库分类
        $model->from_cid = $data['categoryInfo']['cid1'];//来源分类
        $model->type = 21;//商品类型
        $model->origin_id = (string)$data['skuId'];//原始ID
        $model->origin_price = (string)$data['priceInfo']['price'];//原价
        $model->from_id = (string)$data['skuId'];//ID
        $model->title = $data['skuName'];//标题
        $model->sub_title = $data['skuName'];//短标题
        $model->thumb = $data['imageInfo']['imageList'][0]['url'];//商品图片
        if (empty($data['couponInfo']['couponList'])){
            $model->coupon_price=(string)$data['priceInfo']['price'];//原价
            $model->coupon_money = 0;//优惠券金额

            $http=substr($data['materialUrl'],0,4);
            if ($http=='http'){
                $model->coupon_link = $data['materialUrl'];//商品地址
            }else{
                $model->coupon_link = 'https://'.$data['materialUrl'];//商品地址
            }
//           $model->coupon_link=$data['materialUrl'];//商品地址
            $model->coupon_condition = '优惠';
            $model->start_time = TIMESTAMP;
            $model->end_time =TIMESTAMP ;
        }else{
            $model->coupon_price = $data['priceInfo']['price']-$data['couponInfo']['couponList'][0]['discount'];//优惠券后价格
            $model->coupon_money = $data['couponInfo']['couponList'][0]['discount'];//优惠券金额
            if (isset($data['couponInfo']['couponList'][0]['useStartTime'])){
                $model->coupon_start_at = $data['couponInfo']['couponList'][0]['useStartTime'];//优惠券开始时间
                $model->coupon_end_at = $data['couponInfo']['couponList'][0]['useEndTime'];//优惠券结束时间
                $model->start_time = TIMESTAMP;
                $model->end_time = $data['couponInfo']['couponList'][0]['useEndTime'];
            }

            $model->coupon_link = $data['couponInfo']['couponList'][0]['link'];//优惠券链接
            $model->coupon_condition = '优惠'.$data['couponInfo']['couponList'][0]['quota'];//优惠券条件

        }

        $model->commission_money = $data['commissionInfo']['commission'];//佣金
        $model->plan_type = empty($data['Jihua_link']) ? 0 : 1;
        $model->sales_num = $data['inOrderCount30Days'];//商品销量---30天引单数量

        //商品描述
        if (!empty($data['goods_introduce'])) {
            $model->description = $data['goods_introduce'];
        } else {
            $model->description = $model->title . '，现价只需要' . $model->origin_price . '元，领券后下单还可优惠' . $model->coupon_money . '元，赶紧抢购吧！';
        }

        $model->seller_id = $data['shopInfo']['shopId'];//卖家ID/店铺ID
        return $model;
    }
}
