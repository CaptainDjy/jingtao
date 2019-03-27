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
 * @property string $goodsReq
 * @property Number $eliteId
 * @property Number $pageIndex
 * @property Number $pageSize
 * @property string $sortName
 * @property string $sort
 */
class Query extends Request
{
    public $method = 'jd.union.open.goods.jingfen.query';

    public $params = [
        'goodsReq'=> ['require'],
    ];


}