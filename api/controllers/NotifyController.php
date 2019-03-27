<?php

namespace api\controllers;

use backend\models\DistributionConfig;
use common\components\alipay\NotifyHandle;
use common\components\Distribution;
use common\components\wechat\WxAppPay;
use common\helpers\Utils;
use common\helpers\XML;
use common\models\Recharge;
use common\models\UpgradeOrder;
use common\models\User;
use yii\base\Exception;
use yii\db\Expression;

/**
 * 支付异步通知处理
 * Class NotifyController
 * @package api\controllers
 */
class NotifyController extends ControllerBase
{
    /**
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionPay()
    {
        $request = \Yii::$app->request;
        $type = $request->get('type', '');
        if ($type == 'wxpay') {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $this->wxpay();
                $transaction->commit();
                return $this->wechatBack(['code' => "SUCCESS", 'msg' => "OK"]);
            } catch (Exception $e) {
                $transaction->rollBack();
                \Yii::error('会员权益订单信息更新失败:' . $e->getMessage());
                return $this->wechatBack(['code' => "FAIL", 'msg' => $e->getMessage()]);
            }
        } else if ($type == 'alipay') {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $this->alipay();
                $transaction->commit();
                return 'SUCCESS';
            } catch (Exception $e) {
                $transaction->rollBack();
                \Yii::error('会员权益订单信息更新失败:' . $e->getMessage());
                return $e->getMessage();
            }
        } else {
            exit('Fail: Not found pay type');
        }
    }

    /**
     * 微信支付
     * @throws Exception
     */
    private function wxpay()
    {
        $request = \Yii::$app->request;
        $data = XML::parse($request->rawBody);
        if (!$this->checkWxSign($data)) {
            throw new Exception('验签失败:' . json_encode($data));
        }
        if ($data['return_code'] != "SUCCESS" || $data['result_code'] != "SUCCESS") {
            throw new Exception('接口返回业务结果为错误:' . json_encode($data));
        }

        if (preg_match("/^upgrade/", $data['out_trade_no'])) {
            //会员权益订单
            $this->dealWxUpgrade($data);
        }
    }

