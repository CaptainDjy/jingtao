<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/5/31
 * Time: 11:22
 */

namespace console\controllers;


use common\components\robots\DataokeRobot;
use common\components\robots\HdkRobot;
use common\components\robots\DdkRobot;
use common\components\robots\MjdRobot;
use common\components\robots\QtkRobot;
use common\components\robots\TkzsRobot;
use common\components\robots\JdRobot;
use common\components\robots\MiaoRobot;
use common\models\Goods;
use yii\console\Controller;
use yii\httpclient\Client;
use common\components\tblm\top\TopClient;
use common\models\Config;
use common\components\tblm\top\request\TbkDgMaterialOptionalRequest;

class GoodsController extends Controller
{
    private $map;
    /**
     * @throws \yii\base\Exception
     * @throws \yii\base\UserException
     */
    public function actionIndex()
    {

            $this->DelGoods();//删除过期商品
            $this->RobotHdk();//好单库采集(商品列表API)
            $this->RobotTb();//大淘客采集
            $this->RobotPdd();//拼多多采集
//        $this->RobotJd();//京东采集

    }

    /**
     * 删除过期商品
     */
    public function DelGoods()
    {
        $list = Goods::find()->select('id')->where(['<=', 'end_time', time()])->orWhere(['and', ['coupon_remained' => 0], ['!=', 'type', 21]])->orWhere('coupon_end_at <= ' . time())->column();
        if (Goods::deleteAll(['id' => $list])) {
            echo "删除成功\r\n";
        } else {
            echo "删除失败\r\n";
        }
    }

    /**
     * 京东商品
     * @throws \yii\base\Exception
     * @throws \yii\base\UserException
     */
    public function RobotJd()
    {
        $robots = new JdRobot();
        $result = false;
        while ($robots->pageNum < 200) {
            $robots->pageNum = $robots->pageNum + 1;
            $result = $robots->run();
        }
        if ($result === true) {
            echo "GOODS-Jd>>>>SUCCESS>>>>\r\n";
        } else {
            echo "GOODS-Jd>>>>ERROR>>>>" . $result . "\r\n";
        }
    }

    /**
     * 京东同步商品
     * @throws \yii\base\Exception
     * @throws \yii\base\UserException
     */
    public function RobotJtt($data = [])
    {
        $client = new Client();
        for ($i = 1; $i <= 50; $i++) {
            $response = $client
                ->createRequest()
                ->setMethod('post')
                ->setUrl('http://japi.jingtuitui.com/api/get_goods_list')
                ->setData([
                    'appid' => '1811051106081274',
                    'appkey' => '761239be526706983d026d4f272b4633',
                    'page' => $i,
                    'num' => 100,
                ])
                ->send();
//            print_r($response);
//            exit;
            $goods = json_decode($response->content, true)['result']['data'];
            foreach ($goods as $good) {
                $oldModel = Goods::find()->where(['type' => [21], 'origin_id' => (string)$good['goods_id']])->one();
                if (!empty($oldModel)) {
                    $model = $oldModel;
                } else {
                    $model = new Goods();
                    $model->loadDefaultValues();
                }
                $model->from_cid = $good['goods_type'];
                $model->cid = $good['goods_type'];
                $model->type = Goods::TYPE_ID['jd'];
                $model->origin_id = (string)$good['goods_id'];
                $model->from_id = (string)$good['goods_id'];
                $model->title = $good['goods_name'];
                $model->thumb = $good['goods_img'];
                $model->origin_price = $good['goods_price'];
                $model->coupon_price = $good['coupon_price'];
                $model->commission_rate = $good['commission'];

                $model->coupon_start_at = bcdiv($good['discount_start'], 1000, 0);
                $model->coupon_end_at = bcdiv($good['discount_end'], 1000, 0);
                $model->coupon_money = $good['discount_price'];

                $model->coupon_link = $good['discount_link'];

                $model->commission_money = bcmul($model->coupon_price, bcdiv($model->commission_rate, 100, 2), 2);
                $model->start_time = $model->coupon_start_at;
                $model->end_time = $model->coupon_end_at ?: TIMESTAMP + 604800;//无结束时间默认一周
//                print_r($model->end_time);
                $model->save();
            }
        }
    }

    /**
     * 拼多多
     * @throws \yii\base\Exception
     * @throws \yii\base\UserException
     */
    public function RobotPdd()
    {
        $robots = new DdkRobot();
        $result = false;
//        $opt_id=$robots->getLotal();
        while ($robots->pageNum < 100) {
            $robots->pageNum = $robots->pageNum + 1;
            $result = $robots->run('total', ['opt_id' => 0]);
        }
        if ($result === true) {
            echo "GOODS-Pdd>>>>SUCCESS>>>>\r\n";
        } else {
            echo 'GOODS-Pdd>>>>ERROR>>>>' . $result . "\r\n";
        }
    }

    /**
     * 大淘客 淘宝
     * @throws \yii\base\UserException
     * @throws \yii\httpclient\Exception
     */
    public function RobotTb()
    {
        $robots = new DataokeRobot();
        $result = false;
        while ($robots->pageNum < 100) {
            $robots->pageNum = $robots->pageNum + 1;
            $result = $robots->run();
        }
        if ($result === true) {
            echo "GOODS-Tb>>>>SUCCESS>>>>\r\n";
        } else {
            echo 'GOODS-Tb>>>>ERROR>>>>' . $result . "\r\n";
        }
    }

