<?php
return [
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'modules' => [
        'redactor' => [
            'class' => 'backend\components\RedactorModule',
            'uploadDir' => '/uploads',  // 比如这里可以填写 ./uploads
            'uploadUrl' => '@web/uploads',
            'imageAllowExtensions' => ['jpg', 'png', 'gif']
        ],
    ],
];
