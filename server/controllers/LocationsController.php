<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\components\AccessFilter;
use yii\web\Response;
use yii\web\HttpException;

use app\models\Location;

class LocationsController extends Controller {
  
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
    $locations = Location::find()
      ->select([
        'id',
        'name',
        'state',
        'country',
        'longitude',
        'latitude'
      ])
      ->all();

    return array_map(function ($l) {
      return $l->viewAttributes;
    }, $locations);
  }

  public function actionView($id) {
    $location = Location::findOne($id);
    if ($location == null) {
      throw new HttpException(404, 'Can\'t find the location.');
    }

    return $location->viewAttributes;
  }

  public function actionUpdate($id = null) {
    if ($id) {
      $location = Location::findOne($id);
      if ($location == null) {
        throw new HttpException(404, 'Can\'t find the location.');
      }
    } else {
      $location = new Location();
    }

    $location->setAttributes(Yii::$app->request->post());

    if (!$location->save()) {
      throw new HttpException(422, json_encode($location->errors));
    }

    return $this->actionView($location->id);
  }

  public function actionDelete($id) {
    $location = Location::findOne($id);
    if ($location == null) {
      throw new HttpException(404, 'Can\'t find the location.');
    }

    if ($location->user) {
      return false;
    }

    return $location->delete();
  }
}
