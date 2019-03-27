<?php
 //接口类型：互亿无线触发短信接口，支持发送验证码短信、订单通知短信等。
 // 账户注册：请通过该地址开通账户http://sms.ihuyi.com/register.html
 // 注意事项：
 //（1）调试期间，请使用用系统默认的短信内容：您的验证码是：【变量】。请不要把验证码泄露给其他人。；
 //（2）请使用APIID（查看APIID请登录用户中心->验证码短信->产品总览->APIID）及 APIkey来调用接口
 //（3）该代码仅供接入互亿无线短信接口参考使用，客户可根据实际需要自行编写；


class ihuyi_sms{

    /**
     *	@appid								互亿无线apiid
     *	@appkey								互亿无线apikey
     *	@sms_send_time						两次短信发送时间间隔 单位：秒
     *	@sms_send_num						最大发送次数
     *	@sms_send_black_time				限制发送时间 单位：秒 (一小时为 60*60 = 3600 秒)
     *	@url								接口地址
     *	@is_open_send_limit					是否开启发送限制（1开启 0关闭）
     */
	public $log_file = 'log.txt';
	private $appid = '';
	private $appkey = '';
	private $sms_send_time = 60;
	private $sms_send_num = 5;
	private $sms_send_black_time = 600;
	private $url = 'http://106.ihuyi.cn/webservice/sms.php?method=Submit';
	private $is_open_send_limit = 1;

	public function __construct(){
		header("Content-type:text/html; charset=UTF-8");
		//require_once 'config.ihuyi.php';
        $this->appid = \common\models\Config::getConfig('MESSAGE_APPID');
        $this->appkey = \common\models\Config::getConfig('MESSAGE_APPKEY');

	}

	//请求数据到短信接口，检查环境是否 开启 curl init。
	private function post($curlPost,$url){
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_NOBODY, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
			$return_str = curl_exec($curl);
			curl_close($curl);
			return $return_str;
	}

	//将 xml数据转换为数组格式。
	private function xml_to_array($xml){
		$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
		if(preg_match_all($reg, $xml, $matches)){
			$count = count($matches[0]);
			for($i = 0; $i < $count; $i++){
			$subxml= $matches[2][$i];
			$key = $matches[1][$i];
				if(preg_match( $reg, $subxml )){
					$arr[$key] = $this-> xml_to_array( $subxml );
				}else{
					$arr[$key] = $subxml;
				}
			}
		}
		return $arr;
	}

	//random() 函数返回随机整数。
	private function random($length = 6 , $numeric = 0) {
		PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
		if($numeric) {
			$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
		} else {
			$hash = '';
			$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
			$max = strlen($chars) - 1;
			for($i = 0; $i < $length; $i++) {
				$hash .= $chars[mt_rand(0, $max)];
			}
		}
		return $hash;
	}

	//防止恶意攻击
	private function sms_safe(){
		if($this->is_open_send_limit!=1){
			return true;
		}
		if (!empty($_SESSION['sms_send_black']) && $_SESSION['sms_send_black'] + $this->sms_send_black_time > time()) {
			//exit('操作频繁,请'.ceil(($_SESSION['sms_send_black'] + $this->sms_send_black_time - time())/60).'分钟后重试');
			return '操作频繁,请'.ceil(($_SESSION['sms_send_black'] + $this->sms_send_black_time - time())/60).'分钟后重试';
		}

		if (empty($_SESSION['sms_send_num'])) {
			$_SESSION['sms_send_num'] = 1;
		}

		if(!empty($_SESSION['sms_send_time']) && $_SESSION['sms_send_time'] + $this->sms_send_time > time()){
			//exit('操作频繁,请'.($_SESSION['sms_send_time'] + $this->sms_send_time - time()).'秒后重试');
			return '操作频繁,请'.($_SESSION['sms_send_time'] + $this->sms_send_time - time()).'秒后重试';
		}

		if ($_SESSION['sms_send_num'] > $this->sms_send_num) {
			$_SESSION['sms_send_black'] = time();
			unset($_SESSION['sms_send_num']);
			unset($_SESSION['sms_send_time']);
			return '发送次数超过限制';
		}
		return true;
	}

	//发送短信验证码
	public function send_sms($mobile,$send_code){
		// 短信接口地址
		$target = $this->url;
		//获取手机号
		$mobile = $mobile;
		//获取验证码
		//$send_code = md5($send_code);
		//生成的随机数
		$mobile_code = $send_code;//$this->random(4,1);
		if(empty($mobile)){
			exit('手机号码不能为空');
		}

		$preg = "/^1[3456789]\d{9}$/";
		if (!preg_match($preg, $mobile)) {
			exit('手机号码不正确');
		}

		//校验图形验证码
		/*if(empty($_SESSION['vcode']) or $send_code!=$_SESSION['vcode']){
			exit('请输入正确验证码');
		}*/

		//防止恶意攻击 session 部分代码最好通过redis代替session实现
		$saferes = $this->sms_safe();
		if ($saferes !== true){
		    return $saferes;
        }
		$content = "您的验证码是：".$mobile_code."。请不要把验证码泄露给其他人。" ; 
		$post_data = "account=".$this->appid ."&password=".$this->appkey ."&mobile=".$mobile."&content=".rawurlencode($content);
		$gets = $this-> xml_to_array($this->post($post_data, $target));
		//  写日志
        $data = date("Y-m-d H:i:s").' 返回码 : '. $gets['SubmitResult']['code'] .', 返回描述 : '.$gets['SubmitResult']['msg'].' . 发送号码 : '.$mobile.' , 短信详情 : '.$content.PHP_EOL;
        file_put_contents($this->log_file,$data,FILE_APPEND);
		//  判断是否成功
        if($gets['SubmitResult']['code']==2){
			$_SESSION['mobile']			= $mobile;
			$_SESSION['mobile_code']	= $mobile_code;
			$_SESSION['sms_send_time']	= time();
			$_SESSION['sms_send_num']	+= 1;
            return true;
		}
		return $gets['SubmitResult']['msg'];
	}
}

?>