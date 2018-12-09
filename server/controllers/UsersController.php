<?php

namespace app\controllers;

use Yii;
use yii\web\HttpException;
use yii\web\Controller;
use app\components\AccessFilter;
use yii\web\Response;
use yii\db\Expression;

use app\models\User;

class UsersController extends Controller {

  private $attempts = 5;
  
  public function beforeAction($action) {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $this->enableCsrfValidation = false;
    return parent::beforeAction($action);
  }

  public function behaviors() {
    return [
      'access' => [
        'class' => AccessFilter::className(),
        'public' => ['login', 'logout'],
        'admin' => ['index', 'view', 'update', 'delete']
      ]
    ];
  }

  public function actionLogin() {
    if (Yii::$app->request->isPost) {
      $user = User::findByMail(Yii::$app->request->post('mail'));
      $password = Yii::$app->request->post('password');
      if ($user) {
        if ($user->attempts >= $this->attempts) {
          throw new HttpException(203);
        }
        if ($user->validatePassword($password)) {
          $token = hash('sha256', $user->mail . time() . rand(1000, 9999));

          $res = [];
          $res['name'] = $user->person->name;
          $res['token'] = $token;

          $user->attempts = 0;
          $user->logged_at = new Expression('UTC_TIMESTAMP()');
          $user->token = $token;
          $user->save();

          return $res;
        } else {
          $user->attempts++;
          $user->save();
        }
      }
    }
    throw new HttpException(203);
  }

  public function actionInfo() {
    return [
      'admin' => Yii::$app->user->identity->admin,
      'mail' => Yii::$app->user->identity->mail,
      'person' => [
        'id' => Yii::$app->user->identity->person_id,
        'name' => Yii::$app->user->identity->person->name
      ]
    ];
  }

  public function actionProfile() {
    $user = User::findOne(Yii::$app->user->identity->id);
    if ($user == null) {
      throw new HttpException(404, 'Can\'t find the user.');
    }

    $data = Yii::$app->request->post();

    if (isset($data['password'])) {
      $user->setPassword($data['password']);
    }
    if (isset($data['mail'])) {
      $user->mail = $data['mail'];
    }  
    
    return $user->save();
  }

  public function actionIndex() {
    $users = User::find()->all();

    return array_map(function ($u) {
      $r = $u->getAttributes([
        'id',
        'mail',
        'person_id',
        'admin'
      ]);
      $r['person'] = $u->person->simpleAttributes;
      return $r;
    }, $users);
  }

  public function actionView($id) {
    $user = User::findOne($id);
    if ($user == null) {
      throw new HttpException(404, 'Can\'t find the user.');
    }

    $res = $user->getAttributes([
      'id',
      'mail',
      'admin',
      'logged_at',
      'attempts'
    ]);
    $res['logged_at'] = $user->logged_at ? date("Y-m-d H:i:s", strtotime($user->logged_at) + date('Z')) : null;
    $res['person'] = $user->person->simpleAttributes;
    return $res;
  }
  
  public function actionUpdate($id = null) {
    $data = Yii::$app->request->post();

    if ($id) {
      $user = User::findOne($id);
      if ($user == null) {
        throw new HttpException(404, 'Can\'t find the user.');
      }

      if (isset($data['password'])) {
        $user->setPassword($data['password']);
      }
    } else {
      $user = new User();
      $user->setPassword($data['password']);
    }

    unset($data['password']);
    unset($data['logged_at']);

    $user->setAttributes($data);
    $user->person_id = $data['person']['id'];
    $user->save();
    return $this->actionView($user->id);
  }

  public function actionDelete($id) {
    $user = User::findOne($id);
    if ($user == null) {
      throw new HttpException(404, 'Can\'t find the user.');
    }

    return $user->delete();
  }
}
