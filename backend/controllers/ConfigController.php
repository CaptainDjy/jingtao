<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/21
 * Time: 10:28
 */

namespace backend\controllers;

use common\models\Config;
use Yii;

class ConfigController extends ControllerBase
{
    /**
     * 配置列表
     */
    public function actionIndex()
    {
        $searchArr = ['keywords' => '', 'group' => '', 'type' => '', 'status' => ''];

        $query = Config::find();
        $request = Yii::$app->request;
        $keywords = $request->get('keywords', '');
        if (!empty($keywords)) {
            $searchArr['keywords'] = $keywords;
            $query->andWhere("name LIKE '%{$keywords}%' OR title LIKE '%{$keywords}%'");
        }

        $group = $request->get('group', '');
        if (!empty($group)) {
            $searchArr['group'] = $group;
            $query->andWhere(['group' => $group]);
        }

        $type = $request->get('type', '');
        if (!empty($type)) {
            $searchArr['type'] = $type;
            $query->andWhere(['type' => $type]);
        }

        $status = $request->get('status', '');
        if (!empty($status)) {
            $searchArr['status'] = $status;
            $query->andWhere(['status' => $status]);
        }

        $this->view->title = '配置管理';
        return $this->render('index', ['query' => $query, 'searchArr' => $searchArr]);
    }

    /**
     * 添加配置/修改配置
     * @param $id
     * @return string|\yii\web\Response
     */

    public function actionUpdate($id = 0)
    {
        $model = new Config();
        if (!empty($id)) {
            $model = $model::findOne($id);
            if (empty($model)) {
                return $this->message('您要编辑的配置项不存在', ['config/index'], 'error');
            }
        } else {
            $model->loadDefaultValues();
        }

        // 表单提交
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->message('更新成功', ['config/index'], 'success');
            } else {
                return $this->message('更新失败 [{$model->getFirstError}]', ['config/index'], 'error');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * 删除配置
     * @param int $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id = 0)
    {
        $item = Config::findOne($id)->delete();
        if ($item > 0) {
            return $this->message('删除成功', ['config/index'], 'success');
        } else {
            return $this->message('删除失败', ['config/index'], 'error');
        }
    }

    /**
     * 网站设置
     * @param int $id
     * @return string
     */
    public function actionGroup($id = 4)
    {
        $configs = Config::find()->where(['group' => $id])->orderBy('sort ASC')->all();
        if (Yii::$app->request->isPost) {
            $config = Yii::$app->request->post('config');
            foreach ($config as $key => $value) {
                $model = Config::find()->where(['name' => $key])->one();
                if ($model) {
                    $model->value = is_array($value) ? serialize($value) : $value;
                    if (!$model->save()) {
                        return $this->message('更新失败：' . $key . '配置保存失败！'.$model->getErrors()['value'][0], 'referer', 'error');
                    }
                } else {
                    return $this->message('更新失败：' . $key . '配置不存在！', 'referer', 'error');
                }
            }
            return $this->message('更新成功！', 'referer', 'success');
        }
        return $this->render('group', ['id' => $id, 'configs' => $configs]);
    }

    /**
     * ajax更新排序
     * @return array
     */
    public function actionUpdateSort()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            $sort = Yii::$app->request->post('sort');
            if (empty($id)) {
                return $this->responseJson('1', '', '更新失败：ID不能为空');
            }

            $model = Config::findOne($id);
            if (empty($model)) {
                return $this->responseJson('1', '', '更新失败：配置不存在');
            }

            $model->sort = intval($sort);
            $result = $model->save();
            if ($result === false) {
                return $this->responseJson('1', '', '更新失败：配置不存在');
            } else {
                return $this->responseJson('0', '', '更新成功');
            }
        }
        return $this->responseJson('0', '', '请求失败');
    }
}
