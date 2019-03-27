<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\jd\requests;

/**
 * 商品类目查询
 * Class UnionSearchGoodsCategoryRequest
 * @package common\components\jd\requests
 * @property string $parent_id
 * @property string $grade
 */
class UnionSearchGoodsCategoryRequest extends Request
{
    public $method = 'jingdong.union.search.goods.category.query';

    public $params = [
        'parent_id' => ['require'],
        'grade' => ['require'],
    ];

}
