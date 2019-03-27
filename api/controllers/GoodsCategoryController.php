<?php
namespace api\controllers;
use api\models\GoodsCategory;

/**
 *分类展示
 */
class GoodsCategoryController extends ControllerBase {
	public function actionCategory() {
		$res = GoodsCategory::find()->select('title')->asArray()->all(); //查询分类表标题
		return $this->responseJson(1, $res, '分类标题');
		// die();
	}
}