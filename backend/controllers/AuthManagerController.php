<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/13 18:55
 */

namespace backend\controllers;

use backend\models\SystemAuthItem;
use backend\models\SystemAuthItemChild;
use backend\models\SystemAuthRule;
use backend\models\SystemUser;
use common\helpers\Utils;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * 用户角色权限管理 控制器
 */
class AuthManagerController extends ControllerBase
{
    /**
     * 权限列表
     * @return string
     */
    public function actionItemList()
    {
        $list = SystemAuthItem::find()->where(['type' => SystemAuthItem::AUTH])->orderBy('sort ASC')->asArray()->all();
        $this->view->title = '角色权限';
        return $this->render('item-list', ['list' => Utils::tree($list)]);
    }

    /**
     * 权限编辑
     * @return array|string|Response
     */
    public function actionItemEdit()
    {
        $request = Yii::$app->request;
        $name = $request->get('name');
        $model = $this->initActiveRecord($name);

        if ($model->load($request->post())) {
            if ($request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {
                return $model->save() ? $this->message('更新成功', 'referer', 'success') : $this->message('更新失败：' . current($model->getFirstErrors()), 'referer', 'error');
            }
        }

        $pid = $request->get('pid', '0');
        $level = $request->get('level', '1');
        $parent_title = $request->get('parent_title', '无');

        return $this->renderAjax('_item-edit', [
            'model' => $model,
            'parent_title' => $parent_title,
            'pid' => $pid,
            'level' => $level,
        ]);
    }

    /**
     * 权限更新
     * @return array
     */
    public function actionItemUpdate()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            $sort = Yii::$app->request->post('sort');
            if (empty($id)) {
                return $this->responseJson('1', '', '更新失败：ID不能为空');
            }

            $model = SystemAuthItem::findOne(['id' => $id]);
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
     * 权限删除
     * @param $name
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionItemDel($name)
    {
        $model = SystemAuthItem::findOne($name);
        if (!empty($model) && $model->delete()) {
            $this->message('权限删除成功', 'referer', 'success');
        } else {
            $this->message('权限删除失败: ' . current($model->getFirstErrors()), 'referer', 'error');
        }
    }

    /**
     * 角色列表
     * @return string
     */
    public function actionRoleList()
    {
        $model = SystemAuthItem::find()->where(['type' => SystemAuthItem::ROLE]);

        return $this->render('role-list', ['model' => $model]);
    }

    /**
     * 角色编辑
     * @return array|string|Response
     */
    public function actionRoleEdit()
    {
        $request = Yii::$app->request;
        $name = $request->get('name');
        $model = $this->initActiveRecord($name);

        if ($model->load($request->post())) {
            if ($request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {
                /** @var SystemUser $user */
                $user = Yii::$app->user->identity;
                $model->description = (isset($user->username) ?: 'admin') . "|添加了|" . $model->name . "|角色";
                return $model->save() ? $this->message('更新成功', 'referer', 'success') : $this->message('更新失败：' . current($model->getFirstErrors()), 'referer', 'error');
            }
        }

        return $this->renderAjax('_role-edit', [
            'model' => $model
        ]);
    }

    /**
     * 角色授权
     * @return array|string|Response
     */
    public function actionRoleAccredit()
    {
        $request = Yii::$app->request;
        $name = $request->get('name');
        $this->initActiveRecord($name);
        $list = SystemAuthItem::find()->where(['type' => SystemAuthItem::AUTH])->orderBy('sort ASC')->asArray()->all();
        $list = Utils::tree($list);

        $role_child = SystemAuthItemChild::find()->select('child')->where("parent = '{$name}'")->asArray()->all();
        if (!empty($role_child)) {
            foreach ($role_child as $value) {
                $role[] = $value['child'];
            }
        } else {
            $role = '';
        }
        /** @var array $role */
        return $this->render('role-accredit', [
            'name' => $name,
            'role_default' => $role,
            'list' => $list
        ]);
    }


    /**
     * 授权角色
     * @return Response
     */
    public function actionRoleAccreditTree()
    {
        $request = Yii::$app->request;
        if ($request->post()) {
            $data = $request->post('data');
            $name = $request->post('name');

            $model = new SystemAuthItemChild();
            $result = $model->accredit($name, $data);

            if ($result === true) {
                return $this->message('授权成功', 'referer', 'success');
            } else {
                return $this->message('授权失败', 'referer', 'error');
            }
        } else {
            return $this->message('请求失败', 'referer', 'error');
        }
    }

    /**
     * 权限删除
     * @param $name
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionRoleDel($name)
    {
        $model = SystemAuthItem::findOne($name);
        if (!empty($model) && $model->delete()) {
            $this->message('角色删除成功', 'referer', 'success');
        } else {
            $this->message('角色删除失败: ' . current($model->getFirstErrors()), 'referer', 'error');
        }
    }

    /**
     * 规则管理
     * @return string
     */
    public function actionAuthRule()
    {
        $model = SystemAuthRule::find();
        return $this->render('auth-rule', ['model' => $model]);

    }

    /**
     * 规则添加/修改
     * @return array|string|Response
     */
    public function actionAuthRuleEdit()
    {
        $request = Yii::$app->request;
        $name = $request->get('name');
        empty($name) ? $parent_title = '添加' : $parent_title = '编辑';
        $model = new SystemAuthRule();
        $model = $model->initActiveRecord($name);
        if ($model->load($request->post())) {
            if ($request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {
                return $model->save() ? $this->message('更新成功', 'referer', 'success') : $this->message('更新失败：' . current($model->getFirstErrors()), 'referer', 'error');
            }
        }
        return $this->renderAjax('_auth-rule-edit', [
            'model' => $model,
            'parent_title' => $parent_title,
        ]);
    }

    /**
     *
     * 初始化模型 使用编辑时候调用
     * @param $key
     * @return SystemAuthItem
     */
    private function initActiveRecord($key)
    {
        $model = new SystemAuthItem;
        if (!empty($key)) {
            $item = $model::findOne($key);
            if (!empty($item)) {
                return $item;
            }
        }

        $model->loadDefaultValues();
        return $model;
    }

}
