<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/17
 * Time: 18:34
 */

namespace common\components\jd\requests;

/**
 * 获取爆款商品【申请】
 * Class UnionThemeGoodsServiceQueryExplosiveGoods
 * @package common\components\jd\requests
 * @property Number $from
 * @property Number $pageSize
 * @property Number $cid3
 */
class UnionThemeGoodsServiceQueryExplosiveGoods extends Request
{
    public $method = 'jingdong.UnionThemeGoodsService.queryExplosiveGoods';

    public $params = [
        'from' => ['require'],
        'pageSize' => ['require'],
        'cid3' => '',
    ];
}