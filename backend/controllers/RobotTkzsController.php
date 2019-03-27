<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace backend\controllers;

use common\components\robots\TkzsRobot;
use common\models\RobotTkzs;
use yii\base\Exception;

/**
 * 好单库采集 控制器
 * Class RobotsDataokeController
 * @package backend\controllers
 */
class RobotTkzsController extends ControllerBase
{

    public function actionIndex()
    {
        $query = RobotTkzs::find();
        return $this->render('index', ['query' => $query]);

    }

    public function actionUpdate()
    {
        $id = \Yii::$app->request->get('id');
        if (!empty($id)) {
            $model = RobotTkzs::findOne($id);
            if (empty($model)) {
                return $this->message('您要编辑的采集器不存在', ['robot-tkzs/index'], 'error');
            }
        } else {
            $model = new RobotTkzs();
            $model->loadDefaultValues();
        }

        if ($model->load(\Yii::$app->request->post())) {
            if ($model->validate() && $model->save()) {
                return $this->message('更新成功', ['robot-tkzs/index'], 'success');
            } else {
                return $this->message('更新失败: ' . current($model->getFirstErrors()), ['robot-tkzs/index'], 'error');
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
        $model = RobotTkzs::findOne($id);
        if (empty($model)) {
            return $this->message('要删除的信息不存在', ['robot-tkzs/index'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['robot-tkzs/index'], 'error');
        } else {
            return $this->message('操作成功', ['robot-tkzs/index'], 'success');
        }

    }

    public function actionRun()
    {
        $request = \Yii::$app->request;
        $pageNum = intval($request->post('pageNum'));
        if(empty($pageNum)) {
            return $this->responseJson(1, [], '采集失败，页码不能为空！');
        }
        $num = intval($request->post('num'));

        try {
            $robots = new TkzsRobot();
            $robots->pageNum = $pageNum;
            $result = $robots->run();
            $total = $num + $robots->num;
            if ($result === false) {
                return $this->responseJson(1, ['pageNum' => $robots->pageNum, 'num' => $total], "采集完成，请在商品列表中查看：\r\n采集页数:" . $robots->pageNum ."\r\n采集产品:" . $total);
            } else {
                return $this->responseJson(0, ['pageNum' => $robots->pageNum, 'num' => $total], /*'已采集' . $robots->pageNum .'页,*/ '目前共采集到商品' . $total . '件');
            }
        } catch (Exception $e) {
            return $this->responseJson(1, [], '采集失败: ' . $e->getMessage() . '，当前页数' . $pageNum);
        }
    }

}
