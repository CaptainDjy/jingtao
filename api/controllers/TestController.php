<?php
/**
 * Created by PHPSTORM.
 * User: Yuuuuuu
 * Date: 2018/9/6
 * Time: 15:36
 */
namespace api\controllers;

use common\components\miao\MiaoClient;
use common\components\miao\taobao\requests\CreateTkl;
use common\components\miao\taobao\requests\DecodeTkl;
use common\components\miao\taobao\requests\GetTbkOrder;
use common\components\miao\taobao\requests\GetTkMaterial;
use common\helpers\CreatePic;
use common\models\Goods;
use common\models\GoodsCategory;
use yii\db\Query;

class TestController extends ControllerBase {
	public function actionSay($message = '你好') {
		return $message;
	}

	public function actionIndex() {
		$res = GoodsCategory::find()
			->select('id,title')
			->where(['id' => 3])
			->orderBy('id desc')
			->one();

		return $this->responseJson('', $res->goods);
	}

	public function actionHas() {
		$res = Goods::find()
			->select('cid')
			->where([Goods::tableName() . '.id' => 851])
			->joinWith(['goodsCategory' => function ($query) {
				$query->select('id,title')->where([GoodsCategory::tableName() . '.id' => Goods::tableName() . 'cid']);
			}])
			->all();
		return $this->responseHtml($res);
		return $this->responseJson('', $res);
	}

	/**
	 * @return array 多表连接用这种吧
	 */
	public function actionDb() {
		$query = new Query();
		$res = $query->from(Goods::tableName() . ' g')
			->select('g.title')
		//->where(['>','id',2])
			->where(['c.id' => 10])
			->leftJoin(GoodsCategory::tableName() . ' c', 'c.id = g.cid')
			->limit(4)
			->all();
		return $this->responseJson($res);
	}

	/**
	 * 创建淘口令
	 * @return array
	 * @throws \yii\httpclient\Exception
	 */
	public function actionCreateYkl() {
		$client = new MiaoClient();
		$create = new CreateTkl();
		$create->kltext = '测试淘口令';
		$create->klurl = 'https://detail.tmall.com/item.htm?id=558760911386&ut_sk=1.WpZdgoC96bIDAC9nomzAuGhU_21380790_1538026921992.TaoPassword-Weixin.1&sourceType=item&price=5688&origin_price=6888&suid=AD3944B7-9D6E-471B-A11D-15CBA1BD68BE&un=bf09bc58d285c46ecd8415ff413d3508&share_crt_v=1&sp_tk=77%20lWndwT2JmT0lGamLvv6U=';
		return $this->responseJson('', $client->run($create), '创建淘口令');
	}

	/**
	 * @return array 获取淘宝客订单
	 * @throws \yii\httpclient\Exception
	 */
	public function actionGetOrder() {
		$client = new MiaoClient();
		$request = new GetTbkOrder();
		$request->starttime = urlencode('2018-09-28 10:10:22');
		$request->page = 1;
		$request->pagesize = 100;
		$request->span = 1200;
		$request->tkstatus = 1;
		$request->ordertype = 'create_time';
		$request->tbname = 'jxp15008311640';
		return $this->responseJson(0, $client->run($request), '获取淘宝客订单');
	}

	/**
	 * 解析淘口令
	 * @return array
	 * @throws \yii\httpclient\Exception
	 */
	public function actionTkl() {
		$client = new MiaoClient();
		$request = new DecodeTkl();
		$request->kouling = '【【稀缺货源】Apple/苹果iPhone 8 Plus 64G 智能手机 苹果8p iPhone8p】，復·制这段描述￥ZwpObfOIFjb￥后到淘♂寳♀';
		return $this->responseJson(0, $client->run($request), '淘口令解析');
	}

	/**
	 * 查询商品列表
	 * @return array
	 * @throws \yii\httpclient\Exception
	 */
	public function actionGetGoods() {
		$client = new MiaoClient();
		$request = new GetTkMaterial();
		$request->adzoneid = '10862500289';
		$request->siteid = '58550463';
		$request->tbname = 'jxp15008311640';
		$request->pageno = 1;
		$request->pagesize = 100;
		$request->keyword = 'iphone';
		$request->sort = 'tk_rate_des';
		$request->cat = '';
		$res = $client->run($request);
		return $this->responseJson(0, json_decode($res), '获取全网商品');
	}

	public function actionCreatepic() {
		//使用方法-------------------------------------------------
		//数据格式，如没有优惠券coupon_price值为0。
		$gData = [
			'pic' => 'code_png/nv_img.jpg',
			'title' => 'chic韩版工装羽绒棉服女冬中长款2017新款棉袄大毛领收腰棉衣外套',
			'price' => 19.8,
			'original_price' => 119.8,
			'coupon_price' => 100,
		];
		//直接输出

		CreatePic::createSharePng($gData, 'code_png/php_code.jpg');
		//输出到图片
		//CreatePic::createSharePng($gData,'code_png/php_code.jpg','share.png');
	}
}