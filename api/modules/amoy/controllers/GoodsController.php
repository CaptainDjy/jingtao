<?php
/**
 * Created by PhpStorm.
 * @author pine
 * @link http://www.dhsoft.cn
 * Date: 2018/4/27
 * Time: 17:10
 */

namespace api\modules\amoy\controllers;
use api\modules\amoy\library\CreateSharePic;
use common\components\tblm\top\request\TbkDgOptimusMaterialRequest;
use common\components\tblm\top\request\TbkItemInfoGetRequest;
use common\models\Commissionset;
use common\widgets\daterangepicker\DateRangePicker;
use common\models\Goods;
use common\models\GoodsCategory;
use common\helpers\Utils;
use yii\data\ActiveDataProvider;
use common\components\tblm\top\TopClient;
use common\models\Config;
use common\components\tblm\top\request\TbkDgMaterialOptionalRequest;
use yii;
class GoodsController extends ControllerBase
{
    /**
     * 获取商品
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionObtain()
    {
        $method = 'taobao.itemprops.get';
        $fields = "cid,pid,prop_name,vid,name,name_alias,status,sort_order";
        $data = [
            'method' => $method,
            'fields' => $fields,
        ];
        $result = $this->getUrlResult($data);
//        print_r($result);
//        exit;
        if (!empty($result['sub_code'])) {
            return $this->responseJson(1, $result['sub_msg'], '请求结果错误,请重新请求！');
        } else {
            return $this->responseJson(0, $result, '返回数据成功');
        }
    }
//淘抢购
    public function actionTqg(){
        $page = empty(\Yii::$app->request->post('page')) ? 1 : \Yii::$app->request->post('page');
        $res = Goods::find()->where(['>','end_time',time()])->andwhere(['is_tqg'=>1])->orderBy('start_time desc')->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        return $this->responseJson(200, $res, '淘抢购商品信息');

    }


    /**
     * 获取类目信息、（无权限）
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCategory()
    {
        $method = 'alibaba.wholesale.category.get';
        $data = [
            'method' => $method,
        ];
        $result = $this->getUrlResult($data);
        if (!empty($result['sub_code'])) {
            return $this->responseJson(1, $result['sub_msg'], '请求结果错误,请重新请求！');
        } else {
            $arr = $result['alibaba_wholesale_category_get_response']['wholesale_category_result']['result'];
            return $this->responseJson(0, $arr, '返回数据成功');
        }
    }

    /**
     * 更新商品信息
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionItemUpdate()
    {
        $method = 'taobao.item.update';
        $data = [
            'method' => $method,
            'num_iid' => '123',
        ];
        $result = $this->getUrlResult($data);
        if (!empty($result['sub_code'])) {
            return $this->responseJson(1, $result['sub_msg'], '请求结果错误,请重新请求！');
        } else {
            $arr = $result['item_update_response']['item'];
            return $this->responseJson(0, $arr, '返回数据成功');
        }
    }

    /**
     * 添加一个商品
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionItemAdd()
    {
        $method = 'taobao.item.add';
        $data = [
            'method' => $method,
            'location.state' => '123',//省份
            'location.city' => '123',//城市
            'num' => '123', //商品数量
            'type' => '123', //fixed(一口价),auction(拍卖)
            'stuff_status' => '123', //new(新)，second(二手)
            'title' => '123', //宝贝标题
            'desc' => '123', //宝贝描述
            'cid' => '123', //叶子类目id
        ];
        $result = $this->getUrlResult($data);
        if (!empty($result['sub_code'])) {
            return $this->responseJson(1, $result['sub_msg'], '请求结果错误,请重新请求！');
        } else {
            $arr = $result['item_add_response']['item'];
            return $this->responseJson(0, $arr, '返回数据成功');
        }
    }
//商品分类
    public function actionCategory() {
        $res = GoodsCategory::find()->select(['id','img','title'])->asArray()->all(); //查询分类表标题
        foreach ($res as $key=>$value){
            if (!empty($res)){
                $value['img'] = Utils::toMedia($value['img']);
            $arr[]=$value;
        }
        }
        return $this->responseJson(200, $arr, '分类标题');
    }


    //首页商品信息(根据分类查询商品信息)
    public function actionIndex() {
        $page = empty(\Yii::$app->request->post('page')) ? 1 : \Yii::$app->request->post('page');
        $cid=\Yii::$app->request->post('cid');
        if ($cid){
            $res = Goods::find()->where(['cid'=>$cid,'type'=>[11,12]])->andwhere(['>','end_time',time()])->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }else{

            $res = Goods::find()->where(['>','end_time',time()])->andWhere(['type'=>[11,12]])->offset(($page - 1) * 10)->limit(10)->asArray()->all();

        }
//        print_r($res);
//        foreach ($res as $key=>$value){
//            print_r($value);
//        }
        return $this->responseJson(200, $res, '商品信息');

    }
//视频单商品
    public  function actionSp(){
        $cid=\Yii::$app->request->post('cid');
        $page = empty(\Yii::$app->request->post('page')) ? 1 : \Yii::$app->request->post('page');
        $request = \Yii::$app->request;

        $sort = $request->post('sort');
        if ($sort==0){
            $order='id';
        }elseif ($sort==1){
            $order='start_time desc';
        }elseif ($sort==2){
            $order='sales_num';
        }elseif ($sort==3){
            $order='sales_num desc';
        }elseif ($sort==4){
            $order='coupon_price';
        }elseif ($sort==5){
            $order='coupon_price desc';
        }elseif ($sort==6){
            $order='commission_money';
        }elseif ($sort==7){
            $order='commission_money desc';
        }else{
            return $this->responseJson(101, '', '排序不存在');
        }

        if ($cid){
            $res = Goods::find()->select(['id','cid','origin_id','title','sub_title','type','origin_price','coupon_price','thumb','coupon_money','commission_money','description','coupon_link'])
                ->where(['cid'=>$cid,'activity'=>1])->andWhere(['>','end_time',time()])->orderBy($order)->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }else{
            $res = Goods::find()->select(['id','cid','origin_id','title','sub_title','type','origin_price','coupon_price','thumb','coupon_money','commission_money','description'])
                ->where(['activity'=>1])->andWhere(['>','end_time',time()])->orderBy($order)->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }

        return $this->responseJson(200, $res, '视频单商品信息');
    }

//今日推荐
    public function actionJrtj(){
        $jrtj=Goods::find()->where(['choice'=>1])->andWhere(['>','end_time',time()])->limit(16)->asArray()->all();
       return $this->responseJson(200,$jrtj,'今日推荐');

    }

//九块九商品
    public  function actionJkj(){
        $cid=\Yii::$app->request->post('cid');
        $page = empty(\Yii::$app->request->post('page')) ? 1 : \Yii::$app->request->post('page');
        $request = \Yii::$app->request;

        $sort = $request->post('sort');
        if ($sort==0){
            $order='id';
        }elseif ($sort==1){
            $order='start_time desc';
        }elseif ($sort==2){
            $order='sales_num';
        }elseif ($sort==3){
            $order='sales_num desc';
        }elseif ($sort==4){
            $order='coupon_price';
        }elseif ($sort==5){
            $order='coupon_price desc';
        }elseif ($sort==6){
            $order='commission_money';
        }elseif ($sort==7){
            $order='commission_money desc';
        }else{
            return $this->responseJson(101, '', '排序不存在');
        }

        if ($cid){
            $res = Goods::find()->select(['id','cid','origin_id','title','sub_title','type','origin_price','coupon_price','thumb','coupon_money','commission_money','sales_num','description','coupon_link'])
                ->where(['cid'=>$cid])->andWhere(['>','end_time',time()])->andwhere(['<','coupon_price',10])->orderBy($order)->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }else{

            $res = Goods::find()->select(['id','cid','origin_id','title','sub_title','type','origin_price','coupon_price','thumb','coupon_money','commission_money','sales_num','description','coupon_link'])
                ->Where(['>','end_time',time()])->andwhere(['<','coupon_price',10])->orderBy($order)->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }

        return $this->responseJson(200, $res, '九块九商品信息');
    }

//商品信息(根据类型查询商品信息,可以筛选进行排序)
    public  function actionGoodstype(){
        $cid=\Yii::$app->request->post('cid');
        $page = empty(\Yii::$app->request->post('page')) ? 1 : \Yii::$app->request->post('page');
        $request = \Yii::$app->request;
        $type = $request->post('type');
        $sort = $request->post('sort');
        if ($sort==0){
            $order='id';
        }elseif ($sort==1){
            $order='start_time desc';
        }elseif ($sort==2){
            $order='sales_num';
        }elseif ($sort==3){
            $order='sales_num desc';
        }elseif ($sort==4){
            $order='coupon_price';
        }elseif ($sort==5){
            $order='coupon_price desc';
        }elseif ($sort==6){
            $order='commission_money';
        }elseif ($sort==7){
            $order='commission_money desc';
        }else{
            return $this->responseJson(101, '', '排序不存在');
        }


        if ($cid){
            $res = Goods::find()->select(['id','cid','origin_id','title','sub_title','type','origin_price','coupon_price','thumb','sales_num','coupon_money','commission_money','description','coupon_link'])
                ->where(['type'=>$type,'cid'=>$cid])->andWhere(['>','end_time',time()])->orderBy($order)->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }else{
            $res = Goods::find()->select(['id','cid','origin_id','title','sub_title','type','origin_price','coupon_price','thumb','sales_num','coupon_money','commission_money','description','coupon_link'])
                ->where(['type'=>$type])->andWhere(['>','end_time',time()])->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }

        return $this->responseJson(200, $res, '商品信息');
    }
/*
 * 上百券
 * */
    public  function actionSbq(){
        $cid=\Yii::$app->request->post('cid');
        $page = empty(\Yii::$app->request->post('page')) ? 1 : \Yii::$app->request->post('page');
        $request = \Yii::$app->request;
        $sort = $request->post('sort');
        if ($sort==0){
            $order='id';
        }elseif ($sort==1){
            $order='start_time desc';
        }elseif ($sort==2){
            $order='sales_num';
        }elseif ($sort==3){
            $order='sales_num desc';
        }elseif ($sort==4){
            $order='coupon_price';
        }elseif ($sort==5){
            $order='coupon_price desc';
        }elseif ($sort==6){
            $order='commission_money';
        }elseif ($sort==7){
            $order='commission_money desc';
        }else{
            return $this->responseJson(101, '', '排序不存在');
        }

        if ($cid){
            $res = Goods::find()->select(['id','cid','origin_id','title','sub_title','type','origin_price','coupon_price','thumb','coupon_money','commission_money','description','coupon_link'])
                ->where(['cid'=>$cid,'type'=>[11,12]])->andWhere(['>','coupon_money',100])->andWhere(['>','end_time',time()])->orderBy($order)->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }else{
            $res = Goods::find()->select(['id','cid','origin_id','title','sub_title','type','origin_price','coupon_price','thumb','coupon_money','commission_money','description','coupon_link'])
                ->where(['>','end_time',time()])->andWhere(['type'=>[11,12]])->andWhere(['>','coupon_money',100])->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }

        return $this->responseJson(200, $res, '商品信息');
    }


