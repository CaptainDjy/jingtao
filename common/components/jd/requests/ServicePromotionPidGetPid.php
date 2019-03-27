<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/17
 * Time: 9:59
 */

namespace common\components\jd\requests;

/**
 * Class ServicePromotionPidGetPid
 * @package common\components\jd\requests
 * @property Number $unionId
 * @property Number $sonUnionId
 * @property string $mediaName
 * @property string $positionName
 * @property Number $promotionType
 */
class ServicePromotionPidGetPid extends Request
{
    public $method = 'jingdong.service.promotion.pid.getPid';

    public $params = [
        'unionId' => ['require'],
        'sonUnionId' => ['require'],
        'mediaName' => ['require'],
        'positionName' => '',
        'promotionType' => ['require'],
    ];
}