<?php
namespace api\models;
use yii\db\ActiveRecord;

/**
 *用户模型
 */
class User extends ActiveRecord {

	public $imageFile;
	//用户全部订单
	public function getOrder($type,$status) {
		$order = $this->hasMany(Order::classname(), ['uid' => 'uid'])->where(['type' => $type,'order_status'=>$status])->asArray()->all();
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