    /*
    * 淘宝联盟母婴主题
    *
    * */
    public function actionMyzt(){
        $page=\Yii::$app->request->post('page');
        $lv=\Yii::$app->request->post('lv');
        $alimm_pid=Yii::$app->request->post('alimm_pid');
        $uid =explode('_',$alimm_pid);
        $c = new TopClient;
        $c->appkey =Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkDgOptimusMaterialRequest;
        $req->setPageSize('20');
        $req->setAdzoneId($uid[3]);
        $req->setPageNo($page);
        $req->setMaterialId("4040");
        $resp = $c->execute($req);
        $res=json_decode(json_encode($resp),true);
        if(isset($res['result_list'])) {
            $result = [];
            foreach ($res['result_list']['map_data'] as $key => $value) {
                $result['origin_id'] = $value['item_id'];//商品ID
                $result['title'] = $value['title'];//商品标题
                $result['sales_num']=$value['volume'];//销量->30销量
                $result['origin_price'] = $value['zk_final_price']+$value['coupon_amount'];//商品原价
                $result['coupon_price'] = $value['zk_final_price'];//券后价
                if (substr($value['pict_url'],0,4)!=='http'){
                    $result['thumb'] = 'https:'.$value['pict_url'];//商品图
                }else{
                    $result['thumb'] = $value['pict_url'];//商品图
                }
//                $id=$result['origin_id'];
//                $tp=$this->actionInfo($id);//调用详情图
//                $result['imags'] = $tp;//商品详情图
                $result['coupon_money'] = $value['coupon_amount'];//优惠券金额
                $commission = round($value['zk_final_price'] * ($value['commission_rate']/100), 2);//佣金金额
                $commission_money=$this->actionDivide($commission,$lv);
                if (is_array($commission_money)){
                    $result['commission_money']=$commission_money[0];
                    $result['commission_money2']=$commission_money[1];
                }else{
                    $result['commission_money']=$commission_money;
                }
                if (isset($value['coupon_click_url'])){
                    $result['coupon_link'] = $value['coupon_click_url'];//优惠链接
                }else{
                    $result['coupon_link'] = $value['click_url'];//优惠链接
                }
                $ress[] = $result;
            }
            return $this->responseJson(200, $ress, '返回数据成功');
        }
    }

