<?php

namespace backend\controllers;

use common\models\AdvertPosition;
use common\widgets\daterangepicker\DateRangePicker;
use yii\data\ActiveDataProvider;
use yii\db\Exception;

class AdvertPositionController extends ControllerBase
{
    public $title = '广告位管理';

    /**
     * 列表
     * @return string
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $query = AdvertPosition::find();

        $searchArr = [
            'title' => trim($request->get('title', '')),
        ];
        if (!empty($searchArr['title'])) {
            //$query->Where(['title' => $searchArr['title']]);
            $query->Where(['like' , 'title' , $searchArr['title']]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->view->title = $this->title;
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
        $model = AdvertPosition::initModel($id);
        if (empty($model)) {
            return $this->message('信息不存在', ['advert-position/index'], 'error');
        }

        if ($request->isPost) {
            try {
                $model->load($request->post());
                //todo 保存字段
                $saveFields = null;
                if (!$model->save(true, $saveFields)) {
                    throw new Exception('操作失败:' . current($model->getFirstErrors()));
                }
                return $this->message('操作成功', ['advert-position/index'], 'success');
            } catch (Exception $e) {
                return $this->message('操作失败:' . $e->getMessage(), ['advert-position/edit'], 'error');
            }
        }
        $this->view->title = $this->title;
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * 删除广告位
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDel($id)
    {
        $model = AdvertPosition::findOne($id);
        if (empty($model)) {
            return $this->message('信息不存在', ['advert-position/index'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['advert-position/index'], 'error');
        } else {
            return $this->message('操作成功', ['advert-position/index'], 'success');
        }
    }
}
