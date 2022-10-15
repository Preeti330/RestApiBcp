<?php

return [
    // 'class' => 'yii\db\Connection',
    // 'dsn' => 'mysql:host=' . getenv('MYSQL_HOST')
    //     . ';port=' . getenv('MYSQL_PORT')
    //     . ';dbname=' . getenv('MYSQL_DATABASE'),
    // 'username' => getenv('MYSQL_USERNAME'),
    // 'password' => getenv('MYSQL_PASSWORD'),
    // 'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',


    //heidiSql Connection

    // 'class'=>'yii\db\Connection',
    // 'dsn'=> 'mysql:host=localhost;port=8111;dbname=yii2basic',
    // 'username'=>'root',
    // 'password'=>'',
    // 'charset' =>'utf8',

    //postgre sql Connection

    /*
    'class'=>'yii\db\Connection',
    'dsn'=> 'pgsql:host=localhost;port=5432;dbname=testing',
    'username'=>'postgres',
    'password'=>'bcp',
    'charset' =>'utf8',


*/
    'class'=>'yii\db\Connection',
    'dsn'=> 'pgsql:host=localhost;port=5432;dbname=libmang',
    'username'=>'postgres',
    'password'=>'bcp',
    'charset' =>'utf8',

];
