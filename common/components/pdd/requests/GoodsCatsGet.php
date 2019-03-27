<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/6/5
 * Time: 20:50
 */

namespace common\components\pdd\requests;

/**
 * pdd.goods.cats.get（商品标准类目接口）
 * Class GoodsCatsGet
 * @package common\components\pdd\requests
 * @property  $parent_cat_id
 */
class GoodsCatsGet extends Request
{
    public $type = 'pdd.goods.cats.get';

    public $params = [
        'parent_cat_id' => ['require'],
    ];
}