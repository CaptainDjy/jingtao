<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=122.114.216.118;dbname=tb',
            'username' => 'tb',
            'password' => 'JEk2SeNdrb',
            'charset' => 'utf8mb4',
            'tablePrefix' => 'dh_'
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => '@cache',
        ],
    ],
];
