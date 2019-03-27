<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/6/3
 * Time: 14:07
 */

namespace common\components\pdd\requests;

/**
 * 生成推广链接
 * Class DdkCmsPromUrlGenerate
 * @package common\components\pdd\requests
 * @property $p_id
 * @property $goods_id_list

 */
class DdkGoodsUrlGenerate extends Request
{
    public $type = 'pdd.ddk.goods.promotion.url.generate';

    public $params = [
        'p_id' => ['require'],
        'goods_id_list' => ['require'],
    ];
}