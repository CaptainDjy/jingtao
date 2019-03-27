<?php

namespace backend\controllers;

use backend\models\SystemUser;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class ControllerBase extends Controller
{
    /**
     * @param $action
     * @return bool
     * @throws UnauthorizedHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $controller = Yii::$app->controller->id;
        $action = Yii::$app->controller->action->id;
        $permissionName = $controller . '/' . $action;
        $result = SystemUser::isSystemAdmin(Yii::$app->user->identity['username']);
        if (!$result && Yii::$app->controller->id != 'auth' && Yii::$app->controller->id != 'site' && !\Yii::$app->user->can($permissionName) && Yii::$app->getErrorHandler()->exception === null) {
            throw new UnauthorizedHttpException('对不起，您现在还没获此操作的权限');
        }
        return true;
    }

    /**
     * 行为控制
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'captcha'],
                        'allow' => true,
                        'roles' => ['?'],//游客
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],//登录
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $msg
     * @param string|array $redirect
     * @param string $type
     * @return \yii\web\Response
     */
    public function message($msg, $redirect, $type = '')
    {
        Yii::$app->getSession()->setFlash($type, $msg);
        if ($redirect == 'referer') {
            $url = Yii::$app->request->referrer;
        } else if ($redirect == 'refresh') {
            $url = Yii::$app->request->getAbsoluteUrl();
        } else if (is_array($redirect)) {
            $url = Url::toRoute($redirect);
        } else {
            $url = Url::home();
        }
        if (empty($url)) {
            $url = Url::home();
        }
        return $this->redirect($url);
    }

    /**
     * 返回json格式到前台
     * @param int $code
     * @param array $data
     * @param string $msg
     * @return array
     */
    public function responseJson($code = 0, $data = [], $msg = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = ['code' => $code, 'data' => $data, 'msg' => $msg];
        return $data;
    }
}
