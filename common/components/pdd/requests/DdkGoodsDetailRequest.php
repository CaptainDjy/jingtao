<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\pdd\requests;

/**
 * 多多进宝商品详情查询
 * Class DdkGoodsDetailRequest
 * @package common\components\pdd\requests
 * @property string $goods_id_list
 */
class DdkGoodsDetailRequest extends Request
{
    public $type = 'pdd.ddk.goods.detail';

    public $params = [
        'goods_id_list' => ['require'],
    ];
}
