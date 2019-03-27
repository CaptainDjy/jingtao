<?php

namespace common\widgets\kindeditor;

use yii\web\AssetBundle;

/**
 * Class KindeditorAsset
 * @package phpsdks\kindeditor
 */
class KindeditorAsset extends AssetBundle
{
    public $sourcePath = '@npm/kindeditor';

    public function init()
    {
        parent::init();
        if (YII_DEBUG) {
            $this->js[] = 'kindeditor-all.js';
        } else {
            $this->js[] = 'kindeditor-all-min.js';
        }
    }

    public $css = [
        'themes/default/default.css'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'common\assets\AdminLteAsset'
    ];

    public $publishOptions = [
        'except' => [
            'asp',
            'asp.net',
            'attached',
            'docs',
            'jsp',
            'lib',
            'php',
            'test',
            'changelog.txt',
            'component.json',
            'license.txt',
            'package.json',
            'README.md',
        ]
    ];
}
