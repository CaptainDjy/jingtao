<?php

namespace backend\controllers;

use common\components\robots\JdRobot;
use common\models\RobotJd;
use yii\base\Exception;

/**
 * 京东采集 控制器
 * Class RobotJdController
 * @package backend\controllers
 */
class RobotJdController extends ControllerBase
{
    public $enableCsrfValidation = false;
    /**
     * @return string
     */
    public function actionIndex()
    {
        $query = RobotJd::find();
        return $this->render('index', ['query' => $query]);

    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionUpdate()
    {
        $id = \Yii::$app->request->get('id');
        if (!empty($id)) {
            $model = RobotJd::findOne($id);
            if (empty($model)) {
                return $this->message('您要编辑的采集器不存在', ['robot-jd/index'], 'error');
            }
        } else {
            $model = new RobotJd;
            $model->loadDefaultValues();
        }

        if ($model->load(\Yii::$app->request->post())) {
            if ($model->validate() && $model->save()) {
                return $this->message('更新成功', ['robot-jd/index'], 'success');
            } else {
                return $this->message('更新失败: ' . current($model->getFirstErrors()), ['robot-jd/index'], 'error');
            }
        }

        return $this->render('update', ['model' => $model]);

    }

    /**
     * 删除
     * @param int $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id = 0)
    {
        $model = RobotJd::findOne($id);
        if (empty($model)) {
            return $this->message('要删除的信息不存在', ['robot-jd/index'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['robot-jd/index'], 'error');
        } else {
            return $this->message('操作成功', ['robot-jd/index'], 'success');
        }

    }

    /**
     * 一键采集方法
     * @return array
     */
    public function actionRun()
    {
        $request = \Yii::$app->request;
        $pageNum = intval($request->post('pageNum'));
        if (empty($pageNum)) {
            return $this->responseJson(1, [], '采集失败，页码不能为空！');
        }
        $num = intval($request->post('num'));

        try {
            $robots = new JdRobot();
            $robots->pageNum = $pageNum;
            $result = $robots->run();
            $total = $num + $robots->num;
            if ($result === false) {
                return $this->responseJson(1, ['pageNum' => $robots->pageNum, 'num' => $total], '采集完成，请在商品列表中查看：采集页数' . $robots->pageNum . '采集产品' . $total);
            } else {
                return $this->responseJson(0, ['pageNum' => $robots->pageNum, 'num' => $total], '已采集' . $robots->pageNum . '页, 目前共采集到商品' . $total . '件');
            }
        } catch (Exception $e) {
            return $this->responseJson(1, [], '采集失败: ' . $e->getMessage() . '，当前页数' . $robots->pageNum);
        }
    }

}
