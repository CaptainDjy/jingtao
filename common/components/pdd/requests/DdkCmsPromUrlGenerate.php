<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/6/3
 * Time: 14:07
 */

namespace common\components\pdd\requests;

/**
 * 生成商城推广链接
 * Class DdkCmsPromUrlGenerate
 * @package common\components\pdd\requests
 * @property $generate_short_url
 * @property $p_id_list
 * @property $generate_mobile
 * @property $multi_group
 * @property $custom_parameters
 */
class DdkCmsPromUrlGenerate extends Request
{
    public $type = 'pdd.ddk.cms.prom.url.generate';

    public $params = [
        'generate_short_url' => '',
        'p_id_list' => ['require'],
        'generate_mobile' => '',
        'multi_group' => '',
        'custom_parameters' => '',
    ];
}