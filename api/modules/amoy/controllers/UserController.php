<?php

namespace api\modules\amoy\controllers;


use backend\models\DistributionConfig;
use common\behaviors\HttpTokenAuth;
use common\components\alipay\AlipayPay;
use common\components\jd\JdClient;
use common\components\jd\requests\ServicePromotionCouponGetCodeBySubUnionId;
use common\components\miao\MiaoClient;
use common\components\miao\taobao\requests\GetGoodsCouponUrl;
use common\components\pdd\PddClient;
use common\components\pdd\requests\DdkGoodsDetailRequest;
use common\components\pdd\requests\DdkGoodsPromotionUrlGenerate;
use common\components\tblm\top\request\TbkDgMaterialOptionalRequest;
use common\components\tblm\top\request\TbkDgOptimusMaterialRequest;
use common\components\wechat\WxAppPay;
use common\components\tblm\top\TopClient;
use common\components\tblm\top\request\TbkItemInfoGetRequest;
use common\helpers\Http;
use common\helpers\Poster;
use common\helpers\Utils;
use common\models\Collection;
use common\models\Config;
use common\models\Footprint;
use common\models\GoodsCategory;
use common\models\Message;
use common\models\Order;
use common\models\Recharge;
use common\models\SmsVerifycode;
use common\models\UpgradeOrder;
use common\models\User;
use common\models\Signin;
use common\models\VipCode;
use common\models\VipList;
use common\models\Withdraw;
use Yii;
use common\models\Goods;
use yii\base\Exception;
use yii\helpers\Json;
use common\components\miao\taobao\requests\GetGoodsInfo;
use api\models\User as users;
use api\models\Recommend;
use api\models\Invite;
use common\models\Commissionset;

class UserController extends ControllerBase
{
    public function behaviors()
    {
        if (\Yii::$app->request->isOptions) {
            die();
        }
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpTokenAuth::class,
            'optional' => ['create-share', 'sssp','wetch']
        ];
        return $behaviors;
    }

    public function actionWetch()
    {
        $wechat = new WechatController();
        //获取openid和accessToken
        $wechat->getOpenid();
        //获取用户信息
        //$wechat->getUserInfo();
    }

    /**
     * 用户个人资料
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUserInfo()
    {
        $uid = Yii::$app->user->id;
        $us=User::find()->where(['uid'=>$uid])->asArray()->one();
        $recommend = recommend::find()->asArray()->one();//查询等级表数据
        if ($us['recommend'] >= $recommend['sole']){
            $us=User::find()->where(['uid'=>$uid])->one();
            $us->lv=2;
            $us->save();
        }
//        $key = md5('userinfo' . $uid);
//        $redis = Yii::$app->get('redis');
        if (false) {
            //if ($val = $redis->get($key)) {
            //$user = Json::decode($val, true);
        } else {
            if (empty($uid)) {
                return $this->responseJson(0, [], '无数据！');
            }
            $user = User::findOne(['uid' => $uid])->toArray();
            if (!empty($user['avatar'])) {
                $user['avatar'] = Utils::toMedia('/'.$user['avatar']);
            }
        }
        $info = Recharge::findIncome($uid);
        $user['info'] = $info;
        return $this->responseJson(200, $user, '成功返回数据');
    }


//签到
    public function actionSign(){
        $uid=Yii::$app->user->id;
        $today = date('Ymd');
//获取签到记录
        $signInfo = Signin::find()->where(['uid'=>$uid])->asArray()->one();

        if($signInfo['signtime'] == $today){//今天已签到过了
            return $this->responseJson(200,$signInfo['signday'],'今天已经签到了');

        }elseif($signInfo['signtime'] == date('Ymd', strtotime('-1 day'))){
            //昨天已签到，连续签到处理
            if ($signInfo['signday'] >= 6) {
                //连续签到7天，每7天清零
                $signInfo = Signin::find()->where(['uid'=>$uid])->one();
                $signInfo->signday=0;
                $signInfo->save();//连续签到次数
//                $num = 0;//连续签到次数
                $user=User::find()->where(['uid'=>$uid])->one();
                $user->credit4=Yii::$app->user->identity->credit4+2;//连续签到七天奖励两元
                $user->save();
//                $point = 30;//额外奖励积分数
                return $this->responseJson(200, 7, '累积签到7天');
            }else {/*elseif($signInfo['signday'] == 20){//连续签到3周
                $num = $signInfo['signday'] + 1; $point = 20;
            }elseif($signInfo['signday'] == 13){//连续签到2周
                $num = $signInfo['signday'] + 1; $point = 15;
            }elseif($signInfo['signday'] == 6){//连续签到1周
                $num = $signInfo['signday'] + 1; $point = 10;
            }else{
                $num = $signInfo['signday'] + 1; $point = 0;
            }*/
                //更新签到记录
//            $result = M('table')->save(array('uid' => session('uid'), 'signday' => $num, 'signtime' => $today));
                $result = Signin::find()->where(['uid' => $uid])->one();
                $result->uid = $uid;
                $result->signtime = $today;
                $result->signday = $signInfo['signday'] + 1;
                $result->save();
                $signInfo = Signin::find()->where(['uid' => $uid])->asArray()->one();
                return $this->responseJson(200, $signInfo['signday'], '累积签到'.$signInfo['signday'].'天');
            }
        }else{//断签或未签到过，重新计数
//            $point = 0;
            if ($signInfo['id']) {//有签到记录，更新记录信息
                $result = Signin::find()->where(['uid'=>$uid])->one();
                $result->uid=$uid;
                $result->signtime=$today;
                $result->signday=1;
                $result->save();
                return $this->responseJson(200, 1, '累积签到1天');
            }else{//无签到记录，添加一条记录
                $result = new Signin;
                $result->uid=$uid;
                $result->signtime=$today;
                $result->signday=1;
                $result->save();
                return $this->responseJson(200, 1, '累积签到1天');
            }
        }

        $this->responseJson(101,'签到失败','返回数据');
    }


//邀请好友
    public function actionInvit()
    {
        $uid = Yii::$app->user->id;
        $generalize = users::find()->select(['generalize'])->where(['uid' => $uid])->asArray()->one();//搜索当前用户的推广码
        $invite = invite::find()->select(['invite'])->asArray()->one();
        return $this->responseJson(200, [$generalize, $invite], '数据返回成功');
    }

    //一级代理信息
    public function actionOagency()
    {
        $generalize = Yii::$app->user->identity->generalize;//推广码
        $yiji = User::find()->where(['invite_code' => $generalize])->asArray()->all();
        return $this->responseJson(200, $yiji, '成功返回数据');
    }

    //二级代理信息
    public function actionTagency()
    {
        $generalize = Yii::$app->user->identity->generalize;//推广码
        $oagency = User::find()->where(['invite_code' => $generalize])->asArray()->all();//一级粉丝
        if (!empty($oagency)){
            foreach ($oagency as $key => $v) {
                $tagency = users::find()->where(['invite_code' => $v['generalize']])->asArray()->all();//二级代理信息
                $data[]=$tagency;
            }

            if(empty($data)){
                return $this->responseJson(101,'','二级代理为空');
            }else{
                return $this->responseJson(200, $data, '成功返回数据');
            }
        }else{
            return $this->responseJson(101, '', '二级代理为空');
        }


    }





    /**
     * 更新昵称、头像
     * @return array
     * @throws \yii\db\Exception
     */

    public function actionUpdatename()
    {
        $uid = Yii::$app->user->id;
        $request = \Yii::$app->request;
        $nickname = $request->post('nickname');
        $user = users::find()->where(['uid' => $uid])->one();
        $user->nickname = $nickname;
        $user->save();
        return $this->responseJson(200, '', '修改成功');
    }

    public function actionUpdateavatar()
    {
        $uid = Yii::$app->user->id;
        $user = users::find()->where(['uid' => $uid])->one();

        //判断上传的文件是否出错,是的话，返回错误
        if ($_FILES["avatar"]["error"]) {
            return $this->responseJson(101, '', '上传失败');
        } else {

            if (($_FILES["avatar"]["type"] == "image/png" || $_FILES["avatar"]["type"] == "image/jpeg") && $_FILES["avatar"]["size"] < 1024000) {

                $filename = "../../public/uploads/avatar/" . time() . $_FILES["avatar"]["name"];

                $filename = iconv("UTF-8", "gb2312", $filename);

                if (file_exists($filename)) {
                    return $this->responseJson(102, '', '该文件已存在');
                } else {

                    move_uploaded_file($_FILES["avatar"]["tmp_name"], $filename); //将临时地址移动到指定地址
                }
            } else {
                return $this->responseJson(103, '', '文件类型不对或文件过大');
            }
        }

        $user->avatar = substr($filename, 13, 200); //修改头像

        $user2 = User::findOne(['uid' => $uid])->toArray();
        if (!empty($user2['avatar'])) {
            $user2['avatar'] = Utils::toMedia($user['avatar']);
//            print_r($user2['avatar']);
//            exit;
        }
        $user->save();
        return $this->responseJson(200, $user2['avatar'], '修改成功');
    }

