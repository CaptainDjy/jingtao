<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/4/28
 * Time: 10:15
 */

namespace api\modules\amoy\controllers;



class OrderController extends ControllerBase
{
    /**
     * 淘宝客返利授权查询
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRebateAuth()
    {
        $method = 'taobao.tbk.rebate.auth.get';
        $fields = "cid,pid,prop_name,vid,name,name_alias,status,sort_order";
        $data = [
            'method' => $method,
            'fields' => $fields,
            'params' => $fields,
            'type' => $fields,
        ];
        $result = $this->getUrlResult($data);
        if (!empty($result['sub_code'])) {
            return $this->responseJson(1, $result['sub_msg'], '请求结果错误,请重新请求！');
        } else {
            $arr = $result['taobao.tbk.rebate.auth.get']['results']['n_tbk_rebate_auth'][0];
            return $this->responseJson(0, $arr, '返回数据成功');
        }
    }


    /**
     * 推客平台订单回流
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAlianceCreate()
    {
        $method = 'alibaba.trade.aliance.create ';
        $data = [
            "method" => $method,
            "param_isv_create_order_param" => '',//下单请求，可选
        ];
        $result = $this->getUrlResult($data);
        if (!empty($result['sub_code'])) {
            return $this->responseJson(1, $result['sub_msg'], '请求结果错误,请重新请求！');
        } else {
            $arr = $result['alibaba_trade_aliance_create_response']['result'];
            return $this->responseJson(0, $arr, '返回数据成功');
        }
    }


}