<?php
/**
 * Created by PHPSTORM.
 * User: Yuuuuuu
 * Date: 2018/9/27
 * Time: 10:55
 */

namespace common\components\miao\taobao\requests;

/**
 * http://open.21ds.cn/index/index/openapi/id/3.shtml?ptype=1
 * Class CreateTkl
 * @package common\components\miao\taobao\requests
 * @property string $klurl
 * @property string $kltext
 * @property string $tpwdpic
 */
class CreateTkl extends Request{

    public $url = 'apiv1/createtkl';

    public $params = [
        'klurl' =>  ['require'],
        'kltext'  => ['require'],
        'tpwdpic' => ''
    ];
}