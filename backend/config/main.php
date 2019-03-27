<?php
$params = array_merge(
	require (__DIR__ . '/../../common/config/params.php'),
	require (__DIR__ . '/../../common/config/params-local.php'),
	require (__DIR__ . '/params.php'),
	require (__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-backend',
	'name' => 'chain管理后台',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	'controllerNamespace' => 'backend\controllers',
	'components' => [
		'request' => [
			'csrfParam' => '_csrf-backend',
		],
		'user' => [
			'identityClass' => 'backend\models\SystemUser',
			'enableAutoLogin' => true,
			'loginUrl' => ['auth/login'],
			'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
		],
		'authManager' => [
			'class' => 'yii\rbac\DbManager',
			'itemTable' => '{{%system_auth_item}}',
			'itemChildTable' => '{{%system_auth_item_child}}',
			'assignmentTable' => '{{%system_auth_assignment}}',
			'ruleTable' => '{{%system_auth_rule}}',
		],
		'session' => [
			// this is the name of the session cookie used for login on the backend
			'name' => 'tb-backend',
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],

//        'urlManager' => [
		//            'enablePrettyUrl' => true,
		//            'showScriptName' => false,
		//            'rules' => [
		//                '<controller:\w+>/<action:\w+>/<page:\d+>' => '<controller>/<action>',
		//                "<controller:\w+>/<action:\w+>"=>"<controller>/<action>",
		//            ],
		//        ],

	],
	'params' => $params,
];
