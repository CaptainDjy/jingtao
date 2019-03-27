<?php
namespace api\models;
use yii\db\ActiveRecord;

/**
 *订单模型
 */
class Order extends ActiveRecord {
	public function getuser() {
		$user = $this->hasone(user::classname(), ['uid' => 'uid'])->asArray()->all();
		return $user;
	}

	public function getOrder() {
		$order = $this->hasMany(Order::classname(), ['uid' => 'uid'])->asArray()->all();
		return $order;
	}
}