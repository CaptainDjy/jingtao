<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/5/27
 * Time: 16:36
 */

namespace common\components\pdd\requests;

/**
 * 多多进宝推广链接生成
 * Class DdkGoodsPromotionUrlGenerate
 * @package common\components\pdd\requests
 * @property string $p_id
 * @property string $goods_id_list
 * @property boolean $generate_short_url
 * @property boolean $multi_group
 * @property string $custom_parameters
 * @property string $pull_new
 */
class DdkGoodsPromotionUrlGenerate extends Request
{
    public $type = 'pdd.ddk.goods.promotion.url.generate';

    public $params = [
        'p_id' => ['require'],
        'goods_id_list' => ['require'],
        'generate_short_url' => ['require'],
        'multi_group' => '',
        'custom_parameters' => '',
        'pull_new' => '',
    ];
}