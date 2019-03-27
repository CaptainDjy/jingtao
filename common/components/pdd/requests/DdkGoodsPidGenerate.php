<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/17
 * Time: 9:22
 */

namespace common\components\pdd\requests;

/**
 * 创建多多进宝推广位
 * Class DdkGoodsPidGenerate
 * @package common\components\pdd\requests
 * @property number $number
 * @property string $p_id_name_list
 */
class DdkGoodsPidGenerate extends Request
{
    public $type = 'pdd.ddk.goods.pid.generate';

    public $params = [
        'number' => ['require'],
        'p_id_name_list' => '',
    ];
}
