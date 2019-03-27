<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/24 17:46
 */

namespace api\models;


use common\models\SmsVerifycode;
use common\models\User;
use yii\base\Model;

class RegisterForm extends Model
{
    public $mobile;
    public $password;
    public $smsCode;
    public $referrer;
    public $name;
    public $verifyCode;
    public $superior;
    public $wechat_openid;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'smsCode', 'name'], 'trim'],
            [['mobile', 'smsCode', 'password'], 'required'],
            [['mobile'], 'match', 'pattern' => '/^1[0-9]{10}$/', 'message' => '手机号必须为1开头的11位纯数字'],
            ['mobile', 'unique', 'targetClass' => '\common\models\User', 'message' => '注册失败，手机号已被注册！'],
            ['referrer', 'checkReferrer'],
            [['password'], 'min' => 6, 'tooShort' => '密码太过简单，长度不能小于6位数', 'message' => '密码太过简单,请输入6-16数字加字母格式密码'],
            ['smsCode', 'validateSmsCode'],
        ];
    }

    public function checkReferrer($attribute)
    {
        $referrerUser = User::findByMobile($this->referrer);
        if (empty($referrerUser)) {
            $this->addError('referrer', '邀请人不存在！');
        } else {
            $this->superior = $referrerUser->uid . '_' . $referrerUser->superior;
        }

    }

    /**
     * 验证短信验证码
     * @param string $attribute
     */
    public function validateSmsCode($attribute)
    {
        if (!$this->hasErrors()) {
            $smsVerifycode = new SmsVerifycode();
            if (strlen($this->smsCode) != 4) {
                $this->addError($attribute, strlen($this->smsCode));
            }
            $result = $smsVerifycode->check($this->mobile, $this->smsCode);
            if ($result !== true) {
                $this->addError($attribute, $result);
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'mobile' => '手机号',
            'password' => '密码',
            'smsCode' => '短信验证码',
            'referrer' => '邀请人手机号',
            'verifyCode' => '图片验证码',
        ];
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function register()
    {
        $this->beforeValidate();
        if ($this->validate()) {
            $user = new User();
            $user->mobile = $this->mobile;
            $user->realname = $this->name;
            $user->referrer = $this->referrer;
            $user->superior = $this->superior ?: 0;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->scenario = 'first';
            return $user->save();
        }
        return false;
    }
}