    /*
     *淘宝联盟潮流范
     *
     * */
    public function actionClf(){
        $page=\Yii::$app->request->post('page');
        $lv=\Yii::$app->request->post('lv');
        $alimm_pid=Yii::$app->request->post('alimm_pid');
        $uid =explode('_',$alimm_pid);
        $c = new TopClient;
        $c->appkey =Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkDgOptimusMaterialRequest;
        $req->setPageSize('20');
        $req->setAdzoneId($uid[3]);
        $req->setPageNo($page);
        $req->setMaterialId("4093");
        $resp = $c->execute($req);
        $res=json_decode(json_encode($resp),true);
        if(isset($res['result_list'])) {
            $result = [];
            foreach ($res['result_list']['map_data'] as $key => $value) {
                $result['origin_id'] = $value['item_id'];//商品ID
                $result['title'] = $value['title'];//商品标题
                $result['sales_num']=$value['volume'];//销量->30销量
                $result['origin_price'] = $value['zk_final_price']+$value['coupon_amount'];//商品原价
                $result['coupon_price'] = $value['zk_final_price'];//券后价
                if (substr($value['pict_url'],0,4)!=='http'){
                    $result['thumb'] = 'https:'.$value['pict_url'];//商品图
                }else{
                    $result['thumb'] = $value['pict_url'];//商品图
                }
                $result['coupon_money'] = $value['coupon_amount'];//优惠券金额
                $commission = round($value['zk_final_price'] * ($value['commission_rate']/100), 2);//佣金金额
                $commission_money=$this->actionDivide($commission,$lv);
                if (is_array($commission_money)){
                    $result['commission_money']=$commission_money[0];
                    $result['commission_money2']=$commission_money[1];
                }else{
                    $result['commission_money']=$commission_money;
                }
                if (isset($value['coupon_click_url'])){
                    $result['coupon_link'] = $value['coupon_click_url'];//优惠链接
                }else{
                    $result['coupon_link'] = $value['click_url'];//优惠链接
                }
                $ress[] = $result;
            }
            return $this->responseJson(200, $ress, '返回数据成功');
        }
    }
    /*
     * 好券直播
     * */
    public function actionHqzb(){
        $page=\Yii::$app->request->post('page');
        $lv=\Yii::$app->request->post('lv');
        $material_id=\Yii::$app->request->post('cid');
        $alimm_pid=Yii::$app->request->post('alimm_pid');
        $uid =explode('_',$alimm_pid);
        $c = new TopClient;
        $c->appkey =Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkDgOptimusMaterialRequest;
        $req->setPageSize('20');
        $req->setAdzoneId($uid[3]);
        $req->setPageNo($page);
        $req->setMaterialId($material_id);
        $resp = $c->execute($req);
        $res=json_decode(json_encode($resp),true);
        if(isset($res['result_list'])) {
            $result = [];
            foreach ($res['result_list']['map_data'] as $key => $value) {
                $result['origin_id'] = $value['item_id'];//商品ID
                $result['title'] = $value['title'];//商品标题
                $result['sales_num']=$value['volume'];//销量->30销量
                $result['origin_price'] = $value['zk_final_price']+$value['coupon_amount'];//商品原价
                $result['coupon_price'] = $value['zk_final_price'];//券后价
                if (substr($value['pict_url'],0,4)!=='http'){
                    $result['thumb'] = 'https:'.$value['pict_url'];//商品图
                }else{
                    $result['thumb'] = $value['pict_url'];//商品图
                }

                $result['coupon_money'] = $value['coupon_amount'];//优惠券金额
                $commission = round($value['zk_final_price'] * ($value['commission_rate']/100), 2);//佣金金额
                $commission_money=$this->actionDivide($commission,$lv);
                if (is_array($commission_money)){
                    $result['commission_money']=$commission_money[0];
                    $result['commission_money2']=$commission_money[1];
                }else{
                    $result['commission_money']=$commission_money;
                }
                if (isset($value['coupon_click_url'])){
                    $result['coupon_link'] = 'https:'.$value['coupon_click_url'];//优惠链接
                }else{
                    $result['coupon_link'] = $value['click_url'];//优惠链接
                }
                $ress[] = $result;
            }
            return $this->responseJson(200, $ress, '返回数据成功');
        }
    }

