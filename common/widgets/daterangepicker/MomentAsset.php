<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/25 16:25
 */

namespace common\widgets\daterangepicker;


use yii\web\AssetBundle;

class MomentAsset extends AssetBundle
{
    public $sourcePath = '@bower/moment/min';
    public $js = [
        'moment-with-locales.min.js',
    ];
}
