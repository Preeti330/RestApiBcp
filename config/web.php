<?php

$params = include __DIR__ . '/params.php';


$config = [
    'id' => 'boilerplate-api',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/Calcutta',
    'components' => [
        'request' => [
            'cookieValidationKey' =>'bb2e2e33e0c704828580da2c4fa3d2204ca0e45e3cdb703ce0674296fc417df6',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache'
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'assetManager' => [
            'baseUrl' => '/api/assets',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
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
        'db' => include __DIR__ . '/db.php',

        'urlManager' => [
            'baseUrl' => '/api',    // Added for
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                'ping' => 'site/ping',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/user',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' => 'options',
                        'POST login' => 'login',
                        'OPTIONS login' => 'options',
                        'POST signup' => 'signup',
                        'OPTIONS signup' => 'options',
                        'POST confirm' => 'confirm',
                        'OPTIONS confirm' => 'options',
                        'POST password-reset-request' => 'password-reset-request',
                        'OPTIONS password-reset-request' => 'options',
                        'POST password-reset-token-verification' => 'password-reset-token-verification',
                        'OPTIONS password-reset-token-verification' => 'options',
                        'POST password-reset' => 'password-reset',
                        'OPTIONS password-reset' => 'options',
                        'GET me' => 'me',
                        'POST me' => 'me-update',
                        'OPTIONS me' => 'options',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/staff',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' => 'options',
                        'POST login' => 'login',
                        'OPTIONS login' => 'options',
                        'GET get-permissions' => 'get-permissions',
                        'OPTIONS get-permissions' => 'options',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/setting',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'GET public' => 'public',
                        'OPTIONS public' => 'options',
                    ]
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/bookcopy',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' => 'options',
                        'POST login' => 'login',
                        'OPTIONS login' => 'options',
                        'GET get-permissions' => 'get-permissions',
                        'OPTIONS get-permissions' => 'options',

                        'POST loginpage' => 'loginpage',
                        'OPTIONS loginpage' => 'options',

                        'POST updateuser' => 'updateuser',
                        'OPTIONS updateuser' => 'options',
                    ]
                ],


                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/page',
                    'pluralize' => false,
                    'tokens' => [
                    ],
                    'extraPatterns' => [
                        'GET sse' => 'sse',
                        'OPTIONS sse' => 'sse',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/student',
                    'pluralize' => false,
                    'tokens' => [
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' => 'options',
                        'GET sse' => 'sse',
                        'OPTIONS sse' => 'sse',
                        'GET read' => 'read',
                        'OPTIONS read' => 'options',


                        'POST select' => 'select',
                        'OPTIONS select' => 'options',

                        'GET selectbyid'=>'selectbyid',
                        'OPTIONS selectbyid'=>'options',

                        'GET selectbyname'=>'selectbyname',
                        'OPTIONS selectbyname'=>'options',

                        'POST insertintotable'=>'insertintotable',
                        'OPTIONS insertintotable'=>'options',

                        'POST updaterecord'=>'updaterecord',
                        'OPTIONS updaterecord'=>'options',

                        'GET deletebyid' =>'deletebyid',
                         'OPTION deletebyid'=>'options',

                    ]
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/transaction',
                    'pluralize' => false,
                    'tokens' => [
                    ],
                    'extraPatterns' => [
                        'GET sse' => 'sse',
                        'OPTIONS sse' => 'sse',

                        'POST displayname'=>'displayname',
                        'OPTIONS displayname'=>'options',

                        'POST readdata'=>'readdata',
                        'OPTIONS readdata'=>'options',

                        'POST userinfo'=>'userinfo',
                        'OPTIONS userinfo'=>'options',

                        ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/book',
                    'pluralize' => false,
                    'tokens' => [
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' => 'options',

                        'POST loginpage' => 'loginpage',
                        'OPTIONS loginpage' => 'options',

                        'GET sse' => 'sse',
                        'OPTIONS sse' => 'sse',

                        'POST adduser' => 'adduser',
                        'OPTIONS adduser' => 'options',

                        'POST updateuser' => 'updateuser',
                        'OPTIONS updateuser' => 'options',

                        'GET selectuser' => 'selectuser',
                        'OPTIONS selectuser' => 'options',

                        'GET selectbooklist' => 'selectbooklist',
                        'OPTIONS selectbooklist' => 'options',

                        'POST updatebooklist' => 'updatebooklist',
                        'OPTIONS updatebooklist' => 'options',

                        'GET deletebooklist' => 'deletebooklist',
                        'OPTIONS deletebooklist' => 'options',

                        'POST addbooklist' => 'addbooklist',
                        'OPTIONS addbooklist' => 'options',

                        'GET findbookcopies' => 'findbookcopies',
                        'OPTIONS findbookcopies' => 'options',

                        'POST addbookcopy' => 'addbookcopy',
                        'OPTIONS addbookcopy' => 'options',

                        'POST updatebookcopy' => 'updatebookcopy',
                        'OPTIONS updatebookcopy' => 'options',

                        'POST addcategory' => 'addcategory',
                        'OPTIONS addcategory' => 'options',

                        'POST updatecategory' => 'updatecategory',
                        'OPTIONS updatecategory' => 'options',

                        'GET selectcategory' => 'selectcategory',
                        'OPTIONS selectcategory' => 'options',

                        'POST issuerequest' => 'issuerequest',
                        'OPTIONS issuerequest' => 'options',

                        'POST returnbook' => 'returnbook',
                        'OPTIONS returnbook' => 'options',

                        'GET disablebooklist' => 'disablebooklist',
                        'OPTIONS disablebooklist' => 'options',

                        'GET disableuser' => 'disableuser',
                        'OPTIONS disableuser' => 'options',

                        'GET disablecategory' => 'disablecategory',
                        'OPTIONS  disablecategory' => 'options',

                        'GET viewbooks' => 'viewbooks',
                        'OPTIONS viewbooks' => 'options',

                        'POST regenratepwd' => 'regenratepwd',
                        'OPTIONS regenratepwd' => 'options',

                        'GET  reportcategory' => 'reportcategory',
                        'OPTIONS  reportcategory' => 'options',

                        'POST  wishlist' => 'wishlist',
                        'OPTIONS   wishlist' => 'options',


                    ]
                ],

            ]
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->format == 'html') {
                    return $response;
                }

                $responseData = $response->data;

                if (is_string($responseData) && json_decode($responseData)) {
                    $responseData = json_decode($responseData, true);
                }

                if ($response->statusCode >= 200 && $response->statusCode <= 299) {
                    $response->data = [
                        'success' => true,
                        'status' => $response->statusCode,
                        'data' => $responseData,
                    ];
                } else {
                    $response->data = [
                        'success' => false,
                        'status' => $response->statusCode,
                        'data' => $responseData,
                    ];
                }
                return $response;
            },
        ]
    ],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*']
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