    /**
     * 淘客助手 淘宝
     * @throws \yii\base\UserException
     * @throws \yii\httpclient\Exception
     */
    public function RobotTkzs()
    {
        $robots = new TkzsRobot();
        $result = false;
        while ($robots->pageNum < 100) {
            $robots->pageNum = $robots->pageNum + 1;
            $result = $robots->run();
        }
        if ($result === true) {
            echo "GOODS-Tb>>>>SUCCESS>>>>\r\n";
        } else {
            echo 'GOODS-Tb>>>>ERROR>>>>' . $result . "\r\n";
        }
    }
    /*
     * 喵有券京东采集
     * */
    public function RobotMjd(){
        $robots = new MjdRobot();
        $result = false;
        while ($robots->pageNum < 100) {
            $robots->pageNum = $robots->pageNum + 1;
            $result = $robots->run();
        }
        if ($result === true) {
            echo "GOODS-Tb>>>>SUCCESS>>>>\r\n";
        } else {
            echo 'GOODS-Tb>>>>ERROR>>>>' . $result . "\r\n";
        }
    }

    /**
     * 好单库 淘宝
     * @throws \yii\base\UserException
     * @throws \yii\httpclient\Exception
     */
    public function RobotHdk(){
        $robots = new HdkRobot();
        $result = false;
        while ($robots->pageNum < 100) {
            $robots->pageNum = $robots->pageNum + 1;
            $result = $robots->run();
        }
        if ($result === true) {
            echo "GOODS-Tb>>>>SUCCESS>>>>\r\n";
        } else {
            echo 'GOODS-Tb>>>>ERROR>>>>' . (string)$result . "\r\n";
        }
    }

    /**
     * 轻淘客 淘宝
     * @throws \yii\base\UserException
     * @throws \yii\httpclient\Exception
     */
    public function RobotQtk(){
        $robots = new QtkRobot();
        $result = false;
        while ($robots->pageNum < 100) {
            $robots->pageNum = $robots->pageNum + 1;
            $result = $robots->run();
        }
        if ($result === true) {
            echo "GOODS-Tb>>>>SUCCESS>>>>\r\n";
        } else {
            echo 'GOODS-Tb>>>>ERROR>>>>' . (string)$result . "\r\n";
        }
    }

    /**
     * 通用物料 淘宝
     * @throws \yii\base\UserException
     * @throws \yii\httpclient\Exception
     */
    public function RobotWuliao(){

        $c = new TopClient;
        $c->appkey = Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkDgMaterialOptionalRequest();
//        $req->setCat("16");
        $req->setQ("男装");
        $req->setpageSize('100');
//        $req->setMaterialId("2836");
        $req->setHasCoupon("true");
//        $req->setIp("13.2.33.4");
        $req->setAdzoneId(Config::getConfig('TAOBAO_APPPID'));
        $resp = $c->execute($req);
        $result=json_decode(json_encode($resp),true);
//        print_r($result);
//        print_r(count($result['result_list']['map_data']));
//        exit;

        foreach ($result['result_list']['map_data'] as $key=>$data) {
            $oldModel = Goods::find()->where(['type' => [11], 'origin_id' => (string)$data['num_iid']])->one();
//            print_r(count($oldModel));
//            exit;
            if (!empty($oldModel)) {
                $model = $oldModel;
            } else {
                $model = new Goods();
                $model->loadDefaultValues();
            }
//            $model = new Goods();
//            print_r($data);
//            exit;
            $model->type = 11;
            $model->cid = 1;//所属分类
            $model->from_cid = $data['level_one_category_id'];//来源分类
//            $model->type = $data['is_tmall'] == '1' ? Goods::TYPE_ID['tmall'] : Goods::TYPE_ID['taobao'];//商品类型
            $model->origin_id = (string)$data['num_iid'];//原始ID
            $model->origin_price = (string)$data['reserve_price'];//原价
            $model->from_id = (string)$data['num_iid'];//ID
            $model->title = $data['title'];//标题
            $model->sub_title = $data['short_title'];//短标题
            $model->thumb = $data['pict_url'];//商品图片
//            $model->coupon_price = $data['goods_price'] - $data['coupon_price'];//优惠券后价格
            $model->coupon_price = $data['zk_final_price'];//优惠券后价格
//        $model->coupon_id = $data['Quan_id'];
            $a = $data['coupon_info'];
            preg_match_all('/\d+/', $a, $arr);
//            print_r($arr[0][2]);
//            exit;
            $model->coupon_money = $arr[0][2];//优惠券金额
//        $model->coupon_rate = bcdiv($model->coupon_price * 10, $model->origin_price, 1);
            $model->coupon_total = $data['coupon_total_count'];//总量
            $model->coupon_remained = $data['coupon_remain_count'];//券剩余
//            $model->coupon_received = $data['coupon_over'];//已领券数量
            $model->coupon_start_at = strtotime($data['coupon_start_time']);//优惠券开始时间
            $model->coupon_end_at = strtotime($data['coupon_end_time']);//优惠券结束时间
            $model->start_time = strtotime($data['coupon_start_time']);
            $model->end_time = strtotime($data['coupon_end_time']);
            $model->coupon_link = $data['url'];
            $model->commission_money = $data['commission_rate'] / 10000;//佣金
            $model->plan_type = empty($data['Jihua_link']) ? 0 : 1;
//            print_r($model->plan_type);
            if (!$model->save()) {
                print_r($model->error);
            }
//            return $model;
        }

    }

    /**
     * 喵有券 淘宝抓取
     * @throws \yii\base\UserException
     * @throws \yii\httpclient\Exception
     */
    public function RobotMiao()
    {
        $robots = new MiaoRobot();
        $result = false;
        while ($robots->pageNum < 20) {
            $robots->pageNum = $robots->pageNum + 1;
            $result = $robots->run();
        }
        if ($result === false) {
            echo "GOODS-Miao>>>>SUCCESS>>>>\r\n";
        } else {
            echo 'GOODS-Miao>>>>ERROR>>>>' . $result . "\r\n";
        }
    }

}