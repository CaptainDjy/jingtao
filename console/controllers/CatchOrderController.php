<?php

namespace console\controllers;


use backend\models\DistributionConfig;
use common\components\jd\JdClient;
use common\components\jd\requests\Dingdan;
use common\components\jd\requests\Xiangqing;
use common\components\jd\requests\UnionServiceQueryOrderList;
use common\components\miao\MiaoClient;
use common\components\miao\taobao\requests\GetTbkOrder;
use common\components\pdd\PddClient;
use common\components\pdd\requests\DdkOrderListIncrementGet;
use common\helpers\Utils;
use common\models\Config;
use common\models\Cooperationuser;
use common\models\Log;
use common\models\Order;
use common\models\Recharge;
use common\models\User;
use common\models\Commissionset;
use common\behaviors\HttpTokenAuth;
use api\models\User as users;
use phpDocumentor\Reflection\Types\String_;
use Yii;
use PHPExcel_Reader_Excel2007;
use PHPExcel_Reader_Excel5;
use PHPExcel_RichText;
use yii\base\Exception;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\base\UserException;
class CatchOrderController extends Controller
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
     * 抓取各平台返利订单，执行间隔不能超过二十分钟
     * @throws Exception
     */
    public function actionIndex()
    {
        $this->Veorder();
        $this->Veordergx();
    }

//    public function actionJdindex(){
//        $this->JdlmOrder();
//        $this->JdlmOrdergx();
//    }

    public function actionPddindex(){
        $this->PddOrder();
    }


