<?php

namespace common\components\alipay\requests;

/**
 * 单笔转账到支付宝账户接口
 * Class AlipayFundTransToaccountTransferRequest
 * @package common\components\alipay\requests
 * @property string $method
 * @property string $out_biz_no
 * @property string $payee_type
 * @property string $payee_account
 * @property String $amount 单位：元,只支持2位小数，小数点前最大支持13位，金额必须大于等于0.1元。
 * @property String $payer_show_name 付款方姓名
 * @property String $payee_real_name 收款方姓名
 * @property string $remark 转账备注
 */
class AlipayFundTransToaccountTransferRequest extends Request
{
    public $method = 'alipay.fund.trans.toaccount.transfer';

    public $params = [
        'out_biz_no' => ['require'],
        'payee_type' => ['require'],
        'payee_account' => ['require'],
        'amount' => ['require'],
        'payer_show_name' => '',
        'payee_real_name' => '',
        'remark' => '',
    ];
}
