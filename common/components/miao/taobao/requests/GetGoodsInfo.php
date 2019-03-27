<?php
/**
 * Created by PHPSTORM.
 * User: Yuuuuuu
 * Date: 2018/9/27
 * Time: 10:55
 */

namespace common\components\miao\taobao\requests;

/**
 * Class GetGoodsInfo
 * http://open.21ds.cn/index/index/openapi/id/11.shtml?ptype=1
 * @package common\components\miao\taobao\requests
 * @property string $itemid 商品详情
 */
class GetGoodsInfo extends Request{

    public $url = 'apiv1/getiteminfo';

    public $params = [
        'itemid' =>  ['require'],
    ];
}