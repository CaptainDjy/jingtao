<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/17
 * Time: 19:02
 */

namespace common\components\jd\requests;

/**
 * Class UnionSearchQueryCouponGoods
 * @package common\components\jd\requests
 * @property Number $cid3
 * @property Number $pageSize
 * @property Number $pageIndex
 */
class Xiangqing extends Request
{
    public $method = 'jd.union.open.goods.promotiongoodsinfo.query';

    public $params = [
        'skuIds' => ['require'],
    ];

}