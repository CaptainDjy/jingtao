<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/5/29
 * Time: 18:26
 */

namespace common\components\pdd\requests;

/**
 * Class DdkRpPromUrlGenerate
 * @package common\components\pdd\requests
 * @property  $generate_short_url
 * @property  $p_id_list
 * @property  $custom_parameters
 */
class DdkRpPromUrlGenerate extends Request
{
    public $type = 'pdd.ddk.rp.prom.url.generate';

    public $params = [
        'generate_short_url' => '',
        'p_id_list' => ['require'],
        'custom_parameters' => '',
    ];
}