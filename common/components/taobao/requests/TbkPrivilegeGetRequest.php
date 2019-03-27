<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\taobao\requests;

/**
 * 单品券高效转链API 暂时没有权限申请
 * Class TbkPrivilegeGetRequest
 * @package common\components\taobao\requests
 * @property string $item_id
 * @property string $adzone_id
 * @property string $site_id
 * @property Number $platform
 * @property string $me
 */
class TbkPrivilegeGetRequest extends Request
{
    public $method = 'taobao.tbk.privilege.get';

    public $params = [
        'item_id' => '',
        'adzone_id' => ['require'],
        'site_id' => ['require'],
        'platform' => '',
        'me' => '',
    ];
}
