<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/4
 * Time: 17:58
 */

namespace api\modules\amoy\controllers;

use api\models\Order;
use backend\models\DistributionConfig;
use common\components\jd\JdClient;
use common\components\jd\requests\ServicePromotionAppGetcode;
use common\components\jd\requests\ServicePromotionGoodsInfo;
use common\components\miao\MiaoClient;
use common\components\miao\taobao\requests\DecodeTklToID;
use common\components\miao\taobao\requests\GetGoodsCouponUrl;
use common\components\miao\taobao\requests\GetGoodsInfo;
use common\components\miao\taobao\requests\GetTkMaterial;
use common\components\pdd\PddClient;
use common\components\pdd\requests\DdkGoodsSearchRequest;
use common\components\taobao\requests\TbkItemConvertRequest;
use common\components\tblm\top\request\TbkItemInfoGetRequest;
use common\components\taobao\TaobaoClient;
use common\helpers\Utils;
use common\models\Announcement;
use common\models\Article;
use common\models\Biz;
use common\models\Collection;
use common\models\Config;
use common\models\Goods;
use common\models\GoodsCategory;
use common\models\Nav;
use common\models\RobotDdk;
use common\models\RobotJd;
use common\models\User;
use Yii;
use common\models\Log;
use yii\helpers\Json;
use yii\httpclient\Client;
use common\components\tblm\top\TopClient;
use common\components\tblm\top\request\TbkItemGetRequest;
use common\components\taobao\requests\TbkItemGuessLike;
use common\components\tblm\top\request\TbkDgMaterialOptionalRequest;
use common\components\tblm\top\request\TbkTpwdCreateRequest;
use common\behaviors\HttpTokenAuth;
class TaobaoBuyController extends ControllerBase
{
    public function actionNothing()
    {
        return $this->responseJson( 1,'缺省路径','amoy/taobao-buy/nothing');
    }

//    public function behaviors()
//    {
//        if (\Yii::$app->request->isOptions) {
//            die();
//        }
//        $behaviors = parent::behaviors();
//        $behaviors['authenticator'] = [
//            'class' => HttpTokenAuth::class,
//            'optional' => ['create-share']
//        ];
//        return $behaviors;
//    }
    /**
     * 软件首页
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isOptions) {
            die();
        }
        $page = \Yii::$app->request->post('page');
        $type = \Yii::$app->request->post('type');
        $cache = \Yii::$app->cache;
        $tmp = $cache->get('cache_data_key');

        $indexList = $tmp ? Json::decode($tmp, true) : '';//空
        if (empty($indexList)) {
            $indexList = [
                'order' => 1,
                'min_volume' => 1,
                'max_volume' => 9999999,
                'min_rebate' => 10,
                'max_rebate' => 90,
                'min_price' => 1,
                'max_price' => 9999,
                'min_money' => 1,
                'max_money' => 9999,
            ];
        }
        if ($indexList['order'] == 2) {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        if ($type == 1) {
            $conn = "type in (11,12) ";//and coupon_end_at>" . time();  TODO
        } else {
            $conn = "type=" . intval($type) . ' and coupon_end_at>' . time();
        }
        $token = \Yii::$app->request->headers['token'];
        $string = base64_decode($token);
        $uid = json_decode($string, true)['uid'];
        $user = User::findOne(['uid' => $uid]);
        if (empty($user)) {
            $lv = 0;
        } else {
            $lv = $user->lv;
        }
        $redis = \Yii::$app->get('redis');
        $key = md5("Index" . $page . $type . $lv);
        if (false) {
            /*try{
                $info = Json::decode($val, true);
            }catch (\InvalidArgumentException $invalidArgumentException){
                return $this->responseJson(1,'','商品列表json解析失败');
            }
            return $this->responseJson(0, $info, '返回首页数据成功');*/
        } else {
            $goodInfo = Goods::find()
                ->select("coupon_price,origin_price,type,title,sales_num as volume,coupon_money,commission_money,thumb,origin_id")
                ->where(['status' => 1])->andWhere($conn)
                ->andWhere(['between', 'coupon_price', $indexList['min_price'], $indexList['max_price']])
                ->andWhere(['between', 'commission_rate', $indexList['min_rebate'], $indexList['max_rebate']])
                ->andWhere(['between', 'sales_num', $indexList['min_volume'], $indexList['max_volume']])
                ->andWhere(['between', 'coupon_money', $indexList['min_money'], $indexList['max_money']])
                ->orderBy("RAND(),sales_num {$order},commission_rate {$order},coupon_money {$order}")
                ->offset(($page - 1) * 6)->limit(6)
                ->asArray()->all();
            $selfcomm = DistributionConfig::getAll('index')['platform'] * 0.01; //  平台扣留百分比
            $ratio = bcsub(1, $selfcomm, 2) * (DistributionConfig::getAll('partner')['selfcomm'][$lv] * 0.01);
            foreach ($goodInfo as $info => &$goods) {
                if ($goods['volume'] > 10000) {
                    $goods['volume'] = intval(($goods['volume'] / 10000)) . '万+';
                }
                $goods['ratio_price'] = Utils::getTwoPrice(bcmul($goods['commission_money'], $ratio, 2), 2);
                $goods['commission_money'] = $goods['ratio_price'];
            }
//            shuffle($goodInfo);
            if (!empty($goodInfo)) {
                $info = Json::encode($goodInfo);
                $redis->set($key, $info);
                $redis->expire($key, 60 * 60 * 2);
                return $this->responseJson(0, $goodInfo, '返回首页数据成功');
            } else {
                return $this->responseJson(1, '', '查询数据为空');
            }
        }
    }


    /**
     * 获取分类
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCategory()
    {
        $redis = \Yii::$app->get('redis');
        $key = md5("GetCategory");
        if ($val = $redis->get($key)) {
            $info = Json::decode($val, true);
            return $this->responseJson(0, $info, '返回首页数据成功');
        } else {
            $goodCategory = GoodsCategory::find()->asArray()->all();
            if (!empty($goodCategory)) {
                foreach ($goodCategory as &$goods) {
                    if (!empty($goods['img'])) {
                        $goods['img'] = Utils::toMedia($goods['img']);
                    }
                }
                $info = Json::encode($goodCategory);
                $redis->set($key, $info);
                $redis->expire($key, 60);
                return $this->responseJson(0, $goodCategory, '返回首页数据成功');
            } else {
                return $this->responseJson(1, '', '返回数据为空!');
            }
        }
    }

    //淘宝商品查询数据库
    public function actionDemand(){
        if (\Yii::$app->request->isOptions) {
            die();
        }
        $q = \Yii::$app->request->post('keyword');//搜索关键字

        $page = empty(\Yii::$app->request->post('page')) ? 1 : \Yii::$app->request->post('page');
        if (empty($q)) {
            return $this->responseJson(101, '', '查询关键字为空');
        }

        $list = Goods::find()
                ->where(['like', 'title', $q])
                ->orWhere(['like', 'sub_title', $q])
                ->andWhere('type in (11,12)')
                ->offset(($page - 1) * 10)->limit(10)
                ->asArray()->all();

        if (empty($list)) {
            return $this->responseJson(102, [], '查询数据为空');
        }

            foreach ($list as $info => $goods) {
                $arr[$info] = [
                    'origin_id' => $goods['origin_id'],
                    'title' => $goods['title'],
                    'thumb' => $goods['thumb'],
                    'small_images' => '',
                    'origin_price' => $goods['origin_price'],//原价
                    'coupon_price' => $goods['coupon_price'],//券后价
                    'coupon_money' => intval($goods['coupon_money']),//优惠券
                    'volume' => $goods['sales_num'],
                    'type' => $goods['type'],
                    'start_time'=>$goods['start_time'],
                    'commission_money' => $goods['commission_money'],//佣金
                    'coupon_link'=>$goods['coupon_link']//优惠券地址
                ];
            }
            return $this->responseJson(200, $arr, '查询商品数据');
        }


    /**
     * 淘宝客商品查询、类目
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionGetItem()
    {
        if (\Yii::$app->request->isOptions) {
            die();
        }
        $q = \Yii::$app->request->post('keyword');//搜索关键字
        $cate = \Yii::$app->request->post('cate');//搜索分类
        $sort = \Yii::$app->request->post('sort');//排序方式
        $page = empty(\Yii::$app->request->post('page')) ? 1 : \Yii::$app->request->post('page');
        if (empty($q)) {
            return $this->responseJson(1, '', '查询关键字为空');
        }
        //if (!in_array($sort,Goods::ORDER_TB)){
        if (!isset(Goods::ORDER_TB[$sort])){
            return $this->responseJson(1,'','当前排序为空'.$sort);
        }
        if (!empty($cate)) {
            $list = Goods::find()
                ->where(['cid' => intval($cate)])
                ->andWhere('type in (11,12)')
                ->orderBy(Goods::ORDER_TB[$sort])
                ->offset(($page - 1) * 6)->limit(6)
                ->asArray()->all();
        } else {
            $list = Goods::find()
                ->where(['like', 'title', $q])
                ->orWhere(['like', 'sub_title', $q])
                ->andWhere('type in (11,12)')
                ->orderBy(Goods::ORDER_TB[$sort])
                ->offset(($page - 1) * 10)->limit(10)
                ->asArray()->all();
        }
        if (empty($list)) {
            return $this->responseJson(1, [], '查询数据为空');
        } else {
            $token = \Yii::$app->request->headers['token'];
            $string = base64_decode($token);
            $uid = json_decode($string, true)['uid'];
            $user = User::findOne(['uid' => $uid]);
            if (empty($user)) {
                $lv = 0;
            } else {
                $lv = $user->lv;
            }
            $selfcomm = DistributionConfig::getAll('index')['platform'] * 0.01;
            $ratio = bcsub(1, $selfcomm, 2) * DistributionConfig::getAll('partner')['selfcomm'][$lv] * 0.01;
            $arr = [];
            foreach ($list as $info => $goods) {
                $arr[$info] = [
                    'origin_id' => $goods['origin_id'],
                    'title' => $goods['title'],
                    'thumb' => $goods['thumb'],
                    'small_images' => '',
                    'origin_price' => $goods['origin_price'],//原价
                    'coupon_price' => $goods['coupon_price'],//券后价
                    'coupon_money' => intval($goods['coupon_money']),//优惠券
                    'volume' => $goods['sales_num'],
                    'type' => $goods['type'],
                    'start_time'=>$goods['start_time'],
                    'commission_money' => sprintf("%.2f", $goods['commission_money'] * $ratio),
                ];
            }
            return $this->responseJson(200, $arr, '查询商品数据');
        }
    }

//淘宝猜你喜欢商品信息（权限不足）
    public function actionGoodindex(){
        $c = new TaobaoClient();
        $req = new TbkItemGuessLike();
        $req->adzone_id="123";
        $req->os="ios";
        $req->ip="106.11.34.15";
        $req->ua="Mozilla/5.0";
        $req->net="wifi";
        $resp = $c->run($req);
        return $resp;

    }

//淘宝商品查询(搜索)接口测试  无佣金优惠券
    public function actionGood(){
        $c = new TopClient;
        $c->appkey = Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkItemGetRequest();
        $q = \Yii::$app->request->post('keyword');//搜索关键字
        $page=\Yii::$app->request->post('page');//页数
        if (empty($q)){
            return $this->responseJson(101,'','搜索商品不能为空');
        }
        $req->setFields("num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick");
        $req->setQ($q);

        $req->setPageNo($page);

        $resp = $c->execute($req);

        return $this->responseJson(200,$resp->results,'商品信息');
    }

//淘宝商品转链测试 权限不足
    public function actionZhuanlian()
    {
        $c = new TaobaoClient();
        $req = new TbkItemConvertRequest();
        $req->fields = 'num_iid';
        $req->num_iids='579838319103';
        $req->adzone_id='197300446';
        $resp = $c->run($req);
        return $resp;
    }

//轻淘客搜索
    public function actionQtk()
    {
        $pid=Config::getConfig('MIAO_PID');
        $apikey=Config::getConfig('QTK_API_KEY');
        $keyword=\yii::$app->request->post('keyword');
        $page=\Yii::$app->request->post('page');//搜索关键字
        $client = new Client();
        $response = $client->get('http://openapi.qingtaoke.com/search?s_type=1&app_key=' . $apikey . '&v=1.0&cat=0&sort=1&is_ali=0',
            [
                'key_word'=>$keyword,
                'page' => $page,
            ]
        )->send();//对象
//        $response=$response->content;//json字符串
        $result = $response->getData();//数组
        if (empty($result['data'])) {
            return $this->responseJson(101,$result,'未搜索到商品');
        } else {
            foreach ($result['data']['list'] as $key => $data) {
                $data['commission_link']='https://uland.taobao.com/coupon/edetail?activityId=' . $data['coupon_id'] . '&pid=' . $pid . '&itemId=' . $data['goods_id'] . '&src=cd_cdll';
                $data['practical_price']=$data['goods_price']-$data['coupon_price'];
                $goods[]=$data;
            }
        }
        return $this->responseJson(200,$goods,'淘宝搜索信息');
//        print_r($result);
    }

//通用物料搜索API（导购）无需转链
    public function actionWuliao(){
        $c = new TopClient;
        $c->appkey = Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkDgMaterialOptionalRequest();
        $q=\Yii::$app->request->post('keyword');//搜索关键字
        $page=\Yii::$app->request->post('page');//搜索关键字
        $pid=Config::getConfig('TAOBAO_APPPID');
        $req->setPageSize("2");//每页条数
        $req->setPageNo("$page");//页
        $req->setQ("$q");//关键词
        $req->setHasCoupon("true");//优惠券连接
        $req->setAdzoneId("$pid");//默认PID第三段  APPID
        $resp = $c->execute($req);
        $result=json_decode(json_encode($resp),true);
        foreach ($result['result_list']['map_data'] as $key=>$value) {
            $b = explode('元减', $value['coupon_info']);
            $bb = str_replace('元', '', $b['1']);
            $value['coupon_info'] = $bb;//优惠券面额
            $value['commission_rate'] = $value['commission_rate'] / 10000 * $value['zk_final_price'];//佣金
           $vv[]=$value;
        }
        return $this->responseJson(200,$vv,'淘宝搜索信息');
    }

    //喵有券搜索
    public function actionCjs(){
        $q = \Yii::$app->request->post('keyword');//搜索关键字
        $pageno = \Yii::$app->request->post('page');//页数
        $client = new Client();
        $name = Config::getConfig('MIAO_TBNAME');
        $key=Config::getConfig('MIAO_APKEY');
        $pid=Config::getConfig('MIAO_PID');
//        print_r($pid);
        $a = explode('_',$pid);
//        $adzoneid = substr(strrchr($pid,'_'),1);
//
//        print_r($a);
//        exit;
        $response = $client
            ->createRequest()
            ->setMethod('get')
            ->setUrl('https://api.open.21ds.cn/apiv1/gettkmaterial?')
            ->setData([
                'apkey' => $key,
                'adzoneid' =>$a['3'],
                'siteid' => $a['2'],
                'tbname' => $name,
                'keyword'=>$q,
                'pageno'=>$pageno
            ])
            ->send();
        $result=$response->getData();//数组
        return $result;
        exit;
//        print_r($result);
//        exit;

        foreach ($result['data'] as $data) {
//            print_r($data);
//            exit;
            //  高佣转链
            $client = new MiaoClient();
            $request = new GetGoodsCouponUrl();
            $request->tbname = Config::getConfig('MIAO_TBNAME');
            $request->itemid = $data['num_iid'];
            $request->pid = Config::getConfig('MIAO_PID');
            $response = $client->run($request);

            $tmp = Json::decode($response,true);//转链结果
//            print_r($tmp);
//            exit;
//            print_r($tmp['code']);
//            exit;
            if ($tmp['code'] != 200){
                Log::add($tmp,'error');
                continue;
                throw new Exception('喵有券接口报错：'.$response);
            }
            $arr = $tmp['result']['data'];
//            print_r($arr);
//            exit;
//            $data['Commission'] =$arr['max_commission_rate'] ;
            /*print_r($arr['coupon_click_url']);
            exit;*/
            $data['coupon_click_url']=$arr['coupon_click_url'];//转链地址
            print_r($data['url']);
            print_r($data['coupon_click_url']);
            exit;
