<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 20:51
 */

namespace console\components;


use common\components\jd\JdClient;
use common\components\jd\requests\UnionServiceQueryOrderList;
use common\components\pdd\PddClient;
use common\components\pdd\requests\DdkOrderListIncrementGet;
use common\models\Order;
use common\models\Recharge;
use yii\base\BaseObject;
use yii\base\Exception;

class SyncModifyOrder extends BaseObject
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
        $sql = "select * from ftxia_taoke_detail2 WHERE  create_time> '{$time}' ";
        $orderList = \Yii::$app->db->createCommand($sql)->queryAll();
        $data = [];
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($orderList as $k => $list) {
                $order = Order::findOne(['trade_id' => $list['order_sn']]);
                if (empty($order)) {
                    continue;
                }
                if ($list['order_status'] == '订单失效') {
                    $order->updateAttributes([
                        'order_status' => 3,
                        'estimated_effect' => 0,
                    ]);
                    Recharge::deleteAll(['order_id' => $list['order_sn']]);
                } elseif ($list['order_status'] == '订单结算') {
                    $order->updateAttributes([
                        'order_status' => 1,
                    ]);
                }
                $sql = "delete from ftxia_taoke_detail2 WHERE id=" . $list['id'];
                \Yii::$app->db->createCommand($sql)->execute();
            }
            if (!empty($data)) {
                $result = Order::addOrder($data);
                echo date("m-d H:i", time()) . ">>>>TB>>>>SUCESS>>>>" . $result . "\r\n";
            } else {
                echo date("m-d H:i", time()) . ">>>>TB>>>>SUCESS>>>>KONG\r\n";
            }
            $transaction->commit();
        } catch (Exception $e) {
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

        if (!empty($arr['data'])) {
            foreach ($arr['data'] as $k => $v) {
                $order = Order::findOne(['trade_id' => $v['orderId']]);
                if (empty($order)) {
                    continue;
                }
                if (in_array($v['validCode'], array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14), true)) {
                    $order->updateAttributes([
                        'order_status' => 3,
                        'estimated_effect' => 0,
                    ]);
                    Recharge::deleteAll(['order_id' => $v['orderId']]);
                } elseif ($v['validCode'] == 18) {
                    $order->updateAttributes([
                        'order_status' => 1,
                    ]);
                }

            }
        } else {
            echo date("m-d H:i", time()) . ">>>>JD>>>>ERROR>>>>kong\r\n";
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

        if (!empty($arr)) {
            foreach ($arr as $k => $v) {
                $order = Order::findOne(['trade_id' => $v['order_sn']]);
                if (empty($order)) {
                    continue;
                }

                if (in_array($v['order_status'], array(4, 10), true)) {
                    $order->updateAttributes([
                        'order_status' => 3,
                        'estimated_effect' => 0,
                    ]);
                    Recharge::deleteAll(['order_id' => $v['order_sn']]);
                } elseif ($v['order_status'] == 5) {
                    $order->updateAttributes([
                        'order_status' => 1,
                    ]);
                }
            }
        } else {
            echo date("m-d H:i", time()) . ">>>>PDD>>>>ERROR>>>>kong\r\n";
        }
    }
}