<?php

namespace common\helpers;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Yii;

use Intervention\Image\ImageManagerStatic as Image;

class Poster
{
    public $font_path = '@common/font';
    public $params;

    /**
     * Poster constructor.
     * @param $config
     * @throws \Exception
     */
    public function __construct($config)
    {
        if (!isset($config['uid']) || !isset($config['text'])) {
            throw new \Exception('缺少用户ID或邀请链接');
        }
        $config['size'] = isset($config['size']) ? $config['size'] : 300;
        $config['margin'] = isset($config['margin']) ? $config['margin'] : 10;
        $config['from_path'] = isset($config['from_path']) ? $config['from_path'] : Yii::getAlias('@static/template');

        $config['to_path'] = isset($config['to_path']) ? $config['to_path'] : Yii::getAlias('@public/uploads/invite/') . $config['uid'];
        if (!file_exists($config['to_path'])) {
            mkdir($config['to_path'], 0777, true);
        }
        $config['qrcode_path'] = $config['to_path'] . '/qrcode.png';
        $config['logo'] = isset($config['logo']) ? $this->dealLogo($config) : null;
        $this->params = $config;
//        print_r($this->params);
//        exit;
    }

    private function dealLogo($config)
    {
        if (!preg_match('/^http/', $config['logo'])) {
            return \Yii::getAlias("@public") . $config['logo'];
        }
        $res = $this->curlGet($config['logo']);
        if ($res['code'] == 200) {
            //把URL格式的图片转成base64_encode格式的！
            $imgBase64Code = "data:image/jpeg;base64," . base64_encode($res['data']);
            $img_content = $imgBase64Code;//图片内容
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result)) {
                $type = $result[2];//得到图片类型png?jpg?gif?
                if (file_put_contents($config['to_path'] . "/logo.{$type}", base64_decode(str_replace($result[1], '', $img_content)))) {
                    return $config['to_path'] . "/logo.{$type}";
                }
            }
        }
        return null;
    }

    /**
     * @param $img
     * @param $watermark
     * @return \Intervention\Image\Image
     */
    public function watermarkLogo($img, $watermark)
    {
        $img = Image::make($img);
        $watermark = Image::make($watermark);
        $watermark->resize(50, 50);
        $img->insert($watermark, 'center');
        return $img;
    }

    /**
     * @param $img
     * @param $watermark
     * @return \Intervention\Image\Image
     */
    public function watermarkQrcode($img, $watermark)
    {
        $img = Image::make($img);
        $watermark = Image::make($watermark);
        $img->insert($watermark, 'top-left', 250, 884);
        return $img;
    }

    /**
     * @return string
     * @throws \Endroid\QrCode\Exception\InvalidWriterException
     */
    public function qrcode()
    {
        $qrCode = new QrCode($this->params['text']);
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
        $qrCode->setSize($this->params['size']);
        $qrCode->setWriterByName('png');
        $qrCode->setMargin($this->params['margin']);
        $qrCode->setValidateResult(false);
        $qrCode->writeFile($this->params['qrcode_path']);
//       print_r($this->params['logo']);
//       exit;
        if (!empty($this->params['logo'])) {
            $qrHandle = fopen($this->params['qrcode_path'], 'a+');
            $logoHandle = fopen($this->params['logo'], 'a+');
            $img = $this->watermarkLogo($qrHandle, $logoHandle);
            $img->save($this->params['qrcode_path']);
        }

        if (preg_match('/share/', $this->params['to_path'])) {
            return \Yii::$app->urlManager->hostInfo . '/public/uploads/poster/' . $this->params['uid'] . '/qrcode.png';
        } else {
            return \Yii::$app->urlManager->hostInfo . '/public/uploads/invite/' . $this->params['uid'] . '/qrcode.png';
        }
    }

    /**
     * 海报图地址
     * @return array
     * @throws \Endroid\QrCode\Exception\InvalidWriterException
     */
    public function sharePoster()
    {
        $this->qrcode();
        $watermark = $this->params['qrcode_path'];
        $sharePosters = [];

        for ($i = 1; $i <=3; $i++) {
//            $watermarkHandle = file_get_contents($watermark);
            $watermarkHandle = fopen($watermark, 'a+');
            $savePath = $this->params['to_path'] . '/share' . $i . '.jpg';
            $shareLink = \Yii::$app->urlManager->hostInfo . '/uploads/invite/' . $this->params['uid'] . '/share' . $i . '.jpg';
            $img = $this->params['from_path'] . '/bg' . $i . '.jpg';
            $imgHandle = fopen($img, 'a+');
            $img = $this->watermarkQrcode($imgHandle, $watermarkHandle);
            $img->save($savePath);
            $sharePosters[] = $shareLink;
        }

        return $sharePosters;
    }

    private function curlGet($url)
    {
        $header = array(
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
            'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip, deflate',);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $res['data'] = curl_exec($curl);
        $res['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $res;
    }

}
