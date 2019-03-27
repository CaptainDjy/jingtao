<?php
/**
 * Created by PhpStorm.
 * User: zz_biao
 * Date: 2018/7/5
 * Time: 15:00
 */

namespace common\components\taobao\requests;

/**
 * 淘宝客商品猜你喜欢  放到首页 淘宝好货
 * Class TbkItemGuessLike
 * @package common\components\taobao\requests
 */
class TbkItemGuessLike extends Request
{
    public $method = 'taobao.tbk.coupon.convert';

    public $params = [
        'adzone_id' => ['require'],
        'user_nick' => '',
        'user_id' => '',
        'os' => ['require'],
        'idfa' => '',
        'imei' => '',
        'imei_md5' => '',
        'ip' => ['require'],
        'ua' => ['require'],
        'apnm' => '',
        'net' => ['require'],
        'mn' => '',
        'page_no' => '',
        'page_size' => '',
    ];
}