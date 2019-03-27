<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/19
 * Time: 20:23
 */

namespace backend\controllers;


use backend\models\DistributionConfig;
use yii\helpers\Json;

class DistributionController extends ControllerBase
{
    /**
     * 更新配置
     * @return string|\yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $name = $request->get('name', 'index');
        $model = DistributionConfig::findByName($name);
        if (!empty($model->value)) {
            $config = Json::decode($model->value, true);
        } else {
            $config = [];
        }
        if ($request->isPost) {
            $model->value = Json::encode($request->post('config'));
            if ($model->save()) {
                return $this->message('更新成功！', 'referer', 'success');
            } else {
                return $this->message('更新失败！', 'referer', 'error');
            }
        }

        return $this->render('index', [
            'config' => $config,
            'name' => $name
        ]);
    }


}
