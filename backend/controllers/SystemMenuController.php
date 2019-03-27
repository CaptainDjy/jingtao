<?php

namespace backend\controllers;

use backend\models\SystemMenu;
use common\helpers\Utils;
use yii;
use yii\bootstrap\ActiveForm;
use yii\web\Response;

class SystemMenuController extends ControllerBase
{
    /**
     * 菜单列表
     * @return string
     */
    public function actionList()
    {
        $this->view->title = '菜单管理';
        $list = SystemMenu::find()->orderBy('sort ASC')->asArray()->all();

        return $this->render('list', ['list' => Utils::tree($list)]);
    }

    /**
     * 添加菜单/修改菜单
     */
    public function actionEdit()
    {
        $this->view->title = '菜单修改';
        $request = Yii::$app->request;
        $id = intval($request->get('id'));
        if (!empty($id)) {
            $model = SystemMenu::findOne($id);
            if (empty($model)) {
                return $this->message('您要编辑的菜单不存在', ['system-menu/list'], 'error');
            }
        } else {
            $model = new SystemMenu();
            $model = $model->loadDefaultValues();
        }

        if ($model->load($request->post())) {
            if ($request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {
                if ($model->save()) {
                    return $this->message('更新成功', 'referer', 'success');
                } else {
                    return $this->message('更新失败 [{$model->getFirstError}]', 'referer', 'error');
                }
            }
        }

        $pid = $request->get('pid', '0');
        $parent_title = $request->get('parent_title', '无');
        return $this->renderAjax('_edit', [
            'model' => $model,
            'pid' => $pid,
            'parent_title' => $parent_title,
        ]);
    }

    /**
     * AJAX 排序更新
     * @return array
     */
    public function actionUpdate()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            $sort = Yii::$app->request->post('sort');
            if (empty($id)) {
                return $this->responseJson('1', '', '更新失败：ID不能为空');
            }

            $model = SystemMenu::findOne(['id' => $id]);
            if (empty($model)) {
                return $this->responseJson('1', '', '更新失败：信息不存在');
            }

            $model->sort = intval($sort);
            $result = $model->save();
            if ($result === false) {
                return $this->responseJson('1', '', '更新失败：信息不存在');
            } else {
                return $this->responseJson('0', '', '更新成功');
            }
        }
        return $this->responseJson('0', '', '请求失败');
    }


    /**
     * 删除菜单
     * @param $id
     * @return Response
     * @throws \Throwable
     * @throws yii\db\StaleObjectException
     */
    public function actionDel($id)
    {
        $item = SystemMenu::findOne($id)->delete();
        if ($item > 0) {
            return $this->message('删除成功', ['system-menu/list'], 'success');
        } else {
            return $this->message('删除失败', ['system-menu/list'], 'error');
        }
    }
}
