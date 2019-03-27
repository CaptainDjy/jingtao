<?php
$params = array_merge(
	require (__DIR__ . '/../../common/config/params.php'),
	require (__DIR__ . '/../../common/config/params-local.php'),
	require (__DIR__ . '/params.php'),
	require (__DIR__ . '/params-local.php')
);

return [
	'id' => 'chain-api',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'api\controllers',
	'defaultRoute' => 'amoy/taobao-buy/nothing', //  缺省路由
	'bootstrap' => ['log'],
	'modules' => [
		'amoy' => [
			'class' => 'api\modules\amoy\Module',
		],
	],
	'components' => [
		'user' => [
			'identityClass' => 'common\models\User',
			'enableSession' => false,
			'loginUrl' => null,
			'identityCookie' => ['name' => '_identity-api', 'httpOnly' => true],
			'authTimeout' => 3600 * 24 * 30,
		],
		'errorHandler' => [
			'class' => 'api\components\RestErrorHandler',
		],
		'wechat' => [
			'class' => 'api\components\wechat\Wechat',
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 5 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
				// "<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>" => "<module>/<controller>/<action>",
				// "<controller:\w+>/<action:\w+>/<id:\d+>" => "<controller>/<action>",
				// "<controller:\w+>/<action:\w+>" => "<controller>/<action>"],
				// 	'notify/pay/<type:\w+>' => 'notify/pay',
				// 	//                [
				// 	//                    'class' => 'yii\rest\UrlRule',
				// 	//                    'controller' => ['api\controllers'],
				// 	//                    'patterns' => [
				// 	//                        '' => 'options'
				// 	//                    ]
				// 	//                ]
			],

		],
		'redis' => [
			'class' => 'yii\redis\Connection',
			'hostname' => 'localhost',
			'port' => 6379,
			'database' => 0,
		],
	],
	'params' => $params,
];
