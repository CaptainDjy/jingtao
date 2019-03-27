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

class DisOrderController extends Controller
{
    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function actionIndex()
    {
        $this->disOrder();
    }

    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function disOrder()
    {
        $list = Order::find()->select("id,type,uid,product_id,trade_id,product_price,payment_price,commission_rate,commission_price,estimated_effect")->where(['rebate_status' => 1,])->andWhere('order_status !=3  and estimated_effect>0')->asArray()->all();
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
                $dis->disPrice();
                \Yii::$app->db->createCommand()->update(Order::tableName(), ['rebate_status' => 2], ['id' => $v['id']])->execute();
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
    private function modifyTb()
    {
        $list = Order::find()->select("id,product_id,trade_id,order_status")->andWhere(['order_status' => 1, 'type' => 1])->asArray()->all();
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($list as $k => $v) {
                \Yii::$app->db->createCommand()->update(Recharge::tableName(), ['status' => $v['order_status']], ['order_id' => $v['trade_id'], 'goods_id' => $v['product_id']])->execute();
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
    private function modifyPdd()
    {
        $list = Order::find()->select("id,product_id,trade_id,order_status")->andWhere(['order_status' => 1, 'type' => 3])->asArray()->all();
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($list as $k => $v) {
                \Yii::$app->db->createCommand()->update(Recharge::tableName(), ['status' => $v['order_status']], ['order_id' => $v['trade_id'], 'goods_id' => $v['product_id']])->execute();
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception($e->getMessage());
        }
    }

}