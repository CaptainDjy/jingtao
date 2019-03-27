<?php

namespace backend\controllers;

use common\models\Cache;
use yii\db\Exception;

class CacheController extends ControllerBase
{

    /**
     * 清理缓存
     * @return array|string
     */
    public function actionClear()
    {
        if (\Yii::$app->request->isPost) {
            $key = \Yii::$app->request->post('key', 'all');
            try {
                Cache::clear($key);
                return $this->message('操作成功', 'refresh', 'success');
            } catch (Exception $e) {
                return $this->message($e->getMessage(), 'refresh', 'error');
            }
        }
        return $this->render('clear');
    }

}
