<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/25 12:50
 */

namespace common\models;

use common\helpers\Sms;
use common\helpers\Utils;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @property integer $id [int(11) unsigned]
 * @property integer $uid [int(11) unsigned]
 * @property string $mobile [varchar(11)]  手机号
 * @property string $verifycode [varchar(10)]  验证码
 * @property integer $status [tinyint(1) unsigned]  0：未验证 1：已验证
 * @property integer $created_at [int(11) unsigned]  创建时间
 * @property integer $updated_at [int(11) unsigned]  更新时间
 * @property string $ip [varchar(50)]
 */
class SmsVerifycode extends ActiveRecord
{
    /**
     * @param $mobile
     * @param string $scenario
     * @return bool|string
     */
    public function send($mobile, $scenario = 'register')
    {
        $this->uid = intval(Yii::$app->user->id);
        $this->mobile = $mobile;
        $this->verifycode = Utils::buildRandom(4, 'numeric');
        $this->ip = Utils::getIp();
        $this->status = 0;
        $this->scenario = $scenario;
        if (!$this->validate()) {
            return current($this->getFirstErrors());
        }
        $result = $this->save();
        if ($result !== true) {
            return '短信发送失败: 生成记录失败，请重试！';
        }
        //$sign = Config::getConfig('MESSAGE_API_USERNAME');
        //$content = "【{$sign}】您的验证码是" . $this->verifycode . ',5分钟内有效,若非本人操作请忽略此消息';
        return Sms::send($this->mobile, $this->verifycode);
    }

    /**
     * @param $mobile
     * @param string $verifycode
     * @return bool|string
     */
    public function check($mobile, $verifycode = '')
    {
        /*$result = self::find()->where(['mobile' => $mobile, 'status' => 0])->orderBy('id DESC')->limit(1)->one();
        if (empty($result)) {
            return '短信验证码错误，请重新输入或获取[1]！';
        }
        if (empty($result->ip) || $result->ip !== Utils::getIp()) {
            return '短信验证码错误，请重新输入或获取[2]！';
        }
        if (empty($result->verifycode) || $result->verifycode !== $verifycode) {
            return '短信验证码错误，请重新输入或获取！';
        }

        if (empty($result->created_at) || $result->created_at < time() - 300) {
            return '短信验证码过期，请重新获取！';
        }

        $result->status = 1;
        $result->save(false);*/
        $res = Sms::check($mobile,$verifycode);
        if ($res !== true){
            return $res;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'verifycode'], 'required'],
            [['mobile'], 'match', 'pattern' => '/^1[0-9]{10}$/', 'message' => '手机号必须为1开头的11位纯数字！'],
            ['mobile', 'unique', 'targetClass' => '\common\models\User', 'message' => '手机号已注册，请登录！', 'on' => 'register'],
            ['mobile', 'exist', 'targetClass' => '\common\models\User', 'message' => '手机号未注册，请先注册！', 'on' => 'reset'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['register'] = ['mobile', 'verifycode'];
        $scenarios['reset'] = ['mobile', 'verifycode'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => '会员编号',
            'mobile' => '手机号',
            'verifycode' => '验证码',
            'status' => '状态',
            'created_at' => '创建时间',
            'update_at' => '更新时间',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->uid = intval(Yii::$app->user->id);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }
}
