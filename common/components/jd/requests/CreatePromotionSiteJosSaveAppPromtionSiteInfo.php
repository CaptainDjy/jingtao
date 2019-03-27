<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/22
 * Time: 17:22
 */

namespace common\components\jd\requests;

/**
 * 创建app推广位jos接口
 * Class CreatePromotionSiteJosSaveAppPromtionSiteInfo
 * @package common\components\jd\requests
 * @property string $pin
 * @property number $appId
 * @property String $adName
 * @property number $adType
 * @property String $adSize
 */
class CreatePromotionSiteJosSaveAppPromtionSiteInfo extends Request
{
    public $method = 'jingdong.CreatePromotionSiteJos.saveAppPromtionSiteInfo';

    public $params = [
        'pin' => ['require'],
        'appId' => ['require'],
        'adName' => ['require'],
        'adType' => ['require'],
        'adSize' => ['require'],
    ];
}