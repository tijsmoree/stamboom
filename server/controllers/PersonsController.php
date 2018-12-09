<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\components\AccessFilter;
use yii\web\Response;
use yii\web\HttpException;

use app\models\Person;

class PersonsController extends Controller {
  
    public function beforeAction($action) {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $this->enableCsrfValidation = false;
    return parent::beforeAction($action);
  }

  public function behaviors() {
    return [
      'access' => [
        'class' => AccessFilter::className()
      ]
    ];
  }

  public function actionIndex() {
    $people = Person::find()
      ->select([
        'persons.id',
        'first_name',
        'nickname',
        'prefix',
        'last_name',
        'sex'
      ])
      ->joinWith(['birth'])
      ->orderBy('moments.date DESC')
      ->all();

    return array_map(function ($p) {
      return $p->simpleAttributes;
    }, $people);
  }

  public function actionView($id) {
    $person = Person::findOne($id);
    if ($person == null) {
      throw new HttpException(404, 'Can\'t find the person.');
    }

    return $person->viewAttributes;
  }

  public function actionUpdate($id = null) {
    if ($id) {
      $person = Person::findOne($id);
      if ($person == null) {
        throw new HttpException(404, 'Can\'t find the person.');
      }
    } else {
      $person = new Person();
    }

    $person->setAttributes(Yii::$app->request->post());

    if (!$person->save()) {
      throw new HttpException(422, json_encode($person->errors));
    }

    return $this->actionView($person->id);
  }

  public function actionDelete($id) {
    $person = Person::findOne($id);
    if ($person == null) {
      throw new HttpException(404, 'Can\'t find the person.');
    }

    if ($person->user) {
      return false;
    }

    return $person->delete();
  }
}
