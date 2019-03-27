<?php
/**
 * Created by PHPSTORM.
 * User: Yuuuuuu
 * Date: 2018/9/27
 * Time: 10:55
 */

namespace common\components\miao\taobao\requests;

/**
 * http://open.21ds.cn/index/index/openapi/id/2.shtml?ptype=1
 * Class DecodeTkl
 * @package common\components\miao\taobao\requests
 * @property string $kouling 淘口令
 */
class DecodeTkl extends Request{

    public $url = 'apiv1/jiexitkl';

    public $params = [
        'kouling' =>  ['require'],
    ];
}