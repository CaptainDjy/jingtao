<?php
/**
 * @author 河南邦耀网络科技
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * @link http://www.hnbangyao.com
 */

namespace console\components;

use common\components\jd\JdClient;
use common\components\jd\requests\UnionServiceQueryOrderList;
use common\components\pdd\PddClient;
use common\components\pdd\requests\DdkOrderListIncrementGet;
use common\models\Order;
use common\models\User;
use yii\base\BaseObject;
use yii\base\Exception;

/**
 * 订单同步
 * Class SyncOrder
 * @package console\components
 */
class SyncOrder extends BaseObject
{
    const ORDER_STATUS = [
        '订单结算' => 1,
        '订单付款' => 2,
        '订单失效' => 3,
        '订单成功' => 4,
    ];

    const ORDER_TYPE = [
        '天猫' => 1,
        '淘宝' => 2,
        '聚划算' => 3,
    ];

    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function run()
    {
        $this->PddOrder();
        $this->JdOrder();
        $this->TbkOrder();
    }

    /**
     * 淘宝订单
     * @throws Exception
     */
    private function TbkOrder()
    {
        $time = date("Y-m-d", time());
        $sql = "select * from ftxia_taoke_detail2 WHERE  create_time > '{$time}' ";
        $orderList = \Yii::$app->db->createCommand($sql)->queryAll();
        $data = [];
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($orderList as $k => $list) {
                $user = User::find()->where("alimm_pid like  '%{$list['adv_id']}'")->asArray()->one();
                if (empty($user)) {
                    continue;
                }
                $order = Order::findOne(['trade_id' => $list['order_sn']]);
                if (!empty($order)) {
                    continue;
                }
                $data[$k]['uid'] = $user['uid'];
                $data[$k]['trade_id'] = $list['order_sn'];
                $data[$k]['product_id'] = $list['goods_id'];
                $data[$k]['type'] = 1;
                $data[$k]['pid'] = $list['adv_id'];
                $data[$k]['pid_name'] = $list['adv_name'];
                $data[$k]['wangwang'] = $list['wangwang'];
                $data[$k]['shop'] = $list['shop'];
                $data[$k]['product_num'] = $list['goods_number'];
                $data[$k]['product_price'] = $list['goods_price'];
                $order_status = 0;
                if ($list['order_status'] == '订单结算') {
                    $order_status = 1;
                } elseif ($list['order_status'] == '订单付款') {
                    $order_status = 2;
                } elseif ($list['order_status'] == '订单失效') {
                    $order_status = 3;
                } elseif ($list['order_status'] == '订单成功') {
                    $order_status = 4;
                }
                $data[$k]['order_status'] = $order_status;
                $data[$k]['rebate_status'] = 1;
                if ($list['order_type'] == '天猫') {
                    $order_type = 1;
                } elseif ($list['order_type'] == '淘宝') {
                    $order_type = 2;
                } else {
                    $order_type = 3;
                }
                $data[$k]['order_type'] = $order_type;
                $data[$k]['divided_ratio'] = $list['divided_ratio'];
                $data[$k]['payment_price'] = $list['order_amount'];
                $data[$k]['estimated_effect'] = $list['effect_prediction'];
                $data[$k]['settlement_price'] = $list['balance_amount'];
                $data[$k]['estimated_revenue'] = $list['estimated_revenue'];
                $data[$k]['commission_rate'] = $list['commission_ratio'];
                $data[$k]['commission_price'] = $list['commission_amount'];
                if ($list['order_platform'] == '无线') {
                    $order_platform = 1;
                } else {
                    $order_platform = 2;
                }
                $data[$k]['dealing_platform'] = $order_platform;
                $data[$k]['category_name'] = $list['category'];
                $data[$k]['source_media'] = $list['media_id'];
                if (strtotime($list['balance_time']) < 0) {
                    $settlement_at = 0;
                } else {
                    $settlement_at = $list['balance_time'];
                }
                $data[$k]['settlement_at'] = strtotime($settlement_at);
                $data[$k]['title'] = $list['goods_name'];
                $data[$k]['created_at'] = strtotime($list['create_time']);
                $data[$k]['updated_at'] = time();

                $sql = "delete from ftxia_taoke_detail2 WHERE id=" . $list['id'];
                \Yii::$app->db->createCommand($sql)->execute();
            }
            if (!empty($data)) {
                $result = Order::addOrder($data);
                if (!$result) {
                    throw new \yii\db\Exception('淘宝订单数据更新发生错误');
                }
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 京东返利订单抓取
     * 查询业绩订单
     * @throws Exception
     */
    private function JdOrder()
    {
        $client = new JdClient();
        $request = new UnionServiceQueryOrderList();
        $request->unionId = 1000603922;
        $request->time = date("Ymd", time() - 60 * 60);
        $request->pageIndex = 1;
        $request->pageSize = 10;
        $response = $client->run($request);
        $json = $response['jingdong_UnionService_queryOrderList_responce']['result'];
        $arr = json_decode($json, true);
        if ($arr['hasMore'] != false) {
            $request->pageIndex += 1;
            $response = $client->run($request);
            $json = $response['jingdong_UnionService_queryOrderList_responce']['result'];
            $arr = json_decode($json, true);
        }

        $key = ['uid', 'type', 'product_id', 'pid', 'trade_id', 'product_num', 'product_price', 'order_status', 'rebate_status', 'order_type', 'divided_ratio', 'payment_price', 'commission_rate', 'estimated_effect', 'settlement_at', 'created_at', 'updated_at',
        ];
        $data = [];
        if (!empty($arr['data'])) {
            foreach ($arr['data'] as $k => $v) {
                $list = Order::findOne(['trade_id' => $v['orderId']]);
                if (!empty($list)) {
                    continue;
                }
                $user = User::findOne(['jd_pid' => $v['skuList'][0]['subUnionId']]);
                if (empty($user)) {
                    continue;
                }
                $data[$k][] = $user->uid; //uid
                $data[$k][] = 2; //uid
                $data[$k][] = $v['skuList'][0]['skuId']; //uid
                $data[$k][] = $v['skuList'][0]['subUnionId'];
                $data[$k][] = $v['orderId'];
                $data[$k][] = $v['skuList'][0]['skuNum'];
                $data[$k][] = $v['skuList'][0]['price'];
                $data[$k][] = $v['validCode'];
                $data[$k][] = 1;
                $data[$k][] = 0;
                $data[$k][] = $v['skuList'][0]['subSideRate'];
                $data[$k][] = $v['skuList'][0]['price'];
                $data[$k][] = $v['skuList'][0]['finalRate'];
                $data[$k][] = $v['skuList'][0]['estimateFee'];
                $data[$k][] = $v['payMonth'];
                $data[$k][] = time();
                $data[$k][] = time();
            }
            if (!empty($data)) {
                $result = Order::addOrders($key, $data);
                if (!$result) {
                    throw new \yii\db\Exception('京东订单数据更新发生错误');
                }
            }
        }
    }

    /**
     * 拼多多
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function PddOrder()
    {
        $client = new PddClient();
        $request = new DdkOrderListIncrementGet();
        $request->start_update_time = time() - 60;
        $request->end_update_time = time();
        $response = $client->run($request);

        $arr = $response['order_list_get_response']['order_list'];
        $key = ['uid', 'type', 'product_id', 'pid', 'trade_id', 'product_num', 'product_price', 'order_status', 'rebate_status', 'order_type', 'divided_ratio', 'payment_price', 'commission_rate', 'estimated_effect', 'settlement_at', 'created_at', 'updated_at', 'picUrl', 'title',
        ];
        $data = [];
        if (!empty($arr)) {
            foreach ($arr as $k => $v) {
                $list = Order::findOne(['trade_id' => $v['order_sn']]);
                if (!empty($list)) {
                    continue;
                }
                $user = User::findOne(['pdd_pid' => $v['p_id']]);
                if (empty($user)) {
                    continue;
                }
                $data[$k][] = $user->uid; //uid
                $data[$k][] = 3; //uid
                $data[$k][] = $v['goods_id'];
                $data[$k][] = $v['p_id'];
                $data[$k][] = $v['order_sn'];
                $data[$k][] = $v['goods_quantity'];
                $data[$k][] = bcdiv($v['goods_price'], 100, 2);
                $data[$k][] = $v['order_status'];
                $data[$k][] = 1;
                $data[$k][] = $v['type'];
                $data[$k][] = $v['promotion_rate'];
                $data[$k][] = bcdiv($v['order_amount'], 100, 2);
                $data[$k][] = bcdiv($v['promotion_rate'], 1000, 2);
                $data[$k][] = bcdiv($v['promotion_amount'], 100, 2);
                $data[$k][] = $v['order_receive_time'];
                $data[$k][] = time();
                $data[$k][] = time();
                $data[$k][] = $v['goods_thumbnail_url'];
                $data[$k][] = $v['goods_name'];
            }
            if (!empty($data)) {
                $result = Order::addOrders($key, $data);
                if (!$result) {
                    throw new \yii\db\Exception('拼多多订单数据更新发生错误');
                }
            }
        }
    }
}
