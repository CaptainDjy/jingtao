<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/12/7 14:33
 */

namespace frontend\controllers;

use common\components\jd\JdClient;
use common\components\jd\requests\UnionServiceQueryOrderList;
use common\components\pdd\PddClient;
use common\components\pdd\requests\DdkGoodsDetailRequest;
use common\components\pdd\requests\DdkGoodsSearchRequest;
use common\components\pdd\requests\GoodsOptGetRequest;
use common\components\robots\DataokeRobot;
use common\components\taobao\requests\TbkWirelessShareTpwdQuery;
use common\components\taobao\TaobaoClient;
use common\models\Goods;
use common\models\User;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\httpclient\Client;

class TestController extends ControllerBase
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

    public function actionDataoke()
    {
        $pageNum = \Yii::$app->request->get('pageNum', 1);
        $robot = new DataokeRobot([
            'pageNum' => $pageNum
        ]);

        try {
            $result = $robot->run();
            if (!empty($result)) {
                $pageNum = $result['pageNum']++;
                echo '成功采集' . $result['num'] . '条，当前' . $result['pageNum'] . '页';
                return $this->redirect(['test/dataoke', 'page' => $pageNum]);
            }

            echo '成功结束';
            exit;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function actionTaobaoTest()
    {
        $client = new TaobaoClient();
        $client->appkey = '24870773';
        $client->secretKey = '1ec5c0f349171a2b0b3408047bb2b9fc';
        $requst = new TbkWirelessShareTpwdQuery();
        $requst->password_content = '【Skyworth/创维 42X6 42英寸10核全高清智能网络LED液晶平板电视机】http://m.tb.cn/h.3YMFxcm 点击链接，再选择浏览器咑閞；或復·制这段描述€bCJB0B71jJx€后到淘♂寳♀[来自超级会员的分享]';
        $response = $client->run($requst);
        print_r($response);
    }

    public function actionPddTest()
    {
        $client = new PddClient();
        $requst = new DdkGoodsDetailRequest();
        $requst->goods_id_list = '[1152352074]';
        $response = $client->run($requst);
        print_r($response['goods_detail_response']['goods_details'][0]);
        die();
    }

    public function actionJdTest()
    {
        $client = new JdClient();
        $request = new UnionServiceQueryOrderList();
        $request->unionId = 1000603922;
        $request->time = date("Ymd", time() - 60 * 60 * 24 * 1);
        $request->pageIndex = 1;
        $request->pageSize = 500;
        $response = $client->run($request);
        $json = $response['jingdong_UnionService_queryOrderList_responce']['result'];
        $arr = json_decode($json, true);
        foreach ($arr['data'] as $k => $v) {
            if ($v['orderId'] == '77284929189') {
                print_r($v);
            }
            if (!empty($v['skuList'][0]['subUnionId'])) {
                $user = User::findOne(['jd_pid' => 1]);
            } else {
                continue;
            }
            if (empty($user)) {
                continue;
            }
        }
//        print_r($arr);
        die();
    }

    public function actionGet()
    {
        $sql = "Select max(id) id From dh_order Group By trade_id,estimated_effect order by id asc ";
        $orderList = \Yii::$app->db->createCommand($sql)->queryAll();
        $arr = [];
        foreach ($orderList as $item) {
            $arr[] = $item['id'];
        }
        $string = implode(',', $arr);
        var_dump($string);
        die;
        $sql = "delete from dh_order where id not in (" . $string . ")";
        $result = \Yii::$app->db->createCommand($sql)->execute();
        var_dump($result);
        die;
    }

    public function actionTime()
    {
        $id = 570441293229;
        $client = new Client();
        $response = $client
            ->createRequest()
            ->setMethod('get')
            ->setUrl("https://acs.m.taobao.com/h5/mtop.taobao.detail.getdetail/6.0/")
//            ->setUrl("https://hws.m.taobao.com/cache/mtop.wdetail.getItemDescx/4.1/")
            ->setData([
//                'type' => 'jsonp',
//                'dataType' => 'jsonp',
//                'data' => '{"itemNumId":"543544648408"}',
//                'data' => "{item_num_id:'{$id}'}",
                'data' => "{\"itemNumId\":\"{$id}\"}",
            ])
            ->send();
//        print_r($response->content);
        $tmp = json_decode($response->content, true);
        print_r($tmp['data']['item']['images'][0]);
        die();
    }

    public function actionPddGoods()
    {
        $client = new PddClient();
        $request = new DdkGoodsSearchRequest();
        $request->opt_id = 1;
        $request->page_size = '100';
        $request->sort_type = 0;
        $request->with_coupon = 'true';
        $response = $client->run($request);
        var_dump($response);
    }


    public function actionPddGoodsOpt()
    {
        $client = new PddClient();
        $request = new GoodsOptGetRequest();
        $request->parent_opt_id = 0;
        $response = $client->run($request);
        foreach ($response['goods_opt_get_response']['goods_opt_list'] as $item) {
            echo $item['opt_id'] . '=>\'' . $item['opt_name'] . '\',' . PHP_EOL;
        }
    }


    /**
     * @throws Exception
     */
    public function actionJdTest1()
    {
        $client = new Client();
        $response = $client
            ->createRequest()
            ->setMethod('post')
            ->setUrl('http://japi.jingtuitui.com/api/get_goods_list')
            ->setData([
                'appid' => '1806211906298226',
                'appkey' => 'ff8000a2c259600f60fd4a1badb86628',
                'page' => 1,
                'num' => 100,
            ])
            ->send();
        $tmp = json_decode($response->content, true)['result']['data'];
        foreach ($tmp as $good) {
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
            $model->save();
        }
    }

    public function actionTest()
    {
        $url = "http://119.29.94.164/xiaocao/search/taobaoke_item_list.action?pid=mm_132531428_45706160_721332239&qq=372638426&q=" . '女装' . '&sort=2&pageNum=' . 1;
        $client = new Client();
        $response = $client->get($url)->send();
        print_r(json_decode($response->content, true)['success']['data']);
    }

    /**
     *
     */
    public function actionJdTest2()
    {
//        $cookie = Config::getConfig('JD_COOKIE');
        $cookie = 'aud=b3e7a8d40c6f4c395cc2f2173a86b9d8; aud_ver=2; unick=%E4%BA%AC%E6%B7%98%E5%B0%9A%E5%93%81jd; _pst=%E4%BA%AC%E6%B7%98%E5%B0%9A%E5%93%81jd; retina=0; webp=1; sc_width=1920; visitkey=37067480551479068; mobilev=html5; shshshfpa=3a36bf54-f44f-f8a5-3636-cc776dea10d6-1526552350; shshshfp=4f785e663798cca87f099fbe6dc6a68e; mba_muid=15265523513501881572931; pinId=HmZJGf8B8dXFTbrFcA6KQQ; pin=%E4%BA%AC%E6%B7%98%E5%B0%9A%E5%93%81jd; _tp=rnz1Sp6XBqXvx56WZCJJo67JZeyFxWuzqbzc2vJntUcbbZLaAAkD3rylAA%2BtNA6t; user-key=d2059da7-d6b2-42c9-a84e-f58ccda616bf; cn=0; shshshfpb=16bc80c19779f4896aa4cec5cb8d35770a45255526a292fc75afe8e05a; cid=3; __wga=1526644080027.1526644080027.1526631944120.1526552350230.1.3; 3AB9D23F7A4B3C9B=42UIWNO7AOBI57DWPQ3HYPVVC4PBGH57EBCJXJBKKFURKUWVDOPGQ5N425HUEUIXFP2FV4L3XLR6Z5ER6FJIX7ZY24; unpl=V2_ZzNtbUVTRRJ8AUUDfxFfBmJTFFkRUkJGIAhHB34bXARkURQJclRCFXwUR1BnGl8UZAIZXkJcRhxFCEJkeBhcBWQAEV5FU3MlfQAoV0saXDVnAiJdRlREEXELRFB7G1gEZwobXUZRQB13CHZkex9sNTxWfFoXAEYVcw0UBHkeCABXAiJcclZzXhsJC1R%2fGlsBYwAQWUJVRxR1AU9Ufx9fDWUDIlxyVA%3d%3d; _jrda=8; __jdu=15265523513501881572931; TrackID=1WqynaZAMlPg1DrMPKfgL09qORAxu4FBQ5wFv34Ds90_6a_odnIbxb9UQtsYE6O_LTswo-HWu9K_BGJchI1bZ7Tylu1MaECpMVgMt2qhKQL8; logining=1; ceshi3.com=000; thor=6B185B4CC007585C792C6EF04D2B37DA503C473D174E05E22076F83C88D63838BA199D35EC1EB02C40734C3CC6C562EEB7BDBB0D59E09FFD8C442ED2F7902301E6BCF38C51A98B48C033AF723D6932FCBCDFF162157F1CA5A3A0F61A18443184A77FE349844B44B1B685E5AC603D220C11865044C134C2CC891D18DD60A46070; masterClose=yes; __jdv=108460702|baidu|-|organic|not set|1527298873795; avt=42; __jda=95931165.15265523513501881572931.1526552351.1527241218.1527298874.18; __jdc=95931165; __jdb=95931165.3.15265523513501881572931|18.1527298874; asn=2';
        $client = new Client();
        for ($i = 6; $i <= 10; $i++) {
            $response = $client
                ->createRequest()
                ->setMethod('post')
                ->setUrl('https://media.jd.com/gotoadv/getCustomCodeURL')
                ->addHeaders(['cookie' => $cookie])
                ->setData([
                    'adtType' => '33',
                    'siteName' => 'Android-京淘尚品',
                    'unionWebId' => '-1',
                    'protocol' => '2',
                    'codeType' => '2',
                    'type' => '2',
                    'positionId' => '0',
                    'positionName' => $i,
                    'sizeId' => '-1',
                    'logSizeName' => '-1',
                    'unionAppId' => '1287311125',
                    'unionMediaId' => '-1',
                    'logTitle' => '四季穿女白色打底衫小背心女细带纯色修身女装背心 黑色',
                    'imgUrl' => 'http://img14.360buyimg.com/n1/jfs/t21169/208/106319421/141793/b89f5f5/5afc1c4bN222ec018.jpg',
                    'logUnitPrice' => '26.00',
                    'wareUrl' => 'http://item.jd.com/28272314951.html',
                    'materialType' => '1',
                    'actId' => '28272314951',
                    'couponLink' => '-1',
                    'orienPlanId' => '-1',
                    'landingPageType' => '-1',
                    'PopId' => '98784',
                    'materialId' => '28272314951',
                    'adOwner' => '98784',
                    'skuIdList' => '28272314951',
                    'planId' => '96922',
                    'category' => '浅诺旗舰店',
                    'saler' => '1',
                    'logCommissionRate' => '50.00',
                    'requestId' => 's_20180526_5658212',
                    'isApp' => '2',
                ])
                ->send();
        }
    }

    /**
     *
     */
    public function actionTbOrder()
    {
        $cookie = 'cna=eKE/E0TH8EQCAXM8ll94hx0A; t=f3ed0dced749370de7958ed2f1d3e65f; undefined_yxjh-filter-1=true; account-path-guide-s1=true; 132531428_yxjh-filter-1=true; cookie2=155a5b635218d58eebcbd2a66229b3a7; v=0; _tb_token_=33eee6b3179e7; alimamapwag=TW96aWxsYS81LjAgKFdpbmRvd3MgTlQgNi4xOyBXT1c2NCkgQXBwbGVXZWJLaXQvNTM3LjM2IChLSFRNTCwgbGlrZSBHZWNrbykgQ2hyb21lLzY2LjAuMzM1OS4xODEgU2FmYXJpLzUzNy4zNg%3D%3D; cookie32=a9bb2e6d01cb225ed03adebba34fabfb; alimamapw=QSAkHCJbRCVzQX0DEnALFyUDQSFXHCFVABZAbQkCUwcEU1YEVABUDlpQAgAABwoABABQA1YCAlZQ%0AXQYA; cookie31=MTMyNTMxNDI4LCVFNCVCQSVBQyVFNiVCNyU5OCVFNSVCMCU5QSVFNSU5MyU4MWFwcCxqaW5ndGFvc2hhbmdwaW5AMTYzLmNvbSxUQg%3D%3D; pub-message-center=1; login=UtASsssmOIJ0bQ%3D%3D; apushda3ae904e6aafa3475cd9705271554c7=%7B%22ts%22%3A1527835040932%2C%22parentId%22%3A1527834908727%7D; isg=BPT0LwsFEzfjQIa0ycirofdHxbKmZRmAxefoQo5USH8C-ZJDttzbRrX_fTEhAVAP';
        $client = new Client();
        $response = $client
            ->createRequest()
            ->setMethod('get')
            ->setUrl('http://pub.alimama.com/report/getTbkPaymentDetails.json')
            ->addHeaders(['cookie' => $cookie])
            ->setData([
                'startTime' => '2018-03-03',
                'endTime' => '2018-05-31',
                'payStatus' => 3,
                'queryType' => 1,
                'toPage' => 1,
                'perPageSize' => 20,
                'total' => '',
                't' => 1527824619156,
                'pvid' => '',
                '_tb_token_' => '33eee6b3179e7',
                '_input_charset' => 'utf-8',
            ])
            ->send();
        $arr = json_decode($response->content, true);
        var_dump($arr['data']['paymentList']);
    }

    public function actionTest1()
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setUrl("http://119.29.94.164/xiaocao/jd/search/coupon_item_list.acton?qq=372638426&appkey=37263842620180529&page=1")
            ->setMethod('get')
            ->send();
        var_dump($response->content);
        die();
        $arr = json_decode($response['jingdong_union_search_queryCouponGoods_responce']['query_coupon_goods_result'], true);
        var_dump($arr['data'][0]);
    }

    public function actionDdkRobot()
    {
        $request = \Yii::$app->request;
        $pageNum = intval($request->get('pageNum'));
        if (empty($pageNum)) {
            return $this->responseJson(1, [], '采集失败，页码不能为空！');
        }
        $num = intval($request->post('num'));

        try {
            $robots = new DataokeRobot();
            $robots->pageNum = $pageNum;
            $result = $robots->run();
            $total = $num + $robots->num;
            if ($result === false) {
                return $this->responseJson(1, ['pageNum' => $robots->pageNum, 'num' => $total], "采集完成，请在商品列表中查看：\r\n采集页数:" . $robots->pageNum . "\r\n采集产品:" . $total);
            } else {
                return $this->responseJson(0, ['pageNum' => $robots->pageNum, 'num' => $total], '已采集' . $robots->pageNum . '页, 目前共采集到商品' . $total . '件');
            }
        } catch (Exception $e) {
            return $this->responseJson(1, [], '采集失败: ' . $e->getMessage() . '，当前页数' . $pageNum);
        }

    }
}
