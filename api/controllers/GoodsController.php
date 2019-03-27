<?php
namespace api\controllers;
use common\models\Goods;

/**
 *
 */
class GoodsController extends ControllerBase {
//全部商品信息(所有类型)
	public function actionGoods() {
		$res = Goods::find()->asArray()->all();
		return $this->responseJson(1, $res, '商品信息');

	}
//商品信息(根据类型查询商品信息)
    public  function actionGoodslx(){
//        $criteria = new Goods();
//        $criteria->order = 'id DESC';
        $request = \Yii::$app->request;
        $type = $request->post('type');
        $res = Goods::find()->select(['id','title','sub_title','type','origin_price','coupon_price','thumb','coupon_money','commission_money','description'])->where(['type'=>$type])->asArray()->all();
        return $this->responseJson(1, $res, '商品信息');
    }
}