//            $url=$arr['coupon_click_url'];//转链地址
//            var_export($data);die();

//            $model = $this->convertGoodsModel($data);
//            if ($model->isNewRecord) {
//                $this->num++;
//            }
//            if (!$model->save()) {
//
//                throw new UserException('商品保存失败：' . $model->error);
//            }


        }
//        return $data['coupon_click_url'];
//        return $url;
//        print_r($result);
//        return $result;
//        return $this->responseJson(200,$result,'搜索商品信息');


    }

    /**
     * 淘宝全网搜
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionGetSearch()
    {
        if (\Yii::$app->request->isOptions) {
            die();
        }
        $q = \Yii::$app->request->post('keyword');
        $sort = \Yii::$app->request->post('sort');
        if (empty($q)) {
            return $this->responseJson(1, '', '查询关键字为空');
        }
        $arr = [
            1 => 'tk_total_sales_desc',
            2 => 'total_sales_desc',
            3 => 'price_asc',
            4 => 'price_desc',
            5 => 'tk_total_sales_desc',
            6 => 'tk_total_sales_asc',
            7 => 'tk_total_commi_desc',
            8 => 'tk_total_commi_asc',
            9 => 'price_desc',
            10 => 'price_asc',
        ];
        $page = empty(\Yii::$app->request->post('page')) ? 1 : \Yii::$app->request->post('page');
        $token = \Yii::$app->request->headers['token'];
//        print_r($token);
//        exit;
        $string = base64_decode($token);
//        print_r();
//        exit;
        $uid = json_decode($string, true)['uid'];
        $user = User::findOne(['uid' => $uid]);
        if (empty($user)) {
            $user = User::find()->one();
            $lv = 0;
            $userPid = $user->alimm_pid;
        } else {
            $lv = $user->lv;
            $userPid = $user->alimm_pid;
        }

        try{
            $pid = explode('_', $userPid)[3];
            $siteId = explode('_',$userPid)[2];
        }catch (\Exception $e){
            return $this->responseJson(1,$userPid,'用户PID出错：'.$userPid);
        }
        if (empty($pid) || empty($siteId)){
            return $this->responseJson(1,['userpid'=>$userPid,'adzoneid'=>$pid,'siteid'=>$siteId],'用户推广位不能为空：'.$userPid);
        }

        //  物料搜索
        $client = new MiaoClient();
        $request = new GetTkMaterial();
        $request->tbname = Config::getConfig('MIAO_TBNAME');
        $request->keyword = $q;
        $request->adzoneid = $pid;
        $request->siteid = $siteId;
        $request->hascoupon = true;
        $request->pagesize = 10;
        $request->pageno = $page;
        $request->sort = $arr[$sort];
        $response = $client->run($request);

        $tmp = Json::decode($response,true);
//        print_r($tmp);
//        exit;
        $selfcomm = DistributionConfig::getAll('index')['platform'] * 0.01;
        $ratio = bcsub(1, $selfcomm, 2) * DistributionConfig::getAll('partner')['selfcomm'][$lv] * 0.01;

        if ($tmp['code'] == 200 && !empty($tmp['data'])) {
            $list = $tmp['data'];
            $arr = [];
            //$miao_client = new MiaoClient();
            foreach ($list as $info => $goods) {

                //  使用喵有券高佣转链
                $req = new GetGoodsCouponUrl();
                $req->pid = $userPid;
                $req->itemid = $goods['num_iid'];
                $req->tbname = Config::getConfig('MIAO_TBNAME');
                $response = $client->run($req);

                $tmp = Json::decode($response);
                if ($tmp['code'] != 200){
                    return $this->responseJson(1,$tmp,'喵有券接口报错：'.$tmp['msg']);
                }
                $tmp = $tmp['result']['data'];

                $coupon_info = isset($tmp['coupon_info']) ? $tmp['coupon_info'] : $goods['coupon_info'];
                preg_match_all('/(.*减)(\d+)(元)/', $coupon_info, $matches);
                if (empty($matches[2])) {
                    $coupon_money = 0;
                } else {
                    $coupon_money = $matches[2][0];
                }
                $coupon_price = bcsub($goods['zk_final_price'], $coupon_money, 2);
                $arr[$info] = [
                    'origin_id' => $goods['num_iid'],
                    'title' => $goods['title'],
                    'thumb' => $goods['pict_url'],
                    'origin_price' => $goods['zk_final_price'], //原价
                    'coupon_price' => $coupon_price,//券后价
                    'coupon_money' => intval($coupon_money), //优惠券金额
                    'type' => $goods['user_type'],
                    'volume' => $goods['volume'],
                    'coupon_url' => $tmp['coupon_click_url'],
                    'commission_money' => sprintf("%.2f", $coupon_price * $tmp['max_commission_rate'] * 0.01 * $ratio),
                ];
            }
            if ($sort == 7) {
                array_multisort(array_column($arr, 'commission_money'), SORT_DESC, $arr);
            } elseif ($sort == 6) {
                array_multisort(array_column($arr, 'commission_money'), SORT_ASC, $arr);
            }
        } else {  // 查询数据库
            if ($sort == 2) {
                $order = 'sales_num desc';
            } elseif ($sort == 3) {
                $order = 'coupon_price asc';
            } elseif ($sort == 4) {
                $order = 'coupon_price desc';
            } elseif ($sort == 6) {
                $order = 'commission_money asc';
            } elseif ($sort == 7) {
                $order = 'commission_money desc';
            } else {
                $order = 'id desc';
            }
            $list = Goods::find()
                ->where(['like', 'title', $q])
                ->andWhere(['in','type',[11,12]])
                ->orderBy($order)
                ->offset($page * 6)->limit(6)
                ->asArray()->all();
//            print_r($list);
//            exit;
            $arr = [];
            foreach ($list as $k => $goods) {
                $arr[$k] = [
                    'origin_id' => $goods['origin_id'],
                    'title' => $goods['title'],
                    'thumb' => $goods['thumb'],
                    'origin_price' => $goods['origin_price'], //原价
                    'coupon_price' => $goods['coupon_price'],//券后价
                    'coupon_money' => intval($goods['coupon_money']), //优惠券金额
                    'type' => $goods['type'],
                    'volume' => $goods['view'],
                    'coupon_url' => $goods['coupon_link'],
                    'commission_money' => sprintf("%.2f", $goods['commission_money'] * $ratio),
                ];
            }
        }
        return $this->responseJson(0, $arr, '返回成功');
    }

    /**
     * 轮播图
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionNavIndex()
    {
        $redis = \Yii::$app->get('redis');
//        print_r($redis);
//        exit;
        $key = md5("NavIndex");
        if ($val = $redis->get($key)) {
            $info = Json::decode($val, true);
            return $this->responseJson(0, $info, '返回首页数据成功');
        } else {
            $list = Nav::find()->asArray()->all();
            if (!empty($list)) {
                foreach ($list as &$ls) {
                    if (!empty($ls['img'])) {
                        $ls['img'] = Utils::toMedia($ls['img']);
                    }
                }
                $info = Json::encode($list);
                $redis->set($key, $info);
                $redis->expire($key, 60);
                return $this->responseJson(0, $list, '返回首页数据成功');
            } else {
                return $this->responseJson(1, '', '返回数据为空!');
            }
        }
    }

    /**
     * 分类详情
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCategoryInfo()
    {
        $cid = \Yii::$app->request->post('id');
        $redis = \Yii::$app->get('redis');
        $key = md5("CategoryInfo" . $cid);
        if ($val = $redis->get($key)) {
            $info = Json::decode($val, true);
            return $this->responseJson(0, $info, '返回首页数据成功');
        } else {
            $goodList = Goods::find()->where(['cid' => intval($cid)])->asArray()->all();
            if (!empty($goodList)) {
                foreach ($goodList as &$list) {
                    if (!empty($list['img'])) {
                        $list['img'] = Utils::toMedia($list['img']);
                    }
                }
                $info = Json::encode($goodList);
                $redis->set($key, $info);
                $redis->expire($key, 60);
                return $this->responseJson(0, $goodList, '返回首页数据成功');
            } else {
                return $this->responseJson(1, '', '返回数据为空!');
            }
        }
    }

    /**
     * 文章、教程
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionArticle()
    {
        $redis = \Yii::$app->get('redis');
        $key = md5("ArticleCid1");
        if ($val = $redis->get($key)) {
            $info = Json::decode($val, true);
            return $this->responseJson(0, $info, '返回首页数据成功');
        } else {
            $articleList = Article::find()->where(['status' => 1, 'cid' => 1])->asArray()->all();
            if (!empty($articleList)) {
                foreach ($articleList as &$list) {
                    if (!empty($list['img'])) {
                        if (!empty($list['img'])) {
                            $list['img'] = Utils::toMedia($list['img']);
                        }
                    }
                    if (!empty($list['small_img'])) {
                        if (!empty($list['small_img'])) {
                            $list['small_img'] = Utils::toMedia($list['small_img']);
                        }
                    }
                    $list['created_at'] = date("Y-m-d H:i:s", $list['created_at']);
                    $list['updated_at'] = date("Y-m-d H:i:s", $list['updated_at']);
                }
                $info = Json::encode($articleList);
                $redis->set($key, $info);
                $redis->expire($key, 60);
                return $this->responseJson(0, $articleList, '返回首页数据成功');
            } else {
                return $this->responseJson(1, '', '返回数据为空!');
            }
        }
    }

    /**
     * 常见问题
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionProblemArticle()
    {
        $redis = \Yii::$app->get('redis');
        $key = md5("ProblemArticleCid2");
        if ($val = $redis->get($key)) {
            $info = Json::decode($val, true);
            return $this->responseJson(0, $info, '返回首页数据成功');
        } else {
            $articleList = Article::find()->where(['status' => 1, 'cid' => 2])->asArray()->all();
            if (!empty($articleList)) {
                foreach ($articleList as &$list) {
                    if (!empty($list['img'])) {
                        if (!empty($list['img'])) {
                            $list['img'] = Utils::toMedia($list['img']);
                        }
                    }
                    if (!empty($list['small_img'])) {
                        if (!empty($list['small_img'])) {
                            $list['small_img'] = Utils::toMedia($list['small_img']);
                        }
                    }
                    $list['created_at'] = date("Y-m-d H:i:s", $list['created_at']);
                    $list['updated_at'] = date("Y-m-d H:i:s", $list['updated_at']);
                }
                $info = Json::encode($articleList);
                $redis->set($key, $info);
                $redis->expire($key, 60);
                return $this->responseJson(0, $articleList, '返回首页数据成功');
            } else {
                return $this->responseJson(1, '', '返回数据为空!');
            }
        }
    }

    /**
     * 淘宝客商品详情（简版）
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionItemInfo()
    {
        if (\Yii::$app->request->isOptions) {
            die();
        }
        $token = \Yii::$app->request->headers['token'];
        $string = base64_decode($token);
        $uid = json_decode($string, true)['uid'];
        $goodId = \Yii::$app->request->post('num_iid');
        $type = \Yii::$app->request->post('type');
        if (empty($uid)) {
            $user = User::find()->one();
        } else {
            $user = User::findOne(['uid' => $uid]);
        }

        $redis = \Yii::$app->get('redis');

            if ($val = $redis->get($goodId . $uid)) {
                $info = Json::decode($val, true);
            } else {
                $list = Collection::find()->where(['collection_id' => $goodId, 'uid' => $uid, 'status' => 1])->asArray()->all();
                if (empty($list) || empty($uid)) {
                    $collection = 2;
                } else {
                    $collection = 1;
                }
                $good = Goods::find()->where(['origin_id' => $goodId])->asArray()->one();

                //  使用喵有券查询商品详情
                $client = new MiaoClient();
                $clientRequest = new GetGoodsInfo();
                $clientRequest->itemid = $goodId;
                $clientResponse = $client->run($clientRequest);
                $tmp = Json::decode($clientResponse,true);
                if($tmp['code'] != 200){
                    return $this->responseJson(101,$clientResponse,'喵有券接口报错1：'.$clientResponse);
                }
                $tmp = $tmp['data']['n_tbk_item'];
                //  查询优惠券金额
//                $couponRequest = new GetGoodsCouponUrl();
//                $couponRequest->pid = $user->alimm_pid;
//                $couponRequest->tbname = Config::getConfig('MIAO_TBNAME');
//                $couponRequest->itemid = $goodId;
//                $couponResponse = $client->run($couponRequest);
//                $tmpcou = Json::decode($couponResponse,true);
////                print_r($tmpcou);
////                exit;
//                if ($tmpcou['code'] != 200){
////                    return $this->responseJson(102,$clientResponse,'喵有券接口报错2：'.$couponResponse);
//                }
//                if (isset($tmpcou['result']['data']['coupon_info'])){
//                    preg_match_all('/(.*减)(\d+)(元)/', $tmpcou['result']['data']['coupon_info'], $matches);
//                    if (empty($matches[2])) {
//                        $coupon_money = 0;
//                    } else {
//                        $coupon_money = $matches[2][0];
//                    }
//                }else{
//                    $coupon_money = 0;
//                }

                $info = [
                    'num_iid' => $goodId, //商品ID
                    'item_url' => $good['coupon_link'], //优惠券链接
                    'origin_price' => $tmp['zk_final_price'], //原价
//                    'coupon_price' => bcsub($tmp['zk_final_price'], $coupon_money, 2),  //券后价
//                    'coupon_money' => $coupon_money, //券价格
                    'type' => 1,    //平台类型
                    'title' => $tmp['title'],//标题
                    'volume' => $tmp['volume'],//销量
                    'pict_url' => $tmp['pict_url'],//图片
                    'small_images' => empty($tmp['small_images']['string']) ? '' : $tmp['small_images']['string'],//详情图片
                    'recommend' => '',//推荐
                    'collection' => $collection,//关注
                ];
                $tmp = Json::encode($info);
                $redis->set($goodId . $user->lv, $tmp);
                $redis->expire($goodId, 60 * 60);
            }
        //}
        return $this->responseJson(200, $info, '返回数据成功');
    }

    /**
     * 公告
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAnnouncement()
    {
        $key = md5('Announcement');
        $redis = \Yii::$app->get('redis');
        if ($val = $redis->get($key)) {
            $list = Json::decode($val, true);
        } else {
            $list = Announcement::find()->where(['type' => 1])->orderBy('created_at desc')->limit(5)->asArray()->all();
            if (empty($list)) {
                return $this->responseJson(1, '', '查询数据为空！');
            } else {
                foreach ($list as &$item) {
                    $item['created_at'] = date("Y-m-d H:i:s", $item['created_at']);
                    $item['content'] = Utils::get_img_thumb_url($item['content']);
                }
                $info = Json::encode($list);
                $redis->set($key, $info);
                $redis->expire($key, 60);
            }
        }
        return $this->responseJson(0, $list, '返回数据成功！');
    }

    /**
     * 解析淘口令
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionCreateUrl()
    {
        if (\Yii::$app->request->isOptions) {
            die();
        }
        $text = \Yii::$app->request->post('text');
//        if (\Yii::$app->user->isGuest) {
//            $user = User::findOne(['uid' => 1]);
//        } else {
//            $user = User::findOne(['uid' => $this->uid]);
//        }
        $token = \Yii::$app->request->headers['token'];
        $string = base64_decode($token);
        $uid = json_decode($string, true)['uid'];
        if (empty($uid)) {
            $uid = 1;
        }
        $user = User::findOne(['uid' => $uid]);
        $subUnionId = $user->jd_pid;
        $pid = $user->alimm_pid;
        $lv = $user->lv;
        $selfcomm = DistributionConfig::getAll('index')['platform'] * 0.01;
        $ratio = bcsub(1, $selfcomm, 2) * DistributionConfig::getAll('partner')['selfcomm'][$lv] * 0.01;
        if (empty($text)) {
            return $this->responseJson(1, [], '你迷路了');
        }
        if (strpos($text, 'jd.com')) {  //京东

            $client = new JdClient();
            $request = new ServicePromotionAppGetcode();
            $request->jdurl = $text;
            $request->appId = '1287311125';
            $request->subUnionId = $subUnionId;
            $response = $client->run($request);
            $tmp = json_decode($response['jingdong_service_promotion_app_getcode_responce']['query_result'], true);

            $id = ltrim(rtrim(parse_url($text)['path'], '.html'), '/product/');

            $infoRequest = new ServicePromotionGoodsInfo();
            $infoRequest->skuIds = $id;
            $infoResponse = $client->run($infoRequest);
            $tmp1 = json_decode($infoResponse['jingdong_service_promotion_goodsInfo_responce']['getpromotioninfo_result'], true)['result'][0];

            $price = bcmul(bcmul($tmp1['wlUnitPrice'], $tmp1['commisionRatioWl'] * 0.01, 2), $ratio, 2);
            $data = [
                'url' => $tmp['url'],
                'content' => $tmp1['goodsName'],
                'picUrl' => $tmp1['imgUrl'],
                'type' => 21,
                'price' => $price,
            ];
            $collData = [
                'uid' => $uid,
                'collection_id' => $id,
                'type' => 1,
                'status' => 1,
                'good_type' => 21,
            ];
            Collection::add($collData);
            return $this->responseJson(0, $data, "返回信息成功");
        } else { //淘宝


            //  解析淘口令
            $client = new MiaoClient();
            $request = new DecodeTklToID();
            $request->kouling = $text;
            $response = $response = $client->run($request);
            $tmp = Json::decode($response,true);
            if ($tmp['code'] != 200){
                return $this->responseJson(1,$response,'喵有券接口报错：'.$tmp['msg']);
            }
            $itemId = $tmp['data']; //  商品ID

            $request = new GetGoodsCouponUrl();
            $request->tbname = Config::getConfig('MIAO_TBNAME');
            $request->itemid = $itemId;
            $request->pid = $pid;
            $request->shorturl = 1;
            $request->tpwd = 1;
            $response = $client->run($request);
            $tmp = Json::decode($response,true);
            if ($tmp['code'] != 200){
                return $this->responseJson(1,$response,'喵有券接口报错：'.$tmp['msg']);
            }
            $tmp = $tmp['data'];

            // 有这么神奇的写法?太神奇了
            $token = \Yii::$app->request->headers['token'];
            $string = base64_decode($token);
            $uid = json_decode($string, true)['uid'];
            if (empty($uid)) {
                return $this->responseJson(403, '', '返回成功');
            } else {
                $collData = [
                    'uid' => $uid,
                    'collection_id' => $itemId,
                    'type' => 1,
                    'status' => 1,
                    'good_type' => 1,
                ];
                Collection::add($collData);
                return $this->responseJson(0, $tmp, '返回成功');
            }
        }
    }

    /**
     * 淘宝客商品详情（简版）
     * @param $num_iid
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function getUrl($num_iid)
    {
        $data = [
            'method' => 'taobao.tbk.item.info.get',
            'fields' => 'num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,nick,volume,seller_id,cat_leaf_name,cat_name',
            'num_iids' => $num_iid,
        ];
        $result = $this->getUrlResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['tbk_item_info_get_response']['results']['n_tbk_item'][0];
            return $arr;
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 淘宝客商品关联推荐查询
     * @param $num_iid
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function getRecommend($num_iid)
    {
        $data = [
            'method' => 'taobao.tbk.item.recommend.get',
            'fields' => 'num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url',
            'num_iid' => $num_iid,
            'count' => 10,
        ];
        $result = $this->getUrlResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['tbk_item_recommend_get_response']['results']['n_tbk_item'];
            foreach ($arr as $k => &$v) {
                $v['coupon_price'] = bcsub($v['reserve_price'], $v['zk_final_price'], 2);
            }
            return $arr;
        } else {
            return $result;
        }
    }

    /**
     * 淘宝客淘口令
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreateTkl()
    {
        $url = \Yii::$app->request->post('url');
        $title = \Yii::$app->request->post('title');
        $thumb = \Yii::$app->request->post('thumb');
        $c = new TopClient;
        $c->appkey = Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkTpwdCreateRequest;
        $req->setText("$title");
        $req->setUrl("$url");
        $req->setLogo("$thumb");
        $req->setExt("{}");
        if (!empty($url)){
            $resp = $c->execute($req);
        }else{
            return $this->responseJson(101, '', '获取淘口令失败');
        }

        $res=(\GuzzleHttp\json_decode(\GuzzleHttp\json_encode($resp),true));

        if (!empty($res['data'])) {
            return $this->responseJson(200, $res['data'], '淘口令');
        }else{
            return $this->responseJson(101, '', '获取淘口令失败');
        }
    }


    /*
     * 聚划算商品
     *
     * */
    public function actionJhs(){
        $client = new Client();
        $vekey=Config::getConfig('VE_KEY');
        $page=\yii::$app->request->post('page');
        $pid=Config::getConfig('MIAO_PID');
//        $user=user::find()->asArray()->one();
        $response = $client->get('http://api.vephp.com/pintuan?vekey='.$vekey.'&pid='.$pid.'&page_no='.$page
        )->send();//对象
        $result=$response->getData();//数组
        $ress = $result['data'];

//        foreach ($ress as $key=>$data){
//            $request_url = 'http://v2.api.haodanku.com/ratesurl';
//            $request_data['apikey'] = Config::getConfig('HDK_API_KEY');
//            $request_data['itemid'] = $data['item_id'];
//            $request_data['pid'] = Config::getConfig('MIAO_PID');
//            $request_data['tb_name'] = Config::getConfig('MIAO_TBNAME');
//            $ch = curl_init();
//            curl_setopt($ch,CURLOPT_URL,$request_url);
//            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//            curl_setopt($ch, CURLOPT_TIMEOUT,10);
//            curl_setopt($ch,CURLOPT_POST,1);
//            curl_setopt($ch,CURLOPT_POSTFIELDS,$request_data);
//            $res = curl_exec($ch);
//            curl_close($ch);
//            $arr=json_decode($res,true);
//            $data['click_url']=$arr['data']['coupon_click_url'];
//            $jhs[]=$data;
//        }
        return $this->responseJson('200',$ress,'聚划算商品');
    }
    /**
     *解析淘口令(维易)
     */
    public function actionDpiTkl(){
            $client = new Client();
            $vekey=Config::getConfig('VE_KEY');
            $url=\yii::$app->request->post('url');
            $response = $client->get('http://api.vephp.com/dec?vekey='.$vekey.'&para='.$url
            )->send();//对象
            $result=$response->getData();//数组
        if (!empty($result['error'])){
            return $this->responseJson(101,'',$result['msg']);
        }else {
            $request_url = 'http://v2.api.haodanku.com/ratesurl';
            $request_data['apikey'] = Config::getConfig('HDK_API_KEY');
            $request_data['itemid'] = $result['num_iid'];
            $request_data['pid'] = Config::getConfig('MIAO_PID');
            $request_data['tb_name'] = Config::getConfig('MIAO_TBNAME');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);
            $res = curl_exec($ch);
            curl_close($ch);
            $arr = json_decode($res, true);
            $result['item_url'] = $arr['data']['coupon_click_url'];
        }
        return $this->responseJson('200',$result,'解析成功');
    }

    //  淘宝详情
    public function actionInfo(){

        $id=\yii::$app->request->post('goodsid');
        $c = new TopClient;
        $c->appkey = Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkItemInfoGetRequest;
        $req->setNumIids($id);
        $resp = $c->execute($req);
        $result=(json_decode(json_encode($resp),true));
        $res=$result['results']['n_tbk_item'];
        $xq=Goods::find()->where(['origin_id'=>$id])->asArray()->one();
//        print_r($xq);

        if (!empty($xq)) {
            $xq['imags'] = $res['small_images'];
        }
        return $this->responseJson(200,$xq,'数据成功');
    }


    //淘宝客淘口令源码
    public function actionCreateTpwd()
    {
        $url = \Yii::$app->request->post('url');
        $title = \Yii::$app->request->post('title');
        $key = md5($url . $title);
        $redis = \Yii::$app->get('redis');
        if ($val = $redis->get($key)) {
            $arr = $val;
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            $data = [
                'method' => 'taobao.tbk.tpwd.create',
                'text' => $title,
                'url' => $url,
            ];
            $result = $this->getUrlResult($data);
            if (empty($result['code'])) {
                $arr = $result['tbk_tpwd_create_response']['data']['model'];
                $redis->set($key, $arr);
                $redis->expire($key, 60);
                return $this->responseJson(0, $arr, '查询数据成功');
            } else {
                return $this->responseJson(1, '', $result['sub_msg']);
            }
        }
    }

    /**
     * 推荐商铺
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionShop()
    {
        $redis = \Yii::$app->get('redis');
        $key = md5("ShopBiz");
        if ($val = $redis->get($key)) {
            $info = Json::decode($val, true);
            return $this->responseJson(0, $info, '返回首页数据成功');
        } else {
            $bizList = Biz::find()->asArray()->all();
            if (!empty($bizList)) {
                foreach ($bizList as &$list) {
                    if (!empty($list['img'])) {
                        $list['img'] = Utils::toMedia($list['img']);
                    }
                }
                $info = Json::encode($bizList);
                $redis->set($key, $info);
                $redis->expire($key, 60);
                return $this->responseJson(0, $bizList, '返回首页数据成功');
            } else {
                return $this->responseJson(1, '', '返回数据为空!');
            }
        }
    }

    /**
     * 检查版本
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate()
    {
        $key = md5('update');
        $redis = Yii::$app->get('redis');
        if ($val = $redis->get($key)) {
            $data = Json::decode($val, true);
        } else {
            $downloadUrl = Config::getConfig('APP_DOWNLOAD_URL');
            $iosDownloadUrl = Config::getConfig('APP_DOWNLOAD_URL_IOS');
            $vsersion = Config::getConfig('APP_VERSIONS');
            $iosVsersion = Config::getConfig('APP_VERSIONS_IOS');
            if (empty($downloadUrl) || empty($vsersion)) {
                return $this->responseJson(1, [], '系统配置出错');
            } else {
                $data = [
                    'iosUrl' => $iosDownloadUrl,
                    'iosVersion' => $iosVsersion,
                    'url' => $downloadUrl,
                    'version' => $vsersion,
                ];
            }
            $info = Json::encode($data);
            $redis->set($key, $info);
            $redis->expire($key, 60);
        }
        return $this->responseJson(0, $data, '返回成功');
    }

    /**
     * 淘宝精选
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionFeaturedTb()
    {
        if (\Yii::$app->request->isOptions) {
            die();
        }
        $token = \Yii::$app->request->headers['token'];
        $string = base64_decode($token);
        $uid = json_decode($string, true)['uid'];
        $user = User::findOne(['uid' => $uid]);
        if (empty($user)) {
            $lv = 0;
        } else {
            $lv = $user->lv;
        }
        $cateList = GoodsCategory::find()->select('id,title')->asArray()->all();
        $page = \Yii::$app->request->post('page');
        $cate = empty(\Yii::$app->request->post('cate')) ? 1 : \Yii::$app->request->post('cate');
        $key = md5($page . $cate . $lv);
        $redis = \Yii::$app->get('redis');
        if ($val = $redis->get($key)) {
            $data = Json::decode($val, true);
        } else {
            $list = Goods::find()
                ->select("coupon_price,origin_price,type,title,sales_num,coupon_money,commission_money,thumb,origin_id")
                ->where(['cid' => $cate])
                ->andWhere(['or', ['type' => 11], ['type' => 12]])
                ->offset($page * 6)->limit(6)
                ->asArray()->all();
            $arr = [];
            if (!empty($list)) {
                $selfcomm = DistributionConfig::getAll('index')['platform'] * 0.01;
                $ratio = bcsub(1, $selfcomm, 2) * DistributionConfig::getAll('partner')['selfcomm'][$lv] * 0.01;
                foreach ($list as $info => $goods) {
                    if ($goods['sales_num'] > 10000) {
                        $volume = intval(($goods['sales_num'] / 10000)) . '万+';
                    } else {
                        $volume = $goods['sales_num'];
                    }
                    $arr[$info] = [
                        'origin_id' => $goods['origin_id'],
                        'title' => $goods['title'],
                        'thumb' => $goods['thumb'],
                        'origin_price' => $goods['origin_price'],
                        'coupon_price' => $goods['coupon_price'],
                        'coupon_money' => intval($goods['coupon_money']),
                        'volume' => $volume,
                        'coupon_url' => '',
                        'type' => $goods['type'],
                        'commission_money' => sprintf("%.2f", $goods['commission_money'] * $ratio),
                    ];
                }
            }
            $data = [
                'cateList' => $cateList,
                'list' => $arr,
            ];
            $info = Json::encode($data);
            $redis->set($key, $info);
            $redis->expire($key, 60);
        }
        return $this->responseJson(0, $data, '返回数据成功');
    }

    /**
     * 拼多多精选、全网搜
     * @throws \yii\base\Exception
     */
    public function actionFeaturedPdd()
    {
        if (\Yii::$app->request->isOptions) {
            die();
        }
        $token = \Yii::$app->request->headers['token'];
        $string = base64_decode($token);
        $uid = json_decode($string, true)['uid'];
        $user = User::findOne(['uid' => $uid]);
        if (empty($user)) {
            $lv = 0;
        } else {
            $lv = $user->lv;
        }
        $cateList = RobotDdk::find()
            ->select('from_cid as id,title')
            ->where('id !=3 and id !=9 ')
            ->asArray()->all();

        $page = \Yii::$app->request->post('page');
        $keyword = \Yii::$app->request->post('keyword');
        $cate = empty(\Yii::$app->request->post('cate')) ? 1 : \Yii::$app->request->post('cate');
        $sort_type = empty(\Yii::$app->request->post('sort')) ? 1 : \Yii::$app->request->post('sort');

        $key = md5($page . $keyword . $cate . $sort_type . $lv);
        $redis = \Yii::$app->get('redis');
        if ($val = $redis->get($key)) {
            $data = json_decode($val, true);
        } else {
            $client = new PddClient();
            $request = new DdkGoodsSearchRequest();
            if (!empty($keyword)) {
                $request->keyword = $keyword;
                $request->sort_type = Goods::ORDER_PDD[$sort_type];
                $request->with_coupon = 'true';
                $request->page = $page;
                $request->page_size = 20;
            } else {
                $request->opt_id = $cate;
                $request->sort_type = Goods::ORDER_PDD[$sort_type];
                $request->with_coupon = 'true';
                $request->page = $page;
                $request->page_size = 20;
            }
            $response = $client->run($request);
            $list = $response['goods_search_response']['goods_list'];
            $selfcomm = DistributionConfig::getAll('index')['platform'] * 0.01;
            $ratio = bcsub(1, $selfcomm, 2) * DistributionConfig::getAll('partner')['selfcomm'][$lv] * 0.01;
            $arr = [];
            foreach ($list as $info => $goods) {
                $coupon_price = bcdiv(bcsub($goods['min_group_price'], $goods['coupon_discount']), 100, 2);
                $commission_money = $coupon_price * bcdiv($goods['promotion_rate'], 1000, 2);
                if ($goods['sold_quantity'] > 10000) {
                    $volume = intval(($goods['sold_quantity'] / 10000)) . '万+';
                } else {
                    $volume = $goods['sold_quantity'];
                }
                $arr[$info] = [
                    'origin_id' => $goods['goods_id'],
                    'title' => $goods['goods_name'],
                    'thumb' => $goods['goods_thumbnail_url'],
                    'small_images' => '',
                    'origin_price' => bcdiv($goods['min_group_price'], 100, 2),
                    'coupon_price' => $coupon_price,
                    'coupon_money' => bcdiv($goods['coupon_discount'], 100, 2),
                    'volume' => $volume,
                    'coupon_url' => '',
                    'type' => 31,
                    'commission_money' => sprintf("%.2f", $commission_money * $ratio),
                ];
            }
            if (empty($keyword)) {
                $data = [
                    'cateList' => $cateList,
                    'list' => $arr,
                ];
            } else {
                $data = $arr;
            }
            $info = Json::encode($data);
            $redis->set($key, $info);
            $redis->expire($key, 60);
        }

        return $this->responseJson(0, $data, '返回数据成功');
    }

    /**
     * 京东精选
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionFeaturedJd()
    {
        if (\Yii::$app->request->isOptions) {
            die();
        }
        $token = \Yii::$app->request->headers['token'];
        $string = base64_decode($token);
        $uid = json_decode($string, true)['uid'];
        $user = User::findOne(['uid' => $uid]);
        if (empty($user)) {
            $lv = 0;
        } else {
            $lv = $user->lv;
        }
        $page = \Yii::$app->request->post('page');
        $keyword = \Yii::$app->request->post('keyword');
        $cate = empty(\Yii::$app->request->post('cate')) ? 652 : \Yii::$app->request->post('cate');
        $sort = \Yii::$app->request->post('sort');
        $redis = \Yii::$app->get('redis');
        $key = md5('FeaturedJd' . $page . $keyword . $cate . $sort . $lv);
        if ($val = $redis->get($key)) {
            $data = Json::decode($val, true);
        } else {
            $cateList = RobotJd::find()->select('from_cid as id,title')->asArray()->all();
            $arr = Goods::featuredJd($uid, $keyword, $cate, $page, $sort);
            if (empty($keyword)) {
                $data = [
                    'cateList' => $cateList,
                    'list' => $arr,
                ];
            } else {
                $data = $arr;
            }
            $info = Json::encode($data);
            $redis->set($key, $info);
            $redis->expire($key, 60);
        }
        return $this->responseJson(0, $data, '返回数据成功');
    }

}
