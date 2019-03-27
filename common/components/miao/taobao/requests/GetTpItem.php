<?php
/**
 * Created by PHPSTORM.
 * User: Yuuuuuu
 * Date: 2018/9/27
 * Time: 10:55
 */

namespace common\components\miao\taobao\requests;

/**
 * http://open.21ds.cn/index/index/openapi/id/12.shtml?ptype=1
 * Class GetTpItem 获取指定物料商品API
 * @package common\components\miao\taobao\requests
 * @property string $siteid 淘口令
 * @property string $adzoneid 淘口令
 * @property string $tbname 淘口令
 * @property string $materialid 物料（专题）ID，可通过：https://tbk.bbs.taobao.com/detail.html?appId=45301&postId=8576096 获取
 * @property string $pagesize 每页数据多少条，1~100
 * @property string $pageno 第几页
 */
class GetTpItem extends Request{

    public $url = 'apiv1/jiexitkl';

    public $params = [
        'adzoneid' =>  ['require'],
        'siteid' =>  ['require'],
        'tbname' =>  ['require'],
        'materialid'    =>  '',
        'pagesize'    =>  '',
        'pageno'    =>  '',
    ];
}