<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\taobao\requests;

/**
 * 商品获取接口
 * Class TbkItemGetRequest
 * @package common\components\taobao\requests
 * @property string $fields
 * @property string $q
 * @property string $cat
 * @property string $itemloc
 * @property string $sort
 * @property boolean $is_tmall
 * @property boolean $is_overseas
 * @property float $start_price
 * @property float $end_price
 * @property float $start_tk_rate
 * @property float $end_tk_rate
 * @property int $platform
 * @property int $page_no
 * @property int $page_size
 */
class TbkItemGetRequest extends Request
{
    public $method = 'taobao.tbk.item.get';

    public $params = [
        'fields' => ['require'],
        'q' => '',
        'cat' => '',
        'itemloc' => '',
        'sort' => '',
        'is_tmall' => '',
        'is_overseas' => '',
        'start_price' => '',
        'end_price' => '',
        'start_tk_rate' => '',
        'end_tk_rate' => '',
        'platform' => '',
        'page_no' => '',
        'page_size' => '',
    ];
}
