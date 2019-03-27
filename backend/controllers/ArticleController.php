<?php

namespace backend\controllers;

use common\models\Article;
use common\models\ArticleCategory;
use common\widgets\daterangepicker\DateRangePicker;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use backend\models\ArticleForm;
use yii;
class ArticleController extends ControllerBase
{
    public $title = '文章管理';

    /**
     * 列表
     * @return string
     */
    public function actionForm()
    {
        $model = new ArticleForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // 验证 $model 收到的数据
            // 做些有意义的事 ...
//             print_r(Yii::$app->request->post());exit;
            return $this->render('article-confirm', ['model' => $model]);
        } else {
//            // 无论是初始化显示还是数据验证错误
            return $this->render('article-form', ['model' => $model]);
        }
    }
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $query = Article::find()->alias('a')->joinWith('articleCategory c');

        $searchArr = [
            'title' => trim($request->get('title', '')),
        ];
        if (!empty($searchArr['title'])) {
            //$query->Where(['a.title' => $searchArr['title']]);
            $query->Where(['like','a.title' , $searchArr['title']]);
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
        $model = Article::initModel($id);
        if (empty($model)) {
            return $this->message('文章不存在', ['article/index'], 'error');
        }

        if ($request->isPost) {
            try {
                $model->load($request->post());
                //todo 保存字段
                $saveFields = null;
                if (!$model->save(true, $saveFields)) {
                    throw new Exception('操作失败:' . current($model->getFirstErrors()));
                }
                return $this->message('操作成功', ['article/index'], 'success');
            } catch (Exception $e) {
                return $this->message('操作失败:' . $e->getMessage(), ['article/index'], 'error');
            }
        }

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
        $model = Article::findOne($id);
        if (empty($model)) {
            return $this->message('文章不存在', ['article/index'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['article/index'], 'error');
        } else {
            return $this->message('操作成功', ['article/index'], 'success');
        }
    }

    /**
     * 分类
     * @return string
     */
    public function actionCate()
    {
        $request = \Yii::$app->request;
        $query = ArticleCategory::find();

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
        $model = ArticleCategory::initModel($id);
        if (empty($model)) {
            return $this->message('分类不存在', ['article/cate'], 'error');
        }

        if ($request->isPost) {
            try {
                $model->load($request->post());
                //todo 保存字段
                $saveFields = null;
                if (!$model->save(true, $saveFields)) {
                    throw new Exception('操作失败:' . current($model->getFirstErrors()));
                }
                return $this->message('操作成功', ['article/cate'], 'success');
            } catch (Exception $e) {
                return $this->message('操作失败:' . $e->getMessage(), ['article/cate-edit'], 'error');
            }
        }

        $this->view->title = $this->title;
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
        $model = ArticleCategory::findOne($id);
        if (empty($model)) {
            return $this->message('分类不存在', ['article/cate'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['article/cate'], 'error');
        } else {
            return $this->message('操作成功', ['article/cate'], 'success');
        }
    }
}
