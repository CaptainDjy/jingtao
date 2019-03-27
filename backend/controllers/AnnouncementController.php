<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/28 20:58
 */

namespace backend\controllers;

use common\models\Announcement;


use common\models\Config;
use yii;

class AnnouncementController extends ControllerBase
{
    /**
     * 系统公告列表
     * @return string
     */
    public function actionSystem()
    {
        $condition = 'type = 1';
        $keywords = Yii::$app->request->get('keywords', '');
        $keywords = trim($keywords);
        if (!empty($keywords)) {
            $condition .= " AND (id = '{$keywords}' OR title LIKE '%{$keywords}%')";
        }
        $query = Announcement::find()->where($condition);
        $this->view->title = '系统公告';
        return $this->render('system', ['query' => $query, 'keywords' => $keywords]);
    }

    /**
     * 编辑公告/添加公告
     * @return string|yii\web\Response
     */
    public function actionUpdate()
    {
        $id = intval(Yii::$app->request->get('id'));
        if (!empty($id)) {
            $item = Announcement::findOne($id);
            if (empty($item)) {
                return $this->message('您要编辑的系统公告不存在', ['announcement/system'], 'error');
            }
        } else {
            $item = new Announcement();
            $item->loadDefaultValues();
        }
        if ($item->load(Yii::$app->request->post())) {
            $item->type = 1;
            if ($item->validate() && $item->save()) {
                return $this->message('系统公告修改成功', ['announcement/system'], 'success');
            } else {
                return $this->message('系统公告修改失败', ['announcement/system'], 'error');
            }
        }
        return $this->render('update', ['item' => $item]);
    }

    /**
     * 删除公告
     * @param $id
     * @return yii\web\Response
     * @throws \Throwable
     * @throws yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $item = Announcement::findOne($id)->delete();
        if ($item) {
            return $this->message('操作成功', ['announcement/system'], 'success');
        } else {
            return $this->message('操作失败', ['announcement/system'], 'error');
        }
    }

}
