<?php


namespace backend\controllers;


use backend\models\Recommend;
use common\models\Region;
use common\models\User;
use common\widgets\daterangepicker\DateRangePicker;
use Yii;
use yii\base\Exception;
use yii\web\Response;
use backend\models\Cooperation;
class UserController extends ControllerBase
{

    /**
     * 三级联动
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        $actions['get-region'] = [
            'class' => \chenkby\region\RegionAction::className(),
            'model' => Region::class
        ];
        return $actions;
    }

    public function actionCooperation(){
        $cooper=Cooperation::find()->asArray()->one();
//        print_r($a);

        return $this->render('cooperation', ['cooperation'=>$cooper]);
    }

//会员等级设置
    public  function actionLv(){
        if (Yii::$app->request->post()){
            $sole=Yii::$app->request->post('sole');
            $test=Recommend::find()->where(['id'=>1])->one();
            $test->sole=$sole;
            $test->save();

            if($sole && $test->validate()){
                return $this->message('更新成功', ['user/lv'], 'success');
            }else{
                return $this->message('更新失败,内容为整数且不能为空', ['user/lv'], 'error');
            }

        }else{
          $test=Recommend::find()->where(['id'=>1])->asArray()->one();
        $sole=$test['sole'];
            return $this->render('lv', ['sole' => $sole]);
        }
    }


    /**
     * 会员列表
     * @return string
     */
    public function actionList()
    {
        $searchArr = ['keywords' => '', 'date' => ['start' => date('2017-01-01 00:00'), 'end' => date('Y-m-d H:i')], 'report_center' => ''];
        $query = User::find()->andWhere('status != 8');
        $request = Yii::$app->request;
        $keywords = $request->get('keywords', '');
        $keywords = trim($keywords);
        if (!empty($keywords)) {
            $searchArr['keywords'] = $keywords;
            $query->andWhere("uid = '{$keywords}' OR nickname LIKE '%{$keywords}%' OR realname LIKE '%{$keywords}%'  OR mobile LIKE '{$keywords}%' ");
        }

        $date = $request->get('date', []);
        if (!empty($date)) {
            $tmp = explode(DateRangePicker::SEPARATOR, $date);
            $searchArr['date'] = ['start' => $tmp['0'], 'end' => $tmp['1']];
            $query->andWhere(['between', 'created_at', strtotime($tmp['0']), strtotime($tmp['1'])]);
        }
        $type = $request->get('report_center', '');
        if (!empty($type)) {
            $searchArr['report_center'] = $type;
            $query->andWhere(['report_center' => $type]);
        }
//        if ($request->get('op') == 'export') {
//            return Excel::export([
//                'models' => $query->all(),
//                'fileName' => '会员' . date('YmdHs') . '.xlsx',
//                'headers' => ['gender' => '性别', 'created_at' => '注册时间', 'status' => '账户状态'],
//                'columns' => ['uid:text:用户编号', 'nickname', 'lv2', 'volunteer', 'partner', 'realname', 'mobile', 'qq', ['attribute' => 'gender', 'value' => function ($model) {
//                    return $model['gender'] == 1 ? '男' : '女';
//                },], ['attribute' => 'status', 'value' => function ($model) {
//                    return $model['status'] == 0 ? '正常' : '冻结';
//                },], ['attribute' => 'created_at', 'format' => ['date', 'php:Y-m-d H:i:s']],],
//            ]);
//        }
        $today = date("Y-m-d", time());
        $yesday = date("Y-m-d", strtotime("-1 day"));
        $count = User::find()->andWhere('status != 8')->count(1);
        $todaycount = User::find()->andWhere('status != 8')->where(["FROM_UNIXTIME(created_at, '%Y-%m-%d')" => $today])->count(1);
        $yesdaycount = User::find()->andWhere('status != 8')->where(["FROM_UNIXTIME(created_at, '%Y-%m-%d')" => $yesday])->count(1);

        $query->orderBy("uid desc");
        $this->view->title = '会员管理';
        //echo json_encode(['query' => $query, 'searchArr' => $searchArr, 'count' => $count, 'todaycount' => $todaycount, 'yesdaycount' => $yesdaycount]);die();
        return $this->render('list', ['query' => $query, 'searchArr' => $searchArr, 'count' => $count, 'todaycount' => $todaycount, 'yesdaycount' => $yesdaycount]);
    }

