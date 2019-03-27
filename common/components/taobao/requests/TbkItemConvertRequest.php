<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\taobao\requests;


/**
 * 商品链接转换接口
 * Class TbkItemConvertRequest
 * @package common\components\taobao\requests
 * @property string $method
 * @property string $fields
 * @property string $num_iids
 * @property Number $adzone_id
 * @property Number $platform
 * @property string $unid
 * @property string $dx
 */
class TbkItemConvertRequest extends Request
{
    public $method = 'taobao.tbk.item.convert';
//    public $fields = null;
    public $params = [
        'fields' => ['require'],
        'num_iids' => ['require'],
        'adzone_id' => '',
        'platform' => '',
        'unid' => '',
        'dx' => '',
    ];
}
