<?php

namespace api\models;

use common\components\Distribution;
use common\helpers\Utils;
use common\models\Config;
use common\models\SmsVerifycode;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\captcha\CaptchaValidator;
use yii\db\Exception;

/**
 * Class LoginForm
 * @package api\models
 * @property User $user
 */
class LoginForm extends Model {
    public $mobile;
    public $password;
    public $userid;
    public $password_hash;
    public $re_password;
    public $smsCode;
    public $verifyCode;
    public $invite_code = null;
    public $referrer;
    public $superior;
    public $rememberMe = true;
    public $type;
    public $wechat_unionid;

    private $_user = null;

    /**
     * @return array
     */
    public function scenarios() {
        $scenarios = parent::scenarios();
        // 扫码注册 微信补充手机号
        $scenarios['we_login'] = ['mobile', 'smsCode', 'password', 're_password'];
        // 未注册 微信补充手机号
        $scenarios['we_register'] = ['mobile', 'smsCode', 'invite_code', 'password', 're_password'];
        // 未注册 微信补充手机号 不校验推荐码
        $scenarios['we_register_uninvite'] = ['mobile', 'smsCode', 'password', 're_password'];
        //手机号密码登录
        $scenarios['login'] = ['mobile', 'password'];
        //手机号短信验证码登录
        $scenarios['sms_login'] = ['mobile', 'smsCode'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['mobile', 'smsCode', 'password', 're_password'], 'required', 'on' => ['we_login', 'we_register', 'we_register_uninvite']],
            [['mobile', 'password'], 'required', 'on' => 'login'],
            [['mobile', 'smsCode'], 'required', 'on' => 'sms_login'],
            ['invite_code', 'required', 'on' => 'we_register'],

            [['mobile', 'smsCode'], 'trim'],
            [['mobile'], 'match', 'pattern' => '/^1[3-9][0-9]{9}$/', 'message' => '请输入正确的手机号码!'],
//			['mobile', 'validateMobile', 'on' => ['we_register', 'we_login', 'we_register_uninvite']],
            ['invite_code', 'checkInviteCode', 'on' => 'we_register'],
            [['password'], 'string', 'min' => 6, 'tooShort' => '密码太过简单，长度不能小于6位数', 'message' => '密码太过简单，请输入6-16数字加字母格式密码', 'on' => ['we_register', 'we_login', 'we_register_uninvite']],
            [['password'], 'validatePassword', 'on' => 'login'],
//			[['re_password'], 'compare', 'compareAttribute' => 'password', 'message' => '两次密码不一致', 'on' => ['we_register', 'we_login', 'we_register_uninvite']],
//            ['verifyCode', 'validateVerifyCode', 'on' => ['we_login', 'sms_login',]],
//			['smsCode', 'validateSmsCode', 'on' => ['we_register', 'we_login', 'sms_login', 'we_register_uninvite']],
        ];
    }

    public function attributeLabels() {
        return [
            'mobile' => '手机号',
            'smsCode' => '短信验证码',
            'verifyCode' => '图片验证码',
            'password' => '密码',
            're_password' => '确认密码',
            'rememberMe' => '记住账号',
            'invite_code' => '推荐码',
        ];
    }

    /**
     * 校验推荐人
     * @param $attribute
     */
    public function checkInviteCode($attribute) {
        if (!$this->hasErrors() && !empty($this->invite_code)) {
            if ($this->scenario == 'we_register') {
                $referrer = User::findByInviteCode($this->$attribute);
                if (empty($referrer)) {
                    $this->addError($attribute, '推荐人不存在！');
                } else {
                    $this->superior = $referrer->uid . '_' . $referrer->superior;
                    $this->referrer = $referrer->mobile;
                }
            }
        }
    }

    /**
     * 验证图片验证码
     * @param string $attribute
     */
    public function validateVerifyCode($attribute) {
        if (!$this->hasErrors()) {
            //验证图片验证码
            $captcha_validate = new CaptchaValidator(['captchaAction' => '/amoy/auth/captcha']);
            $verifyRs = $captcha_validate->validate($this->$attribute);
            if (!$verifyRs) {
                $this->addError($attribute, '图片验证码有误!');
            }
        }
    }

    /**
     * 验证短信验证码
     * @param string $attribute
     */
    public function validateSmsCode($attribute) {
        if (!$this->hasErrors()) {
            $smsVerifycode = new SmsVerifycode();
            if (strlen($this->$attribute) != 4) {
                $this->addError($attribute, strlen($this->$attribute));
            }
            $result = $smsVerifycode->check($this->mobile, $this->$attribute);
            if ($result !== true) {
                $this->addError($attribute, $result);
            }
        }
    }

    /**
     * 验证手机号是否注册
     * @param string $attribute
     */
    public function validateMobile($attribute) {
        if (!$this->hasErrors()) {
            $user = User::findByMobile($this->mobile);
            if ($user) {
                $this->addError($attribute, '该手机号已注册,请前往登录');
            }
        }
    }

    /**
     * 验证密码
     * @param string $attribute
     */
    public function validatePassword($attribute) {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '用户名或密码错误！');
            }
        }
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function login() {
//        $this->user->generateAccessToken();
        //        return Yii::$app->user->login($this->getUser(), Yii::$app->user->authTimeout);
        if ($this->validate()) {
            $this->user->generateAccessToken();
            return Yii::$app->user->login($this->getUser(), Yii::$app->user->authTimeout);
        }

        return false;
    }

    /**
     * 微信扫码注册-补充手机号
     * @param $userInfo
     * @return User
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public function weMobile($userInfo) {
        $this->scenario = 'we_login';
        if (!$this->validate()) {
            throw new Exception(current($this->getFirstErrors()));
        }

        $user = User::findByUnionid($userInfo['unionid']);
        if (empty($user)) {
            throw new Exception('用户不存在!');
        }
        $user->mobile = $this->mobile;
        $user->generateAuthKey();
        $user->setPassword($this->password);
        $user->nickname = $userInfo['nickname'];
        $user->avatar = $userInfo['headimgurl'];
        $user->generateAccessToken();
        $user->alimmPid();
        $user->pddPid();
        if (!$user->save()) {
            throw new Exception(current($user->getFirstErrors()));
        }
        $user->updateAttributes(['jd_pid' => $user->uid]);
        //TODO
        $relation = rtrim($user->superior, '_0');
        $rela = explode('_', $relation);
        if (!empty($rela[0])) {
            $dis = new Distribution(['uid' => $rela[0]]);
            $dis->upgrade();
        }
        if (!Yii::$app->user->login($user, Yii::$app->user->authTimeout)) {
            throw new Exception('登录失败!');
        }
        return $user;
    }

    /**
     * 微信APP注册-登录
     * @param $userinfo
     * @return User
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public function weRegister($userinfo) {
        $isInvite = Config::getConfig('INVITE_IS_OPEN');
        if ($isInvite) {
            $this->scenario = 'we_register';
        } else {
            $this->scenario = 'we_register_uninvite';
        }
        if (!$this->validate()) {
            throw new Exception(current($this->getFirstErrors()));
        }
        $user = new User();
        $user->loadDefaultValues();
        if ($isInvite) {
            //开启邀请码并校验通过
            $user->referrer = $this->referrer;
            $user->superior = $this->superior;
        }
        $user->mobile = $this->mobile;
        $user->generateAuthKey();
        $user->setPassword($this->password);
        $user->invite_code = Utils::genderRandomStr();
        $user->alimmPid();
        $user->getId();
        $user->lv = Config::getConfig('USER_DEFAULT_LV');
        //$user->pddPid();
        $user->wechat_openid = $userinfo['openid'];
        $user->wechat_unionid = $userinfo['unionid'];
        $user->nickname = $userinfo['nickname'];
        $user->avatar = $userinfo['headimgurl'];
        $user->gender = $userinfo['sex'];
        $user->generateAccessToken();
        if (!$user->save()) {
            throw new Exception(current($user->getFirstErrors()));
        }
        $user->updateAttributes(['jd_pid' => $user->uid]);
        //TODO
        $relation = rtrim($user->superior, '_0');
        $rela = explode('_', $relation);
        if (!empty($rela[0])) {
            $dis = new Distribution(['uid' => $rela[0]]);
            $dis->upgrade();
        }
        if (!Yii::$app->user->login($user, Yii::$app->user->authTimeout)) {
            throw new Exception('登录失败!');
        }
        return $user;
    }

    /**
     * 手机号注册
     * @param $mobile
     * @param $password
     * @param $invite_code
     * @return User
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public function Register($mobile, $password, $invite_code) {
        //  创建用户对象
        $user = new User();
        $user->loadDefaultValues();
        //  校验邀请码
        if (!empty($invite_code)) {
            $referrer = User::findByInviteCode($invite_code);
            if (empty($referrer)) {
                throw new Exception('推荐人不存在！');
            }
            $user->superior = $referrer->uid . '_' . $referrer->superior;
            $user->referrer = $referrer->mobile;
        }
        $user->mobile = $mobile;
        $user->generateAuthKey();
        $user->setPassword($password);
        $user->invite_code = Utils::genderRandomStr();
        $user->alimmPid();
        $user->lv = Config::getConfig('USER_DEFAULT_LV');
        $user->getId();
        /*$user->pddPid();*/
        $user->nickname = '';
        $user->avatar = '';
        $user->gender = 0;
        $user->generateAccessToken();
        if (!$user->save()) {
            throw new Exception(current($user->getFirstErrors()));
        }
        $user->updateAttributes(['jd_pid' => $user->uid]);
        $relation = rtrim($user->superior, '_0');
        $rela = explode('_', $relation);
        if (!empty($rela[0])) {
            $dis = new Distribution(['uid' => $rela[0]]);
            $dis->upgrade();
        }
        if (!Yii::$app->user->login($user, Yii::$app->user->authTimeout)) {
            throw new Exception('登录失败!');
        }
        return $user;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser() {
        if ($this->_user === null) {
            $this->_user = User::findByMobile($this->mobile);
        }
        return $this->_user;
    }

}
