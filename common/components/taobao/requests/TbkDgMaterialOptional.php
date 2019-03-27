<?php
/**
 * Created by PhpStorm.
 * User: zz_bi
 * Date: 2018/6/20
 * Time: 11:45
 */

namespace common\components\taobao\requests;

/**
 * 通用物料搜索API（导购）
 * Class TbkDgMaterialOptional
 * @package common\components\taobao\requests
 * @property  $start_dsr
 * @property  $page_size
 * @property  $page_no
 * @property  $platform
 * @property  $end_tk_rate
 * @property  $start_tk_rate
 * @property  $end_price
 * @property  $start_price
 * @property  $is_overseas
 * @property  $is_tmall
 * @property  $sort
 * @property  $itemloc
 * @property  $cat
 * @property  $q
 * @property  $has_coupon
 * @property  $ip
 * @property  $adzone_id
 * @property  $need_free_shipment
 * @property  $need_prepay
 * @property  $include_pay_rate_30
 * @property  $include_good_rate
 * @property  $include_rfd_rate
 * @property  $npx_level
 */
class TbkDgMaterialOptional extends Request
{
    public $method = 'taobao.tbk.dg.material.optional';

    public $params = [
        'start_dsr' => '',
        'page_size' => '',
        'page_no' => '',
        'platform' => '',
        'end_tk_rate' => '',
        'start_tk_rate' => '',
        'end_price' => '',
        'start_price' => '',
        'is_overseas' => '',
        'is_tmall' => '',
        'sort' => '',
        'itemloc' => '',
        'cat' => '',
        'q' => '',
        'has_coupon' => '',
        'ip' => '',
        'adzone_id' => ['require'],
        'need_free_shipment' => '',
        'need_prepay' => '',
        'include_pay_rate_30' => '',
        'include_good_rate' => '',
        'include_rfd_rate' => '',
        'npx_level' => '',
    ];
}