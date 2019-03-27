<?php
/**
 * Yuuuuuu
 * createtime: 2017/8/25 9:13
 */

namespace common\helpers;

use common\models\Config;

class Sms
{
    const API_URL = 'http://120.24.238.58:8888/sms.aspx?action=send';

    /**
     * @param $mobile
     * @param string $code 验证码
     * @param string $type 短信发送类型 备注
     * @return bool|string
     */
    public static function send($mobile, $code = '', $type = 'other')
    {
        if (empty($mobile)) {
            return '短信发送失败: 手机号不能为空';
        }
        if (!preg_match('/^1[0-9]{10}$/', $mobile)) {
            return '短信发送失败: 手机号不正确';
        }


        $appid = Config::getConfig('MESSAGE_APPID');
        $appkey = Config::getConfig('MESSAGE_APPKEY');

        if (empty($appid) || empty($appkey)){
           return '短信发送失败: 请联系管理员配置通讯密匙：'.$code;
        } else {
            //  互忆短信发送
            session_start();
            date_default_timezone_set("PRC");

            require_once 'smshuyi/sms.class.php';
            $sms = new \ihuyi_sms();
            $res = $sms -> send_sms($mobile,$code);
            if ($res !== true){
                return $res;
            }
            return true;
        }
    }

    /**
     * 验证手机验证码
     * @param $mobile
     * @param string $verifycode
     * @return bool|string
     */
    public static function check($mobile, $verifycode = ''){

        session_start();
        if($mobile!=$_SESSION['mobile'] or $verifycode!=$_SESSION['mobile_code'] or empty($verifycode) or empty($mobile)){
            return '手机验证失败。';
        }else{
            $_SESSION['mobile']			= '';
            $_SESSION['mobile_code']	= '';
            return true;
        }

    }

    /**
     * @param int $code
     * @return string
     */
    private static function errorCode($code = 0)
    {
        $code = intval($code);
        $codeMsg = [
            '30' => '密码错误',
            '40' => '账号不存在',
            '41' => '余额不足',
            '42' => '帐号过期',
            '43' => 'IP地址限制',
            '50' => '内容含有敏感词',
            '51' => '手机号码不正确',
        ];
        return isset($codeMsg[$code]) ? '短信发送失败: ' . $codeMsg[$code] : '短信发送失败: 短信接口返回未知错误';
    }
}
