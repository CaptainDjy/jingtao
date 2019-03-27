<?php

namespace backend\controllers;

use common\models\Nav;
use yii\data\ActiveDataProvider;
use yii\db\Exception;

class NavController extends ControllerBase
{
    /**
     * 列表
     * @return string
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $query = Nav::find();

        $searchArr = [
            'title' => trim($request->get('title', '')),
        ];
        if (!empty($searchArr['title'])) {
            //$query->Where(['title' => $searchArr['title']]);
            $query->Where(['like','title' , $searchArr['title']]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['sort' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchArr' => $searchArr,
        ]);
    }

    /**
     * 编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        $request = \Yii::$app->request;
        $id = (int)$request->get('id', '');
        $model = Nav::initModel($id);
        if (empty($model)) {
            return $this->message('信息不存在', ['nav/index'], 'error');
        }

        if ($request->isPost) {
            try {
                $model->load($request->post());
                //todo 保存字段
                $saveFields = null;
                if (!$model->save(true, $saveFields)) {
                    throw new Exception('操作失败:' . current($model->getFirstErrors()));
                }
                return $this->message('操作成功', ['nav/index'], 'success');
            } catch (Exception $e) {
                return $this->message('操作失败:' . $e->getMessage(), ['nav/index'], 'error');
            }
        }
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * 删除
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDel($id)
    {
        $model = Nav::findOne($id);
        if (empty($model)) {
            return $this->message('信息不存在', ['nav/index'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['nav/index'], 'error');
        } else {
            return $this->message('操作成功', ['nav/index'], 'success');
        }
    }

    /**
     * @return array
     */
    public function actionSort()
    {
        if (\Yii::$app->request->isAjax) {
            $id = (int)\Yii::$app->request->post('id', '');
            $sort = (int)\Yii::$app->request->post('sort', '');
            if (empty($id) || $sort === '') {
                return $this->responseJson(1, '', '操作失败：参数有误');
            }

            $model = Nav::findOne(['id' => $id]);
            if (empty($model)) {
                return $this->responseJson(1, '', '操作失败：信息不存在');
            }

            $model->sort = $sort;
            if (!$model->save()) {
                return $this->responseJson(1, '', '操作失败：' . current($model->getFirstErrors()));
            } else {
                return $this->responseJson(0, '', '操作成功');
            }
        }
        exit('请求失败');
    }
}