    /**
     * 添加/修改会员
     * @return string|Response
     * @throws Exception
     */
    public function actionUpdate()
    {
        $models = new User();
        $uid = intval(Yii::$app->request->get('id'));
        if (!empty($uid)) {
            $model = $models::findOne($uid);
            if (empty($model)) {
                return $this->message('您要编辑的用户不存在', ['user/list'], 'error');
            }
            $this->view->title = '编辑会员资料';
        } else {
            $model = $models->loadDefaultValues();
            $model->auth_key = Yii::$app->security->generateRandomString();
            $this->view->title = '添加会员';
        }
        if ($model->load(Yii::$app->request->post(), "")) {
            $data = Yii::$app->request->post()['User'];

            $mobile = $data['mobile'];

            if (empty($uid)) {
                $mobile = $_POST['User']['mobile'];
                $userInfo = User::findByMobile($mobile);
                if (!empty($userInfo)) {
                    return $this->message('添加失败: ' . '手机号已存在', ['user/list'], 'error');
                }
                $model->mobile = $mobile;
                $model->password_hash = Yii::$app->security->generatePasswordHash($_POST['User']['password_hash'], 6);
            }
//            $model->realname = $_POST['User']['realname'];
            $model->nickname = $_POST['User']['nickname'];
            $model->lv = $_POST['User']['lv'];
            if ($model->password_hash != $_POST['User']['password_hash']) {
                $model->password_hash = Yii::$app->security->generatePasswordHash($_POST['User']['password_hash'], 6);
            }
            if ($model->validate() && $model->save()) {
                return $this->message('更新成功', ['user/list'], 'success');
            } else {
                return $this->message('更新失败: ' . current($model->getFirstErrors()), ['user/list'], 'error');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * 会员完全删除
     * @return Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        $uid = Yii::$app->request->get('id');
        $model = User::findOne($uid)->delete();
        if ($model > 0) {
            return $this->message('删除成功', ['user/list'], 'success');
        } else {
            return $this->message('删除失败', ['user/list'], 'error');
        }
    }

    /**
     * 关系列表
     * @return string
     */
    public function actionRelationList()
    {
        $this->view->title = '关系列表';
        return $this->render('relation-list');
    }

    /**
     * 查找下级
     * @param $data
     * @return array
     */
    public function disTree($data)
    {
        $tree = array();
        foreach ($data as $item) {
            $pid = 0;
            if ($item['superior'] !== "0") {
                preg_match('/^\d*_/', $item['superior'], $matches);
                if (!empty($matches)) {
                    $pid = trim($matches[0], "_");
                }
            }
            if (!empty($data[$pid])) {
                $data[$pid]['children'][] = &$data[$item['id']];
            } else {
                $tree[] = &$data[$item['id']];
            }
        }
        return $tree;
    }

    /**
     * 关系列表
     * @return array
     */
    public function actionRelationLists()
    {
        $id = Yii::$app->request->get("id");
        $data = $userData = array();
        if (isset($id)) {
            switch (Yii::$app->request->get('types')) {
                case "get_node":
                    $temp = User::find()->select(['uid', 'mobile', 'nickname', 'realname', 'superior'])->where(['status' => 0])->asArray()->all();
                    foreach ($temp as $key => $val) {
                        $val["text"] = "<span id='{$val['nickname']}'>" . $val['realname'] . "</span><e style='color:#aaa;font-size:15px' id='{$val['uid']}'>&lt;UID" . $val['uid'] . "&gt;</e>";
                        $val["id"] = $val['uid'];
                        $userData[$val['uid']] = $val;
                    }
                    $data[] = array('id' => "0", 'text' => "总店", "state" => array("opened" => true), 'children' => $this->disTree($userData), "type" => "root");
                    break;
                case "move_node":
                    $id = intval(Yii::$app->request->get("id"));
                    $parent = intval(Yii::$app->request->get("parent"));
                    if (!empty($id)) {
                        $relation = User::find()->select("superior")->where(["uid" => $parent])->asArray()->one();
                        $user_model = User::findOne(["uid" => $id]);
                        $user_model->superior = trim($parent . "_" . $relation['superior'], "_");
                        $user_model->save();
                        $userList = User::find()->select(["uid", "superior"])->where("superior like '{$id}_%' or superior REGEXP '[0-9]_{$id}_'")->asArray()->all();
                        if (!empty($userList)) {
                            foreach ($userList as $k => $v) {
                                $rule = "/(\b" . $id . "\b\-)(.*)/";
                                $s = $parent . "_" . $relation['superior'];
                                $rela = preg_replace($rule, '${1}' . $s, $v["superior"]);
                                if ($v['uid'] > 0) {
                                    $model = User::findOne(["uid" => $v['uid']]);
                                    $model->superior = trim($rela, "_");
                                    $model->save();
                                }
                            }
                        }
                        break;
                    }
                    break;
            }
        }
        $temp = User::find()->select(['uid', 'mobile', 'nickname',  'superior', 'created_at'])->where(['status' => 0])->asArray()->all();
        foreach ($temp as $key => $val) {
            $val["text"] = "<span id='{$val['nickname']}'>" . $val['nickname'] . "</span><e style='color:#aaa;font-size:15px' id='{$val['uid']}'>&lt;UID" . $val['uid'] . "&gt;</e>" . '联系电话:<e style=\'color:#bb5c86;font-size:15px\'>&lt;' . $val['mobile'] . '&gt;</e>' . '注册时间:<e style=\'color:#bb5c86;font-size:15px\'>&lt;' . date("Y-m-d H:i:s", $val['created_at']) . '&gt;</e>';
            $val["id"] = $val['uid'];
            $userData[$val['uid']] = $val;
        }
        $data = $this->disTree($userData);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $data;
    }

}
