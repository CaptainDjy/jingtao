<?php

namespace backend\controllers;

use common\models\Biz;
use common\models\BizCategory;
use common\widgets\daterangepicker\DateRangePicker;
use yii\data\ActiveDataProvider;
use yii\db\Exception;

class BizController extends ControllerBase
{
    /**
     * 商家列表
     * @return string
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $query = Biz::find()->alias('b')
            ->joinWith('bizCategory c', true);

        $searchArr = [
            'title' => trim($request->get('title', '')),
            'cate' => $request->get('cate', ''),
        ];
        if (!empty($searchArr['cate'])) {
            $query->andWhere(['b.cid' => $searchArr['cate']]);
        }
        if (!empty($searchArr['title'])) {
            //$query->andWhere(['b.title' => $searchArr['title']]);
            $query->andWhere(['like' , 'b.title' , $searchArr['title']]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => "id",
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
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
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        $request = \Yii::$app->request;
        $id = (int)$request->get('id', '');

        if (!empty($id)) {
            $model = Biz::findOne($id);
            if (empty($model)) {
                return $this->message('信息不存在', ['biz/index'], 'error');
            }
        } else {
            $model = new Biz();
            $model->loadDefaultValues();
        }

        if ($request->isPost) {
            try {
                $model->load($request->post());
                //todo 保存字段
                $saveFields = null;
                if (!$model->save(true, $saveFields)) {
                    throw new Exception('操作失败:' . current($model->getFirstErrors()));
                }
                return $this->message('操作成功', ['biz/index'], 'success');
            } catch (Exception $e) {
                return $this->message('操作失败:' . $e->getMessage(), ['biz/index'], 'error');
            }
        }
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * 删除商家
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDel($id)
    {
        $model = Biz::findOne($id);
        if (empty($model)) {
            return $this->message('信息不存在', ['biz/index'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['biz/index'], 'error');
        } else {
            return $this->message('操作成功', ['biz/index'], 'success');
        }
    }

    /**
     * 分类列表
     * @return string
     */
    public function actionCate()
    {
        $request = \Yii::$app->request;
        $query = BizCategory::find();

        $searchArr = [
            'title' => trim($request->get('title', '')),
        ];
        if (!empty($searchArr['title'])) {
            $query->Where(['LIKE', 'title', $searchArr['title']]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('cate', [
            'dataProvider' => $dataProvider,
            'searchArr' => $searchArr,
        ]);
    }

    /**
     * 编辑分类
     * @return string|\yii\web\Response
     */
    public function actionCateEdit()
    {
        $request = \Yii::$app->request;
        $id = (int)$request->get('id', '');
        $model = BizCategory::initModel($id);
        if (empty($model)) {
            return $this->message('信息不存在', ['biz/cate'], 'error');
        }

        if ($request->isPost) {
            try {
                $model->load($request->post());
                //todo 保存字段
                $saveFields = null;
                if (!$model->save(true, $saveFields)) {
                    throw new Exception('操作失败:' . current($model->getFirstErrors()));
                }
                return $this->message('操作成功', ['biz/cate'], 'success');
            } catch (Exception $e) {
                return $this->message('操作失败:' . $e->getMessage(), ['biz/cate-edit'], 'error');
            }
        }
        return $this->render('cate-edit', [
            'model' => $model,
        ]);
    }

    /**
     * 删除分类
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionCateDel($id)
    {
        $model = BizCategory::findOne($id);
        if (empty($model)) {
            return $this->message('信息不存在', ['biz/cate'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['biz/cate'], 'error');
        } else {
            return $this->message('操作成功', ['biz/cate'], 'success');
        }
    }

}
