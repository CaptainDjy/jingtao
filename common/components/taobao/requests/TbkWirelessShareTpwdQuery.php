<?php
/**
 * Created by PhpStorm.
 * @author zz_biao@163.com
 * Date: 2018/6/1
 * Time: 10:44
 */

namespace common\components\taobao\requests;

/**
 * 无权限
 * Class TbkWirelessShareTpwdQuery
 * @package common\components\taobao\requests
 * @property String $password_content
 */
class TbkWirelessShareTpwdQuery extends Request
{
    public $method = 'taobao.wireless.share.tpwd.query';

    public $params = [
        'password_content' => ['require'],
    ];
}