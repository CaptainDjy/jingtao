<?php

namespace common\models;


use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%commission_record}}".
 *
 * @property int $id
 * @property int $from_uid 分享UID
 * @property int $to_uid 返佣到UID
 * @property string $order_id 分佣订单ID
 * @property int $type 类型1VIP充值2淘宝3天猫4京东4拼多多
 * @property int $status 状态1未确定,2结算中3已到账
 * @property int $confirm_at 确定时间
 * @property int $arrival_at 到账时间
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class CommissionRecord extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%commission_record}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['from_uid', 'to_uid', 'type', 'status', 'confirm_at', 'arrival_at', 'created_at', 'updated_at'], 'integer'],
            [['to_uid', 'order_id', 'type', 'status'], 'required'],
            [['order_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '序号',
            'from_uid' => '分享者',
            'to_uid' => '得分佣者',
            'order_id' => '订单ID',
            'type' => '类型',
            'status' => '状态',
            'confirm_at' => '确认时间',
            'arrival_at' => '到账时间',
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
}
