<?php
/**
 * @author 河南邦耀网络科技
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * @link http://www.hnbangyao.com
 */

namespace common\models;


use common\components\jd\JdClient;
use common\components\jd\requests\ServicePromotionGoodsInfo;
use common\components\pdd\PddClient;
use common\components\pdd\requests\DdkGoodsDetailRequest;
use common\components\taobao\requests\TbkItemInfoGet;
use common\components\taobao\TaobaoClient;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "dh_order".
 *
 * @property int $id [int(11)]
 * @property int $uid [int(11)]  用户ID
 * @property string $trade_id [varchar(255)]  订单号
 * @property string $product_id [varchar(25)]  商品ID
 * @property bool $type [tinyint(1)]  1天猫2京东3拼多多
 * @property string $pid [varchar(255)]  广告位ID
 * @property int $product_num [int(10) unsigned]  商品数
 * @property string $product_price [decimal(10,2) unsigned]  商品单价c
 * @property bool $order_status [tinyint(1)]  订单状态1订单结算,2订单付款,3订单失效,4订单成功
 * @property bool $rebate_status [tinyint(1)]  返佣状态1未返,2已返
 * @property int $income_ratio [int(10) unsigned]  收入比率
 * @property string $payment_price [decimal(10,2) unsigned]  付款金额
 * @property string $estimated_effect [varchar(255)]  效果预估
 * @property string $settlement_price [decimal(10,2) unsigned]  结算金额
 * @property string $commission_rate [decimal(10,2) unsigned]  佣金比率
 * @property string $commission_price [decimal(10,2) unsigned]  佣金金额
 * @property string $subsidy_ratio [decimal(10,2) unsigned]  补贴比率
 * @property string $subsidy_price [decimal(10,2) unsigned]  补贴金额
 * @property string $pid_name [varchar(255)]  广告位名称
 * @property string $wangwang [varchar(255)]  掌柜旺旺
 * @property string $shop [varchar(255)]  所属店铺
 * @property bool $order_type [tinyint(1) unsigned]  订单类型1天猫,2淘宝,3聚划算
 * @property int $divided_ratio [int(10) unsigned]  分成比率
 * @property string $estimated_revenue [decimal(10,2) unsigned]  预估收入
 * @property bool $subsidy_type [tinyint(1)]  补贴类型
 * @property bool $dealing_platform [tinyint(1)]  成交平台
 * @property bool $service_source [tinyint(1)]  第三方服务来源
 * @property string $category_name [varchar(100)]  类目名称
 * @property int $source_media [int(11)]  来源媒体ID
 * @property string $media_name [varchar(255)]  来源媒体名称
 * @property int $settlement_at [int(11)]  结算时间
 * @property int $created_at [int(11) unsigned]  创建时间
 * @property int $order_time [int(11) unsigned]  订单创建时间
 * @property int $updated_at [int(11) unsigned]  更新时间
 * @property string $picUrl
 * @property string $title [varchar(255)]
 */
class Order extends ActiveRecord
{
    const ORDER_STATUS = [
        -1 => '未支付',
        0 => '已支付',
        1 => '订单结算',
        2 => '订单付款',
        3 => '订单失效',
        4 => '订单成功',
        16 => '订单付款',
    ];

    const PDD_STATUS = [
        -1 => '未支付',
        0 => '已支付',
        1 => '已成团',
        2 => '确认收货',
        3 => '审核成功',
        4 => '审核失败（不可提现）',
        5 => '已经结算',
        6 => '非多多进宝商品',
        10 => '已处罚',
    ];

    const JD_STATUS = [
        -1 => '未知',
        2 => '无效-拆单',
        3 => '无效-取消',
        4 => '无效-京东帮帮主订单',
        5 => '无效-账号异常',
        6 => '无效-赠品类目不返佣',
        7 => '无效-校园订单',
        8 => '无效-企业订单',
        9 => '无效-团购订单',
        10 => '无效-开增值税专用发票订单',
        11 => '无效-乡村推广员下单',
        12 => '无效-自己推广自己下单',
        13 => '无效-违规订单',
        14 => '无效-来源与备案网址不符',
        15 => '待付款',
        16 => '已付款',
        17 => '已完成',
        18 => '已结算',
    ];

    const REBATE_STATUS = [
        1 => '未返',
        2 => '已返',
    ];

