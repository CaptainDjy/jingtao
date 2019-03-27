<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\pdd\requests;

/**
 * 商品获取接口
 * @link https://open.pinduoduo.com/index.html?utm_source=baidubz&utm_medium=sem&utm_term=search#/apidocument/port?id=28
 * Class TbkItemGetRequest
 * @package common\components\pdd\requests
 * @property string $sort_type
 * @property string $keyword
 * @property string $opt_id
 * @property string $page
 * @property string $page_size
 * @property string $with_coupon
 */
class DdkGoodsSearchRequest extends Request
{
    public $type = 'pdd.ddk.goods.search';

    public $params = [
        'keyword' => '',
        'opt_id' => '',
        'page' => '',
        'page_size' => '',
        'sort_type' => ['require'],
        'with_coupon' => ['require'],
        'range_list' => '',
        'cat_id' => '',
        'goods_id_list' => '',
    ];
}
