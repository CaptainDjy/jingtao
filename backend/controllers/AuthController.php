<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/26 13:45
 */

namespace backend\controllers;


use backend\models\LoginForm;
use backend\models\SystemMenu;
use backend\models\SystemUser;
use yii\helpers\Url;
use yii\web\Response;
use yii\widgets\ActiveForm;

class AuthController extends ControllerBase
{
    public function init()
    {
        $this->layout = 'main-login';
    }

    /**
     * @return array|string|\yii\web\Response
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        $request = \Yii::$app->request;
        if ($request->isAjax && $request->isPost) {
            $model->verifyCode = $request->post('verifycode');
            $model->username = $request->post('username');
            $model->password = $request->post('password');
            if ($model->login()) {
                return $this->responseJson(0, ['url' => Url::toRoute(['site/index'])], '登录成功!');
            } else {
                return $this->responseJson(1, '', current($model->getFirstErrors()));

            }
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * 退出登陆
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        //插入日志
        \Yii::$app->cache->delete(SystemMenu::CACHE_KEY);
        \Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * 后台用户添加/修改
     * @return array|string|Response
     */
    public function actionUserEdit()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
        $parent_title = '修改密码';
        $model = SystemUser::findIdentity($id);
        if ($model->load($request->post())) {
            $SystemUser = $request->post('SystemUser');
            if (is_array($SystemUser) && !empty($SystemUser['password_hash'])) {
                $model->setPassword($SystemUser['password_hash']);
            }
            if ($request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {
                return $model->save() ? $this->message('更新成功', 'referer', 'success') : $this->message('更新失败：' . current($model->getFirstErrors()), 'referer', 'error');
            }
        }
        return $this->renderAjax('/auth/user-edit', [
            'model' => $model,
            'parent_title' => $parent_title,
        ]);
    }

}
