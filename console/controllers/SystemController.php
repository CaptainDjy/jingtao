<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/26 16:19
 */

namespace console\controllers;


use backend\models\SystemUser;
use yii\console\Controller;

class SystemController extends Controller
{
    /**
     * Add user
     * @param string $username
     */
    public function actionAddUser($username)
    {
        $model = new SystemUser();
        $model->username = $username;
        $model->setPassword('admin');
        $model->generateAuthKey();
        if ($model->validate() && $model->save()) {
            echo 'SUCCESS' . PHP_EOL;
            echo 'username: ' . $username . PHP_EOL;
            echo 'password: admin' . PHP_EOL;
        } else {
            echo iconv("UTF-8", "GB2312", current($model->getFirstErrors()));
        }
    }
}
