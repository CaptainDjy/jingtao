<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace backend\controllers;

use common\components\robots\Robot;
use common\models\RobotHaoquan;
use yii\base\Exception;

/**
 * 好券清单采集 控制器
 * Class RobotHaoquanController
 * @package backend\controllers
 */
class RobotHaoquanController extends ControllerBase
{
    public function actionIndex()
    {
        $query = RobotHaoquan::find();
        return $this->render('index', ['query' => $query]);
    }

    public function actionUpdate()
    {
        $id = \Yii::$app->request->get('id');
        if (!empty($id)) {
            $model = RobotHaoquan::findOne($id);
            if (empty($model)) {
                return $this->message('您要编辑的采集器不存在', ['robot-haoquan/index'], 'error');
            }
        } else {
            $model = new RobotHaoquan;
            $model->loadDefaultValues();
        }

        if ($model->load(\Yii::$app->request->post())) {
            if ($model->validate() && $model->save()) {
                return $this->message('更新成功', ['robot-haoquan/index'], 'success');
            } else {
                return $this->message('更新失败: ' . current($model->getFirstErrors()), ['robot-haoquan/index'], 'error');
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
        $model = RobotHaoquan::findOne($id);
        if (empty($model)) {
            return $this->message('要删除的信息不存在', ['robot-haoquan/index'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['robot-haoquan/index'], 'error');
        } else {
            return $this->message('操作成功', ['robot-haoquan/index'], 'success');
        }

    }

    public function actionRun()
    {
        echo 111;
        exit;
        $request = \Yii::$app->request;
        $pageNum = intval($request->post('pageNum'));
        if (empty($pageNum)) {
            return $this->responseJson(1, [], '采集失败，页码不能为空！');
        }
        $num = intval($request->post('num'));

        try {
            $robots = new Robot();
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
