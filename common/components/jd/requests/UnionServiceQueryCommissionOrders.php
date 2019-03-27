<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/6/1
 * Time: 19:00
 */

namespace common\components\jd\requests;

/**
 * Class UnionServiceQueryCommissionOrders
 * @package common\components\jd\requests
 * @property  $unionId
 * @property  $time
 * @property  $pageIndex
 * @property  $pageSize
 */
class UnionServiceQueryCommissionOrders extends Request
{
    public $method = 'jingdong.UnionService.queryCommissionOrders';

    public $params = [
        'unionId' => ['require'],
        'time' => ['require'],
        'pageIndex' => ['require'],
        'pageSize' => ['require'],
    ];
}