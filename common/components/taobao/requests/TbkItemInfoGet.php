<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/24
 * Time: 10:23
 */

namespace common\components\taobao\requests;

/**
 * Class TbkItemInfoGet
 * @package common\components\taobao\requests
 * @property string $platform
 * @property string $num_iids
 */
class TbkItemInfoGet extends Request
{
    public $method = 'taobao.tbk.item.info.get';

    public $params = [
        'num_iids' => ['require'],
        'platform' => '',
    ];
}