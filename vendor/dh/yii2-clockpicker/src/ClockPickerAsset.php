<?php
namespace dh\clockpicker;

use yii\web\AssetBundle;

class ClockPickerAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower/clockpicker/dist';
    public $css = [
        'bootstrap-clockpicker.min.css',
    ];
    public $js = [
        'bootstrap-clockpicker.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
