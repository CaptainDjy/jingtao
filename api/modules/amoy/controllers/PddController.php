<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/7
 * Time: 10:26
 */

namespace api\modules\amoy\controllers;
use common\models\Goods;
use common\components\pdd\PddClient;
use common\models\config;
use common\components\pdd\requests\DdkGoodsUrlGenerate;
class PddController extends ControllerBase
{
    /**
     * 多多进宝商品详情查询
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGoodsDetail()
    {
        $goods_id = \Yii::$app->request->post('goodsid');
        $data = [
            'type' => 'pdd.ddk.goods.detail',
            'goods_id_list' => '['.$goods_id.']',//商品ID
        ];

        $result = $this->getPddResult($data);
        $xq=Goods::find()->where(['origin_id'=>$goods_id])->asArray()->one();
        if (empty($result['error_response'])) {
            $arr = $result['goods_detail_response']['goods_details'][0];
            unset($arr['cat_ids']);
            unset($arr['opt_ids']);
            if (!empty($xq)) {
                $xq['imags'] = $arr['goods_gallery_urls'];
            }
            return $this->responseJson(200, $xq, '查询数据成功');
        } else {
            return $this->responseJson(101, '', $result['error_response']['error_msg']);
        }
    }

    /**
     * 多多进宝商品查询
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGoodsSearch($result=null)
    {
        $keyword = \Yii::$app->request->post('keyword');
        $page = $page = empty(\Yii::$app->request->post('page')) ? 1 : \Yii::$app->request->post('page');
        $data = [
            'type' => 'pdd.ddk.goods.search',
            'sort_type' => 0,
            'with_coupon' => 'true',
            'keyword' => $keyword,
            'page' => $page,
            'page_size' => '10'
        ];
        $result = $this->getPddResult($data);

        $aaa= array_map(function ($res){
            $res['mobile_short_url'] =$this->getAotal($res['goods_id']);
            $res['min_group_price']=$res['min_group_price']/100;//最小拼团原价 分转元
            $res['min_normal_price']=$res['min_normal_price']/100;//最小单买原价 分转元
            $res['coupon_discount']=$res['coupon_discount']/100;//优惠券价格  分转元
            $res['coupon_price']=$res['min_group_price']-$res['coupon_discount'];
            $res['commission_money']=$res['coupon_price']*($res['promotion_rate']/1000);
            return $res;
        },$result['goods_search_response']['goods_list']);

        if (!empty($aaa)) {
            return $this->responseJson(200, $aaa, '查询数据成功');
        } else {
            return $this->responseJson(101, '', '未找到结果');
        }
    }

//拼多多商品链接
    private function getAotal($goodid)
    {
        $client = new PddClient();
        $request = new DdkGoodsUrlGenerate();
        $request->p_id = Config::getConfig('DDJB_PID');//'1748819_40169385';
        $request->goods_id_list = '['.$goodid.']';
        $resultData = $client->run($request);

//        return $resultData['goods_promotion_url_generate_response']['goods_promotion_url_list'][0]['mobile_short_url'];
        return $resultData['goods_promotion_url_generate_response']['goods_promotion_url_list'][0]['mobile_url'];
    }

    /**
     * 查询已经生成的推广位信息
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryPid()
    {
        $data = [
            'type' => 'pdd.ddk.goods.pid.query',
        ];
        $result = $this->getPddResult($data);
        if (empty($result['error_response'])) {
            $arr = $result['p_id_query_response'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['error_response']['error_msg']);
        }
    }

    /**
     * 创建多多进宝推广位
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGeneratePid()
    {
        $data = [
            'type' => 'pdd.ddk.goods.pid.generate',
            'number' => '1',
            'p_id_name_list' => '测试',
        ];
        $result = $this->getPddResult($data);
        if (empty($result['error_response'])) {
            $arr = $result['goods_promotion_url_generate_response']['goods_promotion_url_list'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['error_response']['error_msg']);
        }
    }

    /**
     * 用时间段查询推广订单接口
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetList()
    {
        $data = [
            'type' => 'pdd.ddk.order.list.range.get',
            'start_time' => '10',
            'end_time' => '10',
        ];
        $result = $this->getPddResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['order_list_get_response'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['error_response']['error_msg']);
        }
    }

    /**
     * 最后更新时间段增量同步推广订单信息
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetIncrement()
    {
        $data = [
            'type' => 'pdd.ddk.order.list.increment.get',
            'start_update_time' => '10',
            'end_update_time' => '10',
        ];
        $result = $this->getPddResult($data);
        if (empty($result['error_response'])) {
            $arr = $result['order_list_get_response'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['error_response']['error_msg']);
        }
    }

    /**
     * 生成活动推广链接（分享红包赚佣金）
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGenerateUrl()
    {
        $data = [
            'type' => 'pdd.ddk.act.prom.url.generate',
            'url_type' => '10',
            'p_id_list' => '10',
        ];
        $result = $this->getPddResult($data);
        if (empty($result['error_response'])) {
            $arr = $result['act_promotion_url_generate_response'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['error_response']['error_msg']);
        }
    }

    /**
     * 生成商城推广链接
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGeneratePromUrl()
    {
        $data = [
            'type' => 'pdd.ddk.cms.prom.url.generate',
            'generate_short_url' => 'true',
            'p_id_list' => '1001134_11063748',
            'generate_mobile' => 'true',
            'multi_group' => 'true',
        ];
        $result = $this->getPddResult($data);
        if (empty($result['error_response'])) {
            $arr = $result['act_promotion_url_generate_response'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['error_response']['error_msg']);
        }
    }
}