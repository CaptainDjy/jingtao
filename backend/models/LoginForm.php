<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/26 13:50
 */

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\captcha\CaptchaValidator;

/**
 * 后台用户登录表单
 * @property null|\backend\models\SystemUser $user
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $verifyCode;
    public $rememberMe = true;
    private $_user = false;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            ['verifyCode', 'validateCaptcha'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'rememberMe' => '记住我',
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '用户名或密码错误！');
            }
        }
    }

    public function validateCaptcha($attribute)
    {
        if (!$this->hasErrors()) {
            $captcha_validate = new CaptchaValidator();
            $verifyRs = $captcha_validate->validate($this->verifyCode);
            if (!$verifyRs) {
                $this->addError($attribute, '验证码错误！');
            }
        }
    }

    /**
     * @return bool
     */
    public function login()
    {
        if ($this->validate()) {
            $result = Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
            $log = new SystemUserLog();
            $status = $result === true ? 1 : 0;
            $log->write($this->username, $status);

            return $result;
        } else {
            return false;
        }
    }

    /**
     * @return null|SystemUser
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = SystemUser::findByUsername($this->username);
        }

        return $this->_user;
    }

}
