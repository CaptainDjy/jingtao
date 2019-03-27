<?php
/**
 * Created by PHPSTORM.
 * User: Yuuuuuu
 * Date: 2018/9/27
 * Time: 10:55
 */

namespace common\components\miao\taobao\requests;

/**
 * Class DecodeTklToID 解析淘口令成商品ID
 * http://open.21ds.cn/index/index/openapi/id/5.shtml?ptype=1
 * @package common\components\miao\taobao\requests
 * @property string $kouling 淘口令
 */
class DecodeTklToID extends Request{

    public $url = 'apiv1/jiexitkl';

    public $params = [
        'kouling' =>  ['require'],
    ];
}