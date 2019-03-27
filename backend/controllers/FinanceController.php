<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/11
 * Time: 14:32
 */

namespace backend\controllers;


use common\models\Order;
use common\models\Recharge;
use common\models\UpgradeOrder;
use common\widgets\daterangepicker\DateRangePicker;
use yii\data\ActiveDataProvider;
use common\models\Commissionset;
/**
 * 财务统计
 * Class FinanceController
 * @package backend\controllers
 */
class FinanceController extends ControllerBase
{
    public function actionDel($id)
    {
        $model = \api\models\Order::findOne($id);
        if (empty($model)) {
            return $this->message('订单不存在', ['finance/order'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['finance/order'], 'error');
        } else {
            return $this->message('操作成功', ['finance/order'], 'success');
        }
    }

    public function actionDelYj($id)
    {
        $model = \api\models\Order::findOne($id);
        if (empty($model)) {
            return $this->message('订单不存在', ['finance/index'], 'error');
        }
        if (!$model->delete()) {
            return $this->message('操作失败:' . $model->getFirstErrors(), ['finance/index'], 'error');
        } else {
            return $this->message('操作成功', ['finance/index'], 'success');
        }
    }
    /**
     * 佣金记录
     * @return string
     */
//    public function actionIndex()
//    {
//        $request = \Yii::$app->request;
//        $query = Recharge::find();
//
//        $searchArr = [
//            'name' => trim($request->get('name', '')),
//            'date' => [
//                'start' => date('Y-m-d 00:00', strtotime('-31 day')),
//                'end' => date('Y-m-d H:i:s')
//            ],
//        ];
//        if (!empty($searchArr['name'])) {
//            //$query->where(['or', ['like', 'from_uid', $searchArr['name']], ['like', 'to_uid', $searchArr['name']],]);
//            $query->where(['or', ['uid' => $searchArr['name']]]);
//        }
//        $date = $request->get('date', []);
//        if (!empty($date)) {
//            $tmp = explode(DateRangePicker::SEPARATOR, $date);
//            $searchArr['date'] = [
//                'start' => $tmp['0'],
//                'end' => $tmp['1']
//            ];
//        }
//        $query->andWhere(['between', 'created_at', strtotime($searchArr['date']['start']), strtotime($searchArr['date']['end'])]);
//
//        $dataProvider = new ActiveDataProvider([
//            'query' => $query,
//            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
//            'pagination' => [
//                'pageSize' => 20,
//            ],
//        ]);
//
//        return $this->render('index', [
//            'dataProvider' => $dataProvider,
//            'searchArr' => $searchArr,
//        ]);
//    }
//佣金列表
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $query = Order::find();

        $searchArr = [
            'name' => trim($request->get('name', '')),
            'date' => [
                'start' => date('Y-m-d 00:00', strtotime('-31 day')),
                'end' => date('Y-m-d H:i:s')
            ],
        ];
        if (!empty($searchArr['name'])) {
            //$query->where(['or', ['like', 'from_uid', $searchArr['name']], ['like', 'to_uid', $searchArr['name']],]);
            $query->where(['or', ['uid' => $searchArr['name']]]);
        }
        $date = $request->get('date', []);
        if (!empty($date)) {
            $tmp = explode(DateRangePicker::SEPARATOR, $date);
            $searchArr['date'] = [
                'start' => $tmp['0'],
                'end' => $tmp['1']
            ];
        }

        $query->andWhere(['between', 'created_at', strtotime($searchArr['date']['start']), strtotime($searchArr['date']['end'])]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
//        echo '<pre>';
//        print_r($dataProvider);
//        exit;
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchArr' => $searchArr,
        ]);
    }

    /**
     * 佣金设置
     * @return string
     */
    public function actionCommission(){
        $request = \Yii::$app->request;
        $query = Recharge::find();

        $searchArr = [
            'name' => trim($request->get('name', '')),
            'date' => [
                'start' => date('Y-m-d 00:00', strtotime('-31 day')),
                'end' => date('Y-m-d H:i:s')
            ],
        ];
        if (!empty($searchArr['name'])) {
            //$query->where(['or', ['like', 'from_uid', $searchArr['name']], ['like', 'to_uid', $searchArr['name']],]);
            $query->where(['or', ['uid' => $searchArr['name']]]);
        }
        $date = $request->get('date', []);
        if (!empty($date)) {
            $tmp = explode(DateRangePicker::SEPARATOR, $date);
            $searchArr['date'] = [
                'start' => $tmp['0'],
                'end' => $tmp['1']
            ];
        }
        $query->andWhere(['between', 'created_at', strtotime($searchArr['date']['start']), strtotime($searchArr['date']['end'])]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $config=Commissionset::find()->where(['id'=>1])->asArray()->one();
        return $this->render('commission',$config);
    }

    public function actionAas(){
        $request = \Yii::$app->request;
        $detain=$request->post('rzh');//平台比率
        $stair=$request->post('stair');
        $second=$request->post('second');
        $threelevel=$request->post('threelevel');
        $zghy=$request->post('zghy');
        $zgdl=$request->post('zgdl');
        $zgzd=$request->post('zgzd');

        $szh=Commissionset::find()->where(['id'=>1])->one();
        if (empty($szh)){
            $szh=new Commissionset();
            $szh->detain=$detain;//平台扣留
            $szh->stair=$stair;//一级
            $szh->second=$second;//二级
            $szh->threelevel=$threelevel;//三级
            $szh->zghy=$zghy;
            $szh->zgdl=$zgdl;
            $szh->zgzd=$zgzd;
            $szh->save();
            $config=Commissionset::find()->where(['id'=>1])->asArray()->one();
            return $this->render('commission',$config);
        }else{
            $szh->detain=$detain;//平台扣留
            $szh->stair=$stair;//一级
            $szh->second=$second;//二级
            $szh->threelevel=$threelevel;//三级
            $szh->zghy=$zghy;
            $szh->zgdl=$zgdl;
            $szh->zgzd=$zgzd;
            $szh->save();
            $config=Commissionset::find()->where(['id'=>1])->asArray()->one();
            return $this->render('commission',$config);
        }

    }
    /**
     * 订单列表
     * @return string
     */
    public function actionOrder()
    {
        $request = \Yii::$app->request;
        $query = Order::find();
//        print_r($query);
//        exit;

        $searchArr = [
            'name' => trim($request->get('name', '')),
            'date' => [
                'start' => date('Y-m-d 00:00', strtotime('-7 day')),
                'end' => date('Y-m-d H:i')
            ],
        ];
        if (!empty($searchArr['name'])) {
            //$query->where(['or', ['like', 'from_uid', $searchArr['name']], ['like', 'to_uid', $searchArr['name']],]);
            $query->where(['or', ['uid' => $searchArr['name']]]);
        }
        $date = $request->get('date', []);
        if (!empty($date)) {
            $tmp = explode(DateRangePicker::SEPARATOR, $date);
            $searchArr['date'] = [
                'start' => $tmp['0'],
                'end' => $tmp['1']
            ];
        }
        $query->andWhere(['between', 'created_at', strtotime($searchArr['date']['start']), strtotime($searchArr['date']['end'])]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('order', [
            'dataProvider' => $dataProvider,
            'searchArr' => $searchArr,
        ]);
    }


    /**
     * 充值列表
     * @return string
     */
    public function actionRecharge()
    {
        $request = \Yii::$app->request;
        $query = UpgradeOrder::find()->where(['status' => 1]);

        $searchArr = [
            'name' => trim($request->get('name', '')),
            'date' => [
                'start' => date('Y-m-d 00:00', strtotime('-7 day')),
                'end' => date('Y-m-d H:i')
            ],
        ];
        if (!empty($searchArr['name'])) {
            //$query->where(['or', ['like', 'from_uid', $searchArr['name']], ['like', 'to_uid', $searchArr['name']],]);
            $query->where(['or', ['like', 'uid', $searchArr['name']]]);
        }
        $date = $request->get('date', []);
        if (!empty($date)) {
            $tmp = explode(DateRangePicker::SEPARATOR, $date);
            $searchArr['date'] = [
                'start' => $tmp['0'],
                'end' => $tmp['1']
            ];
        }
        $query->andWhere(['between', 'created_at', strtotime($searchArr['date']['start']), strtotime($searchArr['date']['end'])]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('recharge', [
            'dataProvider' => $dataProvider,
            'searchArr' => $searchArr,
        ]);
    }
}