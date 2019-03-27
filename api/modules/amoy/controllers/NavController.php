<?php
namespace api\modules\amoy\controllers;
use fast\Http;
use yii;
use common\models\Nav;
use common\helpers\Utils;
class NavController extends ControllerBase
{
    /**
     * 列表
     * @return string
     */
    public function actionIndex()
    {
//        $query = Nav::find()->asArray()->all();
        $query=Yii::$app->db->createCommand('SELECT * FROM jt_nav  order by created_at desc limit 5')
            ->queryAll();
        foreach ($query as $key=>&$value){
            $url=substr($value['img'],0,4);
             if ($url=='http'){

             }else{$value['img'] = \Yii::$app->urlManager->hostInfo .$value['img'];}

        }
//        print_r($query);

        return $this->responseJson('200',$query,'返回数据成功');
//        print_r($query);
    }


}
