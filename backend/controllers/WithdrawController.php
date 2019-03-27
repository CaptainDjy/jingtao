<?php

namespace backend\controllers;

use api\models\User;
use common\models\Withdraw;
use api\modules\amoy\controllers\UserController;
use common\widgets\daterangepicker\DateRangePicker;
use Yansongda\Pay\Exceptions\GatewayException;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii;
use common\models\Config;
/**
 * 提现审核
 * Class WithdrawController
 * @package backend\controllers
 */
class WithdrawController extends ControllerBase
{
    /**
     * 提现申请
     * @return string
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $query = Withdraw::find()->where(['status' => 0]);

        $searchArr = [
            'sn' => trim($request->get('sn', null)),
            'uid' => (int)$request->get('uid', null),
            'date' => [
                'start' => date('Y-m-d 00:00', strtotime('-31 day')),
                'end' => date('Y-m-d H:i:s')
            ],
        ];
        if (!empty($searchArr['sn'])) {
            $query->where(['trade_sn' => $searchArr['sn']]);
        }
        if (!empty($searchArr['uid']) && $searchArr['uid'] > 0) {
            $query->andWhere(['uid' => $searchArr['uid']]);
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
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        //var_dump($query->createCommand()->getRawSql());return;
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchArr' => $searchArr,
        ]);
    }

    //支付宝提现 转账
    public function actionWithzz($id,$account,$amount){
//        $account=\yii::$app->request->post('account');
//        $amount=\yii::$app->request->post('amount');

        $appid=config::getConfig('ALIPAY_APP_ID');
        $pubkey=config::getConfig('ALIPAY_PUB_KEY');
        $prikey=config::getConfig('ALIPAY_PRIV_KEY');
        $config = [
            'app_id' => $appid,
            'notify_url' => 'http://yansongda.cn/notify.php',
            'return_url' => 'http://yansongda.cn/return.php',
            //公钥
            'ali_public_key' => $pubkey,
            // 加密方式： **RSA2**
            //私钥
            'private_key' => $prikey,
            'log' => [ // optional
                'file' => './logs/alipay.log',
                'level' => 'info', // 建议生产环境等级调整为info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
        ];
        $order = [
            'out_biz_no' => time(),
            'payee_type' => 'ALIPAY_LOGONID',
            'payee_account' => $account,//'15312639801',//转账账户,
            'amount' => $amount,//$amount,//转账金额
        ];

        try{
            $result = \Yansongda\Pay\Pay::alipay($config)->transfer($order);//对,就是这么简单
        }catch (GatewayException $e){
            return $this->message('操作失败:' . $e->getMessage(), ['withdraw/index'], 'error');
        }

       if ($result){
           $with=withdraw::find()->where(['id'=>$id])->one();
           $with->status=2;//转账成功修改状态
           $with->save();
           $uid=withdraw::find()->where(['id'=>$id])->asArray()->one();
           $credit4=User::find()->where(['uid'=>$uid['uid']])->asArray()->one();
           $user=User::find()->where(['uid'=>$uid['uid']])->one();
           $user->credit4=$credit4['credit4']-$amount;//转账成功后总金额扣除
           $user->save();
           return $this->message('操作成功', ['withdraw/index'], 'success');
       }

    }

    /**
     * 审核通过
     * @return string
     */
    public function actionRatify()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id', null);
        if (empty($id)) {
            return $this->message('参数有误', ['withdraw/index'], 'error');
        }
        $model = Withdraw::findOne(['id' => $id, 'status' => Withdraw::STATUS_DEFAULT]);
        if (empty($model)) {
            return $this->message('信息不存在', ['withdraw/index'], 'error');
        }
        $withdraw=Withdraw::find()->where(['id'=>$id])->asArray()->one();
        $account=$withdraw['pay_to'];
        $amount=$withdraw['amount'];
//        echo '<pre>';
////        print_r($withdraw);
////        exit;
        $this->actionWithzz($id,$account,$amount);//审核通过直接提现

//        $transaction = \Yii::$app->db->beginTransaction();
//        try {
//            Withdraw::withdraw($model);
//            $transaction->commit();
//        } catch (Exception $e) {
//            $transaction->rollBack();
//            $model->updateAttributes(['status' => Withdraw::STATUS_FAIL, 'msg' => $e->getMessage()]);
//            Withdraw::updateMoney($model, false);
//
//            return $this->message('操作失败:' . $e->getMessage(), ['withdraw/index'], 'error');
//        }
//        return $this->message('操作成功', ['withdraw/index'], 'success');
    }

    /**
     * 审核拒绝
     * @return string
     */
    public function actionRefuse()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id', null);

        if (empty($id)) {
            return $this->message('参数有误', ['withdraw/index'], 'error');
        }
        $model = Withdraw::findOne(['id' => $id]);
        if (empty($model)) {
            return $this->message('信息不存在', ['withdraw/index'], 'error');
        }

        $withdraw=Withdraw::find()->where(['id'=>$id])->one();
        $withdraw->status=-1;
        $withdraw->save();
//        $account=$withdraw['pay_to'];
//        $amount=$withdraw['amount'];

//        $transaction = \Yii::$app->db->beginTransaction();
//        try {
//            Withdraw::Refuse($model);
//            $transaction->commit();
//        } catch (Exception $e) {
//            $transaction->rollBack();
//            return $this->message('操作失败:' . $e->getMessage(), ['withdraw/index'], 'error');
//        }
        return $this->message('已被拒绝', ['withdraw/index'], 'success');
    }

    /**
     * 备注
     * @return string
     */
    // public function actionRemark()
    // {
    //     $request = \Yii::$app->request;
    //     $id = $request->get('id', null);
    //
    //     if (empty($id)) {
    //         return $this->message('参数有误', ['withdraw/index'], 'error');
    //     }
    //     $model = Withdraw::findOne(['id' => $id]);
    //     if (empty($model)) {
    //         return $this->message('信息不存在', ['withdraw/index'], 'error');
    //     }
    //     if ($request->isPost) {
    //         $model->remark = $request->post('remark', null);
    //         if (!$model->save()) {
    //             return $this->message('操作失败', ['withdraw/index'], 'error');
    //         }
    //     }
    //     return $this->renderAjax('remark', ['model' => $model]);
    // }

}