    /*
 * 有好货
 * */
    public function actionYhh(){

        $page=\Yii::$app->request->post('page');
        $lv=\Yii::$app->request->post('lv');
        $alimm_pid=Yii::$app->request->post('alimm_pid');
        $uid =explode('_',$alimm_pid);
        $c = new TopClient;
        $c->appkey =Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkDgOptimusMaterialRequest;
        $req->setPageSize('20');
        $req->setAdzoneId($uid[3]);
        $req->setPageNo($page);
        $req->setMaterialId('4092');
        $resp = $c->execute($req);
        $res=json_decode(json_encode($resp),true);
        if(isset($res['result_list'])) {
            $result = [];
            foreach ($res['result_list']['map_data'] as $key => $value) {
                $result['origin_id'] = $value['item_id'];//商品ID
                $result['title'] = $value['title'];//商品标题
                $result['origin_price'] = $value['zk_final_price']+$value['coupon_amount'];//商品原价
                $result['coupon_price'] = $value['zk_final_price'];//券后价
                $result['sales_num']=$value['volume'];//销量->30销量
                if (substr($value['pict_url'],0,4)!=='http'){
                    $result['thumb'] = 'https:'.$value['pict_url'];//商品图
                }else{
                    $result['thumb'] = $value['pict_url'];//商品图
                }
                $result['coupon_money'] = $value['coupon_amount'];//优惠券金额
                $commission = round($value['zk_final_price'] * ($value['commission_rate']/100), 2);//佣金金额
                $commission_money=$this->actionDivide($commission,$lv);
                if (is_array($commission_money)){
                    $result['commission_money']=$commission_money[0];
                    $result['commission_money2']=$commission_money[1];
                }else{
                    $result['commission_money']=$commission_money;
                }
                if (isset($value['coupon_click_url'])){
                    $result['coupon_link'] = $value['coupon_click_url'];//优惠链接
                }else{
                    $result['coupon_link'] = $value['click_url'];//优惠链接
                }
                $ress[] = $result;
//                print_r($ress);
//                exit;
            }
            return $this->responseJson(200, $ress, '返回数据成功');
        }
    }

