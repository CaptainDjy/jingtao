<?php

namespace api\controllers;

use common\models\User;
use common\models\UserLog;
use yii\base\Exception;
use yii\rest\Controller;
use yii\web\Response;

/**
 * Class ControllerBase
 * @package api\controllers
 *
 * @property int $uid
 */
class ControllerBase extends Controller {
	/**
	 * @inheritdoc
	 */
//    public function behaviors() {
	//        $behaviors = parent::behaviors();
	//        $behaviors['authenticator'] = [
	//            'class' => HttpTokenAuth::className(),
	//            'except' => []
	//        ];
	//        return $behaviors;
	//    }

	/**
	 * @param int $code
	 * @param array $data
	 * @param string $msg
	 * @return array
	 */
	public function responseJson($code = 0, $data = [], $msg = '') {
		\Yii::$app->response->format = Response::FORMAT_JSON;
		\Yii::$app->response->charset = 'UTF-8';
		$data = ['code' => $code, 'data' => $data, 'msg' => $msg];
		return $data;
	}

	public function responseHtml($data) {
		\Yii::$app->response->format = Response::FORMAT_HTML;
		\Yii::$app->response->charset = 'UTF-8';
		//echo json_encode($data);exit();
		var_export($data);exit();
		return $data;
	}

	/**
	 * 数据进模型
	 * @param $model
	 * @param $data
	 * @return bool
	 */
	public function arrayLoad($model, $data) {
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
	 * 支付密码验证
	 * @param $pwd -原密码
	 * @param $payPwd -输入密码
	 * @return array
	 * @throws Exception
	 */
	public function validatePayPwd($pwd, $payPwd) {
		if (empty($payPwd) || empty($pwd)) {
			throw new Exception('请填写参数');
		}
		if ($pwd == md5($payPwd)) {
			return $this->responseJson(0, '', '验证成功');
		} else {
			throw new Exception('支付密码错误');
		}
	}

	/**
	 * 判断是否实名认证
	 * @param $uid
	 * @return mixed
	 * @throws \yii\db\Exception
	 */
	public function isRealName($uid) {
		$user = User::findByUid($uid);
		return $user->real_status;
	}

	/**
	 * 支付
	 * @param $data
	 * @throws Exception
	 */
	public function pay($data) {
		if (!is_array($data) || empty($data['uid']) || empty($data) || empty($data['price']) || empty($data['pay_pwd'])) {
			throw new Exception('请填写参数');
		}
		if ($data['price'] <= 0) {
			throw new Exception('支付金额错误');
		}
		$user = User::findByUid($data['uid']);
		if (empty($user)) {
			throw new Exception('用户信息有误');
		}
		if (empty(bccomp($user['credit'], $data['price']))) {
			throw new Exception('用户零钱不足');
		}
		$this->validatePayPwd($user->pay_password, $data['pay_pwd']);
		$user->credit = bcsub($user['credit'], $data['price']);
		$user->updated_at = time();
		if (!$user->save()) {
			throw new Exception('金额扣除失败');
		}
	}

	/**
	 * 用户金额变动日志
	 * @param int $uid
	 * @param string $op
	 * @param array $data
	 * @param string $msg
	 * @throws Exception
	 */
	public static function addUserLog($uid, $op, $data, $msg) {
		$model = new UserLog();
		$model->uid = $uid;
		$model->type = UserLog::PROP;
		$model->op = $op;
		$model->credit = !empty($data['credit']) ? $data['credit'] : 0;
		$model->integral = !empty($data['integral']) ? $data['integral'] : 0;
		$model->integral1 = !empty($data['integral1']) ? $data['integral1'] : 0;
		$model->msg = $msg;
		if (!$model->save()) {
			if (YII_ENV == 'dev') {
				throw new Exception('日志记录失败:' . current($model->getFirstErrors()));
			}
			\Yii::error('日志记录失败: ' . current($model->getFirstErrors()));
		}
	}

}