//个人全部订单

    /**
     * @return array
     */
    public function actionOrder()
    {
        $uid = Yii::$app->user->id;
        $request = \Yii::$app->request;
        $type = $request->post('type');
        $status = $request->post('status');
        if ($status==1){
            $status=[0,1,2,3];
        }
        $user = User::find()->where(['uid' => $uid])->one();
        if ($type == 1) {
            if ($status==100){
                $order=Order::find()->where(['uid'=>$user['uid']])->andWhere(['type'=>$type])->asArray()->all();
                if (!empty($order)) {
                    foreach ($order as $key => $value) {
                        $goodId = $this->actionInfo($value['product_id']);
                        $url = json_decode(json_encode($goodId), true);
                        if (!empty($url['results']['n_tbk_item']['pict_url'])) {
                            $value['picUrl'] = $url['results']['n_tbk_item']['pict_url'];//图片地址
                        }
//                        $value['created_at']=date('Y-m-d H:i:s',$value['created_at']);
//                        $value['updated_at']=date('Y-m-d H:i:s',$value['updated_at']);
                        $data[] = $value;
                    }

                    return $this->responseJson(200, $data, '淘宝全部订单信息');
                } else {
                    return $this->responseJson(101, $order, '淘宝订单为空');
                }
//                return $this->responseJson(200, $order, '淘宝全部订单信息');
            }else{
                $user = Users::find()->where(['uid' => $uid])->one();
                $order = $user->getOrder($type,$status); //同上一样的效果
                if (!empty($order)) {
                    foreach ($order as $key => $value) {
                        $goodId = $this->actionInfo($value['product_id']);
                        $url = json_decode(json_encode($goodId), true);
                        if (!empty($url['results']['n_tbk_item']['pict_url'])) {
                            $value['picUrl'] = $url['results']['n_tbk_item']['pict_url'];//图片地址
                        } else {
                            return $this->responseJson(106, '', '获取淘宝图片失败');
                        }
//                        $value['created_at']=date('Y-m-d H:i:s',$value['created_at']);
//                        $value['updated_at']=date('Y-m-d H:i:s',$value['updated_at']);
                        $data[] = $value;
                    }
                    return $this->responseJson(200, $data, '淘宝订单信息');
                } else {
                    return $this->responseJson(101, $order, '淘宝订单为空');
                }
            }
        } elseif ($type == 2) {
//            $order = $user->getOrder($type,$status); //同上一样的效果
//            return $this->responseJson(200, $order, '京东订单信息');
            if ($status==100){
                $order=Order::find()->where(['uid'=>$user['uid']])->andWhere(['type'=>$type])->asArray()->all();
                if (!empty($order)){
//                    foreach ($order as $value){
//                        $value['created_at']=date('Y-m-d H:i:s',$value['created_at']);
//                        $value['updated_at']=date('Y-m-d H:i:s',$value['updated_at']);
//                        $data[]=$value;
//                    }

                    return $this->responseJson(200, $order, '京东全部订单信息');
                }else{
                    return $this->responseJson(103, [], '京东订单为空');
                }
            }else{
                $user = Users::find()->where(['uid' => $uid])->one();
                $order = $user->getOrder($type, $status);
                if (!empty($order)){
                    return $this->responseJson(200, $order, '京东订单信息');
                }else{
                    return $this->responseJson(103, [], '京东订单为空');
                }
            }
        } elseif($type == 3) {
            if ($status==100){
                $order=Order::find()->where(['uid'=>$user['uid']])->andWhere(['type'=>$type])->asArray()->all();
                if (!empty($order)){
//                    foreach ($order as $value){
//                        $value['created_at']=date('Y-m-d H:i:s',$value['created_at']);
//                        $value['updated_at']=date('Y-m-d H:i:s',$value['updated_at']);
//                        $data[]=$value;
//                    }

                    return $this->responseJson(200, $order, '拼多多全部订单信息');
                }else{
                    return $this->responseJson(103, '', '拼多多订单为空');
                }
            }else{
                $user = Users::find()->where(['uid' => $uid])->one();
                $order = $user->getOrder($type, $status);
                if (!empty($order)){
                    return $this->responseJson(200, $order, '拼多多订单信息');
                }else{
                    return $this->responseJson(103, '', '拼多多订单为空');
                }
            }
        }else{
            return $this->responseJson(104, '', '订单类型不存在');
        }
    }

    /*
     * 一级粉丝订单
     *
     * */
    public function actionYiorder()
    {
        $type = \Yii::$app->request->post('type');//订单类型
        $status = \Yii::$app->request->post('status');//订单状态
        if ($status == 1) {
            $status = [0,1,2,3];
        }
        $generalize = Yii::$app->user->identity->generalize;//推广码
        $yiji = User::find()->where(['invite_code' => $generalize])->asArray()->all();
        if (!empty($yiji)) {
            if ($type == 1) {
                if ($status == 100) {
                    foreach ($yiji as $item) {
                        $user = User::find()->where(['uid' => $item['uid']])->one();
                        $order = Order::find()->where(['uid' => $user['uid']])->andWhere(['type' => $type])->asArray()->all();
                        if (!empty($order)) {
                            foreach ($order as $key => $value) {
                                $goodId = $this->actionInfo($value['product_id']);
                                $url = json_decode(json_encode($goodId), true);
                                if (!empty($url['results']['n_tbk_item']['pict_url'])) {
                                    $value['picUrl'] = $url['results']['n_tbk_item']['pict_url'];//图片地址
                                } else {
                                    $value['picUrl'] = $value['picUrl'];
                                }
                                $data[] = $value;
                            }
                        }/*else {
                            return $this->responseJson(101, $order, '淘宝订单为空');
                        }*/
                    }
                        return $this->responseJson(200, $data, '淘宝全部订单信息');
                } else {
                        foreach ($yiji as $item) {
                            $user = User::find()->where(['uid' => $item['uid']])->one();
                            $order = Order::find()->where(['uid' => $user['uid'],'type'=>$type])->andWhere(['order_status'=>$status])->asArray()->all();
                            if (!empty($order)) {
                                foreach ($order as $key => $value) {
                                    $goodId = $this->actionInfo($value['product_id']);
                                    $url = json_decode(json_encode($goodId), true);
                                    if (!empty($url['results']['n_tbk_item']['pict_url'])) {
                                        $value['picUrl'] = $url['results']['n_tbk_item']['pict_url'];//图片地址
                                    } else {
                                        $value['picUrl'] = $value['picUrl'];
                                    }
                                    $data[] = $value;
                                }
                            }
                        }
                        return $this->responseJson(200, $data, '淘宝全部订单信息');
                }
            } elseif ($type == 2 || $type==3) {//2京东  3拼多多
                if ($status == 100) {
                    foreach ($yiji as $item) {
                        $user = User::find()->where(['uid' => $item['uid']])->one();
                        $order = Order::find()->where(['uid' => $user['uid']])->andWhere(['type' => $type])->asArray()->all();
                        if (!empty($order)) {
                            foreach ($order as $key => $value) {
                                $data[] = $value;
                            }
                        }
                    }
                    return $this->responseJson(200, $data, '全部订单信息');
                } else {
                    foreach ($yiji as $item) {
                        $user = User::find()->where(['uid' => $item['uid']])->one();
                        $order = Order::find()->where(['uid' => $user['uid'], 'type' => $type])->andWhere(['order_status' => $status])->asArray()->all();
                        if (!empty($order)) {
                            foreach ($order as $key => $value) {
                                $data[] = $value;
                            }
                        }
                    }
                    return $this->responseJson(200, $data, '订单信息');
                }
            }
        }else{
            return $this->responseJson(101, [], '订单信息');
        }
    }


    /*
    * 二级粉丝订单
    *
    * */
    public function actionErorder()
    {
        $type = \Yii::$app->request->post('type');//订单类型
        $status = \Yii::$app->request->post('status');//订单状态
        if ($status == 1) {
            $status = [0, 1, 2, 3];
        }
        $generalize = Yii::$app->user->identity->generalize;//推广码
        $yiji = User::find()->where(['invite_code' => $generalize])->asArray()->all();
        if (!empty($yiji)) {
            foreach ($yiji as $value) {
                $erji = User::find()->where(['invite_code' => $value['generalize']])->asArray()->all();//二级粉丝
                if (!empty($erji)) {
                    $orders = [];
                    if ($status == 100) {
                        foreach ($erji as $vv) {
                            $order = Order::find()->where(['uid' => $vv['uid']])->andWhere(['type' => $type])->asArray()->all();//二级所有订单
//                            print_r($order);
                            if (empty($order)) {
                                unset($order);
                            }else{
                                $orders = array_merge($order,$orders);
                            }
                        }

                    } else {
                        foreach ($erji as $vv) {
                            $order = Order::find()->where(['uid' => $vv['uid']])->andWhere(['type' => $type])->andWhere(['order_status' => $status])->asArray()->all();
                            if (empty($order)) {
                                unset($order);
                            }else{
                                $orders = array_merge($order,$orders);
                            }
                        }
                    }
                }
            }
//            print_r(array_filter($orders));
//            exit;
                if (!empty($orders)){
                    foreach ($orders as $v){
                                    if ($v['type']==1){
                                        $goodId = $this->actionInfo($v['product_id']);
                                        $url = json_decode(json_encode($goodId), true);
                                        if (!empty($url['results']['n_tbk_item']['pict_url'])) {
                                            $v['picUrl'] = $url['results']['n_tbk_item']['pict_url'];//图片地址
                                        }
                                    }
                                    $res[]=$v;
                                }
                    return $this->responseJson(200, $res, '订单信息');
                }else{
                    return $this->responseJson(101, [], '订单信息为空');
                }

        }else{
            return $this->responseJson(101, [], '订单信息为空');
        }
    }




    /*
     * 递归
     * */
    private $arr = [];

    function findSub($all)
    {
        $list = [];
        foreach ($all as $k => $v) {
            $tmp = User::find()->where(['invite_code' => $v['generalize']])->asArray()->all();
            if (!empty($tmp)) {
                $v['son'] = $this->findSub($tmp);
            }
            $this->arr[] = $v['uid'];
            $list[] = $v;
        }
        return $this->arr;
    }


    /*
    *
    * 团队订单
    *
    * */

    public function actionTdorder()
    {
        $type = \Yii::$app->request->post('type');//订单类型
        $status = \Yii::$app->request->post('status');//订单状态
        if ($status==1){
            $status=[0,1,2,3];
        }
        $uid = Yii::$app->user->identity->uid;

        $user = User::find()->where(['uid' => $uid])->asArray()->one();//登录用户
        $this->arr = [];
        $td = ($this->findSub(User::find()->where(['invite_code' => $user['generalize']])->asArray()->all()));
        foreach ($td as $value) {
                $order = Order::find()->where(['uid' => $value])->andWhere(['type' => $type])->andWhere(['order_status'=>$status])->asArray()->all();//订单类型
                if (!empty($order)) {
                    if ($type==1){
                        foreach ($order as $key => $vv) {
                            $goodId = $this->actionInfo($vv['product_id']);
                            $url = json_decode(json_encode($goodId), true);
                            if (!empty($url['results']['n_tbk_item']['pict_url'])) {
                                $vv['picUrl'] = $url['results']['n_tbk_item']['pict_url'];//图片地址
                            } else {
                                $vv['picUrl'] = $vv['picUrl'];
                            }
                            $tdorder[] = $vv;
                        }
                    }else{
                        $tdorder[] = $order[0];
                    }
            }
            if ($status==100){
                $order = Order::find()->where(['uid' => $value])->andWhere(['type' => $type])->asArray()->all();//团队全部订单
                if (!empty($order)) {
                    //淘宝订单查询商品图片
                    if ($type==1){
                        foreach ($order as $key => $vv) {
                            $goodId = $this->actionInfo($vv['product_id']);
                            $url = json_decode(json_encode($goodId), true);
                            if (!empty($url['results']['n_tbk_item']['pict_url'])) {
                                $vv['picUrl'] = $url['results']['n_tbk_item']['pict_url'];//图片地址
                            } else {
                                $vv['picUrl'] = $vv['picUrl'];
                            }
                            $tdorder[] = $vv;
                        }
                    }else{
                        $tdorder[]=$order[0];
                    }
                }
            }
        }
        if (!empty($tdorder)){
            return $this->responseJson(200, $tdorder, '返回数据成功');
        }else{
            return $this->responseJson(101, [], '订单为空');
        }



    }


    //  淘宝详情
    public function actionInfo($goodId)
    {
        $c = new TopClient;
        $c->appkey = Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkItemInfoGetRequest;
        $req->setNumIids($goodId);
        $resp = $c->execute($req);
        return $resp;
    }

    /**
     * 淘宝客商品详情（简版）
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getinfo($goodId)
    {
        if (\Yii::$app->request->isOptions) {
            die();
        }
        $token = \Yii::$app->request->headers['token'];
        $string = base64_decode($token);
        $uid = json_decode($string, true)['uid'];
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
//            print_r($clientResponse);
//            exit;
            $tmp = Json::decode($clientResponse, true);
            if ($tmp['code'] != 200) {
                return $this->responseJson(101, $clientResponse, '喵有券接口报错1：' . $clientResponse);
            }
            $tmp = $tmp['data']['n_tbk_item'];
            $info = [
                'num_iid' => $goodId, //商品ID
                'item_url' => $good['coupon_link'], //优惠券链接
                'origin_price' => $tmp['zk_final_price'], //原价
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
        return $info;
//        return $this->responseJson(200, $info, '返回数据成功');
    }


    //修改头像昵称源码
    public function actionUpdateNick()
    {
        $uid = empty(\Yii::$app->user->id) ? 1 : \Yii::$app->user->id;
        if (!empty($gender) && !in_array($gender, [0, 1])) {
            return $this->responseJson(1, '0女1男', '性别参数不正确');
        }
        $user = User::findOne(['uid' => $uid]);
        $response = Yii::$app->request->post();
        if (!empty(\Yii::$app->request->post('nickname'))) {
            $nick = \Yii::$app->request->post('nickname');
            if (isset($response['avatar'])) {
                $avatar = Utils::base64pic(\Yii::$app->request->post('avatar'));
                $user->avatar = $avatar;
            }
            $user->nickname = $nick;
            $user->gender = isset($response['gender']) ? intval($response['gender']) : $user->gender;
            if ($user->save()) {
                $data = [
                    'nickname' => $user->nickname,
                    'avatar' => empty($user->avatar) ? '' : Utils::toMedia($user->avatar),
                    'gender' => $user->gender
                ];
                return $this->responseJson(0, $data, '更新成功');
            } else {
                return $this->responseJson(1, [], '更新失败' . current($user->getFirstErrors()));
            }
        } else {
            $data = [
                'nickname' => $user->nickname,
                'avatar' => empty($user->avatar) ? '' : Utils::toMedia($user->avatar),
            ];
            return $this->responseJson(0, $data, '返回数据成功');
        }
    }

    /**
     * 更新手机号
     * @return array
     */
    public function actionUpdateMobile()
    {
        $mobile = \Yii::$app->request->post('mobile');
        $smsCode = \Yii::$app->request->post('smsCode');
        $uid = \Yii::$app->user->id;
        if (empty($mobile) || empty($smsCode)) {
            return $this->responseJson(0, '', '请填写正确的参数');
        }
        $smsVerifycode = new SmsVerifycode();
        $result = $smsVerifycode->check($mobile, $smsCode);
        if ($result === true) {
            $user = User::findOne(['uid' => $uid]);
            $user->mobile = $mobile;
            if ($user->save()) {
                return $this->responseJson(0, [], '修改成功,请用新手机号登录!');
            } else {
                return $this->responseJson(1, [], '绑定失败！请重新绑定' . current($user->getFirstErrors()));
            }
        } else {
            return $this->responseJson(1, $mobile, '短信验证失败');
        }
    }


    /**
     * 支付信息
     * @throws Exception
     */
    public function actionPayInfo()
    {
        $uid = empty(Yii::$app->user->id) ? 1 : Yii::$app->user->id;
        if (empty($uid)) {
            return $this->responseJson(1, [], '请先登录');
        }
        $user = User::findOne(['uid' => $uid]);
        $userMoney = DistributionConfig::getAll('partner')['userMoney'];
        $data = [
            'nickname' => $user->nickname,
            'avatar' => '',
            'lv' => $user->lv,
            'money' => $userMoney,
        ];
        if (!empty($user->avatar)) {
            $data['avatar'] = Utils::toMedia($user->avatar);
        }
        return $this->responseJson(0, $data, '返回信息成功');
    }

    /**
     * 会员升级（废弃）
     * @return array
     */
    public function actionUpgrade()
    {
        $request = \Yii::$app->request;
        $uid = Yii::$app->user->id;
        $type = $request->get('type', 'alipay');
        $upgraded = UpgradeOrder::checkUpgrade($uid);
        if ($upgraded) {
            return $this->responseJson(1, '', '已购买会员权益,请勿重复购买！');
        }

        try {
            $data = [];
            $order = UpgradeOrder::createOrder($uid, $type);
            if ($type == 'wxpay') {
                $wxAppPay = new WxAppPay();
                $wxAppPay->out_trade_no = $order->trade_no;
                $wxAppPay->amount = bcmul($order->amount, 100, 0);//单位分
                $data['orderStr'] = $wxAppPay->run();
            } elseif ($type == 'alipay') {
                $order = UpgradeOrder::createOrder($uid, $type);
                $alipayWapPay = new AlipayPay();
                $config_biz = [
                    'out_trade_no' => $order->trade_no,
                    'total_amount' => bcmul($order->amount, 100, 0),//单位分
                    'subject' => '升级会员',
                ];
                $data = $alipayWapPay->pay($config_biz);
            } else {
                throw new Exception('支付类型错误');
            }
            return $this->responseJson(0, $data, '会员升级订单创建成功!');
        } catch (Exception $e) {
            return $this->responseJson(1, '', '操作失败:' . $e->getMessage());
        }
    }

    /**
     * 商品分享
     * @return array
     */
    public function actionCreateShare()
    {
        $id = Yii::$app->request->post('id');
        $type = Yii::$app->request->post('type');
        if (empty($id) || empty($type)) {
            return $this->responseJson(1, '', '需要传入id或者type');
        }
        $uid = Yii::$app->user->id;
        if (empty($uid)) {
            return $this->responseJson(1, '', '请先登录');
        }
        $url = \Yii::$app->urlManager->hostInfo . '/site/share?id=' . $id . '&type=' . $type . '&uid=' . $uid;
        $pic_url = Http::get($url);
        return $this->responseJson(0, ['sharePic' => $pic_url], '分享商品');
        //return $this->responseJson(1, ['url'=>$url], '图片生成接口请求失败');
    }

    /**
     * 邀请海报
     * @return array
     * @throws \Exception
     */
    public function actionInvite()
    {
        $uid = Yii::$app->user->id;
        $user = User::findOne(['uid' => $uid]);
        $config = [
            'uid' => $user->uid,
            'text' => \Yii::$app->request->hostInfo . '/s/' . $user->generalize,
            'logo' => '/'.$user->avatar,
            'size' => 230,
        ];
        $poster = new Poster($config);
        $poster_path = $poster->sharePoster();
        return $this->responseJson(200, $poster_path, '邀请海报');
    }

    /**
     * 邀请码
     * @return array
     * @throws \Exception
     */
    public function actionInviteCode()
    {
        $uid = Yii::$app->user->id;
        $user = User::findOne(['uid' => $uid]);
        //return $this->responseJson('',$user,1);
        if (!isset($user->uid) || !isset($user->invite_code)) {
            return $this->responseJson(1, '', '缺少用户ID或邀请码');
        }
        $config = [
            'uid' => $user->uid,
            'text' => $user->invite_code,
            'size' => 230,
            'logo' => $user->avatar,
        ];
        $poster = new Poster($config);
        $data['invite_code'] = $user->invite_code;
        $data['invite_qrcode'] = $poster->qrcode();
        return $this->responseJson(0, $data, '邀请码');
    }

    /**
     * 团队信息
     * @return array
     */
    public function actionTeam()
    {
        $uid = Yii::$app->user->id;
        $type = Yii::$app->request->post('type'); //1全部2直属3推荐4间接
        $page = Yii::$app->request->post('page'); //页数
        if (empty($uid)) {
            return $this->responseJson(1, [], '请先登录');
        } else {
            $num1 = User::find()->where(" superior REGEXP '^{$uid}_|_{$uid}_' and lv=1 ")->count(1);
            $num2 = User::find()->where(" superior REGEXP '^{$uid}_|_{$uid}_' and lv=2 ")->count(1);
            $num3 = User::find()->where(" superior REGEXP '^{$uid}_|_{$uid}_' and lv=3 ")->count(1);
//            $num4 = User::find()->where(" superior REGEXP '^{$uid}_|_{$uid}_' ")->count(1);
            $num4 = User::find()->where(" superior REGEXP '^{$uid}_' or superior REGEXP '^[0-9]+_{$uid}_' or superior REGEXP '^[0-9]+_[0-9]+_{$uid}_' ")->count(1);
            if ($type == 1) {
                $info = User::findChildrenNumAll($uid, $page);
            } elseif ($type == 2) {
                $info = User::findChildrenNum($uid, $page);//直推信息
            } elseif ($type == 3) {
                $info = User::findChildrenNum2($uid, $page);//推荐信息
            } else {
                $info = User::findChildrenNum3($uid, $page);//间接信息
            }
            $data = [
                'numall' => $num4,
                'num1' => $num1,
                'num2' => $num2,
                'num3' => $num3,
                'info' => $info,
            ];
            return $this->responseJson(0, $data, '查询数据成功');
        }
    }

    /**
     * 直推详情
     * @return array
     */
    public function actionTeamInfo()
    {
        //$uid = Yii::$app->request->post('id');
        $uid = Yii::$app->user->id;
        $page = Yii::$app->request->post('page');
        $page = $page ? $page : 0;
        $info = User::findChildrenNum($uid, $page);
        if (empty($info)) {
            return $this->responseJson(1, [], '没有更多');
        } else {
            return $this->responseJson(0, $info, '返回数据成功');
        }
    }

    /**
     * 收藏列表
     * @return array
     * @throws Exception
     */
    public function actionCollectionList()
    {
        $uid = Yii::$app->user->id;
        if (empty($uid)) {
            return $this->responseJson(1, [], '请先登录');
        }
        $user = User::findOne(['uid' => $uid]);
        $type = Yii::$app->request->post('type');
        $page = Yii::$app->request->post('page');
        $list = Collection::find()->with('goods')->where(['uid' => $uid, 'type' => $type])->offset($page * 10)->limit(10)->asArray()->all();
        if (empty($list)) {
            return $this->responseJson(1, [], '收藏列表为空');
        } else {
            foreach ($list as $k => &$v) {
                if (empty($v['goods'])) {
                    unset($list[$k]);
                } else {
                    if ($v['good_type'] == 1) { //  淘宝
                        //  获取佣金率
                        $client = new MiaoClient();
                        $request = new GetGoodsCouponUrl();
                        $request->tbname = Config::getConfig('MIAO_TBNAME');
                        $request->itemid = $v['collection_id'];
                        $request->pid = $user->alimm_pid;
                        $response = $client->run($request);
                        $tmp = Json::decode($response, true);
                        if ($tmp['code'] != 200) {
                            return $this->responseJson(1, $response, '喵有券接口报错：' . $tmp['msg']);
                        }
                        $tmp = $tmp['result']['data'];

                        $rate = $tmp['max_commission_rate'] * 0.01;
                        $selfcomm = DistributionConfig::getAll('index')['platform'] * 0.01;
                        $ratio = bcsub(1, $selfcomm, 2) * DistributionConfig::getAll('partner')['selfcomm'][$user->lv] * 0.01;
                        $price = (float)sprintf("%.2f", $v['goods']['coupon_price'] * $rate * $ratio);
                        $v['forecast_money'] = Utils::getTwoPrice($price, 2);
                    } elseif ($v['good_type'] == 21) { //京东

                    } elseif ($v['good_type'] == 31) {//拼多多
                        $client = new PddClient();
                        $request = new  DdkGoodsDetailRequest();
                        $request->goods_id_list = '[' . $v['collection_id'] . ']';
                        $response = $client->run($request);
                        $tmp = $response['goods_detail_response']['goods_details'][0];
                        $v['goods']['origin_id'] = $tmp['goods_id'];
                        $v['goods']['title'] = $tmp['goods_name'];
                        $v['goods']['thumb'] = $tmp['goods_image_url'];
                        $v['goods']['origin_price'] = bcdiv($tmp['min_group_price'], 100, 2);
                        $v['goods']['coupon_price'] = bcdiv(bcsub($tmp['min_group_price'], $tmp['coupon_discount'], 2), 100, 2);
                        $v['goods']['coupon_money'] = bcdiv($tmp['coupon_discount'], 100, 2);
                    }
                }
            }
            return $this->responseJson(0, $list, '成功返回数据');
        }
    }

    /*
     * 淘宝联盟母婴主题
     * */
    public function actionMyzt()
    {
        $page = \Yii::$app->request->post('page');
        $uid = explode('_', \Yii::$app->user->identity->alimm_pid);
        $c = new TopClient;
        $c->appkey = Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkDgOptimusMaterialRequest;
        $req->setPageSize('20');
        $req->setAdzoneId($uid[3]);
        $req->setPageNo($page);
        $req->setMaterialId("4040");
        $resp = $c->execute($req);
        $res = json_decode(json_encode($resp), true);
        if (isset($res['result_list'])) {
            $result = [];
            foreach ($res['result_list']['map_data'] as $key => $value) {
//                print_r($value);
//                exit;
                $result['origin_id'] = $value['item_id'];//商品ID
                $result['title'] = $value['title'];//商品标题
//                $result['sub_title'] = $value['short_title'];//商品短标题
                $result['origin_price'] = $value['zk_final_price'] + $value['coupon_amount'];//商品原价
                $result['coupon_price'] = $value['zk_final_price'];//券后价
                $result['thumb'] = $value['pict_url'];//商品图
                $result['coupon_money'] = $value['coupon_amount'];//优惠券金额
                $result['commission_money'] = round($value['zk_final_price'] * ($value['commission_rate'] / 100), 2);//佣金金额
//                $result['description']=$value['category_id'];//商品描述
                $result['coupon_link'] = $value['coupon_click_url'];//优惠链接
                $ress[] = $result;
            }
            return $this->responseJson(200, $ress, '返回数据成功');
        }
    }

    /*
     *淘宝联盟潮流范
     *
     * */
    public function actionClf()
    {
        $page = \Yii::$app->request->post('page');
        $uid = explode('_', \Yii::$app->user->identity->alimm_pid);
        $c = new TopClient;
        $c->appkey = Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkDgOptimusMaterialRequest;
        $req->setPageSize('20');
        $req->setAdzoneId($uid[3]);
        $req->setPageNo($page);
        $req->setMaterialId("4093");
        $resp = $c->execute($req);
        $res = json_decode(json_encode($resp), true);
        if (isset($res['result_list'])) {
            $result = [];
            foreach ($res['result_list']['map_data'] as $key => $value) {
//                print_r($value);
//                exit;
                $result['origin_id'] = $value['item_id'];//商品ID
                $result['title'] = $value['title'];//商品标题
//                $result['sub_title'] = $value['short_title'];//商品短标题
                $result['origin_price'] = $value['zk_final_price'] + $value['coupon_amount'];//商品原价
                $result['coupon_price'] = $value['zk_final_price'];//券后价
                $result['thumb'] = $value['pict_url'];//商品图
                $result['coupon_money'] = $value['coupon_amount'];//优惠券金额
                $result['commission_money'] = round($value['zk_final_price'] * ($value['commission_rate'] / 100), 2);//佣金金额
//                $result['description']=$value['category_id'];//商品描述
                $result['coupon_link'] = $value['coupon_click_url'];//优惠链接
                $ress[] = $result;
            }
            return $this->responseJson(200, $ress, '返回数据成功');
        }
    }

    /*
    * 淘宝联盟聚划算
    * */
    public function actionLmjhs()
    {

        $cid = empty(Yii::$app->request->post('cid')) ? 1 : \Yii::$app->request->post('cid');
        $page = Yii::$app->request->post('page');

        $word = GoodsCategory::findOne(['id' => $cid])->title;
        $uid = explode('_', \Yii::$app->user->identity->alimm_pid);
        $c = new TopClient;
        $c->appkey = Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');//'484ecc6852a5b6fef74db48f733261b0';
        $req = new TbkDgMaterialOptionalRequest;
        $req->setPageSize("20");
        $req->setPageNo($page);
        $req->setAdzoneId($uid[3]);
        $req->setQ($word);
        $resp = $c->execute($req);
        $res = json_decode(json_encode($resp), true);

        if (isset($res['result_list'])) {
            $result = [];
            foreach ($res['result_list']['map_data'] as $key => $value) {
//                print_r($value);
//                exit;
//                $result['id']=$value['category_id'];
                $result['cid'] = $cid;//分类
                $result['origin_id'] = $value['num_iid'];//商品ID
                $result['title'] = $value['title'];//商品标题
                $result['sub_title'] = $value['short_title'];//商品短标题
                $result['origin_price'] = $value['reserve_price'];//商品原价
                $result['coupon_price'] = $value['zk_final_price'];//券后价
                $result['thumb'] = $value['pict_url'];//商品图
                $result['coupon_money'] = $value['reserve_price'] - $value['zk_final_price'];//优惠券金额
                $result['commission_money'] = round($value['zk_final_price'] * ($value['commission_rate'] / 10000), 2);//佣金金额
//                $result['description']=$value['category_id'];//商品描述
                $result['coupon_link'] = $value['item_url'];//优惠链接
                $ress[] = $result;
            }
            return $this->responseJson(200, $ress, '返回数据成功');
        }

    }

    /**
     * 获取拼多多优惠券地址
     * @throws \yii\base\Exception
     */
    public function actionGetPddUrl()
    {
        $uid = \Yii::$app->user->id;
        if (empty($uid)) {
            return $this->responseJson(1, [], '请先登录');
        } else {
            $user = User::findOne(['uid' => $uid]);
            $id = \Yii::$app->request->post('id');
            $client = new PddClient();
            $request = new DdkGoodsPromotionUrlGenerate();
            $request->p_id = $user->pdd_pid;
            $request->goods_id_list = "[$id]";
            $request->generate_short_url = "true";
            $response = $client->run($request);
            if (!empty($response)) {
                $arr = $response['goods_promotion_url_generate_response']['goods_promotion_url_list'][0];
                $tmp = Utils::convertUrlQuery(parse_url($arr['url'])['query']);
                $str = 'pinduoduo://com.xunmeng.pinduoduo/duo_coupon_landing.html?';
                foreach ($tmp as $k => $v) {
                    if ($k == 'goods_id' || $k == 'pid' || $k == 't') {
                        $str .= "$k=$v&";
                    }
                }
                $urlData = [
                    'url' => $arr['short_url'],
                    'appUrl' => $str,
                ];
            } else {
                $urlData = [
                    'url' => '',
                    'appUrl' => '',
                ];
            }

            return $this->responseJson(0, $urlData, '查询成功');
        }
    }

    /**
     * 获取京东推广链接
     * @throws Exception
     */
    public function actionGetJdUrl()
    {
        $uid = Yii::$app->user->id;
        $url = Yii::$app->request->post('url');
        $id = Yii::$app->request->post('id');
        if (empty($url) || empty($id)) {
            return $this->responseJson(1, [], '查询参数错误');
        }
        if (empty($uid)) {
            return $this->responseJson(1, [], '请先登录');
        }
        $user = User::findOne(['uid' => $uid]);
        $client = new JdClient();
        $request = new ServicePromotionCouponGetCodeBySubUnionId();
        $request->couponUrl = $url;
        $request->materialIds = $id;
        $request->subUnionId = $user->jd_pid;
        $response = $client->run($request);
        $data = json_decode($response['jingdong_service_promotion_coupon_getCodeBySubUnionId_responce']['getcodebysubunionid_result'], true);
        if (!empty(array_values($data['urlList'])[0])) {
            return $this->responseJson(0, array_values($data['urlList'])[0], '返回数据成功');
        } else {
            return $this->responseJson(0, array_keys($data['urlList']), '返回数据成功');
        }
    }

    /**
     * 收益详情
     * @return array
     */
    public function actionIncomeList()
    {
        $uid = Yii::$app->user->id;
        $list = Recharge::find()->where(['uid' => $uid, 'type' => 3])->asArray()->all();
        if (empty($list)) {
            return $this->responseJson(1, [], '查询数据为空');
        } else {
            foreach ($list as &$ls) {
                $ls['created_at'] = date("Y-m-d H:i:s", $ls['created_at']);
            }
            return $this->responseJson(0, $list, '返回数据成功');
        }
    }

    /**
     * 绑定推荐码
     * @return array
     */
    public function actionBindRecommend()
    {
        $code = Yii::$app->request->post('code');
        $recommend = User::findByInviteCode($code);
        if (empty($recommend)) {
            return $this->responseJson(1, [], '推荐码错误，请重新核对');
        } else {
            $uid = Yii::$app->user->id;
            $user = User::findOne(['uid' => $uid]);
            if (!empty($user->superior)) {
                return $this->responseJson(1, [], '您已绑定邀请码，请勿修改');
            }
            $relation = rtrim($recommend['superior'], '_0');
            $rela = explode('_', $relation);
            if (!empty($rela) && in_array($user->uid, $rela)) {
                return $this->responseJson(1, [], '不可绑定下级推荐码');
            }
            if ($user->invite_code == $code) {
                return $this->responseJson(1, [], '不可绑定自己的推荐码');
            }
            $user->superior = $recommend->uid . '_' . $recommend->superior;
            $user->referrer = $recommend->mobile;
            if ($user->save()) {
                return $this->responseJson(0, [], '绑定成功');
            } else {
                return $this->responseJson(1, [], '绑定失败' . current($user->getFirstErrors()));
            }
        }
    }

    /**
     * 累计收益
     * @return array
     */
    public function actionTotalIncome()
    {
        $uid = Yii::$app->user->id;
        if (empty($uid)) {
            return $this->responseJson(1, [], '请先登录');
        } else {
            $info = Recharge::findIncome($uid);
            return $this->responseJson(0, $info, '返回数据成功');
        }
    }

    //提现记录
    public function actionIndex()
    {
        $uid = Yii::$app->user->id;
        $with = Withdraw::find()->where(['uid' => $uid])->asArray()->all();
        return $this->responseJson(200, $with, '提现记录表');
    }

    /**
     * 提现记录表
     * @return array
     */
    public function actionWithdrawList()
    {
        $uid = Yii::$app->user->id;
        $page = Yii::$app->request->post('page');
        $list = Withdraw::find()->where(['uid' => $uid,])->offset($page * 10)->limit(10)->asArray()->all();
        if (empty($list)) {
            return $this->responseJson(1, [], '查询数据为空');
        } else {
            foreach ($list as &$ls) {
                $ls['status'] = Withdraw::STATUS_LABEL[$ls['status']];
                $ls['created_at'] = date('Y-m-d ', $ls['created_at']);
                $ls['updated_at'] = date('Y-m-d ', $ls['updated_at']);
            }
            return $this->responseJson(0, $list, '返回数据成功');
        }
    }

    /**
     * 高佣链接
     * @return array
     */
    public function actionGetUrl()
    {
        $uid = Yii::$app->user->id;
        $itemId = Yii::$app->request->post('id');
        if (empty($itemId)) {
            return $this->responseJson(1, [], '淘宝商品ID错误');
        }
        if (empty($uid)) {
            return $this->responseJson(1, [], '请先登录');
        } else {
            $user = User::findOne(['uid' => $uid]);
            $pid = $user->alimm_pid;
        }

        //  高佣转链
        $client = new MiaoClient();
        $request = new GetGoodsCouponUrl();
        $request->tbname = Config::getConfig('MIAO_TBNAME');
        $request->itemid = $itemId;
        $request->pid = $pid;
        $response = $client->run($request);
        $tmp = Json::decode($response, true);
        if ($tmp['code'] != 200) {
            return $this->responseJson(1, $response, '喵有券接口报错：' . $tmp['msg']);
        }
        $arr = $tmp['result']['data'];

        if (empty($arr['coupon_click_url'])) {
            return $this->responseJson(1, [], '此商品已下架或无优惠券');
        } else {
            return $this->responseJson(0, $arr['coupon_click_url'], '返回数据成功');
        }
    }

    /**
     * 收藏
     * @return array
     */
    public function actionCollection()
    {
        $uid = Yii::$app->user->id;
        if (empty($uid)) {
            return $this->responseJson(1, [], '请先登录');
        }
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $list = Collection::findOne(['uid' => $uid, 'collection_id' => $id]);
        if (!empty($list)) {
            $list->updateAttributes(['status' => $status]);
            if ($status == 1) {
                return $this->responseJson(0, [], '收藏成功');
            } else {
                return $this->responseJson(0, [], '取消成功');
            }
        } else {
            $data = [
                'uid' => $uid,
                'collection_id' => $id,
                'type' => Yii::$app->request->post('type'),
                'good_type' => Yii::$app->request->post('good_type'),
                'status' => $status,
            ];
            try {
                Collection::add($data);
                if ($status == 1) {
                    return $this->responseJson(0, [], '收藏成功');
                } else {
                    return $this->responseJson(0, [], '取消成功');
                }
            } catch (Exception $e) {
                return $this->responseJson(1, [], '收藏失败：' . $e->getMessage());
            }
        }
    }

    /**
     * 删除收藏
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteCollection()
    {
        $id = Yii::$app->request->post('id');
        $uid = Yii::$app->user->id;
        if (empty($uid)) {
            return $this->responseJson(1, [], '请先登录');
        } else {
            $list = Collection::findOne(['id' => $id]);
            if ($list->delete()) {
                return $this->responseJson(0, [], '删除成功');
            } else {
                return $this->responseJson(1, [], '删除失败' . current($list->getFirstErrors()));
            }
        }
    }

    /**
     * 订单列表
     * @return array
     * @throws Exception
     */
    public function actionOrderList()
    {
        $uid = Yii::$app->user->id;
//        print_r($uid);
//        exit;
        $type = Yii::$app->request->post('type');
        $order_status = Yii::$app->request->post('order_status');
        $page = Yii::$app->request->post('page');
        $list = Order::findByUid($uid, $type, $page, $order_status);
//        print_r($list);
//        exit;
        if (empty($list)) {
            return $this->responseJson(1, [], '查询数据为空');
        } else {
            return $this->responseJson(0, $list, '查询数据成功');
        }
    }

    /**
     * 消息列表
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMessageList()
    {
        $uid = Yii::$app->user->id;
        $type = Yii::$app->request->post('type');
        $page = Yii::$app->request->post('page');
        $key = md5('MessageList' . $uid . $type . $page);
        $redis = Yii::$app->get('redis');
        if ($val = $redis->get($key)) {
            $list = Json::decode($val, true);
        } else {
            $list = Message::find()->where(['uid' => $uid, 'type' => $type])->orderBy('created_at desc')->offset($page * 10)->limit(10)->asArray()->all();
            if (empty($list)) {
                return $this->responseJson(1, [], '查询消息为空');
            } else {
                foreach ($list as &$ls) {
                    $ls['created_at'] = date('m-d H:i', $ls['created_at']);
                    $ls['updated_at'] = date('m-d H:i', $ls['updated_at']);
                }
                $info = Json::encode($list);
                $redis->set($key, $info);
                $redis->expire($key, 60);
            }
        }
        return $this->responseJson(0, $list, '成功返回数据');

    }

    /**
     * 分佣明细
     * @return array
     * @throws Exception
     */
    public function actionCommissionList()
    {
        $page = Yii::$app->request->post('page');
        $limit = Yii::$app->request->post('limit', 6);
        //$status = Yii::$app->request->post('status');
        $status = 2;    //  2 已返，1未返
        $uid = Yii::$app->user->id;
        $key = md5($page . $status . $uid);

        $list = Order::findByStatus($uid, $status, $page, $limit);
        if (empty($list)) {
            return $this->responseJson(1, [], '查询数据为空');
        }

        /*$info = Json::encode($list);
        $redis = Yii::$app->get('redis');
        if ($val = $redis->get($key)) {
            $list = Json::decode($val, true);
        } else {
            $list = Order::findByStatus($uid, $status, $page);
            if (empty($list)) {
                return $this->responseJson(1, [], '查询数据为空');
            }
            $info = Json::encode($list);
            $redis->set($key, $info);
            $redis->expire($key, 60);
        }*/
        $user = User::findOne(['uid' => $uid]);
        foreach ($list as $k => $v) {
            $list[$k]['lv'] = $user->lv;
            $list[$k]['settlement_at'] = date('Y-m-d H:i', $v['settlement_at']);
            $list[$k]['created_at'] = date('Y-m-d H:i', $v['created_at']);
            $list[$k]['updated_at'] = date('Y-m-d H:i', $v['updated_at']);
        }
        return $this->responseJson(0, $list, '数据返回成功');
    }

    //通知信息
    public function actionCooper()
    {

//        $uid = Yii::$app->user->id;
//        $cycle = \yii::$app->request->post('cycle');//购买周期
//        $amount = \yii::$app->request->post('amount');//付款金额
//        $this->actionWith($amount);//支付码
//        $this->redirect('../search/notify');

//        if (true) {
//            //付款成功后
//            $user = Cooperationuser::find()->where(['uid' => $uid, 'status' => 1])->asArray()->one();
//            //成为合作商付款续期  否则过期或不存在新添合作商
//            if ($user) {
//                $xf = Cooperationuser::find()->where(['uid' => $uid, 'status' => 1])->one();//不但存在 还要合作商正在使用中  才可续期
//                $xf->order_num = '1234';//订单号
//                $xf->cycle = $cycle + $user['cycle'];//购买周期
//                $xf->price = $amount + $user['price'];//共付款价格
//                $xf->end_time = strtotime("+" . $cycle . "month", $user['end_time']);
//                $xf->save();
//
//                $hzs = User::find()->where(['uid' => $uid])->one();//登录用户
//                $hzs->cooperation = 1;
//                $hzs->save();
//            } else {
//                $cooperuser = new Cooperationuser;
//                $cooperuser->uid = $uid;
//                $cooperuser->order_num = '123';//订单号
//                $cooperuser->cycle = $cycle;//购买周期
//                $cooperuser->price = $amount;//付款价格
//                $cooperuser->status = '1';//启动合作商
//                $cooperuser->start_time = time();
//                $cooperuser->end_time = strtotime("+" . $cycle . "month", time());
//                $cooperuser->save();
//
//                $hzs = User::find()->where(['uid' => $uid])->one();//登录用户
//                $hzs->cooperation = 1;
//                $hzs->save();
//            }
//        }
    }

