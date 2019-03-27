<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/5/25
 * Time: 15:04
 */

namespace common\components\jd\requests;

/**
 * 优惠券,商品二合一转接API-通过unionId获取推广链接【申请】
 * Class ServicePromotionCouponGetCodeByUnionId
 * @package common\components\jd\requests
 * @property string $couponUrl
 * @property string $materialIds
 * @property Number $unionId
 * @property Number $positionId
 * @property string $pid
 */
class ServicePromotionCouponGetCodeByUnionId extends Request
{
    public $method = 'jingdong.service.promotion.coupon.getCodeByUnionId';

    public $params = [
        'couponUrl' => ['require'],
        'materialIds' => ['require'],
        'unionId' => ['require'],
        'positionId' => '',
        'pid' => '',
    ];
}