    /**
     * 支付宝支付
     * @throws Exception
     */
    private function alipay()
    {
        $request = \Yii::$app->request;
        $data = $request->post();
        $notify = new NotifyHandle();
        if (!$notify->check($data)) {
            throw new Exception('验签失败:' . json_encode($data));
        }

        if (empty($data['trade_status']) || !in_array($data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            throw new Exception('订单支付失败:' . json_encode($data));
        }

        if (preg_match("/^upgrade/", $data['out_trade_no'])) {
            //会员权益订单
            $this->dealAlipayUpgrade($data);
        }
    }

    /**
     * @param $data
     * @return bool
     */
    private function checkWxSign($data)
    {
        $wxAppPay = new WxAppPay();
        return $wxAppPay->checkSign($data);
    }

    /**
     * @param $data
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function dealWxUpgrade($data)
    {
        $order = UpgradeOrder::findOne([
            'amount' => bcdiv($data['total_fee'], 100, 2),
            'trade_no' => $data['out_trade_no'],
            'type' => 'wxpay',
            'status' => UpgradeOrder::STATUS_DEFAULT
        ]);
        if (!$order) {
            throw new Exception('没有待处理的订单');
        }
        $user = User::findOne($order->uid);
        $arr = explode('_', $user->superior);
        if ($user->lv != 0) {
            throw new Exception('已购买会员权益');
        }
        if (!$user->updateAttributes(['lv' => 1])) {
            throw new Exception('用户权益状态修改失败');
        } else {
            //TODO  上级判断升级
            $this->distribute($user, $order->trade_no);
            if (!empty($arr[0])) {
                $dis = new Distribution([
                    'order_id' => $order->trade_no,
                    'uid' => $arr[0],
                    'type' => 4,
                ]);
                $dis->upgrade();
            }
        }
        $result = $order->updateAttributes([
            'status' => UpgradeOrder::STATUS_SUCCESS,
            'wechat_trade_no' => $data['transaction_id'],
            'pay_date' => $data['time_end'],
            'updated_at' => TIMESTAMP,
            'msg' => "SUCCESS",
        ]);
        if (!$result) {
            throw new Exception('权益订单更新失败');
        }
    }

    /**
     * @param $data
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function dealAlipayUpgrade($data)
    {
        $order = UpgradeOrder::findOne([
            'amount' => $data['total_amount'],
            'trade_no' => $data['out_trade_no'],
            'type' => 'alipay',
            'status' => UpgradeOrder::STATUS_DEFAULT
        ]);
        if (empty($order)) {
            throw new Exception('订单不存在');
        }
        $user = User::findOne($order->uid);
        if ($user->lv != 0) {
            throw new Exception('会员已购买权益');
        }
        if (!$user->updateAttributes(['lv' => 1])) {
            throw new Exception('修改会员等级失败');
        } else {
            $result = $order->updateAttributes([
                'status' => UpgradeOrder::STATUS_SUCCESS,
                'alipay_trade_no' => $data['trade_no'],
                'pay_date' => $data['gmt_payment'],
                'updated_at' => TIMESTAMP,
                'msg' => "SUCCESS",
            ]);
            if (!$result) {
                throw new Exception('权益订单状态修改失败');
            }

            //TODO  上级判断升级
            $this->distribute($user, $order->trade_no);
            if (!empty($arr[0])) {
                $dis = new Distribution([
                    'order_id' => $order->trade_no,
                    'uid' => $arr[0],
                    'type' => 4,
                ]);
                $dis->upgrade();
            }
        }
    }

    /**
     * 购买会员权益返佣
     * @param $user
     * @param $order
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function distribute($user, $order)
    {
        $relation['superior'] = rtrim($user['superior'], '_0');
        $rela = explode('_', $relation['superior']);
        $data = [];
        if (!empty($rela[0])) {
            $relaUser0 = User::findOne(['uid' => $rela[0]]);
            if ($relaUser0->lv > 0 && $relaUser0->lv >= 1) {
                $relarato1 = DistributionConfig::getAll('partner')['commission'][1];
                $relaUser0->updateAttributes([
                    'credit1' => new Expression('credit1+' . $relarato1),
                    'credit4' => new Expression('credit4+' . $relarato1),
                ]);
                $data[] = [
                    'uid' => $rela[0],
                    'type' => 4,
                    'order_id' => $order,
                    'goods_id' => 1,
                    'order_type' => 4,
                    'price' => Utils::getTwoPrice($relarato1, 2),
                    'credit' => '1',
                    'status' => 2,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
            }
        }

        if (!empty($rela[1])) {
            $relaUser1 = User::findOne(['uid' => $rela[1]]);
            if ($relaUser1->lv > 0 && $relaUser1->lv >= 2) {
                $relarato2 = DistributionConfig::getAll('partner')['commission'][2];
                $relaUser1->updateAttributes([
                    'credit1' => new Expression('credit1+' . $relarato2),
                    'credit4' => new Expression('credit4+' . $relarato2),
                ]);
                $data[] = [
                    'uid' => $rela[1],
                    'type' => 4,
                    'order_id' => $order,
                    'goods_id' => 1,
                    'order_type' => 4,
                    'price' => Utils::getTwoPrice($relarato2, 2),
                    'credit' => '1',
                    'status' => 2,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
            }
        }

        if (!empty($rela[2])) {
            $relaUser2 = User::findOne(['uid' => $rela[2]]);
            if ($relaUser2->lv > 0 && $relaUser2->lv >= 3) {
                $relarato3 = DistributionConfig::getAll('partner')['commission'][3];
                $relaUser2->updateAttributes([
                    'credit1' => new Expression('credit1+' . $relarato3),
                    'credit4' => new Expression('credit4+' . $relarato3),
                ]);
                $data[] = [
                    'uid' => $rela[2],
                    'type' => 4,
                    'order_id' => $order,
                    'goods_id' => 1,
                    'order_type' => 4,
                    'price' => Utils::getTwoPrice($relarato3, 2),
                    'credit' => '1',
                    'status' => 2,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
            }
        }
        if (!empty($data)) {
            Recharge::addOrder($data);
        }
    }

    /**
     * @param $data
     * @return string
     */
    private function wechatBack($data)
    {
        $str = "<xml><return_code><![CDATA["
            . $data['code']
            . "]]></return_code><return_msg><![CDATA["
            . $data['msg']
            . "]]></return_msg></xml>";
        return $str;
    }

}
