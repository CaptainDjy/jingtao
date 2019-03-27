<?php

namespace common\models;

use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserLog
 * @package common\models
 * @property int $id [int(11) unsigned]
 * @property int $uid [int(11) unsigned]  用户UID
 * @property string $order_num [varchar(50)]  订单号
 * @property bool $type [tinyint(1)]  日志类型
 * @property string $op [varchar(255)]  操作标识
 * @property string $credit [decimal(12,2)]  零钱
 * @property int $credit1 [int(11)]  C贝
 * @property string $credit2 [decimal(12,2)]  推广积分
 * @property string $credit3 [decimal(12,2)]  消费积分
 * @property string $credit4 [decimal(12,2) unsigned]  剩余余额
 * @property string $integral1 [decimal(12,2)]
 * @property string $integral [decimal(12,2)]
 * @property string $msg [varchar(2000)]  信息
 * @property string $ip [varchar(255)]  IP
 * @property int $pay_uid [int(11) unsigned]  充值人uid
 * @property int $created_at [int(11) unsigned]  创建时间
 * @property int $updated_at [int(11) unsigned]  更新时间
 * @property string $credit4_change [decimal(10,2)]  余额变动
 * @property string $frozen_change [decimal(10,2)]  冻结变动
 * @property string $frozen [decimal(10,2) unsigned]  冻结剩余
 */
class UserLog extends ActiveRecord
{
    const CREDIT_4 = 4;
    const LAND = 2;
    const PROP = 3;

    /**
     * 提现资产变化记录
     * @param $user User
     * @param $counters array
     * @throws Exception
     */
    public static function addWithdrawLog($user, $counters)
    {
        $model = new self();
        $model->loadDefaultValues();
        $model->uid = $user->uid;
        $model->type = self::CREDIT_4;
        $model->op = 'withdraw';
        $model->credit4 = $user->credit4;
        $model->credit4_change = $counters['credit4'];
        $model->frozen = $user->frozen;
        $model->frozen_change = $counters['frozen'];
        $model->msg = '提现操作';
        if (!$model->save()) {
            if (YII_ENV == 'dev') {
                throw new Exception('用户资产变化记录保存失败:' . current($model->getFirstErrors()));
            }
            \Yii::error('用户资产变化记录保存失败: ' . current($model->getFirstErrors()));
        }
    }
    //
    // /**
    //  * 用户资产变化记录
    //  * @param $uid
    //  * @param $op
    //  * @param $data
    //  * @param $msg
    //  * @return bool
    //  * @throws Exception
    //  */
    // public static function addCreditLog($uid, $op, $data, $msg)
    // {
    //     $model = new self();
    //     $model->uid = $uid;
    //     $model->type = self::CREDIT_4;
    //     $model->op = $op;
    //     $model->credit2 = !empty($data['credit2']) ? $data['credit2'] : 0;
    //     $model->credit6 = !empty($data['credit6']) ? $data['credit6'] : 0;
    //     $model->credit7 = !empty($data['credit7']) ? $data['credit7'] : 0;
    //     $model->pay_uid = !empty($data['pay_uid']) ? $data['pay_uid'] : '';
    //     $model->msg = $msg;
    //     if (!$model->save()) {
    //         if (YII_ENV == 'dev') {
    //             throw new Exception('用户资产变化记录保存失败:' . current($model->getFirstErrors()));
    //         }
    //         \Yii::error('用户资产变化记录保存失败: ' . current($model->getFirstErrors()));
    //     } else {
    //         return $model->save();
    //     }
    // }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        $request = \Yii::$app->request;
        if ($request->isConsoleRequest) {
            $this->ip = 'localhost';
        } else {
            $this->ip = \Yii::$app->request->getUserIP();
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'type', 'ip'], 'required'],
            [['uid', 'type', 'pay_uid', 'created_at', 'updated_at'], 'integer'],
            [['credit4_change', 'frozen_change', 'frozen', 'credit', 'credit1', 'credit2', 'credit3', 'credit4'], 'number'],
            [['op', 'ip', 'order_num'], 'string', 'max' => 255],
            [['msg'], 'string', 'max' => 2000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => '用户编号',
            'type' => '日志类型',
            'op' => '操作标识',
            'credit' => 'C币变化',
            'credit1' => 'C贝',
            'credit4' => '余额',
            'credit4_change' => '余额变动',
            'frozen' => '冻结',
            'frozen_change' => '冻结变动',
            'msg' => '信息',
            'ip' => 'IP',
            'pay_uid' => '充值人uid',
            'created_at' => '创建时间',
        ];
    }

    /**
     * @inheritdoc
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
     * 关联用户信息
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['uid' => 'uid']);
    }

    /**
     * 日志类型
     * @param $type
     * @return string
     */
    public static function logType($type)
    {
        switch ($type) {
            case 1:
                return '资金变动';
            default:
                return '未知类型';
        }
    }

    /**
     * 根据类型查找数据
     * @param $pay_uid
     * @param $page
     * @param $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findByType($pay_uid, $page, $limit = 10)
    {
        return $list = self::find()->select(['*', 'sum(credit) as count_price'])->where("pay_uid = $pay_uid ")->groupBy('id')->offset($page * $limit)->limit($limit)->asArray()->orderBy('created_at desc')->all();

    }

    public function findByTypes($pay_uid, $page, $limit = 10)
    {
        return $list = self::find()->select(['order_num', 'credit7', 'created_at', 'updated_at'])->where("uid = $pay_uid ")->groupBy('id')->offset($page * $limit)->limit($limit)->asArray()->orderBy('created_at desc')->all();

    }
}

