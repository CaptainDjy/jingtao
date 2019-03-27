<?php
/**
 * Created by PhpStorm.
 * User: zz_bi
 * Date: 2018/6/22
 * Time: 21:05
 */

namespace common\components\jd\requests;

/**
 * Class ServicePromotionCouponGetInfo
 * @package common\components\jd\requests
 * @property $couponUrl
 */
class ServicePromotionCouponGetInfo extends Request
{
    public $method = 'jingdong.service.promotion.coupon.getInfo';

    public $params = [
        'couponUrl' => ['require'],
    ];
}