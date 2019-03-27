<?php
/**
 * @author pine
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * @link http://www.hnbangyao.com
 */

namespace console\components;

use common\components\Distribution;
use common\models\Order;
use yii\base\BaseObject;
use yii\base\Exception;

/**
 * 计算分佣
 * Class ComputerCommission
 * @package console\components
 */
class ComputerCommission extends BaseObject
{
    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function run()
    {
        $this->disOrder();
    }

    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function disOrder()
    {
        $list = Order::find()->select("id,uid,trade_id,product_price,payment_price,commission_rate,commission_price,estimated_effect")->where(['rebate_status' => 1,])->andWhere('order_status !=3  and estimated_effect>0')->asArray()->all();
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($list as $k => $v) {
                $dis = new Distribution([
                    'uid' => $v['uid'],
                    'order_id' => $v['trade_id'],
                    'sumPrice' => $v['estimated_effect'],
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
}
