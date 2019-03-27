<?php

namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main frontend application asset bundle.
 */
class MobileAsset extends AssetBundle
{
    public $basePath = '@webroot/static/mobile';
    public $baseUrl = '@static';
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
    public $css = [
//        'css/common.css',
        'css/style.css',
        '//at.alicdn.com/t/font_388047_y2l4p4ibcgl23xr.css',
    ];
//    public $js = [
//        'js/require.js',
//        'js/config.js',
//        'js/common.js'
//    ];
}
