<?php

namespace backend\controllers;

use common\helpers\Utils;
use common\models\Advert;
use common\widgets\daterangepicker\DateRangePicker;
use yii\data\ActiveDataProvider;
use yii\db\Exception;

class AdvertController extends ControllerBase
{
    public $title = '广告管理';

    /**
     * 列表
     * @return string
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $query = Advert::find()->alias('a')->joinWith('advertPosition p');

        $searchArr = [
            'title' => trim($request->get('title', '')),
        ];
        if (!empty($searchArr['title'])) {
            //$query->Where(['a.title' => $searchArr['title']]);
            $query->Where(['like' , 'a.title' , $searchArr['title']]);
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
     * @throws Exception
     */
    public function actionEdit()
    {
        $request = \Yii::$app->request;
        $id = (int)$request->get('id', '');
        $model = Advert::initModel($id);
        if (empty($model)) {
            return $this->message('信息不存在', ['advert/index'], 'error');
        }

        if ($request->isPost) {
            try {
                $model->load($request->post());
                //格式化时间
                $model = Utils::formatDate(['deadline'], $model, 'Y-m-d H:i');
                //todo 保存字段
                $saveFields = null;
                if (!$model->save(true, $saveFields)) {
                    throw new Exception('操作失败:' . current($model->getFirstErrors()));
                }
                return $this->message('操作成功', ['advert/index'], 'success');
            } catch (Exception $e) {
                return $this->message('操作失败:' . $e->getMessage(), ['advert/index'], 'error');
            }
        }
        // 格式化时间
        $model = Utils::formatDate(['deadline'], $model, 'Y-m-d H:i');
        $this->view->title = $this->title;
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
        $model = Advert::findOne($id);
        if (empty($model)) {
            return $this->message('信息不存在', ['advert/index'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['advert/index'], 'error');
        } else {
            return $this->message('操作成功', ['advert/index'], 'success');
        }
    }

}
