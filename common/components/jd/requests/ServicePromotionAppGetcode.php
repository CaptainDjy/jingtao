<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/6/6
 * Time: 20:48
 */

namespace common\components\jd\requests;

/**
 * APP领取代码接口、转链
 * Class ServicePromotionAppGetcode
 * @package common\components\jd\requests
 * @property $jdurl
 * @property $appId
 * @property $subUnionId
 * @property $positionId
 * @property $ext
 * @property $protocol
 * @property $pid
 */
class ServicePromotionAppGetcode extends Request
{
    public $method = 'jingdong.service.promotion.app.getcode';

    public $params = [
        'jdurl' => ['require'],
        'appId' => ['require'],
        'subUnionId' => '',
        'positionId' => '',
        'ext' => '',
        'protocol' => '',
        'pid' => '',
    ];
}