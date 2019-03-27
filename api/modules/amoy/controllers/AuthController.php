<?php

namespace api\modules\amoy\controllers;

use api\huyi\Sms;
use api\models\LoginForm;
use api\models\ResetPasswordForm;
use common\helpers\Utils;
use yii\web\Session;
use common\models\Announcement;
use common\models\Config;
use common\models\SmsVerifycode;
use common\models\User;
use api\models\AdvertSpace;
use Yii;
use yii\db\Exception;
use api\alisms\SendSms;
use api\models\user as users;
class AuthController extends ControllerBase
{

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'common\helpers\CaptchaAction',
                'height' => 68,
                'width' => 200,
                'minLength' => 4,
                'maxLength' => 4,
                'offset' => 18,
                'fontSize' => 38,
                'fontFile' => '@common/font/1.ttf'
            ],
        ];
    }

    /**
     * 微信授权登录、app注册、绑定手机号
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\Exception
     */
    public function actionWeLogin()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->responseJson(1, '', '请求失败!');
        }
        $data = $request->post();
        if (!isset($data['userinfo']) || !isset($data['userinfo']['openid']) || !isset($data['userinfo']['unionid'])) {
            return $this->responseJson(1, '', '参数有误!');
        }
        if (isset($data['type'])) {
            $loginForm = new LoginForm();
            $loginForm->setAttributes($data, false);
            $userinfo = $this->wechat->userInfo($data['userinfo']);
            if ($loginForm->type == 'complete') {
                //完善手机号
                try {
                    $user = $loginForm->weMobile($userinfo);
                } catch (Exception $e) {
                    return $this->responseJson(1, '', $e->getMessage());
                }
            } elseif ($loginForm->type == 'new') {
                //新注册
                try {
                    $user = $loginForm->weRegister($userinfo);
                } catch (Exception $e) {
                    return $this->responseJson(1, '', $e->getMessage());
                }
            } else {
                return $this->responseJson(1, '', '请求有误');
            }
        } else {
            $user = User::findByUnionid($data['userinfo']['unionid']);
            if (empty($user)) {
                //账户不存在->补充手机号->注册
                $data['isInvite'] = Config::getConfig('INVITE_IS_OPEN');
                $data['type'] = 'new';
                return $this->responseJson(0, $data, '请完善手机号码!');
            } elseif (empty($user->mobile)) {
                //微信登录->未绑定手机号->补充手机号
                $data['type'] = 'complete';
                $data['isInvite'] = 0;
                return $this->responseJson(0, $data, '请完善手机号码!');
            }
        }

        // 登录-生成TOKEN
        $this->uid = $user->uid;
        $sign = md5($this->uid . TIMESTAMP . $user->access_token);
        $token = base64_encode(json_encode(['uid' => $this->uid, 'timestamp' => TIMESTAMP, 'sign' => $sign]));
        $data = [
            'token' => $token,
            'nickname' => $user->nickname,
            'uid' => $user->uid,
            'mobile' => $user->mobile,
        ];
        return $this->responseJson(0, $data, '请求成功!');
    }

//微信公众号登陆

    public function actionWechat(){
        $wechat = new WechatController();
        //获取openid和accessToken
//        return $this->responseJson(200,$wechat->getOpenid(),'ksnjk');
        //获取用户信息
//        return $this->responseJson(200,$wechat->getUserInfo(),'ksnjk');
        $openid = json_decode(json_encode($wechat->getUserInfo()),true);
//        $url2=$_SERVER['REQUEST_URI'];
        $url=$_GET['state'];

        $this->actionWechategister($openid,$url);
    }
