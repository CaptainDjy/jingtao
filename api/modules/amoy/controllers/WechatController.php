<?php

namespace api\modules\amoy\controllers;

class WechatController
{
    private $redirect_url = "http://www.jimiyh.top/jimiyh/jmyh/public/api/amoy/auth/wechat";//这里填你自己的地址！！要能访问到这个类下的getOpenid方法！！
//用户同意授权，获取openid
//应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid）,  snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。）
    public function getOpenid($scope = 'snsapi_base')
    {
        if (!isset($_GET['code'])) {
            $url = $this->get_authorize_url($scope);
            Header("Location: $url");
            exit();
        } else {
//获取code码用以获取openid
            $code = $_GET['code'];
            $res = $this->get_token($code);
            return $res;//完整数据
//return $res['openid'];
        }
    }

    /**
     * 拼接url
     */
    public function get_authorize_url($scope)
    {
        $appid = 'wx05e3c5e82223a262';//微信公众号appid
        $state = $_GET['id'];//状态码
//处理回调域名
        $redirect_url = $this->redirect_url;
        $redirect_url = urlencode($redirect_url);    //对中文转码
//拼接url
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?";
        $url = $url . 'appid=' . $appid;
        $url = $url . '&redirect_uri=' . $redirect_url;
        $url = $url . '&response_type=code';
        $url = $url . "&scope=" . $scope;     //应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。
        $url = $url . "&state=" . $state;
        return $url;
    }

    /**
     * 通过code换取openid/token
     */
    public function get_token($code = '')
    {
        $appid = 'wx05e3c5e82223a262';//微信公众号appid
        $secret = '9831b6be73194063c5a130b24f5346f6';//应用密钥

        $wx_access_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
        $wx_access_url = $wx_access_url . 'appid=' . $appid;
        $wx_access_url = $wx_access_url . '&secret=' . $secret;
        $wx_access_url = $wx_access_url . '&code=' . $code;
        $wx_access_url = $wx_access_url . '&grant_type=authorization_code';

        $res = $this->http($wx_access_url);    //发送网络请求
        if ($res[0] == 200) {
            $arr = json_decode($res[1], true);   //返回数组
            return $arr;
        }
        return $res[1];
    }

    /**
     * 微信获取用户信息
     */
    public function getUserInfo()
    {
        $data = $this->getOpenid('snsapi_userinfo');//获取用户信息
        $access_token = $data['access_token'];
        $openId = $data['openid'];

        $infourl = 'https://api.weixin.qq.com/sns/userinfo?';
        $infourl = $infourl . 'access_token=' . $access_token;
        $infourl = $infourl . '&openid=' . $openId;
        $infourl = $infourl . '&lang=zh_CN';
        $info = $this->http($infourl);  //用户信息数据
        if ($info[0] == 200) {
            return json_decode($info[1], true);//返回包含信息的数组
        }
        return $info[1];
    }

//发送网络请求
    public function http($url, $method = '', $postfields = null, $headers = array(), $debug = false)
    {
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
//没有这下面一句，可能报错“SSL certificate problem: unable to get local issuer certificate”
//但是这一句是跳过验证，所以安全敏感度不高的可以用这个方法
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, 0);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));

            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return array($http_code, $response);
    }
}