    /*
    * 淘宝联盟聚划算
    * */
    public function actionLmjhs(){

        $cid=empty(Yii::$app->request->post('cid')) ? 1 : \Yii::$app->request->post('cid');
        $alimm_pid=Yii::$app->request->post('alimm_pid');
        $page=Yii::$app->request->post('page');
        $lv=Yii::$app->request->post('lv');

        $word=GoodsCategory::findOne(['id'=>$cid])->title;
        $uid =explode('_',$alimm_pid);
        $c = new TopClient;
        $c->appkey = Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');//'484ecc6852a5b6fef74db48f733261b0';
        $req = new TbkDgMaterialOptionalRequest;
        $req->setPageSize("20");
        $req->setPageNo($page);
        $req->setAdzoneId($uid[3]);
        $req->setQ($word);
        $resp = $c->execute($req);
        $res=json_decode(json_encode($resp),true);
//print_r($res);exit;
        if(isset($res['result_list'])){
            $result=[];
            foreach ($res['result_list']['map_data'] as $key=>$value){
//                $result['id']=$value['category_id'];
                $result['cid']=$cid;//分类
                $result['origin_id']=$value['num_iid'];//商品ID
                $result['title']=$value['title'];//商品标题
                $result['sub_title']=$value['short_title'];//商品短标题
                $result['origin_price']=$value['reserve_price'];//商品原价
                $result['coupon_price']=$value['zk_final_price'];//券后价
                $result['sales_num']=$value['volume'];//销量->30销量
                if (substr($value['pict_url'],0,4)!=='http'){
                    $result['thumb'] = 'https:'.$value['pict_url'];//商品图
                }else{
                    $result['thumb'] = $value['pict_url'];//商品图
                }
                $result['coupon_money']=$value['reserve_price']-$value['zk_final_price'];//优惠券金额
                $commission=round($value['zk_final_price']*($value['commission_rate']/10000),2);//佣金金额
                $commission_money=$this->actionDivide($commission,$lv);
                if (is_array($commission_money)){
                    $result['commission_money']=$commission_money[0];
                    $result['commission_money2']=$commission_money[1];
                }else{
                    $result['commission_money']=$commission_money;
                }
                $result['coupon_link']=$value['item_url'];//优惠链接
                $ress[]=$result;
            }
            return $this->responseJson(200,$ress,'返回数据成功');
        }

    }

