<?php

return [
  'id' => 'stamboom',
  'basePath' => dirname(__DIR__),
  'controllerNamespace' => 'app\commands',
  'components' => [
    'cache' => [
      'class' => 'yii\caching\FileCache'
    ],
    'db' => require(__DIR__ . '/db_admin.php')
  ],
  'params' => require(__DIR__ . '/params.php')
];
