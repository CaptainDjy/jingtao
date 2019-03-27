<?php

namespace common\models;


use backend\models\DistributionConfig;
use common\helpers\Utils;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

/**
 * This is the model class for table "{{%upgrade_order}}".
 *
 * @property int $id
 * @property int $uid 用户ID
 * @property string $trade_no 订单号
 * @property int $type 支付类型1微信2支付宝
 * @property string $amount 金额
 * @property string $alipay_trade_no 支付宝订单号
 * @property string $wechat_trade_no 微信订单号
 * @property string $pay_date 支付完成时间
 * @property string $msg 支付失败信息
 * @property int $status 订单状态-1支付失败0待支付1支付成功
 * @property string $remark 订单备注
 * @property string $created_at 申请时间
 * @property string $updated_at 更新时间
 */
class UpgradeOrder extends ActiveRecordBase
{
    const STATUS_LABEL = [
        -1 => '支付失败',
        0 => '等待支付',
        1 => '支付成功',
    ];
    const TYPE_LABEL = [
        1 => '微信',
        2 => '支付宝',
    ];

    const TYPE_WECHAT = 1;
    const TYPE_ALIPAY = 2;
    const STATUS_FAIL = -1;
    const STATUS_DEFAULT = 0;
    const STATUS_SUCCESS = 1;
    const UPGRADE_FEE = 598;//TODO 当前为测试金额
    const PAY_TYPE = [
        'alipay' => '支付宝',
        'wxpay' => '微信',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%upgrade_order}}';
    }

    /**
     * 校验是否已购买会员权益
     * @param $uid
     * @return bool
     */
    public static function checkUpgrade($uid)
    {
        $user = User::findOne($uid);
        if ($user->lv != 0) {
            return true;
        }
        $model = self::findOne(['uid' => $uid, 'status' => self::STATUS_SUCCESS]);
        if ($model) {
            return true;
        }
        return false;
    }

    /**
     * 创建升级订单（废弃）
     * @param $uid
     * @param $type
     * @return UpgradeOrder
     * @throws Exception
     */
    public static function createOrder($uid, $type)
    {
        $model = new self();
        $model->loadDefaultValues();
        $model->uid = $uid;
        $model->type = $type;
        $model->amount = DistributionConfig::getAll('partner')['userMoney'];
        if ($model->save()) {
            $result = $model->updateAttributes([
                'trade_no' => Utils::genderOrderId($model->id, 'upgrade')
            ]);
            if (!$result) {
                throw new Exception('订单创建失败:订单号创建失败');
            }
            return $model;
        } else {
            throw new Exception('订单创建失败:' . $model->getError());
        }
    }

    /**
     * 创建升级会员订单
     * @param $uid int Uid
     * @param $type string 类型
     * @param $amount double 金额
     * @return UpgradeOrder
     * @throws Exception
     */
    public static function createUpOrder($uid, $type, $amount)
    {
        $model = new self();
        $model->loadDefaultValues();
        $model->uid = $uid;
        $model->type = $type;
        $model->amount = round($amount, 2);
        if ($model->save()) {
            $result = $model->updateAttributes([
                'trade_no' => Utils::genderOrderId($model->id, 'upgrade')
            ]);
            if (!$result) {
                throw new Exception('订单创建失败:订单号创建失败');
            }
            return $model;
        } else {
            throw new Exception('订单创建失败:' . $model->getError());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uid', 'status', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'required'],
            [['amount'], 'number'],
            [['msg'], 'string'],
            [['trade_no', 'remark'], 'string', 'max' => 255],
            [['alipay_trade_no'], 'string', 'max' => 64],
            [['wechat_trade_no'], 'string', 'max' => 32],
            [['pay_date'], 'string', 'max' => 20],
            [['type'], 'string', 'max' => 10],
            [['trade_no'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => '用户ID',
            'trade_no' => '订单号',
            'type' => '支付类型',
            'amount' => '金额',
            'alipay_trade_no' => '支付宝订单号',
            'wechat_trade_no' => '微信订单号',
            'pay_date' => '支付完成时间',
            'msg' => '支付失败信息',
            'status' => '订单状态',
            'remark' => '订单备注',
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
}