    /*
    * 淘宝联盟特惠-----集米优惠热销榜单
    * */
    public function actionRxbd(){
        $page=\Yii::$app->request->post('page');
        $lv=\Yii::$app->request->post('lv');
        $alimm_pid=Yii::$app->request->post('alimm_pid');
        $uid =explode('_',$alimm_pid);
        $c = new TopClient;
        $c->appkey =Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkDgOptimusMaterialRequest;
        $req->setPageSize('20');
        $req->setAdzoneId($uid[3]);
        $req->setPageNo($page);
        $req->setMaterialId("4094");
        $resp = $c->execute($req);
        $res=json_decode(json_encode($resp),true);
        if(isset($res['result_list'])) {
            $result = [];
            foreach ($res['result_list']['map_data'] as $key => $value) {
                $result['origin_id'] = $value['item_id'];//商品ID
                $result['title'] = $value['title'];//商品标题
                $result['origin_price'] = $value['zk_final_price']+$value['coupon_amount'];//商品原价
                $result['coupon_price'] = $value['zk_final_price'];//券后价
                $result['sales_num']=$value['volume'];//销量->30销量
                if (substr($value['pict_url'],0,4)!=='http'){
                    $result['thumb'] = 'https:'.$value['pict_url'];//商品图
                }else{
                    $result['thumb'] = $value['pict_url'];//商品图
                }
                $result['coupon_money'] = $value['coupon_amount'];//优惠券金额
                $commission = round($value['zk_final_price'] * ($value['commission_rate']/100), 2);//佣金金额
                $commission_money=$this->actionDivide($commission,$lv);
                if (is_array($commission_money)){
                    $result['commission_money']=$commission_money[0];
                    $result['commission_money2']=$commission_money[1];
                }else{
                    $result['commission_money']=$commission_money;
                }

                if (isset($value['coupon_click_url'])){
                    $result['coupon_link'] = $value['coupon_click_url'];//优惠链接
                }else{
                    $result['coupon_link'] = $value['click_url'];//优惠链接
                }

                $ress[] = $result;
            }
            return $this->responseJson(200, $ress, '返回数据成功');
        }
    }