//绑定手机号
    public function actionBangding(){
//        $openid=
    }

    public function actionWechategister($openid,$url)
    {
//        print_r($openid['openid']);
//        exit;
        $user = User::find()->where(['wechat_openid'=>$openid['openid']])->asArray()->one();
        if (empty($user)){
                    $session = \Yii::$app->session;
                    $session->set('openid' , $openid['openid']);
//                    $openid = $session->get('openid');
//                    $session->remove('openid');
//                    print_r($openid);
//                    exit;
            header('Location:http://www.jimiyh.top/jimiyh/jmyh/public/home/dist/index.html#/register');
        }else{
            if(!empty($openid['openid'])){
                $uid=User::find()->where(['wechat_openid'=>$openid['openid']])->asArray()->one();
//                print_r($uid['mobile']);
//                exit;
                $data = [
                    'wechat_openid'=>$openid['openid'],
                    'mobile'=>$uid['mobile'],
                ];

//                $request = \Yii::$app->request;
//                if ($request->isPost) {
                    $model = new LoginForm();
                    $model->scenario = 'we_login';
                    $model->load($data,'');
//                }

                if (!empty($uid)) {
                    // 生成TOKEN
                    $this->uid = $model->user->uid;
                    $sign = md5($this->uid . TIMESTAMP . $model->user->access_token);
                    $token = base64_encode(json_encode(['uid' => $this->uid, 'timestamp' => TIMESTAMP, 'sign' => $sign]));
                    $data = [
                        'token' => $token,
                        'nickname' => $model->user->nickname,
                        'mobile' => $model->user->mobile,
                        'uid' => $model->user->uid,
                        'alimm_pid'=>$model->user->alimm_pid,
                        'jd_pid'=>$model->user->jd_pid,
                        'pdd_pid'=>$model->user->pdd_pid,
                    ];


                    if ($url==1){
                        header('Location:http://www.jimiyh.top/jimiyh/jmyh/public/home/dist/index.html#/my');//个人中心
                    }elseif($url==2){
                        header('Location:http://www.jimiyh.top/jimiyh/jmyh/public/home/dist/index.html#/myinvate');//我的邀请
                    }elseif ($url==3){
                        header('Location:http://www.jimiyh.top/jimiyh/jmyh/public/home/dist/index.html#/order');//我的订单
                    }elseif ($url==11){
                        header('Location:http://www.jimiyh.top/jimiyh/jmyh/public/home/dist/index.html#/chanelList?id=11');//淘宝
                    }elseif ($url==21){
                        header('Location:http://www.jimiyh.top/jimiyh/jmyh/public/home/dist/index.html#/chanelList?id=21');//京东
                    }elseif ($url==31){
                        header('Location:http://www.jimiyh.top/jimiyh/jmyh/public/home/dist/index.html#/chanelList?id=31');//拼多多
                    }else{
                        header('Location:http://www.jimiyh.top/jimiyh/jmyh/public/home/dist/index.html#/chanelList?id=11');//淘宝
                    };

//                    return $this->responseJson(200, $data, '登录成功');

                } else {
                    return $this->responseJson(101,['a'=>'1'],'登录失败');//current($model->getFirstErrors())
                }
            }
            return $this->responseJson(102, '', '未注册或手机号未绑定微信');
        }
    }


