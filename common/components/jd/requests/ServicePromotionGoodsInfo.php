<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/24
 * Time: 10:38
 */

namespace common\components\jd\requests;

/**
 * 获取推广商品信息接口
 * Class ServicePromotionGoodsInfo
 * @package common\components\jd\requests
 * @property string $skuIds
 */
class ServicePromotionGoodsInfo extends Request
{
    public $method = 'jingdong.service.promotion.goodsInfo';

    public $params = [
        'skuIds' => ['require'],
    ];
}