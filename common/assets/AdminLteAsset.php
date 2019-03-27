<?php

namespace common\assets;

use yii\base\Exception;
use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AdminLteAsset extends AssetBundle
{
    public $sourcePath = '@vendor/dh/adminlte';
    public $css = [
        'dist/css/AdminLTE.min.css',
    ];
    public $js = [
        'plugins/slimScroll/jquery.slimscroll.min.js',
        'dist/js/app.min.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'common\assets\FontAwesomeAsset',
    ];
    public $publishOptions = [
        'except' => [
            'bootstrap',
            'build',
            'documentation',
            'pages',
            'Gruntfile.js',
            'Gruntfile.js',
            'LICENSE',
            '*.lock',
            '*.json',
            '*.md',
            '*.html',
        ]
    ];

    public $skin = '_all-skins';

    public function init()
    {
        if ($this->skin) {
            if (('_all-skins' !== $this->skin) && (strpos($this->skin, 'skin-') !== 0)) {
                throw new Exception('无效的皮肤！');
            }
            $this->css[] = sprintf('dist/css/skins/%s.css', $this->skin);
        }
        parent::init();
    }
}
