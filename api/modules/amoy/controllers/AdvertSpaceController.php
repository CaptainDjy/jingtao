<?php
namespace api\modules\amoy\controllers;
use api\models\AdvertSpace;
use api\models\User;
class AdvertSpaceController extends ControllerBase{
    public function actionIndex()
    {
        $adver=AdvertSpace::find()->asArray()->all();
        $user=user::find()->select(['alimm_pid'])->asArray()->all();
//        foreach ();
        print_r($user);

    }

}