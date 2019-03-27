<?php
/**
 * Created by PhpStorm.
 * User: zz_bi
 * Date: 2018/6/27
 * Time: 13:55
 */

namespace common\components\taobao\requests;

/**
 * 单品券高效转链API
 * Class TbkTpwdConvert
 * @package common\components\taobao\requests
 * @property $
 */
class TbkTpwdConvert extends Request
{
    public $method = 'taobao.tbk.privilege.get';

    public $params = [
        'item_id' => '',
        'adzone_id' => ['require'],
        'platform' => '',
        'site_id' => ['require'],
        'me' => '',
        'relation_id' => '',
    ];
}