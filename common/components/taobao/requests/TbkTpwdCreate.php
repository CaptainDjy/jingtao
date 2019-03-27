<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/6/7
 * Time: 17:20
 */

namespace common\components\taobao\requests;

/**
 * 生成淘口令
 * Class TbkTpwdCreate
 * @package common\components\taobao\requests
 * @property $user_id
 * @property $text
 * @property $url
 * @property $logo
 * @property $ext
 */
class TbkTpwdCreate extends Request
{
    public $method = 'taobao.tbk.tpwd.create';

    public $params = [
        'user_id' => '',
        'text' => ['require'],
        'url' => ['require'],
        'logo' => '',
        'ext' => '',
    ];
}