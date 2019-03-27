<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/18
 * Time: 20:51
 */

namespace console\controllers;


use common\components\jd\JdClient;
use common\components\jd\requests\UnionServiceQueryOrderList;
use common\components\pdd\PddClient;
use common\components\pdd\requests\DdkOrderListIncrementGet;
use common\models\Order;
use common\models\Recharge;
use yii\base\Exception;
use yii\console\Controller;

class ModifyOrderController extends Controller
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
     */
    public function actionIndex()


    {
//        $this->PddOrder();
//        $this->JdOrder();
        $this->TbkOrder();
    }

    /**
     * 淘宝订单
     * @throws Exception
     */
    private function TbkOrder()
    {
        $time = date("Y-m-d", time() - 60 * 60 * 24 * 15);
        $sql = "select * from ftxia_taoke_detail2 WHERE  create_time> '{$time}' ";
        $orderList = \Yii::$app->db->createCommand($sql)->queryAll();
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $result = 0;
            foreach ($orderList as $k => $list) {
                $order = Order::findOne(['trade_id' => "{$list['order_sn']}", 'type' => 1, 'product_id' => $list['goods_id']]);
                if (empty($order)) {
                    continue;
                }
                if ($order->order_status != 3 && $list['order_status'] == '订单失效') { //
                    $order->updateAttributes([
                        'order_status' => 3,
                        'estimated_effect' => 0,
                    ]);
                    Recharge::updateAll(['status' => 3, 'price' => 0], ['order_id' => "{$list['order_sn']}", 'goods_id' => $list['goods_id']]);
                } elseif ($order->order_status != 1 && $list['order_status'] == '订单结算') {  //
                    $order->updateAttributes([
                        'order_status' => 1,
                        'estimated_effect' => $list['effect_prediction'],
                        'settlement_at' => strtotime($list['balance_time']),
                    ]);
                    Recharge::updateAll(['status' => 1, 'settlement_at' => strtotime($list['balance_time'])], ['order_id' => "{$list['order_sn']}", 'goods_id' => $list['goods_id']]);
                }
//                $sql = "delete from ftxia_taoke_detail2 WHERE id=" . $list['id'];
//                $result += \Yii::$app->db->createCommand($sql)->execute();
            }
            echo date("m-d H:i", time()) . "MODIFY>>>>TB>>>>SUCCESS>>>>$result" . "\r\n";
            $transaction->commit();
        } catch (Exception $e) {
            echo date("m-d H:i", time()) . "MODIFY>>>>TB>>>>ERROR>>>>" . $e->getMessage() . "\r\n";
            $transaction->rollBack();
            throw  new Exception($e->getMessage());
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
        $request->time = date("Ymd", time() - 60 * 60 * 24 * 18);
        $request->pageIndex = 1;
        $request->pageSize = 500;
        $response = $client->run($request);
        $json = $response['jingdong_UnionService_queryOrderList_responce']['result'];
        $arr = json_decode($json, true);
        if ($arr['hasMore'] != false) {
            $request->pageIndex += 1;
            $response = $client->run($request);
            $json = $response['jingdong_UnionService_queryOrderList_responce']['result'];
            $arr = json_decode($json, true);
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!empty($arr['data'])) {
                foreach ($arr['data'] as $k => $v) {
                    $order = Order::findOne(['trade_id' => $v['orderId'], 'type' => 2, 'product_id' => $v['skuList'][0]['skuId']]);
                    if (empty($order)) {
                        continue;
                    }
                    if (in_array($v['validCode'], array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14), true)) {
                        $order->updateAttributes([
                            'order_status' => 3,
                            'estimated_effect' => 0,
                        ]);
                        Recharge::updateAll(['status' => 3, 'price' => 0], ['order_id' => "{$v['orderId']}", 'goods_id' => $v['skuList'][0]['skuId']]);
                    } elseif ($v['validCode'] == 18) {
                        $order->updateAttributes([
                            'order_status' => 1,
                            'settlement_at' => $v['payMonth'],
                        ]);
                        Recharge::updateAll(['status' => 1, 'settlement_at' => strtotime($v['payMonth'])], ['order_id' => "{$v['orderId']}", 'goods_id' => $v['skuList'][0]['skuId']]);
                    }

                }
                $transaction->commit();
                echo date("m-d H:i", time()) . "MODIFY>>>>JD>>>>SUCCESS\r\n";
            }
        } catch (Exception $e) {
            echo date("m-d H:i", time()) . "MODIFY>>>>JD>>>>ERROR>>>>{$e->getMessage()}\r\n";
            $transaction->rollBack();
        }
    }

    /**
     * 拼多多
     * @throws Exception
     */
    private function PddOrder()
    {
        $client = new PddClient();
        $request = new DdkOrderListIncrementGet();
        $request->start_update_time = time() - 60 * 60 * 24 * 18;
        $request->end_update_time = time();
        $response = $client->run($request);

        $arr = $response['order_list_get_response']['order_list'];

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!empty($arr)) {
                foreach ($arr as $k => $v) {
                    $order = Order::findOne(['trade_id' => $v['order_sn'], 'type' => 3, 'product_id' => $v['goods_id']]);
                    if (empty($order)) {
                        continue;
                    }
                    if (in_array($v['order_status'], [-1, 4, 10], true)) {
                        $order->updateAttributes([
                            'order_status' => 3,
                            'estimated_effect' => 0,
                        ]);
                        Recharge::updateAll(['status' => 3, 'price' => 0], ['order_id' => "{$v['order_sn']}", 'goods_id' => $v['goods_id']]);
                    } elseif ($v['order_status'] == 5) {
                        $order->updateAttributes([
                            'order_status' => 1,
                        ]);
                        Recharge::updateAll(['status' => $v['order_status'], 'settlement_at' => strtotime($v['order_modify_at'])], ['order_id' => "{$v['order_sn']}", 'goods_id' => $v['goods_id']]);
                    } elseif ($order->order_status != $v['order_status'] && $v['order_status'] == 0) {
                        $order->updateAttributes([
                            'order_status' => $v['order_status'],
                            'rebate_status' => 1,
                            'estimated_effect' => bcdiv($v['promotion_amount'], 100, 2),
                            'settlement_at' => $v['order_modify_at'],
                        ]);
                        Recharge::updateAll(['status' => $v['order_status'],], ['order_id' => "{$v['order_sn']}", 'goods_id' => $v['goods_id']]);
                    } elseif ($order->order_status != $v['order_status'] && $v['order_status'] == 1) {
                        $order->updateAttributes([
                            'order_status' => $v['order_status'],
                            'rebate_status' => 1,
                            'estimated_effect' => bcdiv($v['promotion_amount'], 100, 2),
                            'settlement_at' => $v['order_modify_at'],
                        ]);
                        Recharge::updateAll(['status' => $v['order_status'],], ['order_id' => "{$v['order_sn']}", 'goods_id' => $v['goods_id']]);
                    } elseif ($order->order_status != $v['order_status'] && $v['order_status'] == 2) {
                        $order->updateAttributes([
                            'order_status' => $v['order_status'],
                            'rebate_status' => 1,
                            'estimated_effect' => bcdiv($v['promotion_amount'], 100, 2),
                            'settlement_at' => $v['order_modify_at'],
                        ]);
                        Recharge::updateAll(['status' => $v['order_status'],], ['order_id' => "{$v['order_sn']}", 'goods_id' => $v['goods_id']]);
                    } elseif ($order->order_status != $v['order_status'] && $v['order_status'] == 3) {
                        $order->updateAttributes([
                            'order_status' => $v['order_status'],
                            'rebate_status' => 1,
                            'estimated_effect' => bcdiv($v['promotion_amount'], 100, 2),
                            'settlement_at' => $v['order_modify_at'],
                        ]);
                        Recharge::updateAll(['status' => $v['order_status'],], ['order_id' => "{$v['order_sn']}", 'goods_id' => $v['goods_id']]);
                    }
                }
                echo date("m-d H:i", time()) . "MODIFY>>>>PDD>>>>SUCCESS>>>>kong\r\n";
            }
            $transaction->commit();
        } catch (Exception $e) {
            echo date("m-d H:i", time()) . "MODIFY>>>>PDD>>>>ERROR>>>>{$e->getMessage()}\r\n";
            $transaction->rollBack();
        }
    }

}