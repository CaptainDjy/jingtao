<?php

namespace common\models;

use common\components\alipay\AlipayClient;
use common\helpers\Utils;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * Class Withdraw
 * @package common\models
 * @property int $id [int(11)]
 * @property int $uid [int(11)]  用户ID
 * @property string $pay_to [varchar(100)]  收款账号
 * @property string $trade_sn [varchar(255)]  订单号
 * @property bool $type [tinyint(1) unsigned]  到账类型1支付宝
 * @property string $amount [decimal(10,2) unsigned]  提现金额
 * @property string $alipay_order_id [varchar(64)]  支付宝订单号
 * @property string $alipay_date [varchar(20)]  支付宝支付时间
 * @property string $msg 支付失败信息
 * @property bool $status [tinyint(3)]  订单状态-2提现失败-1已拒绝0待审核1审核通过2提现成功
 * @property string $remark [varchar(255)]  订单备注
 * @property int $approve_at [int(10) unsigned]  审批时间
 * @property int $created_at [int(11) unsigned]  申请时间
 * @property int $updated_at [int(11) unsigned]  更新时间
 */
class Withdraw extends ActiveRecord
{
    const TYPE_LABEL = [
        1 => '支付宝'
    ];
    const STATUS_LABEL = [
        -2 => '提现失败',
        -1 => '已拒绝',
        0 => '待审核',
        1 => '审核通过',
        2 => '提现成功',
    ];
    const STATUS_RATIFY = 1;
    const STATUS_REFUSE = -1;
    const STATUS_DEFAULT = 0;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL = -2;
    const TYPE_ALIPAY = 1;

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%withdraw}}';
    }

    /**
     * @param $model Withdraw
     * @param bool $isOk 是否提现成功
     */
    public static function updateMoney($model, $isOk = true)
    {
        if ($isOk) {
            //提现成功 扣冻结
            $counters = [
                'credit4' => 0,
                'frozen' => -$model->amount
            ];
        } else {
            $counters = [
                'credit4' => $model->amount,
                'frozen' => -$model->amount
            ];
        }
        $user = User::findOne($model->uid);
        $result = $user->updateCounters($counters);
        if (!$result) {
            \Yii::error('更新用户资产失败:' . json_encode($model));
        }
        UserLog::addWithdrawLog($user, $counters);
    }

    /**
     * @param $model Withdraw
     */
    public static function Refuse($model)
    {
        $model->updateAttributes(['status' => Withdraw::STATUS_REFUSE, 'msg' => '提现申请被拒绝']);
        self::updateMoney($model, false);
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['trade_sn'] = ['trade_sn'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uid', 'amount', 'pay_to'], 'required'],
            [['trade_sn'], 'required', 'on' => 'trade_sn'],
            [['uid', 'type', 'status', 'approve_at', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
            [['trade_sn', 'remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => '用户ID',
            'pay_to' => '收款账号',
            'trade_sn' => '订单号',
            'type' => '到账类型',
            'amount' => '提现金额',
            'alipay_order_id' => '支付宝订单号',
            'alipay_date' => '支付宝支付时间',
            'msg' => '信息',
            'status' => '订单状态',
            'remark' => '订单备注',
            'approve_at' => '审批时间',
            'created_at' => '申请时间',
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
            ],
        ];
    }

    /**
     * 执行提现
     * @param Withdraw $model
     * @throws Exception
     */
    public static function withdraw($model)
    {
        $result = $model->updateAttributes(['status' => Withdraw::STATUS_RATIFY, 'approve_at' => TIMESTAMP]);
        if (!$result) {
            throw new Exception('提现失败:订单状态修改失败');
        }
        $data = AlipayClient::withdraw($model);
        $result = $model->updateAttributes([
            'alipay_order_id' => $data['order_id'],
            'alipay_date' => $data['pay_date'],
            'msg' => $data['msg'],
            'status' => Withdraw::STATUS_SUCCESS,
        ]);
        if (!$result) {
            throw new Exception('提现失败:支付宝支付信息更新失败');
        }
    }

    /**
     * @param $user User
     * @param array $data
     * @throws Exception
     */
    public static function apply($user, array $data)
    {
        $result = $user->updateCounters([
            'credit4' => -$data['money'],
            'frozen' => $data['money']
        ]);
        if (!$result) {
            throw new Exception('扣减余额至提现冻结失败,请稍后重试');
        }
        $model = new self();
        $model->uid = $user->uid;
        $model->pay_to = $user->withdraw_to;
        $model->type = self::TYPE_ALIPAY;
        $model->amount = sprintf('%.2f', $data['money']);
        $model->status = self::STATUS_DEFAULT;
        if (!$model->save()) {
            throw new Exception('提现订单创建失败,请稍后重试!');
        }
        $result = $model->updateAttributes([
            'trade_sn' => Utils::genderOrderId($model->id, 'withdraw')
        ]);
        if (!$result) {
            throw new Exception('订单号创建失败,请稍后重试!');
        }
    }
}
