<?php
/**
 * Created by PHPSTORM.
 * User: Yuuuuuu
 * Date: 2018/9/27
 * Time: 14:43
 */

namespace api\controllers;



use common\components\alipay\AlipayPay;


/**
 * 支付异步通知处理
 * Class NotifyController
 * @package api\controllers
 */
class AlipayNotifyController extends ControllerBase
{

    /**
     * return_url，用于支付后的跳转
     */
    public function actionBack()
    {
        $alipay = new AlipayPay();
        $data = $alipay->return_back();
        // 订单号：$data->out_trade_no
        // 支付宝交易号：$data->trade_no
        // 订单总金额：$data->total_amount
    }

    /**
     * 通知回调
     * @throws \yii\db\Exception
     */
    public function actionNotify()
    {
        $alipay = new AlipayPay();
        $alipay->notify(\Yii::$app->request->bodyParams);
    }

}