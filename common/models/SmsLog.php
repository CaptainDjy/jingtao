<?php


namespace common\models;


use Yii;
use yii\db\ActiveRecord;

/**
 * @property integer $id [int(11) unsigned]
 * @property integer $uid [int(11) unsigned]  发送用户UID 没有为0
 * @property string $mobile [varchar(11)]  手机号
 * @property string $content [varchar(255)]  短信内容
 * @property string $type [varchar(50)]  类型
 * @property string $result [varchar(255)]  结果
 * @property string $ip [varchar(50)]  ip
 * @property integer $status [tinyint(1) unsigned]  0: 失败 1: 成功
 * @property integer $created_at [int(11) unsigned]  创建时间
 */
class SmsLog extends ActiveRecord
{
    public function rules()
    {
        return [
            [['mobile', 'content'], 'required'],
            [['mobile'], 'match', 'pattern' => '/^1[0-9]{10}$/', 'message' => '手机号必须为1开头的11位纯数字'],
            [['content'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'uid' => '会员编号',
            'mobile' => '手机号',
            'content' => '短信内容',
            'type' => '类型',
            'created_at' => '创建时间',
            'status' => '状态',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->uid = intval(Yii::$app->user->id);
        $this->ip = Yii::$app->request->userIP;
        $this->created_at = time();
        return true;
    }
}
