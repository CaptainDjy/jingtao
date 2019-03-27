<?php
/**
 * Created by PHPSTORM.
 * User: Yuuuuuu
 * Date: 2018/9/27
 * Time: 10:55
 */

namespace common\components\miao\taobao\requests;

/**
 * Class GetGoodsInfo 高佣转链API （商品ID）
 * http://open.21ds.cn/index/index/openapi/id/1.shtml?ptype=1
 * @package common\components\miao\taobao\requests
 * @property string $itemid 商品ID
 * @property string $pid 推广位 mm_0000_0000_000
 * @property string $tbname 淘宝账户名称
 * @property int $shorturl 是否需要短链接
 * @property int $tpwd 是否生成淘口令
 */
class GetGoodsCouponUrl extends Request{

    public $url = 'apiv1/getitemgyurl';

    public $params = [
        'itemid' =>  ['require'],
        'pid' =>  ['require'],
        'tbname' =>  ['require'],
        'shorturl' => '',
        'tpwd' => '',
        'tpwdpic' => '',
    ];
}