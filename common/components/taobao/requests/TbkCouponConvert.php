<?php
/**
 * Created by PhpStorm.
 * User: zz_biao
 * Date: 2018/7/5
 * Time: 14:50
 */

namespace common\components\taobao\requests;

/**
 * 单品券高效转链API  二合一链接
 * https://mo.m.taobao.com/154351
 * Class TbkCouponConvert
 * @package common\components\taobao\requests
 * @property $item_id
 * @property $adzone_id
 * @property $platform
 * @property $me
 */
class TbkCouponConvert extends Request
{
    public $method = 'taobao.tbk.coupon.convert';

    public $params = [
        'item_id' => ['require'],
        'adzone_id' => ['require'],
        'platform' => ['require'],
        'me' => ['require'],
    ];
}