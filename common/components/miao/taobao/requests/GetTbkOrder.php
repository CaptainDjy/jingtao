<?php
/**
 * Created by PHPSTORM.
 * User: Yuuuuuu
 * Date: 2018/9/27
 * Time: 10:55
 */

namespace common\components\miao\taobao\requests;

/**
 * Class CreateTkl
 * http://open.21ds.cn/index/index/openapi/id/4.shtml?ptype=1
 * @package common\components\miao\taobao\requests
 * @property string $starttime 订单查询开始时间（*必要，并且日期需进行urlencode编码）
 * @property string $span 订单查询时间范围,单位:秒,最小60,最大1200（*必要），仅当查询常规订单，及三方订单时需要设置此参数，渠道，及会员订单不需要设置此参数，直接通过设置page_size,page_no 翻页查询数据即可
 * @property integer $page 第几页，默认1，1~100 （必填）
 * @property integer $pagesize 每页数据多少条，1~100 （必填）
 * @property integer $tkstatus 订单状态，1: 全部订单，3：订单结算，12：订单付款， 13：订单失效，14：订单成功； 订单查询类型为‘结算时间’时，只能查订单结算状态 （必填）
 * @property string $ordertype 订单查询类型，创建时间“create_time”，或结算时间“settle_time” （必填）
 * @property string $tbname 授权后的淘宝名称（必填）
 * @property integer $orderscene 订单场景类型，1:常规订单，2:渠道订单，3:会员运营订单，默认为1，通过设置订单场景类型，媒体可以查询指定场景下的订单信息，例如不设置，或者设置为1，表示查询常规订单，常规订单包含淘宝客所有的订单数据，含渠道，及会员运营订单，但不包含3方分成，及维权订单
 * @property integer $ordercounttype 订单数据统计类型，1: 2方订单，2: 3方订单，如果不设置，或者设置为1，表示2方订单
 */
class GetTbkOrder extends Request{

    public $url = 'apiv1/gettkorder';

    public $params = [
        'starttime'  => ['require'],
        'span'  => ['require'],
        'page'  => ['require'],
        'pagesize'  => ['require'],
        'tkstatus'  => ['require'],
        'ordertype'  => ['require'],
        'tbname'  => ['require'],
        'orderscene' => '',
        'ordercounttype' => ''
    ];
}