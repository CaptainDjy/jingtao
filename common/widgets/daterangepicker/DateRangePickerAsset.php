<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/25 16:18
 */

namespace common\widgets\daterangepicker;


use yii\web\AssetBundle;

class DateRangePickerAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-daterangepicker/';
    public $js = [
        'daterangepicker.js',
    ];
    public $css = [
        'daterangepicker.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'common\widgets\daterangepicker\MomentAsset',
    ];

    public $publishOptions = [
        'only' => [
            'daterangepicker.css',
            'daterangepicker.js',
        ]
    ];
}
