<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/6/1
 * Time: 18:20
 */

namespace common\components\jd\requests;

/**
 * 子联盟获取订单
 * Class UnionServiceQueryOrderList
 * @package common\components\jd\requests
 * @property  $unionId
 * @property  $time
 * @property  $pageIndex
 * @property  $pageSize
 */
class UnionServiceQueryOrderList extends Request
{
    public $method = 'jingdong.UnionService.queryOrderList';

    public $params = [
        'unionId' => ['require'],
        'time' => ['require'],
        'pageIndex' => ['require'],
        'pageSize' => ['require'],
    ];
}