<?php
/**
 * Created by PHPSTORM.
 * User: Yuuuuuu
 * Date: 2018/10/11
 * Time: 15:59
 */
namespace common\helpers;

/**
 * 创建分享图片
 */
class CreatePic
{
    /**
     * 分享图片生成
     * @param $gData  商品数据，array
     * @param $codeName 二维码图片
     * @param $fileName string 保存文件名,默认空则直接输入图片
     */
    public static function createSharePng($gData,$codeName,$fileName = ''){
        //创建画布
        $im = imagecreatetruecolor(618, 1000);

        //填充画布背景色
        $color = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $color);

        //字体文件
        $font_file = dirname(dirname(__DIR__)) . "/public/static/fonts/yahei.ttf";
        $font_file_bold = dirname(dirname(__DIR__)) . "/public/static/fonts/yahei_bold.ttf";

        //设定字体的颜色
        $font_color_1 = ImageColorAllocate ($im, 140, 140, 140);
        $font_color_2 = ImageColorAllocate ($im, 28, 28, 28);
        $font_color_3 = ImageColorAllocate ($im, 129, 129, 129);
        $font_color_red = ImageColorAllocate ($im, 217, 45, 32);

        $fang_bg_color = ImageColorAllocate ($im, 254, 216, 217);

        //Logo
        list($l_w,$l_h) = getimagesize(dirname(dirname(__DIR__)) . '/public/static/mobile/images/logo.png');
        $logoImg = @imagecreatefrompng(dirname(dirname(__DIR__)) . '/public/static/mobile/images/logo.png');
        imagecopyresized($im, $logoImg, 274, 28, 0, 0, 70, 70, $l_w, $l_h);

        //温馨提示
        imagettftext($im, 14,0, 100, 130, $font_color_1 ,$font_file, '温馨提示：喜欢长按图片识别二维码即可前往购买');

        //商品图片
        list($g_w,$g_h) = getimagesize($gData['pic']);
        $goodImg = self::createImageFromFile($gData['pic']);
        imagecopyresized($im, $goodImg, 0, 185, 0, 0, 618, 618, $g_w, $g_h);

        //二维码
        list($code_w,$code_h) = getimagesize($codeName);
        $codeImg = self::createImageFromFile($codeName);
        imagecopyresized($im, $codeImg, 440, 820, 0, 0, 170, 170, $code_w, $code_h);

        //商品描述
        $theTitle = self::cn_row_substr($gData['title'],2,19);
        imagettftext($im, 14,0, 8, 845, $font_color_2 ,$font_file, $theTitle[1]);
        imagettftext($im, 14,0, 8, 875, $font_color_2 ,$font_file, $theTitle[2]);

        imagettftext($im, 14,0, 8, 935, $font_color_2 ,$font_file, "券后价￥");
        imagettftext($im, 28,0, 80, 935, $font_color_red ,$font_file_bold, $gData["price"]);
        imagettftext($im, 14,0, 8,970, $font_color_3 ,$font_file, "现价￥".$gData["original_price"]);

        //优惠券
        if($gData['coupon_price']){
            imagerectangle ($im, 125 , 950 , 160 , 975 , $font_color_3);
            imagefilledrectangle ($im, 126 , 951 , 159 , 974 , $fang_bg_color);
            imagettftext($im, 14,0, 135,970, $font_color_3 ,$font_file, "券");

            $coupon_price = strval($gData['coupon_price']);
            imagerectangle ($im, 160 , 950 , 198 + (strlen($coupon_price)* 10), 975 , $font_color_3);
            imagettftext($im, 14,0, 170,970, $font_color_3 ,$font_file, $coupon_price."元");
        }

        //输出图片
        if($fileName){
            imagepng ($im,$fileName);
        }else{
            Header("Content-Type: image/png");
            imagepng ($im);
        }

        //释放空间
        imagedestroy($im);
        imagedestroy($goodImg);
        imagedestroy($codeImg);
    }

    /**
     * 从图片文件创建Image资源
     * @param string $file 图片文件，支持url
     * @return bool|resource    成功返回图片image资源，失败返回false
     */
    public static function createImageFromFile($file){
        if(preg_match('/http(s)?:\/\//',$file)){
            $fileSuffix = self::getNetworkImgType($file);
        }else{
            $fileSuffix = pathinfo($file, PATHINFO_EXTENSION);
        }

        if(!$fileSuffix) return false;

        switch ($fileSuffix){
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
     * @param string $url  网络图片url,支持不带后缀名url
     * @return bool
     */
    public static function getNetworkImgType($url){
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
            $theImgType = explode('/',$http_code['content_type']);

            if($theImgType[0] == 'image'){
                return $theImgType[1];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 分行连续截取字符串
     * @param string $str  需要截取的字符串,UTF-8
     * @param int $row  截取的行数
     * @param int $number   每行截取的字数，中文长度
     * @param bool $suffix  最后行是否添加‘...’后缀
     * @return array    返回数组共$row个元素，下标1到$row
     */
    public static function cn_row_substr($str,$row = 1,$number = 10,$suffix = true){
        $result = array();
        for ($r=1;$r<=$row;$r++){
            $result[$r] = '';
        }

        $str = trim($str);
        if(!$str) return $result;

        $theStrlen = strlen($str);

        //每行实际字节长度
        $oneRowNum = $number * 3;
        for($r=1;$r<=$row;$r++){
            if($r == $row and $theStrlen > $r * $oneRowNum and $suffix){
                $result[$r] = self::mg_cn_substr($str,$oneRowNum-6,($r-1)* $oneRowNum).'...';
            }else{
                $result[$r] = self::mg_cn_substr($str,$oneRowNum,($r-1)* $oneRowNum);
            }
            if($theStrlen < $r * $oneRowNum) break;
        }

        return $result;
    }

    /**
     * 按字节截取utf-8字符串
     * 识别汉字全角符号，全角中文3个字节，半角英文1个字节
     * @param string $str  需要切取的字符串
     * @param int $len  截取长度[字节]
     * @param int $start    截取开始位置，默认0
     * @return string
     */
    public static function mg_cn_substr($str,$len,$start = 0){
        $q_str = '';
        $q_strlen = ($start + $len)>strlen($str) ? strlen($str) : ($start + $len);

        //如果start不为起始位置，若起始位置为乱码就按照UTF-8编码获取新start
        if($start and json_encode(substr($str,$start,1)) === false){
            for($a=0;$a<3;$a++){
                $new_start = $start + $a;
                $m_str = substr($str,$new_start,3);
                if(json_encode($m_str) !== false) {
                    $start = $new_start;
                    break;
                }
            }
        }

        //切取内容
        for($i=$start;$i<$q_strlen;$i++){
            //ord()函数取得substr()的第一个字符的ASCII码，如果大于0xa0的话则是中文字符
            if(ord(substr($str,$i,1))>0xa0){
                $q_str .= substr($str,$i,3);
                $i+=2;
            }else{
                $q_str .= substr($str,$i,1);
            }
        }
        return $q_str;
    }




}