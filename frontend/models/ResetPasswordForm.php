<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/25 21:26
 */

namespace frontend\models;

use common\models\SmsVerifycode;
use common\models\User;
use yii\base\Model;

class ResetPasswordForm extends Model
{
    public $password;
    public $rePassword;
    public $mobile;
    public $uid;
    public $payPwd;
    public $rePayPwd;
    public $smsCode;

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['pay_pwd'] = ['mobile', 'payPwd', 'rePayPwd', 'smsCode'];
        $scenarios['password'] = ['mobile', 'password', 'rePassword'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'rePassword'], 'required', 'on' => 'password'],
            [['mobile', "password"], 'required', 'on' => 'pay_pwd'],
            [['password'], 'min' => 6, 'tooShort' => '密码太过简单，长度不能小于6位数', 'message' => '密码太过简单,请输入6-16数字加字母格式密码'],
            [['payPwd'], 'number', 'message' => '支付密码格式错误,请输入纯数字格式密码'],
            [['password'], 'compare', 'compareAttribute' => 'rePassword', 'message' => '两次密码不一致'],
//            [['payPwd'], 'compare', 'compareAttribute' => 'rePayPwd', 'message' => '两次支付密码不一致'],
            [['smsCode'], 'validateSmsCode']
        ];
    }


    /**
     * 验证短信验证码
     * @param string $attribute
     */
    public function validateSmsCode($attribute)
    {
        if (!$this->hasErrors()) {
            $smsVerifycode = new SmsVerifycode();
            $result = $smsVerifycode->check($this->mobile, $this->smsCode);
            if ($result !== true) {
                $this->addError($attribute, $result);
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'password' => '密码',
            'rePassword' => '确认密码',
            'mobile' => '手机号',
            'smsCode' => '验证码',
            'payPwd' => '支付密码',
            'rePayPwd' => '确认支付密码',
        ];
    }

    /**
     * 修改登陆密码
     * @return null|static
     * @throws \yii\base\Exception
     */
    public function resetPassword()
    {
        if ($this->validate()) {
            if (!empty($this->mobile)) {
                $user = User::findByMobile($this->mobile);
            } else {
                $user = User::findNameByid($this->uid);
            }
            $user->setPassword($this->password);
            $user->save();
            return $user;
        }
        return null;
    }

    /**
     * 修改支付密码
     * @return bool|mixed
     */
    public function resetPayPwd()
    {
        if ($this->validate()) {
            $user = User::findByMobile($this->mobile);
            $user->pay_password = md5($this->payPwd);
            $user->save();
            return $user;
        }
        return null;
    }
}
