<?php
namespace api\controllers;
use api\models\Cooperation;

/**
 *分类展示
 */
class CooperationController extends ControllerBase {
	public function actionIndex() {
		$res = Cooperation::find()->asArray()->all(); //查询分类表标题
		return $this->responseJson(1, $res, '合作商信息');
		// die();
	}
}