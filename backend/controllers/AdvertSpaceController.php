<?php
/**
 * @author pine
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * createtime: 2018/05/26 17:00
 */

namespace backend\controllers;

use common\models\AdvertSpace;
use yii\db\Exception;

class AdvertSpaceController extends ControllerBase
{
    protected $uploadSuccessPath;

    /**
     * 首页
     * @return string
     */
    public function actionIndex()
    {
        $searchArr = [
            'keywords' => '',
        ];
        $query = AdvertSpace::find();
        $request = \Yii::$app->request;
        $keywords = $request->get('keywords', '');
        $keywords = trim($keywords);
        if (!empty($keywords)) {
            $searchArr['keywords'] = $keywords;
            $query->andWhere(['or', ['uid' => $keywords], ['pid' => $keywords]]);
        }
        $type = $request->get('type');
        if (!empty($type)) {
            $query->andWhere(['type' => $type]);
        }

        return $this->render('index', ['query' => $query, 'searchArr' => $searchArr, 'type' => $type]);
    }

    /**
     * 更新
     * @return string|\yii\web\Response
     */
    public function actionUpdate()
    {
        $model = new AdvertSpace();
        $id = intval(\Yii::$app->request->get('id'));
        if (!empty($id)) {
            $model = $model::findOne($id);
            if (empty($model)) {
                return $this->message('您要编辑的信息不存在', ['advert-space/index'], 'error');
            }
        } else {
            $model = $model->loadDefaultValues();
        }
        $model->scenario = 'update';
        if ($model->load(\Yii::$app->request->post())) {
            if ($model->validate() && $model->save()) {
                return $this->message('编辑成功', ['advert-space/index'], 'success');
            } else {
                return $this->message('编辑失败', ['advert-space/index'], 'error');
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * 删除
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        $id = \Yii::$app->request->get('id');
        $model = AdvertSpace::findOne($id);
        if (empty($model)) {
            return $this->message('改推广位不存在', ['advert-space/index'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('删除失败!', ['advert-space/index'], 'error');
        } else {
            return $this->message('删除成功!', ['advert-space/index'], 'success');
        }
    }


    /**
     * 推广位创建
     * @return array|string
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $request = \Yii::$app->request;
        $type = $request->get('type');

        if ($request->isPost) {
            $total = $request->post('total', 0);
            $siteId = $request->post('siteId', '');
            $cookie = $request->post('cookie', '');
            $cur = $request->post('cur', 0);
            $total = intval($total);
            if (empty($total)) {
                return $this->responseJson(1, '', '创建失败，数量不能为空!');
            }
            if ($cur >= $total) {
                return $this->responseJson(0, ['total' => $total, 'cur' => $cur], '创建成功!');
            }
            if (!in_array($type, [1, 2, 3])) {
                return $this->responseJson(1, '', '创建类型不存在');
            }

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if ($type == '1') {
                    $cur = AdvertSpace::createTb($total, $cur, $siteId, $cookie);
                } else if ($type == '2') {
                    $cur = AdvertSpace::createJd($total, $cur, $cookie);
                } else if ($type == '3') {
                    $cur = AdvertSpace::createPdd($total, $cur);
                }

                $transaction->commit();
                return $this->responseJson(0, ['total' => $total, 'cur' => $cur], '正在创建');

                    } catch (Exception $e) {
                $transaction->rollBack();
                return $this->responseJson(1, [], $e->getMessage());
            } catch (\Exception $e) {
                $transaction->rollBack();
                return $this->responseJson(1, $e->getMessage(), '推广位创建失败，可能太频繁了，请稍等10S');
            }
        }

        return $this->render('create', ['type' => $type]);
    }
}
