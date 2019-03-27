<?php

namespace frontend\controllers;


use common\components\jd\JdClient;
use common\components\jd\requests\ServicePromotionCouponGetCodeBySubUnionId;
use common\components\jd\requests\ServicePromotionGoodsInfo;
use common\components\miao\MiaoClient;
use common\components\miao\taobao\requests\GetGoodsCouponUrl;
use common\components\miao\taobao\requests\GetGoodsInfo;
use common\components\pdd\PddClient;
use common\components\pdd\requests\DdkGoodsDetailRequest;
use common\components\pdd\requests\DdkGoodsPromotionUrlGenerate;
use common\components\taobao\requests\TbkItemInfoGet;
use common\components\taobao\requests\TbkTpwdCreate;
use common\components\taobao\TaobaoClient;
use common\helpers\CreatePic;
use common\helpers\Poster;
use common\models\Config;
use common\models\Goods;
use common\models\Log;
use common\models\User;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\helpers\Url;

/**
 * Site controller
 */
class SiteController extends ControllerBase
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
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * 公告信息
     * @return string
     */
    public function actionIndex()
    {
        $this->view->title = '系统公告';
        return $this->render('index');
    }

    /**
     * 扫码注册
     * @return string
     * @throws Exception
     * @throws \yii\httpclient\Exception
     */
    public function actionCode()
    {
        $request = \Yii::$app->request;
        $code = $request->get('code', null);
        $client = new Client();
        $appid = Config::getConfig('WECHAT_WEB_APP_ID');
        if ($code) {
            $invite_code = $request->get('state', null);
            $response = $client->get('https://api.weixin.qq.com/sns/oauth2/access_token', [
                'appid' => $appid,
                'secret' => Config::getConfig('WECHAT_WEB_SECRET'),
                'grant_type' => 'authorization_code',
                'code' => $code,
            ])->send();
            if (!$response->isOk) {
                return $this->error('微信请求失败:' . $response->getStatusCode());
            }
            $userinfo = $this->userInfo($response->getData());
            $userinfo['invite_code'] = $invite_code;
            Log::add($userinfo, 'scan');
            $user = User::findByUnionid($userinfo['unionid']);
            //下载地址
            $download_url = Config::getConfig('APP_DOWNLOAD_URL');
            if ($user) {
                header('location:' . $download_url);
                exit();
            }
            try {
                User::registerByInviteCode($userinfo);
            } catch (Exception $e) {
                return $this->error($e->getMessage());
            }
            header('location:' . $download_url);
            exit();
        } else {
            $invite_code = $request->get('invite_code', null);
            $referrer = User::findOne(['invite_code' => $invite_code, 'status' => User::STATUS_ACTIVE]);
            if (!$referrer) {
                return $this->error('邀请人不存在');
            }
            $redirect_uri = urlencode(Url::toRoute('site/code', true));
            header('location:https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=snsapi_userinfo&state=' . $invite_code . '#wechat_redirect');
            exit();
        }
    }

    /**
     * @param $data
     * @return array|mixed
     * @throws Exception
     */
    public function userInfo($data)
    {
        if (!isset($data['access_token'])) {
            throw new Exception('获取微信用户信息失败');
        }
        $client = new Client();
        $response = $client->get('https://api.weixin.qq.com/sns/userinfo',
            [
                'openid' => $data['openid'],
                'access_token' => $data['access_token'],
            ])->send();
        if ($response->isOk) {
            return $response->getData();
        } else {
            throw new Exception('获取微信用户信息失败,请求状态码' . $response->getStatusCode());
        }
    }

    /**
     * 淘宝领券地址
     * @return string
     * @throws Exception
     */
    public function actionGetCode()
    {
        $id = \Yii::$app->request->get('g');
        $uid = \Yii::$app->request->get('u');
        if (empty($uid) || empty($id)) {
            throw  new Exception('你迷路了');
        } else {
            $user = User::findOne(['uid' => $uid]);
            $pid = $user->alimm_pid;
        }

        //  高佣转链
        $client = new MiaoClient();
        $request = new GetGoodsCouponUrl();
        $request->tbname = Config::getConfig('MIAO_TBNAME');
        $request->itemid = $id;
        $request->pid = $pid;
        $request->tpwd = 1;
        $request->shorturl = 1;
        $response = $client->run($request);
        $tmp = Json::decode($response,true);
        if ($tmp['code'] != 200){
            return $this->error('喵有券高佣转链出错3：'.$response);
        }
        $arr = $tmp['result']['data'];

        if (isset($arr['coupon_info'])) {
            preg_match_all('/(.*减)(\d+)(元)/', $arr['coupon_info'], $matches);
            $coupon_money = $matches[2][0];
        } else {
            $coupon_money = 0;
        }
        $kouling = $arr['tpwd'];


        //  获取商品详细信息
        $request = new GetGoodsInfo();
        $request->itemid = $id;
        $response = $client->run($request);
        $tmp = Json::decode($response,true);
        if ($tmp['code'] != 200){
            return $this->error('喵有券高佣转链出错4：'.$response);
        }
        $arr = $tmp['result']['data'];
        $info = $arr['zk_final_price']; //  商品折扣价格
        $coupon_price = bcsub($info['zk_final_price'], $coupon_money, 2);

        //  各大下载地址
        $downloadUrl = Config::getConfig('APP_DOWNLOAD_URL');
        $iosDownloadUrl = Config::getConfig('APP_DOWNLOAD_URL_IOS');
        $vsersion = Config::getConfig('APP_VERSIONS');
        $iosVsersion = Config::getConfig('APP_VERSIONS_IOS');
        if (empty($downloadUrl) || empty($vsersion)) {
            return $this->render('error', ['msg' => '系统参数错误']);
        } else {
            $versions = [
                'iosUrl' => $iosDownloadUrl,
                'iosVersion' => $iosVsersion,
                'url' => $downloadUrl,
                'version' => $vsersion,
            ];
        }
        return $this->render('codes', ['kouling' => $kouling, 'info' => $info, 'version' => $versions, 'coupon_money' => $coupon_money, 'coupon_price' => $coupon_price]);
    }

    public function actionShare()
    {
        $goodId = \Yii::$app->request->get('id');
        $type = \Yii::$app->request->get('type');
        $uid = \Yii::$app->request->get('uid');
        if (empty($uid) || empty($type) || empty($goodId)) {
            return $this->error('参数有误!');
        }
        $user = User::findOne(['uid' => $uid]);
        if (empty($user)) {
            return $this->error('参数有误!');
        }
        $good = Goods::findOne(['origin_id' => $goodId]);
        if ($type == 21) {  //京东
            $client = new JdClient();
            $request = new ServicePromotionCouponGetCodeBySubUnionId();
            $request->couponUrl = $good['coupon_link'];
            $request->materialIds = $goodId;
            $request->subUnionId = $user->jd_pid;
            $response = $client->run($request);
            $data = json_decode($response['jingdong_service_promotion_coupon_getCodeBySubUnionId_responce']['getcodebysubunionid_result'], true);
            if (!empty(array_values($data['urlList'])[0])) {
                $item_url = array_values($data['urlList'])[0];
            } else {
                $item_url = $good['coupon_link'];
            }

            $goodRequest = new ServicePromotionGoodsInfo();
            $goodRequest->skuIds = $goodId;
            $goodresponse = $client->run($goodRequest);
            $tmp = json_decode($goodresponse['jingdong_service_promotion_goodsInfo_responce']['getpromotioninfo_result'], true)['result'][0];

            $url = 'http://119.29.94.164/xiaocao/jd/couponGetInfo.action?qq=372638426&appkey=37263842620180529&couponUrl=' . urlencode(array_keys($data['urlList'])[0]);
            $client = new Client();
            $response = $client->get($url)->send();
            $arr = Json::decode($response->content);
            $tmp2 = json_decode($arr['msg']['jingdong_service_promotion_coupon_getInfo_responce']['getinfo_result'], true)['data'][0];

            $data = [
                'pic' => $tmp['imgUrl'],
                'type' => 21,
                'title' => $tmp['goodsName'],
                'price' => bcsub($tmp['unitPrice'], $tmp2['discount'], 2), //券后价
                'original_price' => $tmp['unitPrice'],   //现价
                'coupon_price' => $tmp2['discount'],  //优惠券
            ];
        } elseif ($type == 31) {  //拼多多
            $client = new PddClient();
            $request = new DdkGoodsPromotionUrlGenerate();
            $request->p_id = $user->pdd_pid;
            $request->goods_id_list = "[$goodId]";
            $request->generate_short_url = "true";
            $response = $client->run($request);
            $arr = $response['goods_promotion_url_generate_response']['goods_promotion_url_list'][0];
            $item_url = $arr['short_url'];

            $goodrequest = new DdkGoodsDetailRequest();
            $goodrequest->goods_id_list = "[{$goodId}]";
            $goodresponse = $client->run($goodrequest);
            $tmp = $goodresponse['goods_detail_response']['goods_details'][0];

            $data = [
                'pic' => $tmp['goods_thumbnail_url'],
                'type' => 31,
                'title' => $tmp['goods_name'],
                'price' => bcdiv(bcsub($tmp['min_group_price'], $tmp['coupon_discount']), 100, 2),  //券后价
                'original_price' => bcdiv($tmp['min_group_price'], 100, 2),  //现价
                'coupon_price' => bcdiv($tmp['coupon_discount'], 100, 2),  //优惠券
            ];
        } else {
            //  淘宝

            //  高佣转链
            $client = new MiaoClient();
            $request = new GetGoodsCouponUrl();
            $request->tbname = Config::getConfig('MIAO_TBNAME');
            $request->itemid = $goodId;
            $request->pid = $user->alimm_pid;
            $response = $client->run($request);
            $tmp = Json::decode($response,true);
            if ($tmp['code'] != 200){
                return $this->error('喵有券高佣转链出错1：'.$response);
            }
            $arr = $tmp['result']['data'];

            $item_url = \Yii::$app->request->hostInfo . '/site/get-code?g=' . $goodId . '&u=' . $uid; //高佣链接

            //  查询商品信息
            $request = new GetGoodsInfo();
            $request->itemid = $goodId;
            $response = $client->run($request);
            $tmp = Json::decode($response,true);
            if ($tmp['code'] != 200){
                return $this->error('喵有券高佣转链出错2：'.$response);
            }
            $tmp = $tmp['data']['n_tbk_item'];

            if (isset($arr['coupon_info'])){
                preg_match_all('/(.*减)(\d+)(元)/', $arr['coupon_info'], $matches);
                $money = empty($arr['coupon_info']) ? 0 : $matches[2][0];
            }else{
                $money = 0;
            }

            $data = [
                'pic' => $tmp['pict_url'],
                'type' => 11,
                'title' => $tmp['title'],
                'price' => bcsub($tmp['zk_final_price'], $money, 2),  //券后价
                'original_price' => $tmp['zk_final_price'],  //现价
                'coupon_price' => $money,  //优惠券
            ];
        }
        //  生成图片信息
        $config = [
            'text' => $item_url,
            'uid' => $uid,
            'size' => 230,
            'to_path' => \Yii::getAlias('@public/uploads/share/') . $uid,
        ];
        $poster = new Poster($config);

        $data['qrcode'] = $poster->qrcode();

        //使用方法-------------------------------------------------
        //数据格式，如没有优惠券coupon_price值为0。
        $gData = [
            'pic' => $tmp['pict_url'],
            'title' =>$tmp['title'],
            'price' => bcsub($tmp['zk_final_price'], $money, 2),  //券后价
            'original_price' => $tmp['zk_final_price'],
            'coupon_price' => $money,  //优惠券
        ];


        $filename = '/uploads/share/tmp/'.md5(time().$goodId).'.png';
        CreatePic::createSharePng($gData,$data['qrcode'],\Yii::getAlias('@public').$filename);
        return \Yii::$app->request->hostInfo . $filename;
        return CreatePic::createSharePng($gData,$data['qrcode']);

        //return $this->render('share', ['data' => $data]);
    }
}
