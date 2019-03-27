<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-2ze7h8xbsvo42u3xa.mysql.rds.aliyuncs.com;dbname=jingtao',
            'enableSchemaCache' => true,
            'username' => 'jingtao',
            'password' => 'JtIpTZx1J6gx',
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
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 0,
            ],
        ],
    ],
];
