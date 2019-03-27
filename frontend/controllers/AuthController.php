<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/23 9:30
 */

namespace frontend\controllers;

use common\models\SmsVerifycode;
use common\models\User;
use frontend\models\LoginForm;
use frontend\models\RegisterForm;
use frontend\models\ResetPasswordForm;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;

class AuthController extends ControllerBase
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * 登录
     * @return array|string|\yii\web\Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        if (\Yii::$app->request->isAjax) {
            $model = new LoginForm();
            if ($model->load(\Yii::$app->request->post(), '') && $model->login()) {
                $mobile = Yii::$app->request->post('mobile');
                $userInfo = User::findByMobile($mobile);
                $data = [
                    'id' => 'HC' . substr($userInfo['mobile'], -6),
                    'report' => $userInfo['report'],
                    'partner' => $userInfo['partner'],
                    'name' => $userInfo['realname'],
                ];
                return $this->responseJson('0', $data, '登录成功');
            } else {
                return $this->responseJson('1', '', '登录失败 ' . current($model->getFirstErrors()));
            }
        }

        return $this->render('login');
    }

    /**
     * 注册
     * @return array
     */
    public function actionRegister()
    {
        $model = new RegisterForm();
        if (Yii::$app->request->isAjax) {
            $query = Yii::$app->request;
            $model->wechat_openid = ($query->post('openid') ?: "");
            if ($model->load(Yii::$app->request->post(), '') && $model->register()) {
                return $this->responseJson(0, Url::toRoute(['auth/login']), '注册成功，请登录！');
            } else {
                return $this->responseJson(1, '', current($model->getFirstErrors()));
            }
        }
        return $this->responseJson(0, '', '非法请求！');
    }

    /**
     * 重置密码
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionReset()
    {
        $model = new ResetPasswordForm();
        $model->scenario = 'password';
        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post(), '') && $model->resetPassword()) {
                return $this->responseJson(0, '', '密码重置成功，请登录！');
            } else {
                return $this->responseJson(1, '', current($model->getFirstErrors()));
            }
        }
        return $this->responseJson(0, '', '非法请求！');
    }

    /**
     * 短信验证码
     * @return array
     */
    public function actionSmsVerifycode()
    {
        $mobile = Yii::$app->request->post('mobile');
        $scenario = empty(Yii::$app->request->post('scenario')) ? '' : Yii::$app->request->post('scenario');
        $smsVerifycode = new SmsVerifycode();
        $result = $smsVerifycode->send($mobile, $scenario);
        if ($result === true) {
            return $this->responseJson(0, '', '短信已发送，请注意查收！');
        } else {
            return $this->responseJson(1, '', $result);
        }
    }




}
