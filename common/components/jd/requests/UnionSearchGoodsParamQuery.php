<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/15
 * Time: 19:38
 */

namespace common\components\jd\requests;

/**
 *  获取拼购商品接口
 * Class UnionSearchGoodsParamQuery
 * @package common\components\jd\requests
 * @property Number $cat1Id
 * @property String $owner
 * @property Number $pageIndex
 * @property Number $pageSize
 * @property String $sortName
 * @property String $sort
 */
class UnionSearchGoodsParamQuery extends Request
{
    public $method = 'jingdong.union.search.goods.param.query';

    public $params = [
        'cat1Id' => '',
        'owner' => '',
        'pageIndex' => ['require'],
        'pageSize' => ['require'],
        'sortName' => '',
        'sort' => '',
    ];
}