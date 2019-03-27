<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/5/30
 * Time: 15:49
 */

namespace common\components\jd\requests;

/**
 * Class ServicePromotionCouponGetCodeBySubUnionId
 * @package common\components\jd\requests
 * @property String $couponUrl
 * @property String $materialIds
 * @property String $subUnionId
 * @property Number $positionId
 * @property String $pid
 */
class ServicePromotionCouponGetCodeBySubUnionId extends Request
{
    public $method = 'jingdong.service.promotion.coupon.getCodeBySubUnionId';

    public $params = [
        'couponUrl' => ['require'],
        'materialIds' => ['require'],
        'subUnionId' => ['require'],
        'positionId' => '',
        'pid' => '',
    ];
}