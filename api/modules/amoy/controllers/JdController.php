<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/8
 * Time: 18:49
 */

namespace api\modules\amoy\controllers;


use common\components\jd\JdClient;
use common\components\jd\requests\Query;
use common\models\Goods;
use yii\httpclient\Client;
use common\models\Config;
class JdController extends ControllerBase
{
    /**
     * 获取推广商品信息接口
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGoodsInfo()
    {
        $data = [
            'method' => 'jingdong.service.promotion.goodsInfo',
            'skuIds' => '495862246',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['error_response'])) {
            $arr = $result['jingdong_service_promotion_goodsInfo_responce']['getpromotioninfo_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['error_response']['zh_desc']);
        }
    }

    /**
     * 获取拼购商品接口
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryGoods()
    {
        $data = [
            'method' => 'jingdong.union.search.goods.param.query',
            'pageIndex' => '495862246',
            'pageSize' => '495862246',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['error_response'])) {
            $arr = $result['jingdong_union_search_goods_param_query_response']['queryPromotionGoodsByParam_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 商品类目查询
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryGoodsCategory()
    {
        $data = [
            'method' => 'jingdong.union.search.goods.category.query',
            'parent_id' => '2',
            'grade' => '1',
        ];
        $result = $this->getJdResult($data);
        $result = json_decode($result['jingdong_union_search_goods_category_query_responce']['querygoodscategory_result'], true);
        if ($result['resultCode'] == 1) {
            $arr = $result['data'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 联盟设计帮内容推广
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryDesignboomGoods()
    {
        $data = [
            'method' => 'jingdong.service.promotion.queryDesignboomGoods',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_queryDesignboomGoods_response']['querydesignboompromotioncontentjos_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 图书包url地址查询
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryBookUrlList()
    {
        $data = [
            'method' => 'jingdong.UnionDoubanBookService.queryBookUrlList',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_UnionDoubanBookService_queryBookUrlList_response']['querybookkurl_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 内容推广获取转链接口
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCodeContent()
    {
        $data = [
            'method' => 'jingdong.service.promotion.content.getcode',
            'releaseType' => '1',
            'sortName' => '1',
            'sort' => '1',
            'pageSize' => '1',
            'pageIndex' => '1',
            'unionId' => '1',
            'webId' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_content_getcode_response']['result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * APP领取代码接口
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCodeApp()
    {
        $data = [
            'method' => 'jingdong.service.promotion.app.getcode',
            'jdurl' => '1',
            'appId' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_app_getcode_response']['query_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 批量获取代码接口
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCodeBatch()
    {
        $data = [
            'method' => 'jingdong.service.promotion.batch.getcode',
            'id' => '1',
            'url' => '1',
            'unionId' => '1',
            'channel' => '1',
            'webId' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_batch_getcode_response']['querybatch_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 自定义链接转换
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCode()
    {
        $data = [
            'method' => 'jingdong.service.promotion.getcode',
            'promotionType' => '1',
            'materialId' => '1',
            'unionId' => '1',
            'channel' => '1',
            'webId' => '1',
            'adttype' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_getcode_response']['queryjs_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 创建app推广位jos接口
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSiteInfo()
    {
        $data = [
            'method' => 'jingdong.CreatePromotionSiteJos.saveAppPromtionSiteInfo',
            'pin' => '京淘尚品jd',
            'appId' => '1287311125',
            'adName' => '测试',
            'adType' => '1',
            'adSize' => '320*50',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_CreatePromotionSiteJos_saveAppPromtionSiteInfo_response']['query_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 获取联盟PID
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetPid()
    {
        $data = [
            'method' => 'jingdong.service.promotion.pid.getPid',
            'unionId' => '1',
            'sonUnionId' => '1',
            'promotionType' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_pid_getPid_response']['pidResult'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * APP效果数据接口
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAppReport()
    {
        $data = [
            'method' => 'jingdong.service.promotion.appReport',
            'time' => '1',
            'siteKey' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_appReport_response']['appReport_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * PID业绩订单查询
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryPerformanceWithPid()
    {
        $data = [
            'method' => 'jingdong.UnionService.queryPerformanceWithPid',
            'unionId' => '1',
            'childUnionId' => '1',
            'time' => '1',
            'pageIndex' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_UnionService_queryPerformanceWithPid_response']['result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * PID引入订单查询
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryPullInWithPid()
    {
        $data = [
            'method' => 'jingdong.UnionService.queryPullInWithPid',
            'unionId' => '1',
            'childUnionId' => '1',
            'time' => '1',
            'pageIndex' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_UnionService_queryPullInWithPid_response']['result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }


    /**
     * 查询引入订单
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryImportOrders()
    {
        $data = [
            'method' => 'jingdong.UnionService.queryImportOrders',
            'unionId' => '1',
            'time' => '1',
            'pageIndex' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_UnionService_queryImportOrders_response']['queryImportOrders_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 工具商逆向数据查询接口
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryPerformanceForReverseWithKey()
    {
        $data = [
            'method' => 'jingdong.UnionService.queryPerformanceForReverseWithKey',
            'unionId' => '1',
            'key' => '1',
            'time' => '1',
            'pageIndex' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_UnionService_queryPerformanceForReverseWithKey_response']['queryReverseWithKey_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 工具商业绩数据查询接口
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryCommissionOrdersWithKey()
    {
        $data = [
            'method' => 'jingdong.UnionService.queryCommissionOrdersWithKey',
            'unionId' => '1',
            'key' => '1',
            'time' => '1',
            'pageIndex' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_UnionService_queryCommissionOrdersWithKey_response']['queryCommissionOrdersWithKey_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 工具商引入数据查询接口
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryImportOrdersWithKey()
    {
        $data = [
            'method' => 'jingdong.UnionService.queryImportOrdersWithKey',
            'unionId' => '1',
            'key' => '1',
            'time' => '1',
            'pageIndex' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_UnionService_queryCommissionOrdersWithKey_response']['queryCommissionOrdersWithKey_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 联盟逆向流程解冻结算订单明细
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryPerformanceForReverse()
    {
        $data = [
            'method' => 'jingdong.UnionService.queryPerformanceForReverse',
            'unionId' => '1',
            'time' => '1',
            'pageIndex' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_UnionService_queryPerformanceForReverse_response']['queryPerformanceForReverse_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 优惠券领取情况查询接口【申请】
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCouponInfo()
    {
        $data = [
            'method' => 'jingdong.service.promotion.coupon.getInfo',
            'unionId' => '1',
            'time' => '1',
            'pageIndex' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_coupon_getInfo_response']['getinfo_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 联盟微信手q通过subUnionId获取推广链接【申请】
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCodeBySubUnionId()
    {
        $data = [
            'method' => 'jingdong.service.promotion.wxsq.getCodeBySubUnionId',
            'proCont' => '1',
            'materialIds' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_wxsq_getCodeBySubUnionId_response']['getcodebysubunionid_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 联盟微信手q通过unionId获取推广链接【申请】
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCodeByUnionId()
    {
        $data = [
            'method' => 'jingdong.service.promotion.wxsq.getCodeByUnionId',
            'proCont' => '1',
            'materialIds' => '1',
            'unionId' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_wxsq_getCodeByUnionId_response']['getcodebysubunionid_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 一号店生成推广链接接口【申请】
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetPromotionInfo()
    {
        $data = [
            'method' => 'jingdong.service.yhd.promotion.getInfo',
            'proCont' => '1',
            'materialIds' => '1',
            'unionId' => '1',
            'webId' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_yhd_promotion_getInfo_response']['getcode_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 优惠券,商品二合一转接API-通过subUnionId获取推广链
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCouponBySubUnionId()
    {
        $data = [
            'method' => 'jingdong.service.promotion.coupon.getCodeBySubUnionId',
            'couponUrl' => '1',
            'materialIds' => '1',
        ];
        $result = $this->getJdResult($data);
        $result = json_decode($result['jingdong_service_promotion_coupon_getCodeBySubUnionId_responce']['getcodebysubunionid_result'], true);
        if (empty($result['resultCode'])) {
            $arr = $result['jingdong_service_promotion_coupon_getCodeBySubUnionId_response']['getcodebysubunionid_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['resultMessage']);
        }
    }

    /**
     * 优惠券,商品二合一转接API-通过unionId获取推广链接【申
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCouponByUnionId()
    {
        $data = [
            'method' => 'jingdong.service.promotion.coupon.getCodeByUnionId',
            'couponUrl' => '1',
            'materialIds' => '1',
            'unionId' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_coupon_getCodeByUnionId_response']['getcodebyunionid_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 获取类目属性列表
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionFindAttrsByCategoryIdJos()
    {
        $data = [
            'method' => 'jingdong.category.read.findAttrsByCategoryIdJos',
            'cid' => '1',
            'attributeType' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_coupon_getCodeByUnionId_response']['getcodebyunionid_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 关键词查询选品接口【申请】
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQuerySearchGoods()
    {
        $data = [
            'method' => 'jingdong.union.search.goods.keyword.query',
            'page_index' => '1',
            'materialIds' => '1',
            'unionId' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_union_search_goods_keyword_query_response']['querygoodsbykeyword_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 获取爆款商品【申请】
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryExplosiveGoods()
    {
        $data = [
            'method' => 'jingdong.UnionThemeGoodsService.queryExplosiveGoods',
            'from' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_union_search_goods_keyword_query_response']['querygoodsbykeyword_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 获取优惠商品【申请】
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryCouponGoods()
    {
        $data = [
            'method' => 'jingdong.UnionThemeGoodsService.queryCouponGoods',
            'from' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_UnionThemeGoodsService_queryCouponGoods_response']['queryCouponGoods_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 优惠券商品查询接口
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQuerySearchCouponGoods()
    {
        $data = [
            'method' => 'jingdong.union.search.queryCouponGoods',
            'pageIndex' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_union_search_queryCouponGoods_response']['query_coupon_goods_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 订单查询
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryOrderList()
    {
        $data = [
            'method' => 'jingdong.UnionService.queryOrderList',
            'unionId' => '1',
            'time' => '1',
            'pageIndex' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_UnionService_queryOrderList_response']['result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 工具商订单查询
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryOrderListWithKey()
    {
        $data = [
            'method' => 'jingdong.UnionService.queryOrderListWithKey',
            'unionId' => '1',
            'key' => '1',
            'time' => '1',
            'pageIndex' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_UnionService_queryOrderList_response']['result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * PID订单查询
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryOrderListWithPid()
    {
        $data = [
            'method' => 'jingdong.UnionService.queryOrderListWithPid',
            'unionId' => '1',
            'childUnionId' => '1',
            'time' => '1',
            'pageIndex' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_UnionService_queryOrderListWithPid_response']['result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 批量创建推广位【申请】
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreatePromotionSiteBatch()
    {
        $data = [
            'method' => 'jingdong.service.promotion.createPromotionSiteBatch',
            'unionId' => '1',
            'key' => '1',
            'unionType' => '1',
            'type' => '1',
            'siteId' => '1',
            'spaceName' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_createPromotionSiteBatch_response']['create_promotion_site_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 批量查询推广位【申请】
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryPromotionSite()
    {
        $data = [
            'method' => 'jingdong.service.promotion.queryPromotionSite',
            'unionId' => '1',
            'key' => '1',
            'unionType' => '1',
            'pageNo' => '1',
            'pageSize' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_promotion_queryPromotionSite_response']['querypromotionsite_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 学生价商品查询【申请】
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueryStuPriceGoods()
    {
        $data = [
            'method' => 'jingdong.service.goods.queryStuPriceGoods',
            'stuPriceFrom' => '1',
            'stuPriceTo' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_goods_queryStuPriceGoods_response']['querystupricegoods_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 秒杀商品查询【申请】
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQuerySecKillGoods()
    {
        $data = [
            'method' => 'jingdong.service.goods.querySecKillGoods',
            'secKillPriceFrom' => '1',
            'secKillPriceTo' => '1',
        ];
        $result = $this->getJdResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['jingdong_service_goods_querySecKillGoods_response']['querystupricegoods_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }
/*
 * 京东商品详情
 * */
    public function actionGoodsDetail()
    {
        $goods_id = \Yii::$app->request->post('goodsid');

        $client=new Client();
        $response = $client->get('https://api.open.21ds.cn/jd_api_v1/getitemdesc?apkey='.Config::getConfig('MIAO_APKEY').'&skuid='.$goods_id)->send();//对象
        $result = $response->getData();//数组

        if (!empty($result['data'])) {
            $xq=Goods::find()->where(['origin_id'=>$goods_id])->asArray()->one();
            //商品转链
            $zlurl = $client->get('https://api.open.21ds.cn/jd_api_v1/getjdunionpromotioncommon?',
                ['apkey'=>Config::getConfig('MIAO_APKEY'),
                    'materialId'=>$xq['coupon_link'],
                    'siteId'=>Config::getConfig('JD_APPID'),
                ])->send();
            $url=$zlurl->getData();
            foreach ($result['data'] as $v){
                $http=substr($v,0,4);
                if ($http=='http'){
                    $arr[] = $v;//商品地址
                }else{
                    $arr[] = 'https:'.$v;//商品地址
                }
            }
//            $arr = $result['data'];
            if (!empty($xq) && $url['code']==200) {
                $xq['coupon_link']=$url['data']['clickURL'];
                $xq['imags'] = $arr;
            }
            return $this->responseJson(200, $xq, '查询数据成功');
        } else {
            return $this->responseJson(101, '', $result['error_response']['error_msg']);
        }
    }


    public function actionSss(){
        $client = new JdClient();
        $request = new Query();
        $pageIndex=\Yii::$app->request->post('page');//页数
        $sortName=\Yii::$app->request->post('sortName');//排序字段
        $request->goodsReq = [
            'eliteId'=>11,//频道
            'pageIndex'=>$pageIndex,//页数
            'pageSize'=>20,//每页数量
            'sortName'=>$sortName,//排序字段
            'sort'=>'desc'//排序方式
        ];
        $resultData = $client->run($request);
        $result=$resultData['jd_union_open_goods_jingfen_query_response']['result'];
        $res=json_decode($result,true);
       print_r($res);
       exit;
    }

}