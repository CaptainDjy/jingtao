<?php

namespace common\components\robots;

use common\components\miao\MiaoClient;
use common\components\miao\taobao\requests\GetGoodsCouponUrl;
use common\components\miao\taobao\requests\GetTkMaterial;
use common\models\Config;
use common\models\Goods;
use common\models\RobotDataoke;
use yii\base\UserException;
use yii\console\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class MiaoRobot extends Robot
{
    /**
     * 喵有券
     * @var string
     */
    public $apikey ;

    /**
     * 采集特定分类
     * @var int
     */
    private $cid = 0;

    /**
     * 来源和入库分类对应
     * @var array
     */
    private $map;

    /*
    * 接口地址
    */
    public $API_URL = 'https://api.open.21ds.cn/';

    public $keywords;
    public $cat = '';
    public $sort = 'tk_rate_des';
    public $hascoupon = true ;
    public $platform = 2;//链接形式：1：PC，2：无线，默认：１

    public function init()
    {
        $this->apikey = Config::getConfig('MIAO_APKEY');
        $this->map = RobotDataoke::map();
    }

    /**
     * 主运行函数
     * @param string $type
     * @return array|bool
     * @throws UserException
     * @throws \yii\httpclient\Exception
     */
    public function run($type = 'taobao')
    {
        $data = [];
        switch ($type) {
            case 'taobao':
                $data = $this->getTaobao();
                break;
            default :
                $data = $this->getTaobao();
                break;
        }
        return $data;
    }

    /**
     * 获取淘宝数据入库
     * @throws UserException
     * @throws \yii\httpclient\Exception
     */
    private function getTaobao(){

        $default_pid = Config::getConfig('MIAO_PID');
        $pidArr = explode('_',$default_pid);
        if (count($pidArr) < 4){
            throw new Exception('默认PID有误：'.$default_pid);
        }

        $client = new MiaoClient();
        $request = new GetTkMaterial();
        $request->adzoneid = $pidArr[3];
        $request->siteid = $pidArr[2];
        $request->tbname = Config::getConfig('MIAO_TBNAME');
        $request->hascoupon = 'true';
        $request->platform = $this->platform;

        $request->pageno = $this->pageNum;
        $request->pagesize = 100;
        $request->keyword = $this->keywords;
        $request->sort = $this->sort;
        $request->cat = $this->cat;
        $response = $client->run($request);
        $res = Json::decode($response,true);

        if ($res['code'] != 200){
            if ($res['sub_code'] == 'isp.special-campaign-error'){
                return $this->getTaobao();
            }
            throw new Exception('喵有券采集商品错误：'.$response);
        }
        if (empty($res['data'])){
            return false;
        }

        foreach ($res['data'] as $data){
            //  高佣转链
            //$client = new MiaoClient();
            $request = new GetGoodsCouponUrl();
            $request->tbname = Config::getConfig('MIAO_TBNAME');
            $request->itemid = $data['num_iid'];
            $request->pid = $default_pid;
            $response = $client->run($request);
            $tmp = Json::decode($response,true);
            if ($tmp['code'] != 200){
                throw new Exception('喵有券高佣转链错误：'.$response);
            }
            $arr = $tmp['result']['data'];
            $data['Commission'] = $arr['max_commission_rate'];
            $data['category_id'] = $arr['category_id'];

            $model = $this->convertGoodsModel($data);
            if ($model->isNewRecord) {
                $this->num++;
            }

            try{
                $model->save();
            }catch (\Exception $e){
                var_dump($model->error);
                var_dump($e->getMessage());
                var_dump($data);die();
            }

        }
        return true;
    }


    /**
     * 转化数据为商品模型
     * @param array $data
     * @return Goods
     */
    private function convertGoodsModel($data = [])
    {
        $oldModel = Goods::find()->where(['type' => [11, 12], 'origin_id' => (string)$data['num_iid']])->one();
        if (!empty($oldModel)) {
            $model = $oldModel;
        } else {
            $model = new Goods();
            $model->loadDefaultValues();
        }

        //  商品分类
        $model->cid = ArrayHelper::getValue($this->map, $data['category_id'], 0);
        $model->from_cid = $data['category_id'];
        $model->type = isset($data['IsTmall'])&&$data['IsTmall'] == '1' ? Goods::TYPE_ID['tmall'] : Goods::TYPE_ID['taobao'];
        $model->origin_id = (string)$data['num_iid'];

        $model->from_id = (string)$data['category_id'];

        $model->title = $data['title'];
        $model->sub_title = empty($data['short_title']) ? $data['title'] : $data['short_title'];
        $model->thumb = $data['pict_url'];
        $model->origin_price = $data['reserve_price'];
        $model->coupon_price = $data['zk_final_price'];



        preg_match_all('/(.*减)(\d+)(元)/', $data['coupon_info'], $matches);
        $model->coupon_money = empty($matches[2]) ? 0 : $matches[2][0];unset($matches);
        $model->coupon_id = empty($data['coupon_id']) ? '' : $data['coupon_id'];

        $model->coupon_rate = bcdiv($model->coupon_price * 10, $model->origin_price, 1);
        $model->coupon_total = $data['coupon_total_count'];
        $model->coupon_remained = $data['coupon_remain_count'];
        $model->coupon_received = 0;

        $model->coupon_end_at = isset($data['coupon_end_time']) ? strtotime($data['coupon_end_time']) : 0;
        $model->coupon_link =isset($data['coupon_share_url']) ? $data['coupon_share_url'] : '';
        //$model->coupon_link = 'https://uland.taobao.com/coupon/edetail?activityId=' . $data['coupon_id'] . '&pid=XXXXXX&itemId=' . $data['num_iid'] . '&src=cd_cdll';
        $model->coupon_short_link = isset($data['coupon_share_url']) ? $data['coupon_share_url'] : '';
        $model->coupon_condition = empty($data['coupon_info']) ? '' : $data['coupon_info'];

        //$model->commission_money = $data['reserve_price'] * ($data['Commission'] / 100);
        $model->commission_rate = $data['commission_rate'];
        $model->commission_rate_plan = 0;//$data['commission_type'];
        $model->commission_rate_queqiao = 0;
       /* $model->plan_link = $data['Jihua_link'];
        $model->plan_status = $data['Jihua_shenhe'];
        $model->plan_type = empty($data['Jihua_link']) ? 0 : 1;*/

        $model->sales_num = $data['volume'];
        if (isset($data['Introduce'])) {
            $model->description = $data['Introduce'];
        } else {
            $model->description = $model->title . '，现价只需要' . $model->origin_price . '元，领券后下单还可优惠' . $model->coupon_money . '元，赶紧抢购吧！';
        }

        // $model->keywords = $data['D_title'];
        // $model->info = $data['D_title'];
        $model->seller_id = $data['seller_id'];
        $model->seller_nickname = empty($data['shop_title']) ? '' : $data['shop_title'];
        $model->start_time = TIMESTAMP;
        //$model->end_time = strtotime($data['Quan_time']);
        $model->end_time = $model->coupon_end_at;
        $model->commission_money = bcmul($model->coupon_price, bcdiv($model->commission_rate, 100, 2), 2);

        return $model;
    }

}
