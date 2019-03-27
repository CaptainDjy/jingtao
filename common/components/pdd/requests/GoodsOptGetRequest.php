<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\pdd\requests;

/**
 * 查询商品标签列表
 * Class PddGoodsOptGetRequest
 * @package common\components\pdd\requests
 * @property string $parent_opt_id
 */
class GoodsOptGetRequest extends Request
{
    public $type = 'pdd.goods.opt.get';

    public $params = [
        'parent_opt_id' => ['require'],
    ];

}
