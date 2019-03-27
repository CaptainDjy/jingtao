<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;


/**
 * This is the model class for table "{{%recharge}}".
 *
 * @property int $id 序号
 * @property int $uid 用户UID
 * @property int $order_id 用户UID
 * @property int $price 用户UID
 * @property int $type 类型1充值2提现
 * @property int $credit 返佣类型1为credit,2为credit2,3为credit3
 * @property int $status 状态1审核中2已审核3已拒绝
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class Recharge extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%recharge}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uid'], 'required'],
            [['goods_id'], 'string'],
            [['uid', 'order_type', 'type', 'credit', 'status', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '序号',
            'uid' => '用户UID',
            'type' => '类型',
            'price' => '金额',
            'status' => '状态',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

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
     * 添加订单
     * @param $data
     * @return int
     * @throws \yii\db\Exception
     */
    public static function addOrder($data)
    {
        $result = \Yii::$app->db->createCommand()->batchInsert(self::tableName(), ['uid', 'type', 'order_id', 'goods_id', 'order_type', 'price', 'credit', 'status', 'created_at', 'updated_at'], $data)->execute();
        return $result;
    }

    /**
     * 订单类型
     * @param $type
     * @return string
     */
    public static function type($type)
    {
        switch ($type) {
            case 1:
                return "自购";
            case 2:
                return '团队';
            case 3:
                return '分佣';
            case 4:
                return '推广';
            case 5:
                return '分享';
            default:
                return '你触碰到我的知识盲区了！';
        }
    }

    /**
     * @param $status
     * @return string
     */
    public static function status($status)
    {
        switch ($status) {
            case 1:
                return "审核中";
            case 2:
                return '已完成';
            case 3:
                return '已驳回';
            default:
                return '你触碰到我的知识盲区了！';
        }
    }

    /**
     * 总收益
     * @param $uid
     * @return array
     */
    public static function findIncome($uid)
    {
        $user = User::findOne(['uid' => $uid]);
        // TODO  待修改
        $bill_income = self::find()->where(['uid' => $uid, 'status' => 1])->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m')" => date("Y-m", strtotime("last month"))])->andWhere("type !=4")->sum('price'); //上月结算佣金

        $last_revenue = self::find()->where(['uid' => $uid,])->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m')" => date("Y-m", strtotime("last month"))])->andWhere("type !=4")->sum('price'); //上月预计收入

        $predict_income = self::find()->where(['uid' => $uid,])->andWhere("type !=4")->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m')" => date('Y-m', time())])->sum('price'); //本月预计收入

        $month_award = self::find()->where(['type' => 4, 'uid' => $uid])->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m')" => date('Y-m', time())])->sum('price'); //本月推广奖励

        $last_award = self::find()->where(['type' => 4, 'uid' => $uid])->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m')" => date("Y-m", strtotime("last month"))])->sum('price'); //上月推广奖励

        $today_payments = Order::find()->where(['uid' => $uid])->andWhere(['or', ['order_status' => 2], ['order_status' => 0], ['order_status' => 16]])->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m-%d')" => date('Y-m-d', time())])->count(1); //今日付款笔数

        $today_commission = self::find()->where(['uid' => $uid, 'type' => 1])->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m-%d')" => date('Y-m-d', time())])->sum('price'); //今日预估佣金

        $today_share = self::find()->where(['uid' => $uid, 'type' => 5])->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m-%d')" => date('Y-m-d', time())])->sum('price'); //今日分享赚

        $today_award = self::find()->where(['uid' => $uid, 'type' => 4])->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m-%d')" => date('Y-m-d', time())])->sum('price'); //今日推广奖励

        $yesterday_payments = Order::find()->where(['uid' => $uid])->andWhere(['order_status' => 2])->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m-%d')" => date("Y-m-d", strtotime("-1 day"))])->count(1); //昨日付款笔数

        $yesterday_commission = self::find()->where(['uid' => $uid,])->andWhere("type !=4")->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m-%d')" => date("Y-m-d", strtotime("-1 day"))])->sum('price'); //昨日预估佣金

        $yesterday_share = self::find()->where(['uid' => $uid, 'type' => 5])->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m-%d')" => date("Y-m-d", strtotime("-1 day"))])->sum('price'); //昨日分享赚

        $yesterday_award = self::find()->where(['uid' => $uid, 'type' => 4])->andWhere(["FROM_UNIXTIME(created_at, '%Y-%m-%d')" => date("Y-m-d", strtotime("-1 day"))])->sum('price'); //昨日推广奖励

        $bill_income = empty($bill_income) ? 0 : $bill_income;
        $predict_income = empty($predict_income) ? 0 : $predict_income;
        $last_revenue = empty($last_revenue) ? 0 : $last_revenue;
        $month_award = empty($month_award) ? 0 : $month_award;
        $last_award = empty($last_award) ? 0 : $last_award;
        $today_payments = empty($today_payments) ? 0 : intval($today_payments);
        $today_commission = empty($today_commission) ? 0 : $today_commission;
        $today_share = empty($today_share) ? 0 : $today_share;
        $today_award = empty($today_award) ? 0 : $today_award;
        $yesterday_payments = empty($yesterday_payments) ? 0 : $yesterday_payments;
        $yesterday_commission = empty($yesterday_commission) ? 0 : $yesterday_commission;
        $yesterday_share = empty($yesterday_share) ? 0 : $yesterday_share;
        $yesterday_award = empty($yesterday_award) ? 0 : $yesterday_award;

        $info = [
            'balance' => $user->credit4, //账户余额
            'bill_income' => sprintf("%.2f", $bill_income),//上月结算佣金
            'predict_income' => sprintf("%.2f", $predict_income),//本月预计收入
            'last_revenue' => sprintf("%.2f", $last_revenue),//上月预计收入
            'month_award' => sprintf("%.2f", $month_award),//本月推广奖励
            'last_award' => sprintf("%.2f", $last_award),//上月推广奖励
            'today_payments' => intval($today_payments),//今日付款笔数
            'today_commission' => sprintf("%.2f", $today_commission),//今日预估佣金
            'today_share' => sprintf("%.2f", $today_share),//今日分享赚
            'today_award' => sprintf("%.2f", $today_award),//今日推广奖励
            'yesterday_payments' => intval($yesterday_payments),//昨日付款笔数
            'yesterday_commission' => sprintf("%.2f", $yesterday_commission),//昨日预估佣金
            'yesterday_share' => sprintf("%.2f", $yesterday_share),//昨日分享赚
            'yesterday_award' => sprintf("%.2f", $yesterday_award),//昨日推广奖励
        ];
        return $info;
    }

    public function getOrder()
    {
        return $this->hasOne(Order::class, ['trade_id' => 'order_id', 'product_id' => 'goods_id']);
    }

}
