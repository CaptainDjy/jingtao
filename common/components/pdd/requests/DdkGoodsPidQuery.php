<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/6/5
 * Time: 14:54
 */

namespace common\components\pdd\requests;


class DdkGoodsPidQuery extends Request
{
    public $type = 'pdd.ddk.goods.pid.query';

    public $params = [
        'page' => '',
        'page_size' => '',
    ];
}