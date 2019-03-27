<?php
namespace api\models;
use yii\db\ActiveRecord;
/**
 *用户模型
 */
class User extends ActiveRecord {

	public $imageFile;
	//用户全部订单
	public function getOrder() {
		$order = $this->hasMany(Order::classname(), ['uid' => 'uid'])->asArray()->all();
		return $order;
	}
	//用户待付款订单
	public function getOrderstay($type) {
		$order = $this->hasMany(Order::classname(), ['uid' => 'uid'])->where(['order_status' => 1, 'type' => $type])->asArray()->all();
		return $order;
	}

	//用户已付款订单
	public function getOrderend($type) {
		$order = $this->hasMany(Order::classname(), ['uid' => 'uid'])->where(['order_status' => 2, 'type' => $type])->asArray()->all();
		return $order;
	}

	//用户已完成订单
	public function getOrderdone($type) {
		$order = $this->hasMany(Order::classname(), ['uid' => 'uid'])->where(['order_status' => 3, 'type' => $type])->asArray()->all();
		return $order;
	}

	//用户已退款订单
	public function getOrderfade($type) {
		$order = $this->hasMany(Order::classname(), ['uid' => 'uid'])->where(['order_status' => 4, 'type' => $type])->asArray()->all();
		return $order;
	}

	public function rules() {
		return [
					['mobile', 'required'],
                    ['password_hash', 'required'],
					['mobile', 'filter', 'filter' => 'trim'],
					['mobile', 'match', 'pattern' => '/^[1][34578][0-9]{9}$/'],
				];
	}
}