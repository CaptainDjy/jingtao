<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/22 13:55
 */

namespace backend\controllers;


use backend\models\SystemAuthAssignment;
use backend\models\SystemAuthItem;
use backend\models\SystemUser;
use common\models\Config;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

class SystemUserController extends ControllerBase
{
    public function actionList()
    {
        return $this->render('index');
    }

    /**
     * 后台用户添加/修改
     */
    public function actionUserEdit()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        empty($id) ? $parent_title = '添加' : $parent_title = '编辑';
        $model = $this->initActiveRecord($id);
        $long_password_hash = $model->password_hash;
        $SystemUser = $request->post('SystemUser');
        if ($model->load($request->post('SystemUser'), '')) {
            if (is_array($SystemUser) && !empty($SystemUser['password_hash']) && $SystemUser['password_hash'] != $long_password_hash) {
                $model->setPassword($SystemUser['password_hash']);
            }
            if ($request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {
                $result = $model->save();
                $ids = $model->attributes['id'];
                if (empty($id)) {
                    $data = ['name' => Config::getConfig('SYSTEM_ADMIN_ROLE_DEFAULT'), 'description' => $ids];
                    SystemUser::assign($data);
                }
                return $result ? $this->message('更新成功', 'referer', 'success') : $this->message('更新失败：' . current($model->getFirstErrors()), 'referer', 'error');
            }
        }
        return $this->renderAjax('user-edit', [
            'model' => $model,
            'parent_title' => $parent_title,
        ]);
    }

    /**
     * 授权角色
     * @return string|Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpdate()
    {
        $id = \Yii::$app->request->get('id');
        $model = SystemAuthItem::find()->where(['type' => SystemAuthItem::ROLE])->asArray()->all();
        $item = SystemUser::findIdentity($id);
        if (!$item) {
            return $this->message('用户不存在或被删除', 'referer', 'error');
        }
        $role_id = \Yii::$app->request->post('SystemUser')['role_id'];
        if ($item->load(\Yii::$app->request->post())) {
            $name = SystemAuthItem::roleStatus($role_id);
            $items = ['name' => $name, 'description' => $id];
            $assign = SystemAuthAssignment::find()->where("user_id = $id")->one();
            if (!empty($assign)) {
                $assign->delete();
            }
            $result = SystemUser::assign($items);
            return $item->save() && $result === true ? $this->message('更新成功', 'referer', 'success') : $this->message('更新失败：' . current($item->getFirstErrors()), 'referer', 'error');
        }
        return $this->renderAjax('update', [
            'model' => $model,
            'item' => $item,
        ]);
    }

    /**
     * 删除管理员
     */
    public function actionDelete()
    {
        $id = \Yii::$app->request->get('id');
        $item = SystemUser::findIdentity($id);
        $item->status = 0;
        if (!empty($item) && $item->save()) {
            $this->message('删除成功', 'referer', 'success');
        } else {
            $this->message('删除失败: ' . current($item->getFirstErrors()), 'referer', 'error');
        }

    }


    /**
     * 初始化模型 使用编辑时候调用
     * @param $key
     * @return SystemUser|null|static
     */
    private function initActiveRecord($key)
    {
        $model = new SystemUser();
        if (!empty($key)) {
            $item = $model::findOne($key);
            if (!empty($item)) {
                return $item;
            }
        }

        $model->role_id = SystemAuthItem::roleId(Config::getConfig('SYSTEM_ADMIN_ROLE_DEFAULT'));
        $model->generateAuthKey();
        $model->loadDefaultValues();
        return $model;
    }

}