//订单抓取,维易订单
    public function Veorder(){
        $client = new Client();
        $vekey=Config::getConfig('VE_KEY');
        $response = $client->get('http://apiorder.vephp.com/order?vekey='.$vekey.'&start_time='.urlencode(date("Y-m-d H:i:s",strtotime("-15 min"))).'&span=1200'
        )->send();//对象

        if (empty($result['data'])){

        }else{
        foreach ($result['data'] as $key=>$value) {
            $orders = Order::find()->where(['trade_id' => $value['trade_id']])->one();

            if (empty($orders)) {
                $order = new \api\models\Order();
                $user = User::find()->where("alimm_pid like  '%{$value['adzone_id']}'")->asArray()->one();   //  该订单所属用户

                if (empty($user)){

                }else{
                    $order->uid = $user['uid'];
                }

                $order->trade_id = $value['trade_id'];//订单编号
                $order->product_id = $value['num_iid'];//商品ID

                if ($value['order_type'] == '天猫') {
                    $order_type = 1;
                } elseif ($value['order_type'] == '淘宝') {
                    $order_type = 1;
                } else {
                    $order_type = 3;
                }
                $order->order_type = $order_type;//订单商品类型
                $order->type = $order_type;//订单商品类型
                $order->payment_price = $value['alipay_total_price'];//付款金额
                $order->product_price = $value['price'];//商品单价
                $order->title = $value['item_title'];//商品标题
                $order->product_num = $value['item_num'];//商品数量
                $order->order_status = $value['tk_status'];//订单商品状态
                $order->pid = $value['adzone_id'];//广告位ID
                $order->settlement_price = $value['alipay_total_price'];//结算金额
                $order->commission_rate = $value['total_commission_rate'];//佣金比率
                $order->estimated_effect=$value['pub_share_pre_fee'];//效果预估(佣金)
                $order->estimated_revenue = $value['pub_share_pre_fee'];//预估收入(同上)
                if ($value['tk_status'] == 3) {
                    $order->commission_price = $value['total_commission_fee'];//佣金金额
                } else {
                    $order->commission_price = 0;//佣金金额
                }
                $order->pid_name = $value['adzone_name'];//广告位名称
                $order->wangwang = $value['seller_nick'];//掌柜旺旺
                $order->shop = $value['seller_shop_title'];//所属店铺
                $order->source_media = $value['site_id'];//来源媒体ID
                $order->media_name = $value['site_name'];//来源媒体名称
                $order->category_name = $value['auction_category'];//类目名称
                if ($value['tk_status'] == 3) {
                    $order->settlement_at = strtotime($value['earning_time']);//结算时间
                } else {
                    $order->settlement_at = 0;//结算时间
                }
                $order->created_at = strtotime($value['create_time']);//创建时间

                $order->save();

                //  保存订单
                if (!$order->save()) {
                    //var_dump($order->getErrors());
                    self::say('error', '保存订单错误', $order->getErrors());
                }
            } else {//如果订单不存在添加  存在修改
                $orders = \api\models\Order::find()->where(['trade_id' => $value['trade_id']])->one();
                $orders->order_status = $value['tk_status'];//修改订单商品状态
                $orders->settlement_price = $value['alipay_total_price'];//修改结算金额
                if ($value['tk_status'] == 3) {//修改佣金金额
                    $orders->commission_price = $value['total_commission_fee'];//佣金金额
                }
                $orders->save();
            }
        }
        }
        $this->yugu();
        $this->Orderends();
    }

    //订单更新
    public function Veordergx()
    {
        $client = new Client();
        $vekey = Config::getConfig('VE_KEY');
        $start = \api\models\Order::find()->where(['type' => 1])->andWhere(['!=', 'order_status', 3])->asArray()->all();//查询淘宝没有结算的订单

        if (!empty($start)) {
            foreach ($start as $key => $value) {
                $created = date('Y-m-d H:i:s', $value['created_at'] - 900);
                $response = $client->get('http://apiorder.vephp.com/order?vekey=' . $vekey . '&start_time=' . urlencode($created) . '&span=1200'
                )->send();//对象
                $result = $response->getData();//数组

                if (!empty($result['data'])) {
                    $orders = \api\models\Order::find()->where(['trade_id' => $value['trade_id']])->one();
                    $orders->order_status = $result['data'][0]['tk_status'];//修改订单商品状态
                    if (isset($result['data'][0]['total_commission_fee'])) {
                        $orders->commission_price = $result['data'][0]['total_commission_fee'];//修改佣金金额
                    }

                    $orders->save();
                }
            }
        }
    }



    public function actionGetOrderTest(){
        $time = 1540278000;
        for ($i=1080;$i>0;$i++){
            $starttime = date('Y-m-d H:i:s',$time - 86400*15 + $i*1200);   //  	订单查询开始时间（*必要，并且日期需进行urlencode编码）
            echo $starttime;
            $page = 1;
            $code = 200;
            while ($code > 0){
                $client = new MiaoClient();
                $request = new GetTbkOrder();
                $request->starttime = urlencode($starttime);
                $request->span = 1200;
                $request->page = $page++;
                $request->pagesize = 100;
                $request->tkstatus = 1;
                $request->ordertype = 'settle_time';
                $request->tbname = Config::getConfig('MIAO_TBNAME');
                $response = $client->run($request);
                $tmp = Json::decode($response,true);
                if (empty($tmp)){
                    continue;
                }
                if ($tmp['code'] != 200){
                    if ($tmp['msg'] = '本时间内无订单' || empty($tmp['data'])){
                        $code = $tmp['code'];
                        self::say('noOrder','',$tmp);
                    }else{
                        self::say('updateOrderErr','订单更新错误',$tmp);
                    }
                    continue;
                }

                //  更新到数据库
                $order_list = $tmp['data']['n_tbk_order'];
                //  写入订单
                $tmp = fopen('./order.log','a+');
                fwrite($tmp,json_encode($order_list).PHP_EOL);
                fclose($tmp);
                try{
                    if (isset($order_list['adzone_id'])){   //  如果是单笔订单
                        $this->order_save($order_list);
                    }else{
                        foreach ($order_list as $item){
                            $this->order_save($item);
                        }
                    }
                }catch (\Exception $e){
                    self::say('exception','意外错误',$order_list);
                    self::say('exception','',$e->getMessage());
                }
            }
        }

        echo date("m-d H:i", time()) . "CATCH-MiaoTbk ok\r\n";
    }

    /**
     * 通过喵有券抓取淘宝返利订单，每次查询最近15天
     * @throws \yii\httpclient\Exception
     */
    private function MiaoTbkOrder(){
        $time = time();
//        for ($i=0;$i<1080;$i++){
//            $a=strtotime('2019-01-11 23:49:55');
            $starttime = date('Y-m-d H:i:s',$time - 800);   // $time - 86400 + $i*1200 	订单查询开始时间（*必要，并且日期需进行urlencode编码）
            $page = 1;
            $code = 200;
            while ($code > 0){
                $client = new MiaoClient();
                $request = new GetTbkOrder();
                $request->starttime = urlencode($starttime);
                $request->span = 1200;
                $request->page = $page++;
                $request->pagesize = 100;
                $request->tkstatus = 1;
                $request->ordertype = 'create_time';
                $request->tbname = Config::getConfig('MIAO_TBNAME');
                $response = $client->run($request);
                $tmp = Json::decode($response,true);
//                print_r($tmp);
//                exit;
                if (empty($tmp)){
                    continue;
                }
                if ($tmp['code'] != 200){
                    if ($tmp['msg'] = '本时间内无订单' || empty($tmp['data'])){
                        $code = $tmp['code'];
                    }else{
                        self::say('updateOrderErr','订单更新错误',$tmp);
                    }
                    continue;
                }

                //  更新到数据库
                $order_list = $tmp['data']['n_tbk_order'];

                //  写入订单
                $tmp = fopen('./order.log','a+');
                fwrite($tmp,json_encode($order_list));
                fclose($tmp);
                try{
                    if (isset($order_list['adzone_id'])){   //  如果是单笔订单
                        $this->order_save($order_list);
                    }else{
                        foreach ($order_list as $item){
                            $this->order_save($item);
                        }
                    }
                }catch (\Exception $e){
                    self::say('exception','意外错误',$order_list);
                    self::say('exception','',$e->getMessage());
                }

            }
//        }

        echo date("m-d H:i", time()) . "CATCH-MiaoTbk ok\r\n";
        $this->yugu();
        $this->Orderends();
    }

    private function order_save($item){
        $order = Order::findOne(['trade_id' => $item['trade_id']]);
//        print_r($order);
//        exit;
        if (!$order){
            $order = new Order();
        }else{
            $orders = \api\models\Order::find()->where(['trade_id' => $item['trade_id']])->one();
            switch ($item['tk_status']){
                case 3:
                    //  订单结算
                    $tk_status = 5;
                    $rebate_status = 2;
                    break;
                case 12:
                    //  订单付款
                    $tk_status = 1;
                    $rebate_status = 1;
                    break;
                case 13:
                    //  订单失效
                    $tk_status = 4;
                    $rebate_status = 1;
                    break;
                case 14:
                    //  订单成功
                    $tk_status = 2;
                    $rebate_status = 1;
                    break;
                default:
                    $tk_status = 4;
                    $rebate_status = 1;
                    break;
            }
                $order->order_status = $tk_status;//修改订单商品状态
                if (array_key_exists("total_commission_fee", $item)) {
                    $orders->commission_price = $item['total_commission_fee'];//修改佣金金额
                }
                $orders->save();
        }
        $user = User::find()->where("alimm_pid like  '%{$item['adzone_id']}'")->asArray()->one();   //  该订单所属用户

        if (!empty($user)){
            $order->uid = $user['uid'];
        }else{
            $order->uid = 0;
        }
        $order->trade_id = $item['trade_id'];
        $order->product_id = $item['num_iid'];
        $order->type = 1;
        $order->payment_price = $item['alipay_total_price'];//付款金额

        $order->estimated_effect = $item['pub_share_pre_fee'];//预估佣金

        $order->product_price = $item['price'];
        $order->title = $item['item_title'];
        $order->product_num = $item['item_num'];
        $order->order_time = $item['create_time'];  //  订单创建时间
        switch ($item['tk_status']){
            case 3:
                //  订单结算
                $tk_status = 5;
                $rebate_status = 2;
                break;
            case 12:
                //  订单付款
                $tk_status = 1;
                $rebate_status = 1;
                break;
            case 13:
                //  订单失效
                $tk_status = 4;
                $rebate_status = 1;
                break;
            case 14:
                //  订单成功
                $tk_status = 2;
                $rebate_status = 1;
                break;
            default:
                $tk_status = 4;
                $rebate_status = 1;
                break;
        }
        $order->order_status = $tk_status;
        $order->rebate_status = $rebate_status;//返佣状态
        $order->income_ratio = intval($item['commission_rate'] * 100);
        $order->picUrl = '';
        $order->pid = $item['adzone_id'];
        $order->settlement_price = $item['alipay_total_price'];   // $item['pay_price']订单结算后才会显示结算金额  结算金额
        $order->commission_rate = $item['total_commission_rate'];
        $order->commission_price = $item['commission'];//empty($item['total_commission_fee']) ? round($item['pay_price'] * $item['total_commission_rate']) : $item['total_commission_fee'];
        $order->subsidy_ratio = $item['subsidy_rate'];
        $order->subsidy_price = empty($item['subsidy_fee']) ? round($item['pay_price'] * $item['total_commission_rate']) : $item['subsidy_fee'];
        $order->pid_name = $item['adzone_name'];
        $order->wangwang = '';
        $order->shop = $item['seller_shop_title'];
        if ($item['order_type'] == '天猫') {
            $order_type = 1;
        } elseif ($item['order_type'] == '淘宝') {
            $order_type = 2;
        } else {
            $order_type = 3;
        }
        $order->order_type = $order_type;
//        $order->divided_ratio = intval($item['total_commission_fee']);//分成比率----结算佣金
        $order->estimated_revenue = $item['pub_share_pre_fee'];
        $order->category_name = '未分类';
        /*$order->subsidy_type = $item['subsidy_type'];
        $order->dealing_platform = $item['terminal_type'];
        $order->service_source = $item['tk3rd_type'];
        $order->source_media = $item['site_id'];
        $order->media_name = $item['site_name'];
        $order->settlement_at = $item['earning_time'];*/

        //  保存订单
        if(!$order->save()){
            //var_dump($order->getErrors());
            self::say('error','保存订单错误',$order->getErrors());
        }
    }

    /**
     * 订单结算后返佣金
     * @param $user array 订单所属用户
     * @param $order object 订单详情
     * @throws Exception
     */
    private function settleCommission($user,$order){
        $commission = DistributionConfig::getAll('rebates')['deduction'];
        $per_comm = round(1-($commission/100),2); //bcsub(1,bcdiv($commission,100,2),2); //  该用户返利百分比
        $obt_fee = round($per_comm * $order->divided_ratio,2);//bcmul($per_comm,$order->divided_ratio,2);    //  所有佣金总和
        $self_obt_fee = round($obt_fee * round(DistributionConfig::getAll('rebates')['selfObt'] / 100,2),2);// bcmul($obt_fee,round(DistributionConfig::getAll('rebates')['selfObt'] / 100,2),2);

        $trans = \Yii::$app->db->beginTransaction();
        try{
            $_user = User::findOne(['uid' => $user['uid']]);
            $_user->credit1 += $self_obt_fee;
            $_user->credit4 += $self_obt_fee;
            $_user->save();
            //  记录
            Recharge::addOrder([[
                'uid' => $_user->uid,
                'type' => 3,
                'order_id' => $order->trade_id,
                'goods_id' => $order->product_id,
                'order_type' => 4,
                'price' => $order->divided_ratio,
                'credit' => $self_obt_fee,
                'status' => 2,
                'created_at' => time(),
                'updated_at' => time(),
            ]]);
            //  上级返利
            $this->distribute($_user,$order->trade_id,$obt_fee);

            $trans->commit();
        }catch (\Exception $exce){
            $trans->rollBack();
            self::say('exception','',$exce->getMessage());
            //var_dump($exce->getMessage());
        }

    }

    /**
     * 用户上级返佣
     * @param $user Object 用户
     * @param $order string 订单ID
     * @param $obt_fee float 总应该返的佣金
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function distribute($user, $order, $obt_fee)
    {
        $relation['superior'] = rtrim($user->superior, '_0');
        $rela = explode('_', $relation['superior']);
        $data = [];
        if (!empty($rela[0])) {
            $relaUser0 = User::findOne(['uid' => $rela[0]]);
            $relarato1 = DistributionConfig::getAll('rebates')['selfcomm'][1];
            if ($relarato1 > 0) {
                $price = bcmul($obt_fee,bcdiv($relarato1,100,2),2);
                $relaUser0->updateAttributes([
                    'credit1' => $relaUser0['credit1'] + $price,
                    'credit4' => $relaUser0['credit4'] + $price,
                ]);
                $data[] = [
                    'uid' => $rela[0],
                    'type' => 3,
                    'order_id' => $order,
                    'goods_id' => 1,
                    'order_type' => 4,
                    'price' => $price,
                    'credit' => '1',
                    'status' => 2,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
            }
        }

        if (!empty($rela[1])) {
            $relaUser1 = User::findOne(['uid' => $rela[1]]);
            $relarato2 = DistributionConfig::getAll('rebates')['selfcomm'][2];
            if ($relarato2 > 0) {
                $price = bcmul($obt_fee,bcdiv($relarato2,100,2),2);
                $relaUser1->updateAttributes([
                    'credit1' => $relaUser1['credit1'] + $price,
                    'credit4' => $relaUser1['credit4'] + $price,
                ]);

                $data[] = [
                    'uid' => $rela[1],
                    'type' => 3,
                    'order_id' => $order,
                    'goods_id' => 1,
                    'order_type' => 4,
                    'price' => $price,
                    'credit' => '1',
                    'status' => 2,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
            }
        }

        if (!empty($rela[2])) {
            $relaUser2 = User::findOne(['uid' => $rela[2]]);
            $relarato3 = DistributionConfig::getAll('rebates')['selfcomm'][3];
            if ($relarato3 > 0) {
                $price = bcmul($obt_fee,bcdiv($relarato3,100,2),2);
                $relaUser2->updateAttributes([
                    'credit1' => $relaUser2['credit1'] + $price,
                    'credit4' => $relaUser2['credit4'] + $price,
                ]);
                $data[] = [
                    'uid' => $rela[2],
                    'type' => 3,
                    'order_id' => $order,
                    'goods_id' => 1,
                    'order_type' => 4,
                    'price' => $price,
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
     * 淘宝订单
     * @throws Exception
     */
    private function TbkOrder()
    {
        $time = date("Y-m-d", mktime(0, 0, 0, date('m'), 1, date('Y')));

        $sql = "select * from ftxia_taoke_detail2 WHERE  create_time> '{$time}' ";
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
//                    if ($list['order_status'] == '订单失效') {
//                        $order->updateAttributes([
//                            'order_status' => 3,
//                            'estimated_effect' => 0,
//                        ]);
//                        Recharge::deleteAll(['order_id' => $list['order_sn']]);
//                    } elseif ($list['order_status'] == '订单结算') {
//                        $order->updateAttributes([
//                            'order_status' => 1,
//                        ]);
//                    }
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
//                if ($list['order_status'] == '订单结算') {
//                    $order_status = 1;
//                } elseif ($list['order_status'] == '订单付款') {
//                    $order_status = 2;
//                } elseif ($list['order_status'] == '订单失效') {
//                    $order_status = 3;
//                } elseif ($list['order_status'] == '订单成功') {
//                    $order_status = 4;
//                }
//                $data[$k]['order_status'] = $order_status;
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

//                $sql = "delete from ftxia_taoke_detail2 WHERE id=" . $list['id'];
//                \Yii::$app->db->createCommand($sql)->execute();
            }

            if (!empty($data)) {
                $result = Order::addOrder($data);
                echo date("m-d H:i", time()) . ">>>>CATCH-TB>>>>SUCESS>>>>" . $result . "\r\n";
            } else {
                echo date("m-d H:i", time()) . ">>>>CATCH-TB>>>>SUCESS>>>>KONG\r\n";
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
        $request->time = date("Ymd", time() - 60 * 60 * 24 * 0);
        $request->pageIndex = 1;
        $request->pageSize = 500;
        $response = $client->run($request);
        $json = $response['jingdong_UnionService_queryOrderList_responce']['result'];
        $arr = json_decode($json, true);

        $key = ['uid', 'type', 'product_id', 'pid', 'trade_id', 'product_num', 'product_price', 'order_status', 'rebate_status', 'order_type', 'divided_ratio', 'payment_price', 'commission_rate', 'estimated_effect', 'settlement_at', 'created_at', 'updated_at',
        ];
        $data = [];
        if (!empty($arr['data'])) {
            foreach ($arr['data'] as $k => $v) {
                $list = Order::findOne(['trade_id' => $v['orderId']]);
                if (!empty($list)) {
                    continue;
                }
                if (!empty($v['skuList'][0]['subUnionId'])) {
                    $user = User::findOne(['jd_pid' => $v['skuList'][0]['subUnionId']]);
                } else {
                    continue;
                }
                if (empty($user)) {
                    continue;
                }
//            $user = User::findOne(['uid' => 1]);
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
                $data[$k][] = (int)bcdiv($v['orderTime'], 1000);
                $data[$k][] = time();
            }
            if (!empty($data)) {
                $result = Order::addOrders($key, $data);
                if ($result > 0) {
                    echo date("m-d H:i", time()) . ">>>>CATCH-JD>>>>SUCCESS\r\n";
                } else {
                    echo date("m-d H:i", time()) . '>>>>CATCH-JD>>>>ERROR' . $result . "\r\n";
                }
            } else {
                echo date("m-d H:i", time()) . ">>>>CATCH-JD>>>>ERROR>>>>kong\r\n";
            }
        } else {
            echo date("m-d H:i", time()) . ">>>>CATCH-JD>>>>ERROR>>>>kong\r\n";
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
        $request->start_update_time = time() - 60 * 60 * 24;// * 18;
        $request->end_update_time = time();
        $request->page_size = 100;
        $request->page = 1;
        $response = $client->run($request);
//        print_r($response);
//        exit;
        $arr = $response['order_list_get_response']['order_list'];
        $key = ['uid', 'type', 'product_id', 'pid', 'trade_id', 'product_num', 'product_price', 'order_status', 'rebate_status', 'order_type', 'divided_ratio', 'payment_price', 'commission_rate', 'estimated_effect','estimated_revenue', 'settlement_at', 'created_at', 'updated_at', 'picUrl', 'title','order_time'
        ];
        $data = [];
        if (!empty($arr)) {
            foreach ($arr as $k => $v) {
                $list = Order::findOne(['trade_id' => $v['order_sn']]);
                $user = User::findOne(['pdd_pid' => $v['p_id']]);
                if (!empty($list)) {
                 //如果订单编号存在 修改订单状态
                    $test=\api\models\Order::find()->where(['trade_id' => $v['order_sn']])->one();
                    $test->order_status = $v['order_status'];//更新订单状态
                    if ($v['order_status'] != -1){
                        $fk=bcdiv($v['order_amount'], 100, 2);
                        $test->payment_price=bcdiv($v['order_amount'], 100, 2);//更新付款金额
                        $test->estimated_effect=$fk*(bcdiv($v['promotion_rate'], 1000, 2));//效果预估(佣金)
                        $test->commission_price = bcdiv($v['promotion_amount'], 100, 2);//佣金金额
                        $test->save();
                    }
                }else {
                if (empty($user)) {
                    //continue;//如果用户pid和订单pid不匹配 跳出循环
                    $data[$k][] = 0;
                }else{
                $data[$k][] = $user->uid; //uid
                    }
                $data[$k][] = 3;
                $data[$k][] = $v['goods_id'];
                $data[$k][] = $v['p_id'];
                $data[$k][] = $v['order_sn'];
                $data[$k][] = $v['goods_quantity'];
                $data[$k][] = bcdiv($v['goods_price'], 100, 2);
                    $data[$k][] = $v['order_status'];//订单状态
                    $data[$k][] = 1;//返佣状态
                    $data[$k][] = $v['type'];
                    $data[$k][] = $v['promotion_rate'];
                    $data[$k][] = bcdiv($v['order_amount'], 100, 2);//实际付款金额
                    $data[$k][] = bcdiv($v['promotion_rate'], 1000, 2);//佣金比率
                    $data[$k][] = bcdiv($v['promotion_amount'], 100, 2);//预估效果=>佣金金额
                    $data[$k][] = bcdiv($v['promotion_amount'], 100, 2);//预估收入=>佣金金额
                    $data[$k][] = $v['order_receive_time'];
                    $data[$k][] = $v['order_create_time'];
                    $data[$k][] = time();
                    $data[$k][] = $v['goods_thumbnail_url'];
                    $data[$k][] = $v['goods_name'];
                    $data[$k][] = date('Y-m-d H:i:s',$v['order_create_time']);//订单创建时间----下单时间
                }
            }

            if (!empty($data)) {
                $result = Order::addOrders($key, $data);
                if ($result > 0) {
                    echo date("m-d H:i", time()) . ">>>>CATCH-PDD>>>>SUCCESS\r\n";
                } else {
                    echo date("m-d H:i", time()) . '>>>>CATCH-PDD>>>>ERROR' . $result . "\r\n";
                }
            } else {
                echo date("m-d H:i", time()) . ">>>>CATCH-PDD>>>>ERROR>>>>kong\r\n";
            }
        } else {
            echo date("m-d H:i", time()) . ">>>>CATCH-PDD>>>>ERROR>>>>kong\r\n";
        }

        $this->yugu();
        $this->Orderends();
    }

    public function Xiangqing($vv)
    {
        $client = new JdClient();
        $request = new Xiangqing();
        $request->skuIds = '' . $vv['skuId'] . '';
        $result = $client->run($request);
        $res = json_decode($result['jd_union_open_goods_promotiongoodsinfo_query_response']['result'], true);
        return $res['data'][0]['imgUrl'];
    }

    /*
     *
     * 京东联盟订单
     * */
    private function JdlmOrder()
    {
        $client = new JdClient();
        $request = new Dingdan();
        for ($i=0;$i<100;$i++) {

            $time = date("YmdHi", time()) - $i;//当前时间
//            echo  $i.PHP_EOL;
//            echo  $time.PHP_EOL;

            $request->orderReq = [
                'pageNo' => 1,
                'pageSize' => 500,
                'type' => 1,//查询订单时间类型:1下单时间   2完成时间  3更新时间
                'time' => '' . $time . '',
            ];//查询订单时间
            $result = $client->run($request);
        print_r($result);
        exit;
            if (isset($result['errorResponse'])) {
                echo '执行异常';
            } else {
                $dingdan = $result['jd_union_open_order_query _response']['result'];
                $dingdan = json_decode($dingdan, true);

                if (!empty($dingdan['data'])) {
                    foreach ($dingdan['data'] as $key => $value) {
                        $sfcz = Order::findOne(['trade_id' => $value['orderId']]);//订单不存在添加
                        if (empty($sfcz)) {
                            $jd_dingdan = new Order;
                            $jd_dingdan->trade_id = $value['orderId'];//订单号
                            $jd_dingdan->order_time = date('Y-m-d H:i:s', substr($value['orderTime'], 0, strlen($value['orderTime']) - 3));//下单时间

                            foreach ($value['skuList'] as $kk => $vv) {
                                $user = User::findOne(['jd_pid' => '1000876334_' . $vv['siteId'] . '_' . $vv['positionId']]);
                                if (!empty($user)) {
                                    $jd_dingdan->uid = $user->uid;//用户ID
                                } else {
                                    $jd_dingdan->uid = 0;//用户ID
                                }
                                $jd_dingdan->product_id = $vv['skuId'];//商品ID
                                $jd_dingdan->type = 2;//商品类型
                                $jd_dingdan->payment_price = $vv['price'] * $vv['skuNum'];//付款金额
                                $jd_dingdan->estimated_effect = $vv['estimateFee'];//效果预估佣金金额
                                $jd_dingdan->product_price = $vv['price'];//商品单价
                                $jd_dingdan->title = $vv['skuName'];//商品标题
                                $jd_dingdan->product_num = $vv['skuNum'];//商品数量
                                $jd_dingdan->order_status = -1;//订单状态(订单生成-1待付款)
                                $img = $this->Xiangqing($vv);//查询商品图
                                $jd_dingdan->picUrl = $img;//商品图
                                $jd_dingdan->pid = $vv['positionId'];//pid最后一段
//                    $jd_dingdan->settlement_price=$vv;//结算金额
                                $jd_dingdan->commission_rate = $vv['commissionRate'] / 100;//佣金比率
                                $jd_dingdan->commission_price = $vv['estimateFee'];//结算佣金金额---->京东预估佣金
                                $jd_dingdan->divided_ratio = $vv['finalRate'];//分成比率
                            }

                            $jd_dingdan->save();
                            if (!$jd_dingdan->save()) {
                                self::say('error', '保存订单错误', $jd_dingdan->getErrors());
                            }
                        }
                    }
                }
            }
        }
        $this->yugu();
        $this->Orderends();

    }

    public function JdlmOrdergx()
    {
        $wfk = Order::find()->where(['type' => 2, 'order_status' => 11])->asArray()->all();//查询京东付款的订单

        foreach ($wfk as $key => $value) {
            $sjc =$value['settlement_at'];
            $client = new JdClient();
            $request = new Dingdan();
            $time = date("YmdHi", $sjc);//下单时间
//            print_r($time2);
//            exit;
            $request->orderReq = [
                'pageNo' => 1,
                'pageSize' => 500,
                'type' => 1,//查询订单时间类型:1下单时间   2完成时间  3更新时间
                'time' => '' . $time . '',//'201901061227'//下单前一分钟
            ];//查询订单时间
            $result = $client->run($request);
//            print_r($result);
//            exit;
            if (isset($result['errorResponse'])) {
                echo '执行异常';
            } else {
                $dingdan = $result['jd_union_open_order_query _response']['result'];
                $dingdan = json_decode($dingdan, true);

                if (!empty($dingdan['data'])) {
                    foreach ($dingdan['data'] as $key => $value) {
//                        print_r($value);
//                        exit;
                        if ($value['validCode']==16){//付款
                            foreach ($value['skuList'] as $kk=>$vv) {
                                $czgx = Order::findOne(['trade_id' => $value['orderId']]);//订单存在更新
                                $czgx->order_status = 1;//订单状态   和拼多多一致已付款
                                $czgx->save();
                            }
                        } elseif ($value['validCode']==17){//17京东完成订单
                            foreach ($value['skuList'] as $kk=>$vv) {
                                $czgx = Order::findOne(['trade_id' => $value['orderId']]);//订单存在更新
                                $czgx->order_status = 2;//订单状态   和拼多多一致已完成
                                $czgx->commission_price = $vv['actualFee'];//实际所得佣金
                                $czgx->save();
                            }
                        } elseif ($value['validCode']==18){//结算订单
                            foreach ($value['skuList'] as $kk=>$vv) {
                                $czgx = Order::findOne(['trade_id' => $value['orderId']]);//订单存在更新
                                $czgx->order_status = 5;//订单状态   和拼多多一致已结算
                                $czgx->save();
                            }
                        }else{
                            foreach ($value['skuList'] as $kk=>$vv) {
                                $czgx = Order::findOne(['trade_id' => $value['orderId']]);
                                $czgx->order_status = 4;//失效
                                $czgx->save();
                            }
                        }
                    }
                }
            }
        }
        $this->yugu();
        $this->Orderends();
    }

//本月结算订单返佣分销
    public function Orderends()
    {
        $aa = strtotime("-1 month");//前一个月的订单
        $bb = time();
        $order = Order::find()->where(['order_status' => [5], 'rebatestatus' => 0])->andWhere(['between', 'created_at', $aa, $bb])->asArray()->all();//查询该用户已结算订单未反佣订单
        $commissionset = commissionset::find()->asArray()->one();//设置佣金比率表
        $sum = 0;
        $sum2 = 0;
        $sum3 = 0;
        $aa = array();
        foreach ($order as $key => $value) {
            $detain = $value['commission_price'] * ($commissionset['detain'] / 100);//平台金额
            $sj = $value['commission_price'] - $detain;//三级共金额

            $user = \api\models\User::find()->where(['uid' => $value['uid']])->asArray()->one();
            if (!empty($user)) {
                if ($user['lv'] == 1) {
                    $threelevel = $sj * ($commissionset['zghy'] / 100);
                } elseif ($user['lv'] == 2) {
                    $threelevel = $sj * ($commissionset['zgdl'] / 100);
                }
            }

            $second = $sj * ($commissionset['second'] / 100);//二级金额
            $stair = $sj * ($commissionset['stair'] / 100);//一级金额
            if (!empty($user)) {
                $sum += $threelevel;//三级所有订单
            }
            $sum2 += $second;//二级所有订单
            $sum3 += $stair;//一级所有订单
            $aa[] = $value;

            if (!empty($order) && !empty($user)) {
                $test = Users::find()->where(['uid' => $value['uid']])->one();
                $settlement = Users::find()->where(['uid' => $value['uid']])->asArray()->one();
                $test->settlement = $sum + $settlement['settlement'];
                $test->save();//本用户(三级)本月佣金修改到数据库

                $invite_code = Users::find()->select(['invite_code'])->where(['uid' => $value['uid']])->asArray()->one();//当前用户 三级邀请码
                $generalize = Users::find()->where(['generalize' => $invite_code['invite_code']])->asArray()->one();//二级邀请码
                $test = Users::find()->where(['uid' => $generalize['uid']])->one();

                if (!empty($invite_code['invite_code'])) {//如果该用户的邀请码存在(二级存在)
                    $test->settlement = $sum2 + $generalize['settlement'];//返利的金额+本月自己的金额更新到数据库
                    $test->save();//二级的本月佣金修改到数据库
                }

                if (!empty($generalize['invite_code'])) {//(一级存在)
                    $generalize2 = Users::find()->where(['generalize' => $generalize['invite_code']])->asArray()->one();//一级邀请码
                    $test = Users::find()->where(['uid' => $generalize2['uid']])->one();
                    $test->settlement = $sum3 + $generalize2['settlement'];
                    $test->save();//一级本月佣金修改到数据库
                }
            }
            foreach ($aa as $key => $value) {
                $res = order::find()->where(['trade_id' => $value['trade_id']])->one();
                $res->rebatestatus = 1;
                $res->save();
            }
        }
    }

//本月付款订单预估佣金
    public function Yugu()
    {
        $aa = strtotime("-1 month");//前一个月的订单
        $bb = time();
        $order = Order::find()->where(['order_status' => [0, 1, 2, 3, 5], 'yugu' => 0])->andWhere(['between', 'created_at', $aa, $bb])->asArray()->all();//查询已付款订单未反佣订单
        $commissionset = commissionset::find()->asArray()->one();//设置佣金比率表

        $sum = 0;
        $sum2 = 0;
        $sum3 = 0;
        $aa = array();
        if (!empty($order)) {
            foreach ($order as $key => $value) {
                $detain = $value['estimated_effect'] * ($commissionset['detain'] / 100);//平台金额
                $sj = $value['estimated_effect'] - $detain;//三级共金额

            $user = \api\models\User::find()->where(['uid' => $value['uid']])->asArray()->one();//根据订单搜索 用户表

            if (!empty($user)) {//三级金额  PID  自己
                if ($user['lv'] == 1) {
                    $threelevel = $sj * ($commissionset['zghy'] / 100);
                } elseif ($user['lv'] == 2) {
                    $threelevel = $sj * ($commissionset['zgdl'] / 100);
                }
            }

            $second = $sj * ($commissionset['second'] / 100);//二级金额
            $stair = $sj * ($commissionset['stair'] / 100);//一级金额
            if (!empty($user)) {
                $sum += $threelevel;//三级所有付款订单预计佣金
            }
            $sum2 += $second;//二级所有付款订单预计佣金
            $sum3 += $stair;//一级所有付款订单预计佣金
            $aa[] = $value;

            if (!empty($order) && !empty($user)) {
                $test = Users::find()->where(['uid' => $value['uid']])->one();
                $settlement = Users::find()->where(['uid' => $value['uid']])->asArray()->one();
                $test->thisestimate = $sum + $settlement['thisestimate'];//本月预估
                $test->save();//本用户(三级)本月佣金修改到数据库

                $invite_code = Users::find()->select(['invite_code'])->where(['uid' => $value['uid']])->asArray()->one();//当前用户 三级邀请码
                $generalize = Users::find()->where(['generalize' => $invite_code['invite_code']])->asArray()->one();//二级邀请码
                $test = Users::find()->where(['uid' => $generalize['uid']])->one();

                if (!empty($invite_code['invite_code'])) {//如果该用户的邀请码存在(二级存在)
                    $test->thisestimate = $sum2 + $generalize['thisestimate'];//返利的金额+本月自己的金额更新到数据库
                    $test->save();//二级的本月佣金修改到数据库
                }

                if (!empty($generalize['invite_code'])) {//(一级存在)
                    $generalize2 = Users::find()->where(['generalize' => $generalize['invite_code']])->asArray()->one();//一级邀请码
                    $test = Users::find()->where(['uid' => $generalize2['uid']])->one();
                    $test->thisestimate = $sum3 + $generalize2['thisestimate'];
                    $test->save();//一级本月佣金修改到数据库
                }
            }
            foreach ($aa as $kk => $vv) {
                $res = order::find()->where(['trade_id' => $vv['trade_id']])->one();
                $res->yugu = 1;
                $res->save();
            }
        }
    }
    }

    /**
     * 日志记录
     * @param string $category
     * @param $msg
     * @param array $data
     */
    public static function say($category = 'INFO', $msg, $data = [])
    {
        $log = date('Y-m-d H:i:s') . " [{$category}] " . $msg . PHP_EOL;
        if (!empty($data)) {
            $log .= 'DATA:' . PHP_EOL;
            $log .= var_export($data, true) . PHP_EOL;
        }
        echo $log;
    }

    /**
     * 淘宝返利订单抓取
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \yii\db\Exception
     */
//    private function TbkOrder()
//    {
//        $startTime = date('Y-m-d H:i:s', time() - 60 * 5);
//        $endTime = date('Y-m-d H:i:s', time());
//        $cookie = Config::getConfig('ALIMAMA_COOKIE');
//        $time = urlencode("startTime={$startTime}&endTime={$endTime}");
//        $url = "http://pub.alimama.com/report/getTbkPaymentDetails.json?DownloadID=DOWNLOAD_REPORT_INCOME_NEW&queryType=1&payStatus=&startTime=2018-06-11%2000:00:00&endTime=2018-06-11%2023:00:00";
//        // curl下载文件
//        $ch = curl_init();
//        $timeout = 5;
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
//        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
//        $xls = curl_exec($ch);
//        curl_close($ch);
//        $file = \Yii::getAlias('@public') . '/static/tb' . date('Y-m-d', time()) . '.xls';
//        // 保存文件到制定路径
//        file_put_contents($file, $xls);
//        $excelData = $this->importExecl($file);
//        $key = ['uid', 'type', 'product_id', 'pid', 'trade_id', 'product_num', 'product_price', 'order_status', 'rebate_status', 'order_type', 'divided_ratio', 'payment_price', 'commission_rate', 'commission_price', 'settlement_at', 'created_at', 'updated_at', 'estimated_revenue',
//        ];
//        $data = [];
//        foreach ($excelData as $k => $v) {
//            if ($k == 1) {
//                continue;
//            }
//            $list = Order::findOne(['trade_id' => $v['Y']]);
//            if (!empty($list)) {
//                continue;
//            }
//            $user = User::find()->where("alimm_pid like  '%{$v['AC']}' ")->asArray()->one();
//            if (empty($user)) {
//                continue;
//            }
//            $data[$k][] = $user['uid']; //uid
//            $data[$k][] = 1; //uid
//            $data[$k][] = $v['D'];
//            $data[$k][] = $v['AC'];
//            $data[$k][] = $v['Y'];
//            $data[$k][] = $v['G'];
//            $data[$k][] = $v['H'];
//            $data[$k][] = self::ORDER_STATUS["{$v['I']}"];
//            $data[$k][] = 1;
//            $data[$k][] = self::ORDER_TYPE["{$v['J']}"];
//            $data[$k][] = rtrim($v['L'], '%');
//            $data[$k][] = $v['M'];
//            $data[$k][] = rtrim($v['R'], '%');
//            $data[$k][] = $v['S'];
//            $data[$k][] = $v['Q'];
//            $data[$k][] = time();
//            $data[$k][] = time();
//            $data[$k][] = $v['P'];
//        }
//        if (!empty($data)) {
//            $result = Order::addOrders($key, $data);
//            if ($result > 0) {
//                echo "TB>>>>SUCCESS\r\n";
//            } else {
//                echo 'TB>>>>ERROR' . $result . "\r\n";
//            }
//        } else {
//            echo "TB>>>>ERROR>>>>kong\r\n";
//        }
//        unlink($file); //删除文件
//    }
//
//    /**
//     * @param string $file
//     * @param int $sheet
//     * @return array
//     * @throws \PHPExcel_Exception
//     * @throws \PHPExcel_Reader_Exception
//     */
//    private function importExecl($file = '', $sheet = 0)
//    {
//        $file = iconv("utf-8", "gb2312", $file);   //转码
//        if (empty($file) OR !file_exists($file)) {
//            die('file not exists!');
//        }
////        include('PHPExcel.php');  //引入PHP EXCEL类
//        $objRead = new PHPExcel_Reader_Excel2007();   //建立reader对象
//        if (!$objRead->canRead($file)) {
//            $objRead = new PHPExcel_Reader_Excel5();
//            if (!$objRead->canRead($file)) {
//                die('No Excel!');
//            }
//        }
//
//        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
//
//        $obj = $objRead->load($file);  //建立excel对象
//        $currSheet = $obj->getSheet($sheet);   //获取指定的sheet表
//        $columnH = $currSheet->getHighestColumn();   //取得最大的列号
//        $columnCnt = array_search($columnH, $cellName);
//        $rowCnt = $currSheet->getHighestRow();   //获取总行数
//
//        $data = array();
//        for ($_row = 1; $_row <= $rowCnt; $_row++) {  //读取内容
//            for ($_column = 0; $_column <= $columnCnt; $_column++) {
//                $cellId = $cellName[$_column] . $_row;
//                $cellValue = $currSheet->getCell($cellId)->getValue();
//                //$cellValue = $currSheet->getCell($cellId)->getCalculatedValue();  #获取公式计算的值
//                if ($cellValue instanceof PHPExcel_RichText) {   //富文本转换字符串
//                    $cellValue = $cellValue->__toString();
//                }
//                $data[$_row][$cellName[$_column]] = $cellValue;
//            }
//        }
//
//        // $total_line = $currSheet->getHighestRow();
//        // $total_column = $currSheet->getHighestColumn();
//
//        // for ($row = 1; $row <= $total_line; $row++) {
//        //     $data = array();
//        //     for ($column = 'A'; $column <= $total_column; $column++) {
//        //         $data[$row][$column] = trim($currSheet->getCell($column.$row) -> getValue());
//        //     }
//        // }
//        return $data;
//    }

}