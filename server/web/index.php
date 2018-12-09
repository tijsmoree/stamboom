<?php

if (gethostname() == "Bacchus") {
  defined('YII_DEBUG') or define('YII_DEBUG', false);
  defined('YII_ENV') or define('YII_ENV', 'live');
} else {
  defined('YII_DEBUG') or define('YII_DEBUG', true);
  defined('YII_ENV') or define('YII_ENV', 'local');
}

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
