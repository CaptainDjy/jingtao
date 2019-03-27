<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/4/28
 * Time: 17:31
 */

namespace api\modules\amoy\controllers;


use common\models\Recharge;
use common\models\Cooperationuser;
use common\models\User;
class SearchController extends ControllerBase
{
    /**
     * 淘宝客订单查询
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTbkOrder()
    {
        $time = \Yii::$app->request->post('time');
        $data = [
            'method' => 'taobao.tbk.order.get',
            'fields' => 'tb_trade_parent_id,tb_trade_id,num_iid,item_title,item_num,price,pay_price,seller_nick,seller_shop_title,commission,commission_rate,unid,create_time,earning_time,tk3rd_pub_id,tk3rd_site_id,tk3rd_adzone_id',
            'start_time' => $time,
            'span' => 600,
        ];
        $result = $this->getUrlResult($data);
//        print_r($result);
//        exit;
        if (empty($result['sub_code'])) {
            $arr = $result['tbk_order_get_response']['results']['n_tbk_order'][0];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    public function actionNotify(){
        $data = [
//            'time'  =>  date('Y-m-d H:i:s'),
            'get'   =>$data=\yii::$app->request->get(),
//            'post'   =>$data=\yii::$app->request->post()
        ];
//        print_r($data);
//        exit;
        if (!empty($data['get'])){
            //返回订单号查充值记录
            $recharge=Recharge::find()->where(['order_id'=>$data['get']['out_trade_no']])->asArray()->one();
//            //付款成功后
            if (!empty($recharge)) {
                $user = Cooperationuser::find()->where(['uid' => $recharge['uid'], 'status' => 1])->asArray()->one();
                //成为合作商付款续期  否则过期或不存在新添合作商
                if ($user) {
                    $xf = Cooperationuser::find()->where(['uid' => $recharge['uid'], 'status' => 1])->one();//不但存在 还要合作商正在使用中  才可续期
                    $xf->order_num =$recharge['order_id'];//订单号
                    $xf->cycle = $recharge['cycle'] + $user['cycle'];//购买周期
                    $xf->price = $recharge['price'] + $user['price'];//共付款价格
                    $xf->end_time = strtotime("+" . $recharge['cycle'] . "month", $user['end_time']);
                    $xf->save();

                    $hzs = User::find()->where(['uid' => $recharge['uid']])->one();//登录用户
                    $hzs->cooperation = 1;
                    $hzs->save();
                } else {
                    $cooperuser = new Cooperationuser;
                    $cooperuser->uid = $recharge['uid'];
                    $cooperuser->order_num = $recharge['order_id'];//订单号
                    $cooperuser->cycle = $recharge['cycle'];//购买周期
                    $cooperuser->price = $recharge['price'];//付款价格
                    $cooperuser->status = '1';//启动合作商
                    $cooperuser->start_time = time();
                    $cooperuser->end_time = strtotime("+" . $recharge['cycle'] . "month", time());
                    $cooperuser->save();

                    $hzs = User::find()->where(['uid' => $recharge['uid']])->one();//登录用户
                    $hzs->cooperation = 1;
                    $hzs->save();
                }
            }
        }
        echo 'success';
        exit;
    }


}