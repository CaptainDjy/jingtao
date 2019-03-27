<?php

namespace common\models;

use backend\models\DistributionConfig;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

/**
 * This is the model class for table "dh_goods".
 *
 * @property int $id [int(11) unsigned]
 * @property int $cid [int(11) unsigned]  所属分类
 * @property int $from_cid [int(11) unsigned]  来源分类ID
 * @property int $activity [int(11)]活动类型
 * @property bool $type [tinyint(1) unsigned]  商品类型 0未知类型 11淘宝 12 天猫 21京东 31拼多多
 * @property string $origin_id [varchar(50)]  原始ID
 * @property string $from_id [varchar(50)]  商品来源ID
 * @property string $origin_price [decimal(11,2) unsigned]  原价
 * @property string $coupon_price [decimal(11,2) unsigned]  优惠券价格
 * @property string $title [varchar(255)]  标题
 * @property string $sub_title [varchar(255)]  副标题
 * @property string $thumb [varchar(255)]  缩略图
 * @property string $coupon_id [varchar(50)]  优惠券ID
 * @property string $coupon_money [decimal(11,2) unsigned]  优惠券金额
 * @property string $coupon_rate [decimal(11,1) unsigned]  优惠券折扣
 * @property int $coupon_total [int(11) unsigned]  优惠券总量
 * @property int $coupon_remained [int(11) unsigned]  优惠券剩余
 * @property int $coupon_received [int(11) unsigned]  已领券数量
 * @property int $coupon_start_at [int(11) unsigned]  券开始时间
 * @property int $coupon_end_at [int(11) unsigned]  券结束时间
 * @property string $coupon_link [varchar(500)]  优惠券链接
 * @property string $coupon_short_link [varchar(500)]  优惠券短链
 * @property string $coupon_condition [varchar(255)]  优惠券条件
 * @property string $commission_money [decimal(11,2) unsigned]  佣金
 * @property string $commission_rate [decimal(11,2) unsigned]  佣金比率
 * @property string $commission_rate_plan [decimal(11,2) unsigned]  计划佣金比率
 * @property string $commission_rate_queqiao [decimal(11,2) unsigned]  鹊桥佣金比率
 * @property bool $plan_type [tinyint(1) unsigned]  计划类型
 * @property bool $plan_status [tinyint(1) unsigned]  计划状态
 * @property string $plan_link [varchar(255)]  计划链接
 * @property int $sales_num [int(11) unsigned]  商品销量
 * @property string $keywords [varchar(255)]  关键词
 * @property string $description [varchar(255)]  描述
 * @property string $info 商品详情
 * @property string $seller_id [varchar(255)]  卖家id
 * @property string $seller_nickname [varchar(255)]  卖家昵称
 * @property bool $choice [tinyint(1) unsigned]  精选
 * @property bool $settop [tinyint(1) unsigned]  置顶
 * @property int $praise [int(11) unsigned]  赞
 * @property int $view [int(11) unsigned]  浏览量
 * @property bool $ems_type [tinyint(1)]  包邮，0表不包邮，1表示包邮
 * @property int $start_time [int(11) unsigned]  开始时间
 * @property int $end_time [int(11) unsigned]  结束时间
 * @property string $reply 回复
 * @property bool $status [tinyint(1) unsigned]  状态 0隐藏 1显示
 * @property int $created_at [int(11) unsigned]  创建时间
 * @property int $updated_at [int(11) unsigned]  更新时间
 * @property int $is_ju [int(11) unsigned]  是否聚划算  0否1是
 * @property int $is_tqg [int(11) unsigned]  是否淘抢购 0否1是
 */
class Goods extends ActiveRecordBase
{
    const TYPE_ID = [
        'taobao' => 11,
        'tmall' => 12,
        'jd' => 21,
        'pdd' => 31,
    ];

    //bool类型通用label
    const BOOL_LABEL = [
        0 => '否',
        1 => '是',
    ];
    //筛选类型label
    const TYPE_LABEL = [
        'all' => '全部',
        11 => '淘宝',
        12 => '天猫',
        21 => '京东',
        31 => '拼多多',
    ];

    //淘宝
    const ORDER_TB = [
        1 => 'start_time desc',//开始时间倒叙
        2 => 'sales_num asc',//销量正序
        3 => 'sales_num desc',//销量倒叙
        4 => 'coupon_price asc',//优惠券后价正序
        5 => 'coupon_price desc',//优惠券后价倒叙
        6 => 'commission_money asc',//佣金正序
        7 => 'commission_money desc',//佣金倒叙
    ];

    //京东
    const ORDER_JD = [
        1 => 'start_time desc',
        2 => 'sales_num asc',
        3 => 'sales_num desc',
        4 => 'coupon_price asc',
        5 => 'coupon_price desc',
        6 => 'commission_money asc',
        7 => 'commission_money desc',
    ];

