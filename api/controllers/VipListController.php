<?php
namespace api\controllers;
use api\models\VipList;

/**
 *会员等级模型(代理,总代)
 */
class VipListController extends ControllerBase {
    public function actionIndex() {
        $res = VipList::find()->select(['agency','sole','people'])->asArray()->all();
        return $this->responseJson(1, $res, '等级信息');
    }
}