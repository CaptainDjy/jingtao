<?php
/**
 * Created by PHPSTORM.
 * User: Yuuuuuu
 * Date: 2018/9/27
 * Time: 10:55
 */

namespace common\components\miao\taobao\requests;

/**
 * Class GetTkMaterial 获取全网淘客商品
 * http://open.21ds.cn/index/index/openapi/id/9.shtml?ptype=1
 * @package common\components\miao\taobao\requests
 * @property string $adzoneid adzone_id，PID第三段
 * @property string $siteid site_id，PID第二段
 * @property string $tbname 	授权后的淘宝名称
 * @property integer $pageno 	页数
 * @property integer $pagesize 	每页条数，默认100
 * @property string $keyword 	关键词
 * @property int $starttkrate 	淘客佣金比率下限，如：1234表示12.34%
 * @property int $endtkrate 	淘客佣金比率上限，如：1234表示12.34%
 * @property int $startprice    折扣价范围下限，单位：元
 * @property int $endprice	折扣价范围上限，单位：元
 * @property string $hascoupon 	是否有优惠券，设置为true表示该商品有优惠券，设置为false或不设置表示不判断这个属性
 * @property string $sort 	排序 排序_des（降序），排序_asc（升序），销量（total_sales），淘客佣金比率（tk_rate）， 累计推广量（tk_total_sales），总支出佣金（tk_total_commi），价格（price）
 */
class GetTkMaterial extends Request{

    public $url = 'apiv1/gettkmaterial';    //  请求链接后缀


    //  更多配置参数详见 http://open.21ds.cn/index/index/openapi/id/9.shtml?ptype=1
    public $params = [
        'adzoneid' =>  ['require'],
        'siteid' =>  ['require'],
        'tbname' =>  ['require'],
        'startdsr' =>  '',
        'pagesize' =>  '',
        'pageno' =>  '',
        'platform' =>  '',
        'endtkrate' =>  '',
        'starttkrate' =>  '',
        'endprice' =>  '',
        'startprice' =>  '',
        'isoverseas' =>  '',
        'istmall' =>  '',
        'keyword' =>  '',
        'cat' =>  '',
        'sort' =>  '',
        'hascoupon' =>  '',
    ];
}