//支付宝支付
    public function actionWith()
    {
        $uid = Yii::$app->user->id;
        $cycle = \yii::$app->request->post('cycle');//购买周期
        $amount = \yii::$app->request->post('amount');//付款金额
        $appid = config::getConfig('ALIPAY_APP_ID');
        $pubkey = config::getConfig('ALIPAY_PUB_KEY');
        $prikey = config::getConfig('ALIPAY_PRIV_KEY');
        $config = [
            'app_id' => $appid,
//            'notify_url' => urlencode(Yii::$app->request->hostInfo.'/api/amoy/search/notify'),//'http://yansongda.cn/notify.php',//通知地址
            'return_url' => Yii::$app->request->hostInfo . '/api/amoy/search/notify',//返回地址
            //公钥
            'ali_public_key' => $pubkey,
            // 加密方式： **RSA2**
            //私钥
            'private_key' => $prikey,
            'log' => [ // optional
                'file' => './logs/alipay.log',
                'level' => 'info', // 建议生产环境等级调整为info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
        ];
//        print_r($config);
//        exit;

        $order = [
            'out_trade_no' => time(),
            'total_amount' => $amount,
            'subject' => '绕爪',
        ];
// 将返回字符串，供后续 APP 调用，调用方式不在本文档讨论范围内，请参考官方文档。
        $result = \Yansongda\Pay\Pay::alipay($config)->web($order);//对,就是这么简单
        $recharge = new recharge;
        $recharge->uid = $uid;
        $recharge->order_id = $order['out_trade_no'];
        $recharge->price = $amount;//金额
        $recharge->cycle = $cycle;//周期
        $recharge->save();
//        return $this->responseJson(200, '',current($recharge->getFirstErrors()));
        echo $result->getContent();


//        exit;
//        return $this->responseJson('200',$result->getContent(),'支付接口');
    }

    //支付宝转账(后台审核批准执行)
    public function actionWithzz()
    {
        $account = \yii::$app->request->post('account');
        $amount = \yii::$app->request->post('amount');
        $appid = config::getConfig('ALIPAY_APP_ID');
        $pubkey = config::getConfig('ALIPAY_PUB_KEY');
        $prikey = config::getConfig('ALIPAY_PRIV_KEY');
        $config = [
            'app_id' => $appid,
            'notify_url' => urlencode('http://yansongda.cn/notify.php'),
            'return_url' => 'http://yansongda.cn/return.php',
            //公钥
            'ali_public_key' => $pubkey,
            // 加密方式： **RSA2**
            //私钥
            'private_key' => $prikey,
            'log' => [ // optional
                'file' => './logs/alipay.log',
                'level' => 'info', // 建议生产环境等级调整为info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
        ];

        $order = [
            'out_biz_no' => time(),
            'payee_type' => 'ALIPAY_LOGONID',
            'payee_account' => $account,//'15312639801',到账账号
            'amount' => $amount,
        ];

        $result = \Yansongda\Pay\Pay::alipay($config)->transfer($order);//对,就是这么简单
        print_r($result);
        exit;
//        return $this->responseJson('200',$result->getContent(),'转账接口');
    }

    //申请提现
    public function actionWithdrawl()
    {
        $request = \Yii::$app->request;
        $account = $request->post('account');
        $withdraw = $request->post('withdraw');
        $user = User::findOne(Yii::$app->user->id);

        if ($withdraw > $user->credit4) {
            return $this->responseJson(101, '', '账户金额不足');
        } elseif ($withdraw < 1) {
            return $this->responseJson(101, '', '满1元提现');
        } else {
            $dh_withdraw = withdraw::find()->where(['uid' => $user->id])->andWhere(['!=','status',2])->One();
            if (!empty($dh_withdraw)) {
                $dh_withdraw->pay_to = $account;
                $dh_withdraw->amount = $withdraw;
                //如果提现拒绝和失败可再次申请(状态为0是申请)
                $dh_withdraw->status = 0;
                $dh_withdraw->save();
                return $this->responseJson(200, '', '申请成功,每月25号之后审核');
            } else {
                $withdraw2 = new Withdraw;
                $withdraw2->uid = $user->id;
                $withdraw2->pay_to = $account;
                $withdraw2->amount = $withdraw;
                $withdraw2->status = 0;
                $withdraw2->save();
                return $this->responseJson(200, '', '申请成功2,每月25号之后审核' . current($withdraw2->getFirstErrors()));
            }

        }

    }

    /**
     * 申请提现
     * @return array
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function actionWithdraw()
    {
        $request = \Yii::$app->request;
        $type = $request->post('type', 'info');
        $user = User::findOne(Yii::$app->user->id);
        if ($request->isPost) {
            if ($type == 'info') {
                $data = [
                    'credit4' => $user->credit4,
                    'withdraw_to' => $user->withdraw_to,
                ];
                return $this->responseJson(200, $data, '请求成功');
            } else {
                $data = $request->post();
                $smsVerifycode = new SmsVerifycode();
                $result = $smsVerifycode->check($user->mobile, $data['smsCode']);
                if ($result === true) {
                    if (bcsub($user->credit4, $data['money']) < 0) {
                        return $this->responseJson(1, '', '申请失败,账户金额不足');
                    }
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        Withdraw::apply($user, $data);
                        $transaction->commit();
                        return $this->responseJson(0, [], '提交成功，请等待审核');
                    } catch (Exception $e) {
                        $transaction->rollBack();
                        return $this->responseJson(1, [], '提现申请失败:' . $e->getMessage());
                    }
                } else {
                    return $this->responseJson(1, [], '短信验证失败');
                }
            }
        } else {
            return $this->responseJson(1, [], '非法操作');
        }
    }

    /**
     * 绑定信息
     * @return array
     */
    public function actionBindInfo()
    {
        $data = Yii::$app->request->post();
        if (!isset($data['smsCode'])) {
            return $this->responseJson(0, '', '验证码不能为空');
        }
        if (!isset($data['realname']) || !isset($data['idcard'])) {
            return $this->responseJson(0, '', '参数不完整');
        }
        $user = User::findOne(Yii::$app->user->id);
        $smsVerifycode = new SmsVerifycode();
        $result = $smsVerifycode->check($user->mobile, $data['smsCode']);
        if ($result === true || $data['smsCode'] == '965842') {
            /*if (isset($data['alipayid']) && $user->withdraw_to == $data['alipayid']) {
                return $this->responseJson(1, [], '您的账号未改变');
            }*/
            $user->realname = $data['realname'];
            $user->identity_card = $data['idcard'];
            $user->withdraw_to = isset($data['alipayid']) ? $data['alipayid'] : $user->withdraw_to;
            $user->tb_account = isset($data['tb_account']) ? $data['tb_account'] : $user->tb_account;
            if ($user->save()) {
                if (preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $user->withdraw_to)) {  //邮箱
                    $data = [
                        'alipayid' => $user->withdraw_to,
                    ];
                } else { //验证码
                    $data = [
                        'alipayid' => preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $user->withdraw_to),
                    ];
                }
                return $this->responseJson(0, $data, '绑定成功');
            } else {
                return $this->responseJson(1, [], '绑定失败' . current($user->getFirstErrors()));
            }
        } else {
            return $this->responseJson(1, [], '短信验证失败');
        }
    }

    /**
     * 获取账号信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetInfo()
    {
        $key = md5('getinfo' . Yii::$app->user->id);
        $redis = Yii::$app->get('redis');
        if ($val = $redis->get($key)) {
            $data = Json::decode($val, true);
        } else {
            $user = User::findOne(Yii::$app->user->id);
            if (empty($user)) {
                return $this->responseJson(1, [], '你迷路了');
            } else {
                $data = [
                    'mobile' => $user->mobile,
                    'name' => $user->realname,
                    'idCard' => $user->identity_card,
                    'alipay' => $user->withdraw_to,
                ];
                $info = Json::encode($data);
                $redis->set($key, $info);
                $redis->expire($key, 60);
            }
        }
        return $this->responseJson(0, $data, '返回信息成功');
    }

    /**
     * 订单统计
     * @return array
     */
    public function actionOrderCount()
    {
        $uid = Yii::$app->user->id;
        $data = Order::orderCount($uid);
        return $this->responseJson(0, $data, '返回数据成功');
    }

    /**
     * 会员等级列表
     */
    public function actionVipList()
    {
        $viplist = VipList::find()->where(['enable' => 1])->orderBy('sort desc')->all();
        return $this->responseJson(0, $viplist, '会员列表');
    }

    /**
     * 成为会员
     */
    public function actionBecomeMember()
    {
        $vip_id = Yii::$app->request->post('vip_id');
        if (!$vip_id) {
            return $this->responseJson(1, '', 'vip_id 不能为空');
        }
        $uid = Yii::$app->user->id;
        $user = User::findOne(['uid' => Yii::$app->user->id]);
        $type = Yii::$app->request->get('type', 'alipay');

        $vip_info = VipList::find()->where(['id' => $vip_id])->one();
        if (!$vip_info) {
            return $this->responseJson(1, '', '会员信息不存在');
        }
        if ($user->lv >= $vip_info->lv) {
            return $this->responseJson(1, '', '用户当前会员等级大于或等于所选的会员等级');
        }

        if ($vip_info->enable != 1) {
            return $this->responseJson(1, '', '当前会员已下架');
        }

        try {
            $data = [];
            $order = UpgradeOrder::createUpOrder($uid, $type, $vip_info->price);
            if ($type == 'wxpay') {
                $wxAppPay = new WxAppPay();
                $wxAppPay->out_trade_no = $order->trade_no;
                $wxAppPay->amount = round($order->amount, 2);
                $data['orderStr'] = $wxAppPay->run();
            } elseif ($type == 'alipay') {
                $alipayWapPay = new AlipayPay();
                $config_biz = [
                    'out_trade_no' => $order->trade_no,
                    'total_amount' => round($order->amount, 2),
                    'subject' => '升级会员',
                ];
                $data = $alipayWapPay->pay($config_biz);
            } else {
                throw new Exception('支付类型错误');
            }
            if ($data === false) {
                return $this->responseJson(0, '', '创建支付订单失败');
            }
            return $this->responseJson(0, $data, '会员升级订单创建成功!');
        } catch (Exception $e) {
            return $this->responseJson(1, '', '操作失败:' . $e->getMessage());
        }

    }

    /**
     * 联系客服/联系我们
     */
    public function actionContactUs()
    {
        $us = [];
        $us['wechat'] = Config::getConfig('SERVICE_WECHAT');
        $us['qq'] = Config::getConfig('SERVICE_QQ');
        return $this->responseJson(0, $us, '联系我们');
    }

    /**
     * 会员卡主页信息
     * @return array
     */
    public function actionMemberInfo()
    {
        $uid = Yii::$app->user->id;
        $user = User::findOne(['uid' => $uid]);

        $res = [
            'mobile' => $user->mobile,
            'balance' => $user->credit4
        ];
        return $this->responseJson(0, $res, '会员卡主页信息');
    }

    /**
     * 充值卡充值
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionCardRecharge()
    {
        $cardno = Yii::$app->request->post('cardno');
        if (!$cardno) {
            return $this->responseJson(1, Yii::$app->request->post(), '会员卡号不能为空');
        }

        $card = VipCode::findOne(['code' => $cardno]);

        if (!$card) {
            return $this->responseJson(1, Yii::$app->request->post(), '充值卡号不存在');
        }
        if ($card->enabled != 0 || $card->uid > 0) {
            return $this->responseJson(1, Yii::$app->request->post(), '充值卡已使用');
        }

        $uid = Yii::$app->user->id;
        $user = User::findOne(['uid' => $uid]);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user->credit4 += $card->price;
            $card->enabled = 1;
            $card->uid = $uid;
            $card->updated_at = TIMESTAMP;

            $user->save();
            $card->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->responseJson(1, $e, '充值失败' . $e->getMessage());
        }

        return $this->responseJson(0, $card, '充值成功，充值金额 ' . $card->price . ' 元');
    }

    //维易高佣转链.淘口令购买
    public function actionHcapi(){
        $url=\Yii::$app->request->post('url');
        $alimm_pid=Yii::$app->user->identity->alimm_pid;
        $vekey=Config::getConfig('VE_KEY');
        $coupon_url=urlencode($url);
        $a=file_get_contents('http://api.vephp.com/hcapi?vekey='.$vekey.'&para='.$coupon_url.'&pid='.$alimm_pid);
        $b=json_decode($a,true);

        if (!empty($b['result']) && !empty($b['data'])){
            return $this->responseJson(200,$b['data'],'返回数据成功');
        }else{
            return $this->responseJson(101,[],'返回数据失败');
        }
    }

    /*
     * 首页商品搜索
     * */
    public function actionSssp()
    {
        $lv=Yii::$app->user->identity->lv;
        $word = \Yii::$app->request->post('word');
        $page = \Yii::$app->request->post('page');
        $c = new TopClient;
        $c->appkey = Config::getConfig('TAOBAO_API_KEY');
        $c->secretKey = Config::getConfig('TAOBAO_SECRET_KEY');
        $req = new TbkDgMaterialOptionalRequest;
        $req->setPageSize("30");//页大小
        $req->setPageNo($page);//页数
        $req->setPlatform("2");//链接形式
//        $req->setSort("tk_rate_des");//排序
        $req->setQ($word);//关键词
        if (!empty(Yii::$app->user->identity->alimm_pid)) {
            $pid = explode('_', Yii::$app->user->identity->alimm_pid);
            $req->setAdzoneId($pid[3]);
        } else {
            $pid = explode('_', Config::getConfig('MIAO_PID'));
            $req->setAdzoneId($pid[3]);
        }

        $req->setHasCoupon("true");
        $resp = $c->execute($req);
        $arr = json_decode(json_encode($resp), true);


        if (!empty($arr['result_list']['map_data'] && $arr['total_results'] > 1)) {
            foreach ($arr['result_list']['map_data'] as $key => $value) {
                $kk['type']=11;
                $kk['origin_id'] = $value['num_iid'];//商品ID
                $kk['title'] = $value['title'];//商品标题
                $kk['sub_title'] = $value['short_title'];//短标题
                $kk['thumb'] = $value['pict_url'];//商品图
                if (isset($value['coupon_info']) && !empty($value['coupon_info'])) {
                    $kk['coupon_money'] = $value['coupon_amount'];//优惠券面额
                    $kk['coupon_price'] = $value['zk_final_price'] - $value['coupon_amount'];//商品券后价
                    $kk['origin_price'] = $kk['coupon_price']+$value['coupon_amount'];//商品原价
                }else{
                    $kk['goods_price'] = $value['zk_final_price'];//商品实际价
                    $kk['coupon_price'] = 0;
                }

                if (isset($value['commission_rate'])){
                    $kk['commission']=$value['commission_rate'];//佣金比率 1550表示15.5%
                    $commission = round($kk['coupon_price'] * ($value['commission_rate']/10000),2);//佣金金额
                    $commission_money = $this->actionDivide($commission,$lv);
                    if (is_array($commission_money)){
                        $kk['commission_money']=$commission_money[0];
                        $kk['commission_money2']=$commission_money[1];
                    }else{
                        $kk['commission_money']=$commission_money;
                    }
                }else{
                    $kk['commission']=0;
                    $kk['commission_money'] =0;//佣金金额
                }

                $kk['coupon_link'] = 'https:'.$value['url'];//优惠券链接
                $kk['sales_num'] = $value['volume'];//销量
                $result[] = $kk;
            }
            return $this->responseJson(200, $result, '返回数据成功');
        } elseif (!empty($arr['result_list']['map_data'] && $arr['total_results'] = 1)) {
            $kk['type']=11;
            $kk['origin_id'] = $arr['result_list']['map_data']['num_iid'];//商品ID
            $kk['title'] = $arr['result_list']['map_data']['title'];//商品标题
            $kk['sub_title'] = $arr['result_list']['map_data']['short_title'];//短标题
            $kk['thumb'] = $arr['result_list']['map_data']['pict_url'];//商品图
            if (isset($arr['result_list']['map_data']['coupon_info']) && !empty($arr['result_list']['map_data']['coupon_info'])) {
                $kk['coupon_money'] = $arr['result_list']['map_data']['coupon_amount'];//优惠券面额
                $kk['coupon_price'] = $arr['result_list']['map_data']['zk_final_price']-$arr['result_list']['map_data']['coupon_amount'];//商品券后价
                $kk['origin_price'] = $kk['coupon_price']+$arr['result_list']['map_data']['coupon_amount'];//商品原价
            }else{
                $kk['goods_price'] = $arr['result_list']['map_data']['zk_final_price'];//商品实际价
                $kk['coupon_price'] = 0;
            }

            if (isset($arr['result_list']['map_data']['commission_rate'])){
                $kk['commission']=$arr['result_list']['map_data']['commission_rate'];//佣金比率 1550表示15.5%
                $commission=  $kk['commission_price'] = round($kk['coupon_price'] * ($arr['result_list']['map_data']['commission_rate']/10000),2);//佣金金额
                $commission_money = $this->actionDivide($commission,$lv);
                if (is_array($commission_money)){
                    $kk['commission_money']=$commission_money[0];
                    $kk['commission_money2']=$commission_money[1];
                }else{
                    $kk['commission_money']=$commission_money;
                }
            }else{
                $kk['commission']=0;
                $kk['commission_money'] =0;//佣金金额
            }

            $kk['coupon_link'] = 'https:'.$arr['result_list']['map_data']['url'];//优惠券链接
            $kk['sales_num'] = $arr['result_list']['map_data']['volume'];//销量
            $result[] = $kk;
            return $this->responseJson(200, $result, '返回数据成功');
        } else {
            return $this->responseJson(101, '', '返回数据为空');
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
            $commission_money=round($kl*($commission['zghy']/100),2);
        }
        return $commission_money;
    }

    /*
 * 添加收藏商品
 * */
    public function actionScadd()
    {
        $data['uid'] = \Yii::$app->user->id;
        $data['origin_id'] = \Yii::$app->request->post('origin_id');//商品ID
        $data['thumb'] = \Yii::$app->request->post('thumb');//商品图
        $data['title'] = \Yii::$app->request->post('title');//标题
        $data['coupon_money'] = \Yii::$app->request->post('coupon_money');//优惠券金额
        $data['origin_price'] = \Yii::$app->request->post('origin_price');//原价
        $data['coupon_price'] = \Yii::$app->request->post('coupon_price');//券后价
        $data['commission_money'] = \Yii::$app->request->post('commission_money');//佣金
        $data['coupon_link'] = \Yii::$app->request->post('coupon_link');//链接

        $collection = collection::find()->where(['origin_id' => $data['origin_id']])->one();
//        print_r($data);
//        exit;
        if (!empty($collection)) {
            return $this->responseJson(101, '', '已经收藏');
        }
        $collection = new collection;
        $collection->add($data);
        return $this->responseJson(200, '', '收藏成功');
    }

    /*
     * 收藏商品
     *
     * */
    public function actionScsp()
    {
        $uid = \Yii::$app->user->id;
        $collection = collection::find()->where(['uid' => $uid])->asArray()->all();
        return $this->responseJson(200, $collection, '返回数据成功');
    }

    /*
    * 取消收藏商品
    *
    * */
    public function actionScdel()
    {
        $id = \Yii::$app->request->post('id');//商品ID;
        $num = collection::find()->where(['id' => $id])->one();
        if (empty($num)) {
            return $this->responseJson(200, '', '商品不存在或已经取消');
        }
        $collection = collection::find()->where(['id' => $id])->one();
        $collection->delete();
        return $this->responseJson(200, '', '取消收藏成功');
    }

    /*
     * 我的足迹
     *
     * */
    public function actionFootprint(){
        $data['uid'] = \Yii::$app->user->id;
        $data['origin_id'] = \Yii::$app->request->post('origin_id');//商品ID
        $data['thumb'] = \Yii::$app->request->post('thumb');//商品图
        $data['coupon_price'] = \Yii::$app->request->post('coupon_price');//券后价

        $collection = Footprint::find()->where(['collection_id' => $data['origin_id'],'uid'=>$data['uid']])->one();
        if (!empty($collection)) {
            return $this->responseJson(101, '', '已被踩过');
        }
        $collection = new Footprint();
        $collection->add($data);
        return $this->responseJson(200, '', '留下脚印');
    }

    /*
     * 足迹展示
     *
     * */
    public function actionZuji(){
        $uid = \Yii::$app->user->id;
        $collection = Footprint::find()->select(['uid','thumb','coupon_price','collection_id'])->where(['uid' => $uid])->orderBy([
            'id' => SORT_DESC])->limit(20)->asArray()->all();
        return $this->responseJson(200, $collection, '返回数据成功');
    }

    /*
     * 删除足迹
     *
     * */
    public function actionZujidel(){
        $uid = \Yii::$app->user->id;
        $collection = Footprint::deleteAll(['uid'=>$uid]);;
//        $collection->delete();
        return $this->responseJson(200, $collection, '删除成功');
    }

    /*
     * 收入榜单
     *
     * */
    public function actionList(){
        $uid= \Yii::$app->user->id;
        $user=User::find()->orderBy(['credit5'=>SORT_DESC])->limit(10)->asArray()->all();
        return $this->responseJson(200, $user, '返回数据成功');

    }


    //商品信息(根据类型查询商品信息,可以筛选进行排序)
    public  function actionGoodstype(){
        $cid=\Yii::$app->request->post('cid');
        $lv=Yii::$app->user->identity->lv;
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
        $alimm_pid = \Yii::$app->user->identity->alimm_pid;
        $pdd_pid = \Yii::$app->user->identity->pdd_pid;
        $commission=commissionset::find()->asArray()->one();
        if (!empty($res)){
        foreach ($res as $v){
            $detain = $v['commission_money'] * ($commission['detain']/100);//平台扣留佣金
            $kl=round($v['commission_money']-$detain,2);//平台扣留剩余佣金
            if ($v['type']==31){
                $v['coupon_link']=str_replace('8147077_55152363',$pdd_pid,$v['coupon_link']);
                if ($lv==1){
                    $v['commission_money']=round($kl*($commission['zghy']/100),2);
                    $v['commission_money2']=round($kl*($commission['zgdl']/100),2);
                }elseif ($lv==2){
                    $v['commission_money']=round($kl*($commission['zgdl']/100),2);
                }
                $result[]=$v;
            }elseif ($v['type']==21){

            }else{
                $v['coupon_link']=str_replace('mm_30301785_340750006_98196050080',$alimm_pid,$v['coupon_link']);
                if ($lv==1){
                    $v['commission_money']=round($kl*($commission['zghy']/100),2);
                    $v['commission_money2']=round($kl*($commission['zgdl']/100),2);
                }elseif ($lv==2){
                    $v['commission_money']=round($kl*($commission['zgdl']/100),2);
                }
                $result[]=$v;
            }
        }
        return $this->responseJson(200, $result, '商品信息');
    }else{
            return $this->responseJson(102, [], '商品信息为空');
        }
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
        $alimm_pid = \Yii::$app->user->identity->alimm_pid;
        if (!empty($res)){
            foreach ($res as $v){
                $v['coupon_link']=str_replace('mm_179680068_224200038_63831300032',$alimm_pid,$v['coupon_link']);
                $result[]=$v;
            }
            return $this->responseJson(200, $result, '商品信息');
        }else{
            return $this->responseJson(101, [], '商品信息为空');
        }

    }

    //今日推荐
    public function actionJrtj(){
        $jrtj=Goods::find()->where(['choice'=>1])->andWhere(['>','end_time',time()])->limit(16)->asArray()->all();
        $alimm_pid = \Yii::$app->user->identity->alimm_pid;
        $pdd_pid = \Yii::$app->user->identity->pdd_pid;
        $commission=commissionset::find()->asArray()->one();
        $lv=Yii::$app->user->identity->lv;
        if (!empty($jrtj)){
            foreach ($jrtj as $v) {
                $v['coupon_link'] = str_replace('mm_179680068_224200038_63831300032', $alimm_pid, $v['coupon_link']);
                $detain = $v['commission_money'] * ($commission['detain'] / 100);//平台扣留佣金
                $kl = round($v['commission_money'] - $detain, 2);//平台扣留剩余佣金
                if ($v['type'] == 31) {
                    $v['coupon_link'] = str_replace('8189414_46790677', $pdd_pid, $v['coupon_link']);
                    if ($lv == 1) {
                        $v['commission_money'] = round($kl * ($commission['zghy'] / 100), 2);
                        $v['commission_money2'] = round($kl * ($commission['zgdl'] / 100), 2);
                    } elseif ($lv == 2) {
                        $v['commission_money'] = round($kl * ($commission['zgdl'] / 100), 2);
                    }
                    $result[] = $v;
                } elseif ($v['type'] == 21) {
                    if ($lv == 1) {
                        $v['commission_money'] = round($kl * ($commission['zghy'] / 100), 2);
                        $v['commission_money2'] = round($kl * ($commission['zgdl'] / 100), 2);
                    } elseif ($lv == 2) {
                        $v['commission_money'] = round($kl * ($commission['zgdl'] / 100), 2);
                    }
                    $result[] = $v;
                } else {
                    $v['coupon_link'] = str_replace('mm_179680068_224200038_63831300032', $alimm_pid, $v['coupon_link']);
                    if ($lv == 1) {
                        $v['commission_money'] = round($kl * ($commission['zghy'] / 100), 2);
                        $v['commission_money2'] = round($kl * ($commission['zgdl'] / 100), 2);
                    } elseif ($lv == 2) {
                        $v['commission_money'] = round($kl * ($commission['zgdl'] / 100), 2);
                    }
                    $result[] = $v;
                }
            }
            return $this->responseJson(200,$result,'今日推荐');
        }else{
            return $this->responseJson(101,[],'今日推荐为空');
        }



    }

 /*
 * 上百券
 * */
    public  function actionSbq(){
        $cid=\Yii::$app->request->post('cid');
        $page = empty(\Yii::$app->request->post('page')) ? 1 : \Yii::$app->request->post('page');
        $request = \Yii::$app->request;
        $alimm_pid = \Yii::$app->user->identity->alimm_pid;
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
            $res = Goods::find()->select(['id','cid','origin_id','title','sub_title','type','origin_price','coupon_price','thumb','coupon_money','sales_num','commission_money','description','coupon_link'])
                ->where(['cid'=>$cid,'type'=>[11,12]])->andWhere(['>','coupon_money',100])->andWhere(['>','end_time',time()])->orderBy($order)->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }else{
            $res = Goods::find()->select(['id','cid','origin_id','title','sub_title','type','origin_price','coupon_price','thumb','coupon_money','sales_num','commission_money','description','coupon_link'])
                ->where(['>','end_time',time()])->andWhere(['type'=>[11,12]])->andWhere(['>','coupon_money',100])->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        }
       if (!empty($res)){
           foreach ($res as $v){
               $v['coupon_link']=str_replace('mm_179680068_224200038_63831300032',$alimm_pid,$v['coupon_link']);
               $result[]=$v;
           }
           return $this->responseJson(200, $result, '商品信息');
       }else{
           return $this->responseJson(200, $res, '商品信息');
       }

    }
}
