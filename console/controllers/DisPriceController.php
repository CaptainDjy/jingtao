<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/6/11
 * Time: 19:33
 */

namespace console\controllers;


use common\components\Distribution;
use common\models\Order;
use common\models\Recharge;
use yii\base\Exception;
use yii\console\Controller;

class DisPriceController extends Controller
{
    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function actionIndex()
    {
        $this->modifyTime();
        $this->disTbOrder();
        $this->disJdOrder();
        $this->disPddOrder();
    }

    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function disTbOrder()
    {
        $list = Order::find()->select("id,type,uid,product_id,trade_id,product_price,payment_price,commission_rate,commission_price,estimated_effect")->where(['rebate_status' => 2, 'type' => 1, 'order_status' => 1])->andWhere('estimated_effect>0')->andWhere(["FROM_UNIXTIME(settlement_at, '%Y-%m')" => date("Y-m", strtotime("last month"))])->asArray()->all();
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($list as $k => $v) {
                $dis = new Distribution([
                    'uid' => $v['uid'],
                    'order_id' => $v['trade_id'],
                    'sumPrice' => $v['estimated_effect'],
                    'goods_id' => $v['product_id'],
                    'type' => $v['type'],
                ]);
                $dis->disMoney();
                \Yii::$app->db->createCommand()->update(Order::tableName(), ['rebate_status' => 3], ['id' => $v['id']])->execute();
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function disJdOrder()
    {
        $list = Order::find()->select("id,type,uid,product_id,trade_id,product_price,payment_price,commission_rate,commission_price,estimated_effect")->where(['rebate_status' => 2, 'type' => 2, 'order_status' => 18])->andWhere(["FROM_UNIXTIME(settlement_at, '%Y-%m')" => date("Y-m", strtotime("last month"))])->andWhere('estimated_effect>0')->asArray()->all();
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($list as $k => $v) {
                $dis = new Distribution([
                    'uid' => $v['uid'],
                    'order_id' => $v['trade_id'],
                    'sumPrice' => $v['estimated_effect'],
                    'goods_id' => $v['product_id'],
                    'type' => $v['type'],
                ]);
                $dis->disMoney();
                \Yii::$app->db->createCommand()->update(Order::tableName(), ['rebate_status' => 3], ['id' => $v['id']])->execute();
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function disPddOrder()
    {
        $list = Order::find()->select("id,type,uid,product_id,trade_id,product_price,payment_price,commission_rate,commission_price,estimated_effect")->where(['rebate_status' => 2, 'type' => 3, 'order_status' => 1])->andWhere(["FROM_UNIXTIME(settlement_at, '%Y-%m')" => date("Y-m", strtotime("last month"))])->andWhere('estimated_effect>0')->asArray()->all();
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($list as $k => $v) {
                $dis = new Distribution([
                    'uid' => $v['uid'],
                    'order_id' => $v['trade_id'],
                    'sumPrice' => $v['estimated_effect'],
                    'goods_id' => $v['product_id'],
                    'type' => $v['type'],
                ]);
                $dis->disMoney();
                \Yii::$app->db->createCommand()->update(Order::tableName(), ['rebate_status' => 3], ['id' => $v['id']])->execute();
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function modifyTime()
    {
        $list1 = Order::find()->select("id,type,uid,product_id,trade_id,estimated_effect,settlement_at")->where(['order_status' => 1, 'type' => 1])->andWhere('estimated_effect>0 and settlement_at >100000')->andWhere(["FROM_UNIXTIME(settlement_at, '%Y-%m')" => date("Y-m", strtotime("last month"))])->asArray()->all();
        $list2 = Order::find()->select("id,type,uid,product_id,trade_id,estimated_effect,settlement_at")->where(['order_status' => 18, 'type' => 2])->andWhere('estimated_effect>0 and settlement_at >100000')->andWhere(["FROM_UNIXTIME(settlement_at, '%Y-%m')" => date("Y-m", strtotime("last month"))])->asArray()->all();
        $list3 = Order::find()->select("id,type,uid,product_id,trade_id,estimated_effect,settlement_at")->where(['order_status' => 1, 'type' => 3])->andWhere('estimated_effect>0 and settlement_at >100000')->andWhere(["FROM_UNIXTIME(settlement_at, '%Y-%m')" => date("Y-m", strtotime("last month"))])->asArray()->all();
        $list = array_merge($list1,$list2,$list3);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($list as $k => $v) {
                \Yii::$app->db->createCommand()->update(Recharge::tableName(), ['settlement_at' => $v['settlement_at']], ['order_id' => $v['trade_id'], 'goods_id' => $v['product_id']])->execute();
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception($e->getMessage());
        }
    }

}