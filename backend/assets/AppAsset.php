<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
    ];
    public $js = [
        'static/libs/require.js',
        'static/libs/main.js',
        'static/libs/layer/layer.js'
    ];
    public $depends = [
        'common\assets\AdminLteAsset'
    ];
}
