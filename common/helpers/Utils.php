<?php

namespace common\helpers;


use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class Utils
{
    /**
     * 获取链接的参数信息
     * @param string $url site/index?type=save
     * @return array|bool
     */
    public static function getUrlParams($url)
    {
        if (empty($url)) {
            return false;
        }
        $urlArr = parse_url($url);
        if (empty($urlArr['query'])) {
            return false;
        }
        parse_str($urlArr['query'], $arr);
        return $arr;
    }

    /**
     * 普通URl格式化为Url::to()的参数
     * @param string $url site/index?type=save
     * @return array
     * @see Url::to()
     */
    public static function parseUrl($url)
    {
        $data = [];
        $arr = parse_url($url);
        $data[] = $arr['path'];
        if (!empty($arr['query'])) {
            parse_str($arr['query'], $query);
            $data = ArrayHelper::merge($data, $query);
        }
        return $data;
    }

    /**
     * 将特殊字符转换为 HTML 实体
     * @param string|array $var HTML字符
     * @return array|mixed
     */
    public static function iHtmlSpecialChars($var)
    {
        if (is_array($var)) {
            foreach ($var as $key => $value) {
                $var[htmlspecialchars($key)] = self::iHtmlSpecialChars($value);
            }
        } else {
            $var = str_replace('&amp;', '&', htmlspecialchars($var, ENT_QUOTES));
        }
        return $var;
    }

    /**
     * 生成随机字符串
     * @param int $length 字符串长度
     * @param bool $numeric true表示纯数字
     * @return string
     */
    public static function random($length, $numeric = FALSE)
    {
        $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
        if ($numeric) {
            $hash = '';
        } else {
            $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
            $length--;
        }
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed{mt_rand(0, $max)};
        }
        return $hash;
    }

    /**
     * 能用的随机数生成
     * @param string $type 类型 alpha/alnum/numeric/nozero/unique/md5/encrypt/sha1
     * @param int $len 长度
     * @return string
     */
    public static function buildRandom( $len = 8,$type = 'alnum')
    {
        switch ($type)
        {
            case 'alpha':
            case 'alnum':
            case 'numeric':
            case 'nozero':
                switch ($type)
                {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'unique':
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt':
            case 'sha1':
                return sha1(uniqid(mt_rand(), TRUE));
        }
    }

    /**
     * 数组转xml
     * @param array $arr
     * @param int $level
     * @return mixed|string
     */
    public static function array2xml($arr, $level = 1)
    {
        $s = $level == 1 ? "<xml>" : '';
        foreach ($arr as $tagName => $value) {
            if (is_numeric($tagName)) {
                $tagName = $value['TagName'];
                unset($value['TagName']);
            }
            if (!is_array($value)) {
                $s .= "<{$tagName}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagName}>";
            } else {
                $s .= "<{$tagName}>" . self::array2xml($value, $level + 1) . "</{$tagName}>";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s . "</xml>" : $s;
    }

    /**
     * base64图片解码上传图片
     * @param $date
     * @return string
     * @throws \yii\db\Exception
     */
    public static function base64pic($date)
    {
        //项目绝对路径
        $url = str_replace('\\', '/', \Yii::getAlias('@public'));

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $date, $result)) {
            $type = $result[2];
            $img = base64_decode(str_replace($result[1], '', $date));
            $new_file = $url . '/uploads/image/' . date('Ym', time()) . '/' . date('d', time()) . '/';
            if (!file_exists($new_file)) {
                mkdir($new_file, 0777, true);
            }
            $info_file = date("YmdHis") . '_' . rand(10000, 99999) . '.' . "{$type}";
            $new_file = $new_file . $info_file;
            //数据库存储地址
            $db_file = '/uploads/image/' . date('Ym', time()) . '/' . date('d', time()) . '/' . $info_file;
            if ($file = file_put_contents($new_file, $img)) {
                return $db_file;
            } else {
                throw new Exception('文件保存失败1');
            }
        } else {
            throw new Exception('文件保存失败2');
        }
    }

    /**
     * 递归生成目录树
     * @param array $data
     * @param int $pid
     * @param string $idName
     * @param string $pidName
     * @param string $sonName
     * @return array
     */
    public static function tree($data = [], $pid = 0, $idName = "id", $pidName = 'pid', $sonName = 'son')
    {
        $arr = [];
        foreach ($data as $v) {
            if ($v[$pidName] == $pid) {
                $v[$sonName] = self::tree($data, $v[$idName], $idName, $pidName);
                $arr[] = $v;
            }
        }
        return $arr;
    }

    /**
     * 输出图像方法
     * @param $src
     * @return string
     */
    public static function toMedia($src)
    {
        $url = \Yii::$app->request->hostInfo;
        $http = strtolower($src);
        if (strpos($http, 'http://') !== false || strpos($http, 'https://') !== false) {
            return $src;
        }
        if (substr($src, 0, 1) == '/') {
            $src = $url .$src;
        } else {
            $src = $url . '/' . $src;
        }

        return $src;
    }


    /**
     * 获取用户真实IP
     * @return array|false|string
     */
    public static function getIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $ip = $_SERVER['HTTP_CDN_SRC_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }

    /**
     * 生成订单号
     * @param string $prefix
     * @param int $id 唯一数字
     * @param int $length
     * @return string
     */
    public static function genderOrderId($id, $prefix = 'order', $length = 5)
    {
        $id = substr($id, -$length);
        return $prefix . date('ymd') . str_pad($id, 5, 0, STR_PAD_LEFT);
    }


    /**
     * 字符串中img中加域名
     * @param string $content
     * @return mixed|string
     */
    public static function get_img_thumb_url($content = "")
    {
        $suffix = \Yii::$app->request->hostInfo;
        $pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
        $content = preg_replace($pregRule, '<img src="' . $suffix . '${1}" style="max-width:100%">', $content);
        return $content;
    }

    /**
     * 截取给定长度字符串
     * @param string $string
     * @param integer $len
     * @return null|string
     */
    public static function cutStr($string = null, $len = 10)
    {
        $str_len = mb_strlen($string);
        if (empty($string) || $str_len <= $len) {
            return $string;
        }
        return mb_substr($string, 0, $len) . '...';
    }

    /**
     * 格式化时间
     * @param null $model ActiveRecord
     * @param array $attributes
     * @param string $format
     * @return bool|ActiveRecord
     * @throws Exception
     */
    public static function formatDate($attributes = [], $model = null, $format = "Y-m-d H:i:s")
    {
        if ($model instanceof ActiveRecord) {
            $modelAttributes = array_keys($model->attributes);
            if (@is_array($attributes)) {
                foreach ($attributes as $attribute) {
                    if (!@in_array($attribute, $modelAttributes)) {
                        throw new Exception('属性不存在:' . $attribute);
                    }
                    if (empty($model->$attribute)) {
                        $model->$attribute = null;
                    } elseif (is_string($model->$attribute)) {
                        $model->$attribute = strtotime($model->$attribute);
                    } elseif (is_int($model->$attribute)) {
                        $model->$attribute = date($format, $model->$attribute);
                    } else {
                        //default null
                        continue;
                    }
                }
                return $model;
            } else {
                //TODO 非数组类型操作
                throw new Exception('请设置数组格式参数');
            }
        } else {
            //TODO 非AR模型操作
            throw new Exception('暂无');
        }
    }

    /**
     * 产生指定长度的随机字符串(基于随机数)
     * @param string $prefix 随机字符串前缀
     * @param int $len 随机字符串长度(不含前缀)
     * @return string
     */
    public static function genderRandomStr($prefix = '', $len = 6)
    {
        $chars = array_merge(range(0, 9), range('A', 'Z'), range('a', 'z'));
        $count = count($chars);
        $str = '';
        if ($len < 0) {
            $len = 0;
        }

        for ($i = 0; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $count - 1)];
        }
        return $prefix . $str;
    }

    /**
     * 生成二维码
     * @param array $gData 商品数据
     * @param string $codeName 二维码图片
     * @param string $fileName 保存文件名,默认空则直接输入图片
     */
    public static function createSharePng(array $gData, $codeName, $fileName = '')
    {
        //创建画布
//        $im = imagecreatetruecolor(618, 900);
        $im = imagecreatefromjpeg(\Yii::getAlias('@static') . "/img/di.jpg");

        //填充画布背景色
        $color = imagecolorallocate($im, 248, 248, 248);
        imagefill($im, 0, 0, $color);

        //字体文件
        $font_file = \Yii::getAlias('@static') . "/fonts/msyh.ttc";

        //设定字体的颜色
        $font_color_2 = ImageColorAllocate($im, 28, 28, 28);
        $font_color_3 = ImageColorAllocate($im, 255, 255, 255);
        $font_color_4 = ImageColorAllocate($im, 255, 78, 0);
        $font_color_5 = ImageColorAllocate($im, 86, 86, 86);

        if ($gData['type'] == 11) {  //淘宝
            list($p_w, $p_h) = getimagesize(\Yii::getAlias('@static') . "/img/tb.jpg");
            $logo = imagecreatefromjpeg(\Yii::getAlias('@static') . "/img/tb.jpg");
        } elseif ($gData['type'] == 12) { //天猫
            list($p_w, $p_h) = getimagesize(\Yii::getAlias('@static') . "/img/tm.jpg");
            $logo = imagecreatefromjpeg(\Yii::getAlias('@static') . "/img/tm.jpg");
        } elseif ($gData['type'] == 21) {  // 京东
            list($p_w, $p_h) = getimagesize(\Yii::getAlias('@static') . "/img/jd.jpg");
            $logo = imagecreatefromjpeg(\Yii::getAlias('@static') . "/img/jd.jpg");
        } else { //拼多多
            list($p_w, $p_h) = getimagesize(\Yii::getAlias('@static') . "/img/pdd.jpg");
            $logo = imagecreatefromjpeg(\Yii::getAlias('@static') . "/img/pdd.jpg");
        }
        imagecopyresized($im, $logo, 30, 435, 50, 50, 20, 20, $p_w, $p_h);

        //商品图片
        @list($g_w, $g_h) = getimagesize($gData['pic']);
        $goodImg = self::createImageFromFile($gData['pic']);
        imagecopyresized($im, $goodImg, 78, 50, 0, 0, 350, 350, $g_w, $g_h);

        //二维码
        @list($code_w, $code_h) = getimagesize($codeName);
        $codeImg = self::createImageFromFile($codeName);
        if (is_resource($codeImg)) {
            imagecopyresized($im, $codeImg, 325, 670, 0, 0, 170, 170, $code_w, $code_h);
        }

        //商品描述
        $theTitle = self::cn_row_substr($gData['title'], 2, 20);
        imagettftext($im, 14, 0, 60, 450, $font_color_2, $font_file, $theTitle[1]);
        imagettftext($im, 14, 0, 60, 480, $font_color_2, $font_file, $theTitle[2]);

        imagettftext($im, 16, 0, 132, 541, $font_color_4, $font_file, $gData["price"]);
        imagettftext($im, 12, 0, 100, 582, $font_color_5, $font_file, $gData["original_price"]);

        //优惠券
        if ($gData['coupon_price']) {

            list($c_w, $c_h) = getimagesize(\Yii::getAlias('@static') . "/img/coupon.jpg");
            $couponImgs = imagecreatefromjpeg(\Yii::getAlias('@static') . "/img/coupon.jpg");
            imagecopyresized($im, $couponImgs, 310, 520, 0, 0, 173, 71, $c_w, $c_h);

            $coupon_price = strval($gData['coupon_price']);
            imagerectangle($im, 160, 950, 198 + (strlen($coupon_price) * 10), 925, $font_color_3);
            imagettftext($im, 28, 0, 335, 566, $font_color_3, $font_file, "￥" . $coupon_price);
        }

        //输出图片
        if ($fileName) {
//            imagepng($im, $fileName);
            imagejpeg($im, $fileName);
        } else {
            header("Content-Type: image/jpeg");
//            imagepng($im);
            imagejpeg($im);
        }

        //释放空间
        imagedestroy($im);
        imagedestroy($goodImg);
        imagedestroy($codeImg);
        exit();
    }

    /**
     * 从图片文件创建Image资源
     * @param string $file 图片文件，支持url
     * @return bool|resource    成功返回图片image资源，失败返回false
     */
    public static function createImageFromFile($file)
    {
        if (preg_match('/http(s)?:\/\//', $file)) {
            $fileSuffix = self::getNetworkImgType($file);
        } else {
            $fileSuffix = pathinfo($file, PATHINFO_EXTENSION);
        }
        if (!$fileSuffix) return false;

        switch ($fileSuffix) {
            case 'jpeg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'jpg':
                $theImage = @imagecreatefromjpeg($file);
                break;
            case 'png':
                $theImage = @imagecreatefrompng($file);
                break;
            case 'gif':
                $theImage = @imagecreatefromgif($file);
                break;
            default:
                $theImage = @imagecreatefromstring(file_get_contents($file));
                break;
        }

        return $theImage;
    }

    /**
     * 获取网络图片类型
     * @param string $url 网络图片url,支持不带后缀名url
     * @return bool
     */
    public static function getNetworkImgType($url)
    {
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //设置需要获取的URL
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //支持https
        curl_exec($ch);//执行curl会话
        $http_code = curl_getinfo($ch);//获取curl连接资源句柄信息
        curl_close($ch);//关闭资源连接

        if ($http_code['http_code'] == 200) {
            $theImgType = explode('/', $http_code['content_type']);

            if ($theImgType[0] == 'image') {
                return $theImgType[1];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param string $str 需要截取的字符串,UTF-8
     * @param int $row 截取的行数
     * @param int $number 每行截取的字数，中文长度
     * @param bool $suffix 最后行是否添加‘...’后缀
     * @return array    返回数组共$row个元素，下标1到$row
     */
    public static function cn_row_substr($str, $row = 1, $number = 10, $suffix = true)
    {
        $result = array();
        for ($r = 1; $r <= $row; $r++) {
            $result[$r] = '';
        }

        $str = trim($str);
        if (!$str) return $result;

        $theStrlen = strlen($str);

        //每行实际字节长度
        $oneRowNum = $number * 3;
        for ($r = 1; $r <= $row; $r++) {
            if ($r == $row and $theStrlen > $r * $oneRowNum and $suffix) {
                $result[$r] = self::mg_cn_substr($str, $oneRowNum - 6, ($r - 1) * $oneRowNum) . '...';
            } else {
                $result[$r] = self::mg_cn_substr($str, $oneRowNum, ($r - 1) * $oneRowNum);
            }
            if ($theStrlen < $r * $oneRowNum) break;
        }

        return $result;
    }

    /**
     * 按字节截取utf-8字符串
     * 识别汉字全角符号，全角中文3个字节，半角英文1个字节
     * @param $str
     * @param $len
     * @param int $start
     * @return string
     */
    public static function mg_cn_substr($str, $len, $start = 0)
    {
        $q_str = '';
        $q_strlen = ($start + $len) > strlen($str) ? strlen($str) : ($start + $len);

        //如果start不为起始位置，若起始位置为乱码就按照UTF-8编码获取新start
        if ($start and json_encode(substr($str, $start, 1)) === false) {
            for ($a = 0; $a < 3; $a++) {
                $new_start = $start + $a;
                $m_str = substr($str, $new_start, 3);
                if (json_encode($m_str) !== false) {
                    $start = $new_start;
                    break;
                }
            }
        }

        //切取内容
        for ($i = $start; $i < $q_strlen; $i++) {
            //ord()函数取得substr()的第一个字符的ASCII码，如果大于0xa0的话则是中文字符
            if (ord(substr($str, $i, 1)) > 0xa0) {
                $q_str .= substr($str, $i, 3);
                $i += 2;
            } else {
                $q_str .= substr($str, $i, 1);
            }
        }
        return $q_str;
    }

    public static function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    public static function getUrlQuery($array_query)
    {
        $tmp = array();
        foreach ($array_query as $k => $param) {
            $tmp[] = $k . '=' . $param;
        }
        $params = implode('&', $tmp);
        return $params;
    }

    /**
     * 获取当前毫秒
     * @return float
     */
    public static function getMsectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }

    /**
     * 根据链接信息获取最终的淘宝ID
     * @param string $clickUrl
     * @return \yii\web\HeaderCollection
     * @throws \yii\base\Exception
     */
    public static function getTaoBaoId($clickUrl = '')
    {
        if (!preg_match('/s\.click\.taobao\.com/', $clickUrl)) {
            throw new \yii\base\Exception('淘宝链接不符合要求');
        }
        $headers = get_headers($clickUrl, TRUE);
        $location = $headers['Location'];
        $locationParams = self::parseUrl($location);
        $tu = $locationParams['tu'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tu);
        curl_setopt($ch, CURLOPT_REFERER, $location);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_exec($ch);
        $dd = curl_getinfo($ch);
        curl_close($ch);
        $item_url = $dd['url'];
        $item_url = self::parseUrl($item_url);
        return $item_url['id'];
    }

    /**
     * @param  float $num 钱数
     * @param  int $n 保留小数
     * @return float|int
     */
    public static function getTwoPrice($num, $n)
    {
        $result = intval($num * pow(10, $n)) / pow(10, $n);
        return $result;
    }

}