/*
*
* 注册
*
*/

    public function actionAdd() {
        $model = new user();
        $request = \Yii::$app->request;
        $res=user::find()->where(['mobile'=>$request->post('mobile')])->asArray()->all();//查询输入的手机号是否存在

        if (!empty($res)){
            return $this->responseJson(101, '101', '此手机号已注册');
        }

        if (empty($res)){
            $model->mobile = $request->post('mobile');
            $password=$request->post('password');
            $model->password_hash = Yii::$app->security->generatePasswordHash($password, 6);

            if (Yii::$app->cache->get($request->post('mobile')) == $request->post('yzm')) {
//            if ($_SESSION['mobile'] == $request->post('mobile') && $_SESSION['mobile_code'] == $request->post('yzm')) {
                $model->userid = rand(10000, 99999); //自动生成用户ID
                $model->generalize = substr(md5(time()), 0, 7); //用户推广码
                $model->created_at=time();
                $model->avatar='uploads/avatar/avatar.png';
                $model->invite_code = $request->post('invite'); //邀请码
                $alimmpid=AdvertSpace::find()->where(['uid'=>null,'type'=>1])->asArray()->one();//阿里妈妈推广位未占用
//                $jdpid=AdvertSpace::find()->where(['uid'=>null,'type'=>2])->asArray()->one();//京东推广位未占用
                $ddjbpid=AdvertSpace::find()->where(['uid'=>null,'type'=>3])->asArray()->one();//多多进宝推广位未占用

                if (empty($alimmpid)){
                    return $this->responseJson(105, '105', '淘宝广告位不足,请联系管理员');
                }
//                if (empty($jdpid)){
//                    return $this->responseJson(107, '107', '京东广告位不足,请联系管理员');
//                }
                if (empty($ddjbpid)){
                    return $this->responseJson(106, '106', '拼多多广告位不足,请联系管理员');
                }
//                $session = \Yii::$app->session;
//                $openid = $session->get('openid');
//                $model->wechat_openid=$openid;
                $model->alimm_pid=$alimmpid['pid'];//淘宝联盟推广位
//                $model->jd_pid=$jdpid['pid'];//京东联盟推广位
                $model->pdd_pid=$ddjbpid['pid'];//拼多多推广位
                $superior=user::find()->select(['uid','superior','generalize','recommend'])->where(['generalize'=>$request->post('invite')])->asArray()->one();
                $model->superior=$superior['uid'].'_';//上一级   uid_
                $invit = $request->post('invite');//邀请码
                //注册如果有微信ID说明是微信注册
//                if (!empty($openid['openid'])) {
//                    $model->wechat_openid = $openid['openid'];//微信ID
//                }
                //邀请码为空注册
                if ($request->post('invite')==null){
                    $model->save();
                    $uid=User::find()->where(['alimm_pid'=>$alimmpid])->asArray()->one();
                    $test=AdvertSpace::find()->where(['pid'=>$alimmpid['pid']])->one();//阿里妈妈pid被占用
//                    $jdlm=AdvertSpace::find()->where(['pid'=>$jdpid['pid']])->one();//京东pid被占用
                    $ddjb=AdvertSpace::find()->where(['pid'=>$ddjbpid['pid']])->one();//多多进宝pid被占用
                    $test->uid=$uid['uid'];
//                    $jdlm->uid=$uid['uid'];
                    $ddjb->uid=$uid['uid'];
                    $test->save();//taobao
//                    $jdlm->save();//jingdong
                    $ddjb->save();//pinduoduo

                    return $this->responseJson(200, '200', '注册成功');
                }

                //邀请码存在注册
                $invite = user::find()->where(['=', 'generalize', $invit])->asArray()->all(); //查询推广码
                if (!empty($invite)) {
                    $model->save();
                    $uid=User::find()->where(['alimm_pid'=>$alimmpid])->asArray()->one();
                    $test=AdvertSpace::find()->where(['pid'=>$alimmpid['pid']])->one();
//                    $jdlm=AdvertSpace::find()->where(['pid'=>$jdpid['pid']])->one();
                    $ddjb=AdvertSpace::find()->where(['pid'=>$ddjbpid['pid']])->one();//多多进宝pid被占用
                    $test->uid=$uid['uid'];
//                    $jdlm->uid=$uid['uid'];
                    $ddjb->uid=$uid['uid'];
                    $test->save();
//                    $jdlm->save();
                    $ddjb->save();//修改多多进宝uid
                    $superior=user::find()->where(['generalize'=>$request->post('invite')])->one();
                    $superior->recommend=$superior['recommend']+1;
                    $superior->save();
                    return $this->responseJson(200, '200', '注册成功');
                }else{
                    return $this->responseJson(103, '103', '邀请码错误');
                }
            } else {
                return $this->responseJson(104, '104', '注册失败手机验证码错误');
            }
        }

    }




    //手机号注册源码
    public function actionRegister()
    {
        $request = Yii::$app->request;
        if (!$request->isPost)
        {
            return $this->responseJson(1,'请使用post','请求失败');
        }
        $mobile = $request->post('mobile');
        $smsCode = $request->post('smsCode');
        $password = $request->post('password');
        $invite_code = $request->post('invite_code');
        if (empty($mobile) || empty($smsCode) || empty($password))
        {
            return $this->responseJson(1,$request->post(),'请填写完整参数');
        }
        if (User::findByMobile($mobile))
        {
            return $this->responseJson(1,$mobile,'该手机号已注册,请前往登录');
        }
        //  检验验证码
//        $smscheck = new SmsVerifycode();
//        $result = $smscheck->check($mobile,$smsCode);
//        if ($result !== true)
//        {
//            return $this->responseJson(1,'',$result);
//        }
        //  注册
        if (Yii::$app->cache->get($request->post('mobile')) == $request->post('yzm')){
        $loginform = new LoginForm();
            $user = $loginform->Register($mobile,$password, $invite_code);
        try {
            $user = $loginform->Register($mobile,$password, $invite_code);
        } catch (\yii\base\Exception $e) {

            return $this->responseJson(1,'',$e->getMessage());
        }
            return $this->responseJson(0,$user,'注册成功');
        }
        else{
            return $this->responseJson(0,'','注册');
        }
    }

    /**
     * 手机号登录
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionLogin()
    {
        $request = \Yii::$app->request;
        if ($request->isPost) {
            //  验证是否存在邀请码
            /*$invite_code = $request->post('invite_code');
            if (isset($invite_code)){
                $sup = User::findOne(['invite_code' => $invite_code]);  //  上级用户
                if (!$sup){
                    return $this->responseJson(1,'','邀请码不存在');
                }
            }*/

            $type = $request->post('type', null);
            $model = new LoginForm();
            $model->scenario = 'login';
            if ($type == 'sms') {
                //短信登录
                $model->scenario = 'sms_login';
            }
            $model->load($request->post(), '');
            if ($model->login()) {
                // 生成TOKEN
                $this->uid = $model->user->uid;
                $sign = md5($this->uid . TIMESTAMP . $model->user->access_token);
                $token = base64_encode(json_encode(['uid' => $this->uid, 'timestamp' => TIMESTAMP, 'sign' => $sign]));
                $data = [
                    'token' => $token,
                    'nickname' => $model->user->nickname,
                    'mobile' => $model->user->mobile,
                    'uid' => $model->user->uid,
                    'lv' => $model->user->lv,
                    'alimm_pid'=>$model->user->alimm_pid,
                    'jd_pid'=>$model->user->jd_pid,
                    'pdd_pid'=>$model->user->pdd_pid,
                ];
                return $this->responseJson(200, $data, '登录成功');
            } else {
                return $this->responseJson(0,['a'=>'1','b'=>'2','c'=>'3'],current($model->getFirstErrors()));
            }
        }
        exit('请求失败');
    }

    /**
     * 忘记密码
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionReset() {
        $model = new users();
        $request = \Yii::$app->request;
        $password=$request->post('password');
        $res = users::find()->where(['mobile' => $request->post('mobile')])->one(); //查询数据库是否有此手机号
//        print_r($res);
//        exit;
        if ($res) {
            if ($request->post('yzm') == null) {
                return $this->responseJson(103, '', '验证码不能为空');
            }
            if (Yii::$app->cache->get($request->post('mobile')) == $request->post('yzm')) {
                $mm = Yii::$app->security->generatePasswordHash($password, 6);
                $res->password_hash=$mm;
                $res->save();
                return $this->responseJson(200, '', '修改成功');
            } else {
                return $this->responseJson(101, '', '修改失败');
            }

        } else {
            return $this->responseJson(102, '', '此手机号还没有注册');
        }
    }

    //忘记密码源码
    public function actionResets()
    {
        $mobile = \Yii::$app->request->post('mobile');
        $smsCode = \Yii::$app->request->post('smsCode');
        if (empty($mobile) || empty($smsCode)) {
            return $this->responseJson(0, '', '手机号和验证码不能为空');
        }

        //  重置密码
        $model = new ResetPasswordForm();
        $model->scenario = 'password';
        if ($this->arrayLoad($model, Yii::$app->request->post()) && $model->resetPassword()) {
            return $this->responseJson(0, '', '密码设置成功，请登录！');
        } else {
            return $this->responseJson(1, '', current($model->getFirstErrors()));
        }
    }

    /**
     * 修改支付密码
     * @return array
     */
    public function actionResetPayPwd()
    {
        $model = new ResetPasswordForm();
        $model->scenario = 'pay_pwd';
        if ($this->arrayLoad($model, Yii::$app->request->post()) && $model->resetPayPwd()) {
            return $this->responseJson(0, '', '支付密码更新成功');
        } else {
            return $this->responseJson(1, '', current($model->getFirstErrors()));
        }
    }

    /**
     * 验证短信验证码
     * @return array
     */
        public function actionValidateSmsCode()
    {
        $mobile = \Yii::$app->request->post('mobile');
        $smsCode = \Yii::$app->request->post('smsCode');
        if (empty($mobile) || empty($smsCode)) {
            return $this->responseJson(0, '', '请填写正确的参数');
        }
        $smsVerifycode = new SmsVerifycode();
        $result = $smsVerifycode->check($mobile, $smsCode);
        if ($result === true) {
            return $this->responseJson(0, $mobile, '短信验证成功');
        } else {
            return $this->responseJson(1, $mobile, '短信验证失败');
        }
    }

    public function actionHysms(){
        $request = \Yii::$app->request;
        $mobile=$username = $request->post('mobile');
       $send_code=mt_rand(10000, 99999);

        $sms=new Sms();
        $m=$sms->send_sms($mobile,$send_code);

        if ($m['SubmitResult']['code']==2) {
            Yii::$app->cache->set($mobile, $send_code, 600);
            //验证码发送成功
            return $this->responseJson(200, '', '发送成功');

        }else{
            return $this->responseJson(101, '', '发送失败');
        }
    }

    /**
     * 获取短信验证码
     * @return array
     */

    public function actionVerificationCode() {
        $request = \Yii::$app->request;
        $username = $request->post('mobile');
        if (empty($username)) {
            return $this->responseJson(0, $username, '手机号不能为空');
        }

        // die();
        // exit();
        // (new MobilePhoneNumber())->goCheck();
        //获取对象，如果上面没有引入命名空间，可以这样实例化：$sms = new \alisms\SendSms()
        $sms = new SendSms();
        //$mobile为手机号
        $mobile = $username;
        //模板参数，自定义了随机数，你可以在这里保存在缓存或者cookie等设置有效期以便逻辑发送后用户使用后的逻辑处理
        $code = mt_rand(100000, 999999);

        // $source = Yii::$app->cache->get('mobile');
        $templateParam = array("code" => $code);
        $m = $sms->send($mobile, $templateParam);
        //类中有说明，默认返回的数组格式，如果需要json，在自行修改类，或者在这里将$m转换后在输出
        if ($m['Code'] == 'OK') {

            // if (!$result) {
            // 	throw new Exception('缓存写入失败,系统内部错误', 500);
            // }
            $emp = User::find()->where(['mobile' => $mobile])->asArray()->all();
            Yii::$app->cache->set($mobile, $code, 600);
            //验证码发送成功
            return $this->responseJson(200, '', '发送成功');

        }if ($m['Code'] == 'isv.BUSINESS_LIMIT_CONTROL') {
            //短信验证码发送失败
//			return json_encode(['code' => 204, 'data' => $m], 320);
            return $this->responseJson(101, '', '发送失败');
        }

    }

    //获取短信验证码源码
    public function actionSmsVerifycode()
    {
        $mobile = \Yii::$app->request->post('mobile');
        $scenario = \Yii::$app->request->post('scenario', 'default');
        if (empty($mobile) || !preg_match('/^1[3-9][0-9]{9}$/', $mobile)) {
            return $this->responseJson(1, '', '输入的手机号错误');
        }
        //  调用发送短信API
        $smsVerifycode = new SmsVerifycode();
        $result = $smsVerifycode->send($mobile, $scenario);
        if ($result === true) {
            return $this->responseJson(0, '', '短信已发送，请注意查收！');
        } else {
            return $this->responseJson(1, '', $result);
        }
    }

    /**
     * 注册须知
     * @return array
     */
    public function actionRegisterAgreement()
    {
        $item = Announcement::find()->where("type = 5")->asArray()->one();
        if (!empty($item)) {
            return $this->responseJson(0, $item, '获取信息成功');
        } else {
            return $this->responseJson(1, '', '获取信息失败');
        }
    }

    /**
     * 退出
     */
    public function actionLoginOut()
    {
        Yii::$app->user->logout();
        return $this->responseJson(1,'','已退出');
    }


}
