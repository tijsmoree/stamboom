<?php

namespace app\components;

use yii\base\ActionFilter;
use yii\web\HttpException;
use app\models\User;
use yii\db\Expression;

class AccessFilter extends ActionFilter {

  public $admin = [];
  public $public = [];

  public function beforeAction($action) {
    $token = \Yii::$app->request->headers['authorization'];
    $user = null;

    if ($token) {
      $user = User::findIdentityByAccessToken($token);
      if ($user) {
        $user->logged_at = new Expression('UTC_TIMESTAMP()');
        $user->save();
      }
    }
    
    \Yii::$app->user->setIdentity($user);

    if (!$user && !in_array($action->id, $this->public)) {
      throw new HttpException(401);
    } elseif ($user && !$user->admin && in_array($action->id, $this->admin)) {
      throw new HttpException(403);
    }

    return true;
  }
}