    //拼多多
    const ORDER_PDD = [
        1 => 12,
        2 => 5,
        3 => 6,
        4 => 9,
        5 => 10,
        6 => 13,
        7 => 14,
    ];

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoodsCategory()
    {
        return $this->hasOne(GoodsCategory::class, ['id' => 'cid']);
    }

    /**
     * 添加商品
     * @param array $data
     * @throws Exception
     */
    public static function add($data = [])
    {
        $model = new self();
        $model->setAttributes($data);
        if (!$model->save()) {
            throw new Exception('商品保存失败：' . $model->error);
        }
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cid', 'origin_price', 'title','coupon_remained'], 'required'],
            [['cid', 'from_cid', 'type', 'coupon_total', 'coupon_remained', 'coupon_received', 'coupon_start_at', 'coupon_end_at', 'plan_type', 'plan_status', 'sales_num', 'choice', 'settop', 'praise', 'view', 'ems_type', 'start_time', 'end_time', 'status', 'created_at', 'updated_at'], 'integer'],
            [['origin_price', 'coupon_price', 'coupon_money', 'coupon_rate', 'commission_money', 'commission_rate', 'commission_rate_plan', 'commission_rate_queqiao'], 'number'],
            [['info', 'reply'], 'string'],
            [['origin_id', 'from_id', 'coupon_id'], 'string', 'max' => 50],
            [['title', 'sub_title', 'thumb', 'coupon_condition', 'plan_link', 'keywords', 'description', 'seller_nickname'], 'string', 'max' => 255],
            [['coupon_link', 'coupon_short_link'], 'string', 'max' => 800],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cid' => '所属分类',
            'from_cid' => '来源分类',
            'type' => '商品类型',// 0未知类型 11淘宝 12 天猫 21京东 31拼多多
            'origin_id' => '原始ID',
            'from_id' => '商品来源ID',
            'origin_price' => '原价',
            'title' => '标题',
            'sub_title' => '副标题',
            'thumb' => '缩略图',
            'coupon_id' => '优惠券ID',
            'coupon_price' => '券后价',
            'coupon_money' => '优惠券',
            'coupon_rate' => '优惠券折扣',
            'coupon_total' => '优惠券总量',
            'coupon_remained' => '券剩余',
            'coupon_received' => '已领券数量',
            'coupon_start_at' => '券开始时间',
            'coupon_end_at' => '券结束时间',
            'coupon_link' => '优惠券链接',
            'coupon_short_link' => '优惠券短链',
            'coupon_condition' => '优惠券条件',
            'commission_money' => '佣金',
            'commission_rate' => '佣金比率',
            'commission_rate_plan' => '计划佣金比率',
            'commission_rate_queqiao' => '鹊桥佣金比率',
            'plan_type' => '计划类型',
            'plan_status' => '计划状态',
            'plan_link' => '计划链接',
            'sales_num' => '商品销量',
            'keywords' => '关键词',
            'description' => '描述',
            'info' => '商品详情',
            'seller_id' => '卖家id',
            'seller_nickname' => '卖家昵称',
            'choice' => '是否精选',//0否1是
            'settop' => '是否置顶',//0否1是
            'praise' => '赞',
            'view' => '浏览量',
            'ems_type' => '是否包邮',//0否1是
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'reply' => '回复',
            'status' => '是否显示',//0否1是
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'is_ju'=>'是否聚划算',//0否1是
            'is_tqg'=>'是否淘抢购',//0否1是
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class
            ]
        ];
    }

    /**
     * 京东精选
     * @param $uid
     * @param $keyword
     * @param $cate
     * @param $page
     * @param $sort
     * @return array
     * @throws \yii\base\Exception
     */
    public static function featuredJd($uid, $keyword, $cate, $page, $sort)
    {
        if (empty($keyword)) {  //搜索
            $list = Goods::find()->select("coupon_price,origin_price,type,title,sales_num,coupon_money,commission_money,thumb,origin_id")->where(['from_cid' => $cate, 'type' => 21])->offset($page * 10)->limit(10)->asArray()->all();
        } else {
            $list = Goods::find()->select("coupon_price,origin_price,type,title,sales_num,coupon_money,commission_money,thumb,origin_id")->where(['type' => 21])->andWhere(['like', 'title', $keyword])->orderBy(self::ORDER_JD[$sort])->offset($page * 10)->limit(10)->asArray()->all();
        }
        $arr = [];
        if (!empty($list)) {
            $user = User::findOne(['uid' => $uid]);
            if (empty($user)) {
                $lv = 0;
            } else {
                $lv = $user->lv;
            }
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
                    'coupon_money' => $goods['coupon_money'],
                    'volume' => $volume,
                    'coupon_url' => '',
                    'type' => $goods['type'],
                    'commission_money' => sprintf("%.2f", $goods['commission_money'] * $ratio),
                ];
            }
        }
        return $arr;
    }

}