    public function actionDivide($money,$lv){
        $commission=Commissionset::find()->asArray()->one();
        $detain = $money * ($commission['detain']/100);//平台扣留佣金
        $kl=round($money-$detain,2);//平台扣留之后佣金
        if ($lv==1){
            $commission_money1=round($kl*($commission['zghy']/100),2);
            $commission_money2=round($kl*($commission['zgdl']/100),2);
            $commission_money=[$commission_money1,$commission_money2];
        }elseif ($lv==2){
            $commission_money=round($kl*($commission['zgdl']/100),2);
        }else{
            $commission_money1=round($kl*($commission['zghy']/100),2);
            $commission_money2=round($kl*($commission['zgdl']/100),2);
            $commission_money=[$commission_money1,$commission_money2];
        }
        return $commission_money;
    }
    /*
     * 今日上新
     * */
    public function actionJrsx(){
        $starttime = strtotime(date("Y-m-d"),time());//今日开始时间
        $endtime=$starttime+60*60*24;//今日结束时间

        $cid=\Yii::$app->request->post('cid');
        $page = empty(\Yii::$app->request->post('page')) ? 1 : \Yii::$app->request->post('page');
        $request = \Yii::$app->request;
        $sort = $request->post('sort');
        if ($sort==0){
            $order='id';
        }elseif ($sort==1){
            $order='start_time desc';
        }elseif ($sort==2){
            $order='sales_num';
        }elseif ($sort==3){
            $order='sales_num desc';
        }elseif ($sort==4){
            $order='coupon_price';
        }elseif ($sort==5){
            $order='coupon_price desc';
        }elseif ($sort==6){
            $order='commission_money';
        }elseif ($sort==7){
            $order='commission_money desc';
        }else{
            return $this->responseJson(101, '', '排序不存在');
        }

        if ($cid){
            $res = Goods::find()->select(['id','cid','origin_id','title','sub_title','type','origin_price','coupon_price','thumb','coupon_money','commission_money','sales_num','description','coupon_link'])
                ->where(['cid'=>$cid,'type'=>[11,12]])->andWhere(['>','end_time',time()])/*->andWhere(['>','created_at',$starttime])->andWhere(['<','created_at',$endtime])*/->orderBy('start_time desc'/*$order*/)->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }else{
            $res = Goods::find()->select(['id','cid','origin_id','title','sub_title','type','origin_price','coupon_price','thumb','coupon_money','commission_money','sales_num','description','coupon_link'])
                ->andWhere(['type'=>[11,12]])->where(['>','end_time',time()])/*->andWhere(['>','created_at',$starttime])->andWhere(['<','created_at',$endtime])*/->orderBy('start_time desc')->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }

        return $this->responseJson(200, $res, '商品信息');
    }

    public function actionGetsharepic()
    {
        $params=\Yii::$app->request->post();
        $origin_price=explode(',',$params['origin_price']);
        $now_price=explode(',',$params['now_price']);
        $qr_url=explode(',',$params['qr_url']);
        $title=explode(',',$params['title']);
        $pic_url=explode(',',$params['pic_url']);

        if (count($origin_price)!==count($now_price) || count($qr_url)!==count($pic_url)){
            return $this->responseJson(101,$params,'缺少参数');
        }
        //$fx[]=['origin_price'=>$origin_price,'now_price'=>$now_price,'qr_url'=>$qr_url,'title'=>$title,'pic_url'=>$pic_url];
        //$fz=[];
//        $share= '';
        for ($i=0;$i<count($origin_price);$i++){
            /* $tmp = [
                 'origin_price' => $origin_price[$i],
                 'now_price'     =>  $now_price[$i],
                 'qr_url'        =>  $qr_url[$i],
                 'title'         =>  $title[$i],
                 'pic_url'       =>  $pic_url[$i]
             ];
             $fz[] = $tmp;*/
            $url='http://api.weibo.com/2/short_url/shorten.json?source=1681459862&url_long='.$qr_url[$i];
            $urls=json_decode(file_get_contents($url),true);
            $createPic = new Createsharepic();
            $file_name = md5(md5(time().mt_rand(1000,9999))).'.png';
            $pic = './../../public/uploads/share/'.$file_name;
            $createPic->setOriginPrice($origin_price[$i])
                ->setNowPrice($now_price[$i])
                ->setCouponPrice($origin_price[$i] - $now_price[$i])
                ->setQrUrl($urls['urls'][0]['url_short'])//$params['qr_url']
                ->setTitle($title[$i])
                ->setGoodsPicUrl($pic_url[$i])
                ->setOutFilename($pic)
                ->create();

            $pics=substr($pic,8,100);
            $share[] = Yii::$app->urlManager->getHostInfo().'/uploads/share/'.$file_name;
        }

        return $this->responseJson(200,$share,'返回数据成功');
    }

    //  淘宝详情
    public function actionInfo($id){

        $c = new TopClient;
        $c->appkey = Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkItemInfoGetRequest;
        $req->setNumIids($id);
        $resp = $c->execute($req);
        $result=(json_decode(json_encode($resp),true));
        $res=$result['results']['n_tbk_item'];

        if (!empty($res)&& isset($res['small_images'])) {
            return $res['small_images'];
        }else{
            return null;
        }

    }


}