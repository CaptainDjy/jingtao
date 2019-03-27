<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/5/27
 * Time: 14:12
 */

namespace common\components\pdd\requests;

/**
 * 最后更新时间段增量同步推广订单信息
 * Class DdkOrderListIncrementGet
 * @package common\components\pdd\requests
 * @property number $start_update_time
 * @property number $end_update_time
 * @property string $p_id
 * @property number $page_size
 * @property number $page
 */
class DdkOrderListIncrementGet extends Request
{
    public $type = 'pdd.ddk.order.list.increment.get';

    public $params = [
        'start_update_time' => ['require'],
        'end_update_time' => ['require'],
        'p_id' => '',
        'page_size' => '',
        'page' => '',
    ];
}