<?php
//[b]命名空间为alisms[/b]
namespace api\alisms;

//引入sdk的命名空间
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;

//引入autoload.php，EXTEND_PATH就是指tp5根目录下的extend目录，系统自带常量。alisms为我们复制api_sdk过来后更改的目录名称
//require_once EXTEND_PATH.'./extend/alisms/vendor/autoload.php';
require_once 'vendor/autoload.php';
Config::load(); //加载区域结点配置

class SendSms {
	//关键的配置，我们用成员属性
	public $accessKeyId = 'LTAI8ZEVyJ2AMkKn'; //阿里云短信获取的accessKeyId
	public $accessKeySecret = 'vvDrrr1WZ8LAjgaKSzmJFY3Wu6kDwT'; //阿里云短信获取的accessKeySecret
	public $signName = '再出发网络科技'; //短信签名，要审核通过
	public $templateCode = 'SMS_137710100'; //短信模板ID，记得要审核通过的

	public function send($mobile, $templateParam) {
		//获取成员属性
		$accessKeyId = $this->accessKeyId;
		$accessKeySecret = $this->accessKeySecret;
		$signName = $this->signName;
		$templateCode = $this->templateCode;
		//短信API产品名（短信产品名固定，无需修改）
		$product = "Dysmsapi";
		//短信API产品域名（接口地址固定，无需修改）
		$domain = "dysmsapi.aliyuncs.com";
		//暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
		$region = "cn-hangzhou";

		// 初始化用户Profile实例
		$profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
		// 增加服务结点
		DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
		// 初始化AcsClient用于发起请求
		$acsClient = new DefaultAcsClient($profile);

		// 初始化SendSmsRequest实例用于设置发送短信的参数
		$request = new SendSmsRequest();
		// 必填，设置雉短信接收号码
		$request->setPhoneNumbers($mobile);

		// 必填，设置签名名称
		$request->setSignName($signName);

		// 必填，设置模板CODE
		$request->setTemplateCode($templateCode);

		// 可选，设置模板参数
		//        $templateParam参数格式:

		if ($templateParam) {
//            $request->setTemplateParam(json_encode($templateParam));
			// 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
			$request->setTemplateParam(json_encode(array( // 短信模板中字段的值
				"code" => $templateParam['code'],
				"product" => "dsd",
			), JSON_UNESCAPED_UNICODE));

			// 可选，设置流水号
			$request->setOutId("yourOutId");

			// 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
			$request->setSmsUpExtendCode("1234567");

			//发起访问请求
			$acsResponse = $acsClient->getAcsResponse($request);

			//返回请求结果，这里为为数组格式
			$result = json_decode(json_encode($acsResponse), true);
			return $result;
		}

	}

	/**
	 * 批量发送短信
	 * @return stdClass
	 */
	public static function sendBatchSms() {

		// 初始化SendSmsRequest实例用于设置发送短信的参数
		$request = new SendBatchSmsRequest();

		//可选-启用https协议
		//$request->setProtocol("https");

		// 必填:待发送手机号。支持JSON格式的批量调用，批量上限为100个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
		$request->setPhoneNumberJson(json_encode(array(
			"1500000000",
			"1500000001",
		), JSON_UNESCAPED_UNICODE));

		// 必填:短信签名-支持不同的号码发送不同的短信签名
		$request->setSignNameJson(json_encode(array(
			"云通信",
			"云通信",
		), JSON_UNESCAPED_UNICODE));

		// 必填:短信模板-可在短信控制台中找到
		$request->setTemplateCode("SMS_1000000");

		// 必填:模板中的变量替换JSON串,如模板内容为"亲爱的${name},您的验证码为${code}"时,此处的值为
		// 友情提示:如果JSON中需要带换行符,请参照标准的JSON协议对换行符的要求,比如短信内容中包含\r\n的情况在JSON中需要表示成\\r\\n,否则会导致JSON在服务端解析失败
		$request->setTemplateParamJson(json_encode(array(
			array(
				"name" => "Tom",
				"code" => "123",
			),
			array(
				"name" => "Jack",
				"code" => "456",
			),
		), JSON_UNESCAPED_UNICODE));

		// 可选-上行短信扩展码(扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段)
		// $request->setSmsUpExtendCodeJson("[\"90997\",\"90998\"]");

		// 发起访问请求
		$acsResponse = static::getAcsClient()->getAcsResponse($request);

		return $acsResponse;
	}

	/**
	 * 短信发送记录查询
	 * @return stdClass
	 */
	public static function querySendDetails() {

		// 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
		$request = new QuerySendDetailsRequest();

		//可选-启用https协议
		//$request->setProtocol("https");

		// 必填，短信接收号码
		$request->setPhoneNumber("15262066511");

		// 必填，短信发送日期，格式Ymd，支持近30天记录查询
		$request->setSendDate("20180918");

		// 必填，分页大小
		$request->setPageSize(10);

		// 必填，当前页码
		$request->setCurrentPage(1);

		// 选填，短信发送流水号
		$request->setBizId("yourBizId");

		// 发起访问请求
		$acsResponse = static::getAcsClient()->getAcsResponse($request);

		return $acsResponse;
	}

}

//// 调用示例：
//set_time_limit(0);
//header('Content-Type: text/plain; charset=utf-8');
//
//$response = SmsDemo::sendSms();
//echo "发送短信(sendSms)接口返回的结果:\n";
//print_r($response);
//
//sleep(2);
//
//$response = SmsDemo::sendBatchSms();
//echo "批量发送短信(sendBatchSms)接口返回的结果:\n";
//print_r($response);
//
//sleep(2);
//
//$response = SmsDemo::querySendDetails();
//echo "查询短信发送情况(querySendDetails)接口返回的结果:\n";
//print_r($response);