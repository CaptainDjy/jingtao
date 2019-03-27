<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/19
 * Time: 14:09
 */

namespace frontend\controllers;


use backend\models\DistributionConfig;
use common\components\alipay\AlipayClient;
use common\models\Recharge;
use common\models\UpgradeOrder;
use common\models\User;
use Yii;
use yii\base\Exception;
use yii\db\Expression;
use yii\filters\AccessControl;

class NotifyController extends ControllerBase
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@', '?'],
                    ],
                ],
            ],
        ];
    }

    /**
     * 支付宝回调地址
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionAlipayNotify()
    {
        $request = Yii::$app->request;
        $notify_time = Yii::$app->request->post('notify_time');
        $out_trade_no = Yii::$app->request->post('out_trade_no');
        //通知类型  batch_trans_notify
        $notifyType = Yii::$app->request->post('notify_type');

        //通知校验ID    70fec0c2730b27528665af4517c27b95
        $notifyId = Yii::$app->request->post('notify_id');

        //签名方式  MD5
        $signType = Yii::$app->request->post('sign_type');

        //签名    e7d51bf34a1317714d93fab13bbeab73
        $sign = Yii::$app->request->post('sign');

        //批次号
        $batchNo = Yii::$app->request->post('trade_no');

        $file = fopen("/www/log/alipay" . date("Y-m-d", time()) . ".txt", "a+");
        fwrite($file, $out_trade_no . "\r\n");
        fwrite($file, $batchNo . "\r\n");
        fclose($file);
        //付款账号
        $payAccountNo = Yii::$app->request->post('buyer_email');

        if (Yii::$app->request->post('trade_status') == 'TRADE_FINISHED') {
            //判断该笔订单是否在商户网站中已经做过处理
            //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
            //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
            //如果有做过处理，不执行商户的业务程序

            //注意：
            //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");

        } else if (Yii::$app->request->post('trade_status') == 'TRADE_SUCCESS') {
            //判断该笔订单是否在商户网站中已经做过处理
            //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
            //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
            //如果有做过处理，不执行商户的业务程序

            //注意：
            //付款完成后，支付宝系统发送该交易状态通知

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            if (mb_substr($out_trade_no, 0, 6) == 'upgrade') {
                //验签
                $params = $request->get();
                unset($params['sign']);
                $aliclient = new AlipayClient();
                $result = $aliclient->checkSign($params, $sign);
                if (!$result) {
                    $params['sign'] = $sign;
                    \Yii::info('验签失败:' . json_encode($params), 'upgrade');
                    return "fail";
                }
                //会员升级订单
                $order = UpgradeOrder::findOne(['trade_no' => $out_trade_no, 'status' => UpgradeOrder::STATUS_DEFAULT]);
                if (!$order) {
                    return "fail";
                }
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    //todo 提现成功记录
                    $num = $order->updateAttributes([
                        'status' => UpgradeOrder::STATUS_SUCCESS,
                    ]);

                    if (!$num) {
                        \Yii::info('订单信息更新失败:' . $out_trade_no, 'upgrade');
                        return "fail";
                    }
                    $transaction->commit();
                    return "success";
                } catch (Exception $e) {
                    $transaction->rollBack();
                    return "fail";
                }
            } else {
                $list = Recharge::findOne(['order_id' => $out_trade_no]);
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($list->status == 0) {
                        $user = User::findOne(['uid' => $list->uid]);
                        $relation['superior'] = rtrim($user['superior'], '_0');
                        $rela = explode('_', $relation['superior']);
                        if (!empty($rela[0])) {
                            $relaUser0 = User::findOne(['uid' => $rela[0]]);
                            if ($relaUser0->lv > 0 && $relaUser0->lv >= 1) {
                                $relarato1 = DistributionConfig::getAll('partner')['commission'][1];
                                $relaUser0->updateAttributes([
                                    'credit1' => new Expression('credit1+' . $relarato1),
                                    'credit4' => new Expression('credit4+' . $relarato1),
                                ]);
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
                            }
                        }
                        $list->updateAttributes([
                            'pay_order' => $batchNo,
                            'status' => 2,
                            'updated_at' => time(),
                        ]);
                    }
                    $transaction->commit();
                    return "success";
                } catch (Exception $e) {
                    $transaction->rollBack();
                    return "fail";
                }
            }
        } else {
            return "fail";
        }
    }

    /**
     * 微信回调
     * @throws \yii\base\Exception
     */
    public function actionWechatNotify()
    {
        $input = file_get_contents('php://input');
        $obj = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
        $data = json_decode(json_encode($obj), true);
        $out_trade_no = $data['out_trade_no'];
        $type = substr($data['out_trade_no'], 0, 2);
        $list = Recharge::findOne(['order_id' => $out_trade_no]);
        $file = fopen("/www/log/wechat" . date("Y-m-d", time()) . ".txt", "a+");
        fwrite($file, $input . "\r\n");
        $transaction = Yii::$app->db->beginTransaction();
        try {
//            '<xml><appid><![CDATA[wx9849a14638a0be93]]></appid>
//            <bank_type><![CDATA[SPDB_DEBIT]]></bank_type>
//            <cash_fee><![CDATA[9000]]></cash_fee>
//            <fee_type><![CDATA[CNY]]></fee_type>
//            <is_subscribe><![CDATA[N]]></is_subscribe>
//            <mch_id><![CDATA[1494461832]]></mch_id>
//            <nonce_str><![CDATA[5K8264ILTKCH16CQ25]]></nonce_str>
//            <openid><![CDATA[o3os21Gx_JWlmBM0arb2HXbSocMU]]></openid>
//            <out_trade_no><![CDATA[ZCH3239102899823281]]></out_trade_no>
//            <result_code><![CDATA[SUCCESS]]></result_code>
//            <return_code><![CDATA[SUCCESS]]></return_code>
//            <sign><![CDATA[D4C4734B870792F875C1AB4A24FF8328]]></sign>
//            <time_end><![CDATA[20180323154353]]></time_end>
//            <total_fee>9000</total_fee>
//            <trade_type><![CDATA[APP]]></trade_type>
//            <transaction_id><![CDATA[4200000065201803234013887939]]></transaction_id>
//            </xml>'
            if ($data['result_code'] == 'SUCCESS' && $data['return_code'] == 'SUCCESS' && $list->status == 0) {
                $list->updateAttributes([
                    'pay_order' => $data['transaction_id'],
                    'status' => 2,
                    'updated_at' => time(),
                ]);
                $user = User::findOne(['uid' => $list->uid]);
                $relation['superior'] = rtrim($user['superior'], '_0');
                $rela = explode('_', $relation['superior']);
                if (!empty($rela[0])) {
                    $relaUser0 = User::findOne(['uid' => $rela[0]]);
                    if ($relaUser0->lv > 0 && $relaUser0->lv >= 1) {
                        $relarato1 = DistributionConfig::getAll('partner')['commission'][1];
                        $relaUser0->updateAttributes([
                            'credit1' => new Expression('credit1+' . $relarato1),
                            'credit4' => new Expression('credit4+' . $relarato1),
                        ]);
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
                    }
                }
            } else {
                exit("FAIL");
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            exit("FAIL");
        }
        fclose($file);
    }

}