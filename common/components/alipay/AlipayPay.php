<?php

namespace common\components\alipay;

use backend\models\DistributionConfig;
use common\models\Config;
use common\models\Recharge;
use yii\base\Model;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;
use yii\helpers\Url;
use common\components\Distribution;
use common\helpers\Utils;
use common\models\UpgradeOrder;
use common\models\User;
use Exception;
use yii\db\Expression;


class AlipayPay extends Model
{
    /**
     * @var array 支付宝配置
     */
    protected $config = [];

    public function init()
    {
        $this->config = [
            'app_id' => Config::getConfig('ALIPAY_APP_ID'),
            'notify_url' => Url::toRoute('/alipay-notify/notify',true),
            'return_url' => Url::toRoute('/alipay-notify/back',true),
            'ali_public_key' => Config::getConfig('ALIPAY_PUB_KEY'),
            'private_key' => Config::getConfig('ALIPAY_PRIV_KEY'),
            'log' => [ // optional
                'file' => './logs/alipay.log',
                'level' => 'debug', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],

            //'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
        ];
    }

    /**
     * @param $config_biz // eg：$config_biz = ['out_trade_no' => $this->out_trade_no,'total_amount' => $this->total_amount,'subject' => $this->subject]
     * @return mixed
     */
    public function pay($config_biz){

        /*$pay = new Pay($this->config);
        return $pay->driver('alipay')->gateway()->pay($config_biz);*/
        $alipay = Pay::alipay($this->config)->web($config_biz);
        return $alipay->getContent();
    }

   public function return_back(){
       /*$pay = new Pay($this->config);

       return $pay->driver('alipay')->gateway()->verify($request);*/
       return $data = Pay::alipay($this->config)->verify(); // 是的，验签就这么简单！

   }

    /**
     * @param $request
     * @throws \yii\db\Exception
     * @throws Exception
     */
    public function notify($request){

        $alipay = Pay::alipay($this->config);

        try{
            $data = $alipay->verify(); // 是的，验签就这么简单！

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况
            if (isset($request['trade_status'])){
                if ($request['trade_status'] == 'TRADE_SUCCESS'){
                    //   支付成功
                    //会员升级订单
                    if (preg_match("/^upgrade/", $request['out_trade_no'])) {
                        $this->dealAlipayUpgrade($request);
                    }
                }else if ($request['trade_status'] == 'TRADE_CLOSED'){
                    //   交易关闭
                    $this->cancelAlipayUpgrade($request);
                }
            }

            Log::debug('Alipay notify', $data->all());
        } catch (Exception $e) {
             throw new Exception("支付宝通知出错：".$e->getMessage());
        }
        return $alipay->success()->send();
   }


    /**
     * 取消订单
     * @throws Exception
     */
    private function cancelAlipayUpgrade($data){
        $order = UpgradeOrder::findOne([
            'amount' => $data['total_amount'],
            'type'  => 'alipay',
            'trade_no'  =>  $data['out_trade_no'],
            'status' => UpgradeOrder::STATUS_DEFAULT
        ]);
        if (empty($order)) {
            throw new Exception('订单不存在');
        }
        $result = $order->updateAttributes([
                'status' => UpgradeOrder::STATUS_FAIL,
                'alipay_trade_no' => $data['trade_no'],
                'updated_at' => TIMESTAMP,
                'msg' => "交易关闭",
            ]);
        if (!$result) {
            throw new Exception('取消订单修改失败');
        }
    }


    /**
     * 会员升级
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
}
