<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/28 20:58
 */

namespace frontend\controllers;


use common\models\SmsVerifycode;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 *
 * @property bool $tradeStatus
 */
class ControllerBase extends Controller
{
    public $layout = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $msg
     * @param array $redirect
     * @param string $type
     * @return \yii\web\Response
     */
    public function message($msg, $redirect = [], $type = '')
    {
        \Yii::$app->getSession()->setFlash($type, $msg);
        return $this->redirect($redirect);
    }

    /**
     * @param int $code
     * @param array $data
     * @param string $msg
     * @return array
     */
    public function responseJson($code = 0, $data = [], $msg = '')
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $data = ['code' => $code, 'data' => $data, 'msg' => $msg];
        return $data;
    }

    /**
     * 支付密码验证
     * @param $pwd -原密码
     * @param $payPwd -输入密码
     * @return array
     * @throws Exception
     */
    public function validatePayPwd($pwd, $payPwd)
    {
        if (empty($pwd)) {
            throw new Exception('请设置支付密码');
        }
        if (empty($payPwd)) {
            throw new Exception('请填写支付密码');
        }
        if ($pwd == md5($payPwd)) {
            return $this->responseJson(0, '', '验证成功');
        } else {
            throw new Exception('支付密码错误');
        }
    }

    /**
     * 生成订单号
     * @param $uid
     * @return string
     */
    public function createOrderNumber($uid)
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        return $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99)) . $uid;
    }

    /**
     * 验证短信验证码
     * @param $mobile
     * @param $smsCode
     * @return array|bool|string
     */
    public function ValidateSmsCode($mobile, $smsCode)
    {
        if (empty($mobile) || empty($smsCode)) {
            return $this->responseJson(0, '', '请填写正确的参数');
        }
        $smsVerifycode = new SmsVerifycode();
        $result = $smsVerifycode->check($mobile, $smsCode);
        return $result;
    }

    /**
     * 数据进模型
     * @param $model
     * @param array $data
     * @return bool
     */
    public function arrayLoad($model, array $data)
    {
        if (!empty($data) && is_array($data) && is_object($model)) {
            foreach ($data as $k => $v) {
                $model->$k = $v;
            }
            return $model;
        } else {
            return false;
        }
    }

    /**
     * 错误提示页面
     * @param string $msg 错误消息
     * @return string
     */
    public function error($msg)
    {
        return $this->render('/site/error', ['msg' => $msg]);
    }

}
