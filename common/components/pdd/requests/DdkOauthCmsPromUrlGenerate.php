<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/5/28
 * Time: 18:00
 */

namespace common\components\pdd\requests;

/**
 * 生成商城推广链接接口
 * Class DdkOauthCmsPromUrlGenerate
 * @package common\components\pdd\requests
 * @property string $generate_short_url
 * @property string $p_id_list
 * @property string $generate_mobile
 * @property string $multi_group
 * @property string $custom_parameters
 */
class DdkOauthCmsPromUrlGenerate extends Request
{
    public $type = 'pdd.ddk.oauth.cms.prom.url.generate';

    public $params = [
        'generate_short_url' => '',
        'p_id_list' => ['require'],
        'generate_mobile' => '',
        'multi_group' => '',
        'custom_parameters' => '',
    ];
}