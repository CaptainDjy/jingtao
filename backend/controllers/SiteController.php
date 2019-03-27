<?php

namespace backend\controllers;
use api\models\Order;
use api\models\User;
use common\models\Goods;
use backend\models\cooperationuser;
class SiteController extends ControllerBase
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'common\helpers\CaptchaAction',
                'height' => 100,
                'width' => 200,
                'minLength' => 4,
                'maxLength' => 4,
                'offset' => 18,
                'fontSize' => 40,
                'imageLibrary' => 'gd',
                'fontFile' => '@common/font/1.ttf'
            ],
        ];
    }

    /**
     * 系统首页.
     */
    public function actionIndex()
    {
        $order=order::find()->count();//订单总数
        $user=user::find()->count();//注册总数
        $goods=round(goods::find()->count()/10000,2);//商品总数
        return $this->render('index',['order'=>$order,'user'=>$user,'goods'=>$goods]);
    }

}
