<?php
/**
 * @author 河南邦耀网络科技
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * createtime: 2018/05/26 17:00
 */

namespace common\components\jd\requests;

/**
 * 自定义链接转换接口
 * Class ServicePromotionGetCode
 * @package common\components\jd\requests
 * @property int $promotionType 推广类型 0 活动推广 1：pop单品推广 2:pop店铺推广 3 专柜推广 4 自营频道页推广 5 搜索推广 6 组件推广 7 自定义推广 8 自定义单品 9 自定义首页 10 自定义其他 11 聚合页首跳 12 聚合页推广 13 pop频道推广 14 组合推广 15 超值购频道推广 16自定义店铺推广 17 自定义活动推广 30 自营活动推广 60/61 文章推广-站外 62 文章推广-站内
 * @property string $materialId 推广物料 就是落地页
 * @property int $unionId 联盟ID
 * @property string $subUnionId 子联盟ID
 * @property string $siteId 推广位ID，获取京东饭粒的推广链接时必填
 * @property string $channel 推广渠道（PC/WL）
 * @property string $webId 网站id
 * @property string $adttype 推广渠道 6：cps网站
 * @property string $protocol 传输协议 1为https 其他为 http
 * @property string $pid	母子账号
 */
class ServicePromotionGetCode extends Request
{
    public $method = 'jingdong.service.promotion.getcode';

    public $params = [
        'promotionType' => ['require'],
        'materialId' => ['require'],
        'unionId' => ['require'],
        'subUnionId' => '',
        'siteId' => '',
        'channel' => ['require'],
        'webId' => ['require'],
        'adttype' => ['require'],
        'protocol' => '',
        'pid' => '',
    ];
}