    const  TYPE = [
        '1' => '天猫',
        '2' => '京东',
        '3' => '拼多多',
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uid', 'trade_id', 'pid', 'product_num', 'product_price', 'payment_price', 'commission_rate'], 'required'],
            [['uid', 'product_num', 'type', 'order_type', 'income_ratio', 'divided_ratio', 'subsidy_type', 'dealing_platform', 'service_source', 'source_media', 'settlement_at', 'created_at', 'updated_at', 'rebate_status'], 'integer'],
            [['product_price', 'order_status', 'payment_price', 'settlement_price', 'estimated_revenue', 'commission_rate', 'commission_price', 'subsidy_ratio', 'subsidy_price'], 'number'],
            [['pid_name', 'wangwang', 'shop', 'media_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '序号',
            'uid' => '用户ID',
            'trade_id' => '订单号',
            'pid' => '推广位ID',
            'title'=>'订单标题',
            'type'=>'订单类型',
            'pid_name' => '推广位名称',
            'wangwang' => '阿里旺旺',
            'shop' => '所属店铺',
            'product_num' => '商品数',
            'product_price' => '商品单价',
            'order_status' => '订单状态',
            'order_type' => '订单类型',
            'income_ratio' => '收入比率',
            'divided_ratio' => '分成比率',
            'payment_price' => '付款金额',
            'estimated_effect' => '效果预估',
            'settlement_price' => '结算金额(付款)',
            'estimated_revenue' => '预估收入',
            'commission_rate' => '佣金比率',
            'commission_price' => '结算佣金',
            'subsidy_ratio' => '补贴比率',
            'subsidy_price' => '补贴金额',
            'subsidy_type' => '补贴类型',
            'dealing_platform' => '成交平台',
            'service_source' => '第三方服务来源',
            'category_name' => '类目名称',
            'source_media' => '来源媒体ID',
            'media_name' => '来源媒体名称',
            'settlement_at' => '结算时间',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * 新增订单记录
     * @param array $keys
     * @param array $data
     * @return int
     * @throws \yii\db\Exception
     */
    public static function addOrders(array $keys, array $data)
    {
        $result = 0;
        if ($keys && $data && is_array($data)) {
            $result = Order::find()
                ->createCommand()
                ->batchInsert(Order::tableName(), $keys, $data)
                ->execute();
        }
        return $result ? $result : 0;
    }

    /**
     * @param $uid
     * @param $type
     * @param $page
     * @param $order_status
     * @return array|Announcement[]|Article[]|Biz[]|Collection[]|Goods[]|GoodsCategory[]|Message[]|Nav[]|Order[]|Recharge[]|RobotDdk[]|RobotJd[]|Withdraw[]|ActiveRecord[]
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public static function findByUid($uid, $type, $page, $order_status)
    {
        if ($type == 2) {
            if ($order_status == 1) {
                $order = '18';
            } elseif ($order_status == 2) {
                $order = '16';
            } elseif ($order_status == 3) {
                $order = '2,3,4,5,6,7,8,9,10,11,12,13,14';
            } else {
                $order = $order_status;
            }
        } elseif ($type == 3) {
            if ($order_status == 1) {
                $order = '5';
            } elseif ($order_status == 2) {
                $order = '0';
            } elseif ($order_status == 3) {
                $order = '-1,4';
            } else {
                $order = $order_status;
            }
        } else {
            $order = $order_status;
        }
        if ($order_status == 0) {
            $list = Recharge::find()->alias('a')
                ->joinWith('order b', true)
                ->select("a.price,a.order_id,a.goods_id")
                ->where(['a.uid' => $uid, 'b.type' => $type,])
                ->andWhere("a.price>0")
                ->offset($page * 6)
                ->limit(6)
                ->orderBy('b.id desc')
                ->asArray()->all();
        } elseif ($order_status == 3) {
            $list = self::find()->where(['uid' => $uid,])
                ->andWhere(['type' => $type,])
                ->andWhere("order_status in ({$order})")
                ->offset($page * 6)
                ->limit(6)
                ->orderBy('created_at desc')
                ->asArray()->all();
        } else {
            $list = Recharge::find()->alias('a')
                ->joinWith('order b', true)
                ->select("a.price,a.order_id,a.goods_id")
                ->where(['a.uid' => $uid, 'b.type' => $type,])
                ->andWhere(" b.order_status in ({$order}) and a.price>0")
                ->offset($page * 6)
                ->limit(6)
                ->orderBy('b.id desc')
                ->asArray()->all();
        }
        if (!empty($list)) {
            foreach ($list as &$ls) {
                if (empty($ls['order']['picUrl'])) {
                    if ($ls['order']['type'] == 1) { //天猫
                        $client = new TaobaoClient();
                        $requst = new TbkItemInfoGet();
                        $requst->num_iids = $ls['order']['product_id'];
                        $requst->platform = 2;
                        $response = $client->run($requst);
                        if (!empty($response['tbk_item_info_get_response']['results']['n_tbk_item'][0])) {
                            \Yii::$app->db->createCommand()->update(self::tableName(), ['picUrl' => $response['tbk_item_info_get_response']['results']['n_tbk_item'][0]['pict_url']], ['trade_id' => $ls['order']['trade_id']])->execute();
                        } else {
                            \Yii::$app->db->createCommand()->update(self::tableName(), ['picUrl' => \Yii::$app->request->hostInfo . '/static/mobile/img/logo.png'], ['trade_id' => $ls['order']['trade_id']])->execute();
                        }
                    } elseif ($ls['order']['type'] == 2) { //京东
                        $client = new JdClient();
                        $requst = new ServicePromotionGoodsInfo();
                        $requst->skuIds = $ls['order']['product_id'];
                        $response = $client->run($requst);
                        \Yii::$app->db->createCommand()->update(self::tableName(), ['picUrl' => json_decode($response['jingdong_service_promotion_goodsInfo_responce']['getpromotioninfo_result'], true)['result'][0]['imgUrl'], 'title' => json_decode($response['jingdong_service_promotion_goodsInfo_responce']['getpromotioninfo_result'], true)['result'][0]['goodsName']], ['trade_id' => $ls['order']['trade_id']])->execute();
                    } else { // 拼多多
                        $client = new PddClient();
                        $requst = new DdkGoodsDetailRequest();
                        $requst->goods_id_list = "[{$ls['order']['product_id']}]";
                        $response = $client->run($requst);
                        \Yii::$app->db->createCommand()->update(self::tableName(), ['picUrl' => $response['goods_detail_response']['goods_details'][0]['goods_thumbnail_url'], 'title' => $response['goods_detail_response']['goods_details'][0]['goods_name']], ['trade_id' => $ls['order']['trade_id']])->execute();
                    }
                }
                if ($ls['order']['type'] == 1) { //淘宝
                    $ls['order']['status'] = self::ORDER_STATUS[$ls['order']['order_status']];
                } elseif ($ls['order']['type'] == 2) { //京东
                    $ls['order']['status'] = self::JD_STATUS[$ls['order']['order_status']];
                } else { //拼多多
                    $ls['order']['status'] = self::PDD_STATUS[$ls['order']['order_status']];
                }
//                $ls['order']['estimated_effect'] = bcmul($ls['order']['estimated_effect'], $zongRate, 2);
                $ls['order']['created_at'] = date('m-d H:i', $ls['order']['created_at']);
                if ($ls['order']['settlement_at'] > 1) {
                    $ls['order']['settlement_at'] = date('m-d H:i', $ls['order']['settlement_at']);
                }
            }
//            }

        }
        return $list;
    }

    /**
     * @param $uid
     * @param $status
     * @param $page
     * @return array|Announcement[]|Article[]|Biz[]|Collection[]|Goods[]|GoodsCategory[]|Message[]|Nav[]|Order[]|Recharge[]|RobotDdk[]|Withdraw[]|ActiveRecord[]
     * @throws \yii\base\Exception
     */
    public static function findByStatus($uid, $status, $page, $limit = 6)
    {
        $list = self::find()->where(['uid' => $uid/*, 'rebate_status' => $status*/])->offset($page * $limit)->limit($limit)->asArray()->all();
        /*if (!empty($list)) {
            foreach ($list as &$ls) {
                if ($ls['type'] == 1) { //天猫
                    $client = new TaobaoClient();
                    $requst = new TbkItemInfoGet();
                    $requst->num_iids = $ls['product_id'];
                    $response = $client->run($requst);
                    $ls['picUrl'] = $response['tbk_item_info_get_response']['results']['n_tbk_item'][0]['pict_url'];
                    $ls['title'] = $response['tbk_item_info_get_response']['results']['n_tbk_item'][0]['title'];
                } elseif ($ls['type'] == 2) { //京东
                    $client = new JdClient();
                    $requst = new ServicePromotionGoodsInfo();
                    $requst->skuIds = $ls['product_id'];
                    $response = $client->run($requst);
                    $ls['picUrl'] = json_decode($response['jingdong_service_promotion_goodsInfo_responce']['getpromotioninfo_result'], true)['result'][0]['imgUrl'];
                    $ls['title'] = json_decode($response['jingdong_service_promotion_goodsInfo_responce']['getpromotioninfo_result'], true)['result'][0]['goodsName'];
                } else { // 拼多多
                    $client = new PddClient();
                    $requst = new DdkGoodsDetailRequest();
                    $requst->goods_id_list = '[' . $ls['product_id'] . ']';
                    $response = $client->run($requst);
                    $ls['picUrl'] = $response['goods_detail_response']['goods_details'][0]['goods_thumbnail_url'];
                    $ls['title'] = $response['goods_detail_response']['goods_details'][0]['goods_name'];
                }
                $ls['status'] = self::ORDER_STATUS[$ls['order_status']];
                $ls['rebate_status'] = self::REBATE_STATUS[$ls['rebate_status']];
                $ls['created_at'] = date('Y-m-d H:i:s', $ls['created_at']);
                $ls['settlement_at'] = date('Y-m-d H:i:s', $ls['settlement_at']);
            }
        }*/
        return $list;
    }

    /**
     * 统计订单
     * @param $uid
     * @return array
     */
    public static function orderCount($uid)
    {
        $tbTotal = self::find()->where(['uid' => $uid, 'type' => 1])->asArray()->count(1);//天猫总订单
        $tbSettled = self::find()->where(['uid' => $uid, 'type' => 1, 'order_status' => 1])->asArray()->count(1);//天猫已结算
        $tbPaid = self::find()->where(['uid' => $uid, 'type' => 1, 'order_status' => 2])->asArray()->count(1);//天猫已付款
        $tbExpired = self::find()->where(['uid' => $uid, 'type' => 1, 'order_status' => 3])->asArray()->count(1);//天猫已失效

        $jdTotal = self::find()->where(['uid' => $uid, 'type' => 2])->asArray()->count(1);//Jd总订单
        $jdSettled = self::find()->where(['uid' => $uid, 'type' => 2, 'order_status' => 1])->asArray()->count(1);//Jd已结算
        $jdPaid = self::find()->where(['uid' => $uid, 'type' => 2, 'order_status' => 2])->asArray()->count(1);//Jd已付款
        $jdExpired = self::find()->where(['uid' => $uid, 'type' => 2, 'order_status' => 3])->asArray()->count(1);//Jd已失效

        $pddTotal = self::find()->where(['uid' => $uid, 'type' => 3])->asArray()->count(1);//pdd总订单
        $pddSettled = self::find()->where(['uid' => $uid, 'type' => 3, 'order_status' => 1])->asArray()->count(1);//pdd已结算
        $pddPaid = self::find()->where(['uid' => $uid, 'type' => 3, 'order_status' => 2])->asArray()->count(1);//pdd已付款
        $pddExpired = self::find()->where(['uid' => $uid, 'type' => 3, 'order_status' => 3])->asArray()->count(1);//pdd已失效

        $data = [
            ['tbTotal' => $tbTotal, 'tbPaid' => $tbPaid, 'tbSettled' => $tbSettled, 'tbExpired' => $tbExpired],
            ['jdTotal' => $jdTotal, 'jdPaid' => $jdPaid, 'jdSettled' => $jdSettled, 'jdExpired' => $jdExpired],
            ['pddTotal' => $pddTotal, 'pddPaid' => $pddPaid, 'pddSettled' => $pddSettled, 'pddExpired' => $pddExpired],
        ];
        return $data;
    }

    /**
     * @param $data
     * @return int
     * @throws \yii\db\Exception
     */
    public static function addOrder($data)
    {
        $result = \Yii::$app->db->createCommand()->batchInsert(self::tableName(),
            [
                'uid',
                'trade_id',
                'product_id',
                'type',
                'pid',
                'pid_name',
                'wangwang',
                'shop',
                'product_num',
                'product_price',
                'order_status',
                'rebate_status',
                'order_type',
                'divided_ratio',
                'payment_price',
                'estimated_effect',
                'settlement_price',
                'estimated_revenue',
                'commission_rate',
                'commission_price',
                'dealing_platform',
                'category_name',
                'source_media',
                'settlement_at',
                'title',
                'created_at',
                'updated_at',
            ], $data)->execute();
        return $result;
    }

}
