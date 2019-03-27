<?php
/**
 * @author 河南邦耀网络科技
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * createtime: 2018/05/26 17:00
 */

namespace backend\controllers;

use backend\models\DistributionConfig;
use common\models\Goods;
use common\models\GoodsCategory;
use common\widgets\daterangepicker\DateRangePicker;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\helpers\Json;

class GoodsController extends ControllerBase
{
    /**
     * 商品列表
     * @return string
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $query = Goods::find()->alias('g')
            ->joinWith('goodsCategory c', true)
            ->where(['>', 'g.end_time', TIMESTAMP])
            ->andwhere(['or', ['and', ['!=', 'g.type', 21]], ['and', ['g.type' => 21]]]);
        $searchArr = [
            'title' => trim($request->get('title', '')),
            'type' => $request->get('type', 'all'),
            'date' => [
                'start' => date('Y-m-d 00:00', strtotime('-31 day')),
                'end' => date('Y-m-d H:i:s')
            ],
        ];
        if (is_array($searchArr['type']) && $searchArr['type'][0] != 'all') {
            $query->andWhere(['g.type' => $searchArr['type']]);
        }
        if (!empty($searchArr['title'])) {
            //$query->andWhere(['g.title' => $searchArr['title']]);     //  改成商品名称模糊查询
            $query->andWhere(['like','g.title' , $searchArr['title']]);
        }
        $date = $request->get('date', []);
        if (!empty($date)) {
            $tmp = explode(DateRangePicker::SEPARATOR, $date);
            $searchArr['date'] = [
                'start' => $tmp['0'],
                'end' => $tmp['1']
            ];
        }
        $query->andWhere(['between', 'g.created_at', strtotime($searchArr['date']['start']), strtotime($searchArr['date']['end'])]);
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
     * 过期商品列表
     * @return string
     */
    public function actionStale()
    {
        $request = \Yii::$app->request;
        $query = Goods::find()->alias('g')
            ->joinWith('goodsCategory c', true)
            ->where(['<=', 'g.end_time', TIMESTAMP]);
//            ->orwhere(['and',['!=', 'g.type', 21]]);

        $searchArr = [
            'title' => trim($request->get('title', '')),
            'type' => $request->get('type', 'all'),
            'date' => [
                'start' => date('Y-m-d 00:00', strtotime('-31 day')),
                'end' => date('Y-m-d H:i:s')
            ],
        ];
        if (is_array($searchArr['type']) && $searchArr['type'][0] != 'all') {
            $query->andWhere(['g.type' => $searchArr['type']]);
        }
        if (!empty($searchArr['title'])) {
            //$query->andWhere(['g.title' => $searchArr['title']]);
            $query->andWhere(['like','g.title' , $searchArr['title']]); //  改成标题模糊查询
        }
        $date = $request->get('date', []);
        if (!empty($date)) {
            $tmp = explode(DateRangePicker::SEPARATOR, $date);
            $searchArr['date'] = [
                'start' => $tmp['0'],
                'end' => $tmp['1']
            ];
        }
        $query->andWhere(['between', 'g.created_at', strtotime($searchArr['date']['start']), strtotime($searchArr['date']['end'])]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('stale', [
            'dataProvider' => $dataProvider,
            'searchArr' => $searchArr,
        ]);
    }

    /**
     * 删除选中商品
     * @return array
     */
    public function actionDelSelect()
    {
        $request = \Yii::$app->request;
        if ($request->isAjax) {
            $ids = $request->post('ids');
            $num = count($ids);
            if (empty($num)) {
                return $this->responseJson(1, '', '请选择要删除的记录!');
            }
            $count = Goods::deleteAll(['id' => $ids]);
            if ($count != $num) {
                return $this->responseJson(1, '', '预计删除 ' . $num . ' 条记录,实际删除 ' . $count . ' 条记录');
            }
            return $this->responseJson(0, '', '删除成功!');
        }
        exit('请求失败!');
    }

    /**
     * 添加|编辑商品
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        $request = \Yii::$app->request;
        $id = (int)$request->get('id', '');

        if (!empty($id)) {
            $model = Goods::findOne($id);
            if (empty($model)) {
                return $this->message('商品不存在', ['goods/index'], 'error');
            }
        } else {
            $model = new Goods();
            $model->loadDefaultValues();
        }

        if ($request->isPost) {
            try {
                $model->load($request->post());
//                echo '<pre>';
//                print_r($request->post());
//                exit;
                $searchArr = [
                    'date' => [
                        'start' => date('Y-m-d 00:00', strtotime('-31 day')),
                        'end' => date('Y-m-d H:i:s')
                    ],
                    ];
                $date = $request->post('date', []);
//                print_r($date);
//                exit;
                if (!empty($date)) {
                    $tmp = explode(DateRangePicker::SEPARATOR, $date);
                    $searchArr['date'] = [
                        'start' => $tmp['0'],
                        'end' => $tmp['1']
                    ];
                }

//                $model->start_time=strtotime($searchArr['date']['start']);//商品开始时间
//                $model->end_time=strtotime($searchArr['date']['end']);//商品过期时间

//                echo '<pre>';
//                print_r($request->post('name'));
//                exit;
                //todo 保存字段
                $saveFields = null;
                if (!$model->save(true, $saveFields)) {
                    throw new Exception('操作失败:' . current($model->getFirstErrors()));
                }
                return $this->message('操作成功', ['goods/index'], 'success');
            } catch (Exception $e) {
                return $this->message('操作失败:' . $e->getMessage(), ['goods/index'], 'error');
            }
        }
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * 删除商品
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDel($id)
    {
        $model = Goods::findOne($id);
        if (empty($model)) {
            return $this->message('商品不存在', ['goods/index'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['goods/index'], 'error');
        } else {
            return $this->message('操作成功', ['goods/index'], 'success');
        }
    }

    /**
     * 分类列表
     * @return string
     */
    public function actionCate()
    {
        $request = \Yii::$app->request;
        $query = GoodsCategory::find();

        $searchArr = [
            'title' => trim($request->get('title', '')),
        ];
        if (!empty($searchArr['title'])) {
            $query->Where(['LIKE', 'title', $searchArr['title']]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['sort' => SORT_DESC]],
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
        $model = GoodsCategory::initModel($id);
        if (empty($model)) {
            return $this->message('分类不存在', ['goods/cate'], 'error');
        }

        if ($request->isPost) {
            try {
                $model->load($request->post());
                //todo 保存字段
                $saveFields = null;
                if (!$model->save(true, $saveFields)) {
                    throw new Exception('操作失败:' . current($model->getFirstErrors()));
                }
                return $this->message('操作成功', ['goods/cate'], 'success');
            } catch (Exception $e) {
                return $this->message('操作失败:' . $e->getMessage(), ['goods/cate-edit'], 'error');
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
        $model = GoodsCategory::findOne($id);
        if (empty($model)) {
            return $this->message('商品分类不存在', ['goods/cate'], 'error');
        }
//        $cate=GoodsCategory::find()->select(['id'])->asArray()->all();

//        if ($id<12){
//            return $this->message('操作失败:ID 12之前不允许删除', ['goods/cate'], 'error');
//        }
//        if ($id>=12){
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['goods/cate'], 'error');
        } else {
            return $this->message('操作成功', ['goods/cate'], 'success');
        }
//        }
    }

    /**
     * 分类排序
     * @return array
     */
    public function actionCateSort()
    {
        if (\Yii::$app->request->isAjax) {
            $id = (int)\Yii::$app->request->post('id', '');
            $sort = (int)\Yii::$app->request->post('sort', '');
            if (empty($id) || $sort === '') {
                return $this->responseJson(1, '', '操作失败：参数有误');
            }

            $model = GoodsCategory::findOne(['id' => $id]);
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

    /**
     * 首页参数设置
     * @return string|\yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionIndexList()
    {
        $request = \Yii::$app->request;
        $name = $request->get('name', 'indexlist');
        $model = DistributionConfig::findByName($name);
        if (!empty($model->value)) {
            $config = Json::decode($model->value, true);
        } else {
            $config = [];
        }
        if ($request->isPost) {
            $model->value = Json::encode($request->post('config'));
            if ($model->save()) {
                $cache = \Yii::$app->cache;
                $cacheData = Json::encode($request->post('config'));
                $cache->set('cache_data_key', $cacheData, 0);
                return $this->message('更新成功！', 'referer', 'success');
            } else {
                return $this->message('更新失败！', 'referer', 'error');
            }
        }

        return $this->render('index-edit', [
            'config' => $config,
        ]);
    }
}
