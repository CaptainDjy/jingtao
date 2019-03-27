<?php
namespace api\controllers;
use common\models\Order;

class OrderController extends ControllerBase {

/**
 *
 */
	public function actionOrder() {
		$request = \Yii::$app->request;
		$id = $request->post('id');
		$user = User::find()->where(['uid' => $id])->one();
		// $order = $user->hasMany(order::classname(), ['uid' => 'uid'])->asArray()->all();
		// $order = $user->getOrder();
		$order = $user->order; //同上一样的效果
		return $this->responseJson(1, $order, '订单信息');
	}
}