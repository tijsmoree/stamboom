<?php

namespace app\controllers;

use app\models\Log;
use Yii;
use yii\web\Controller;
use app\components\AccessFilter;
use yii\web\HttpException;
use yii\web\Response;
use yii\data\ActiveDataProvider;

class LogsController extends Controller {

	public function beforeAction($action) {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $this->enableCsrfValidation = false;
    return parent::beforeAction($action);
  }

  public function behaviors() {
    return [
      'access' => [
        'class' => AccessFilter::className(),
        'admin' => ['index', 'revert']
      ]
    ];
  }

  public function actionIndex($page = 1, $size = 10, $cudFilter = null, $query = null) {
    if ($cudFilter) {
      $q = Log::find()->where(['change_type' => $cudFilter]);
    } else {
      $q = Log::find();
    }
    if($query !== null) {
      $idsQueried = [];
      foreach ($q->each() as $model) {
        if ($model->getSearch($query)) {
          array_push($idsQueried, $model->id);
        }
      }

      $q = $q->andWhere(['id' => $idsQueried]);
    }

    $provider = new ActiveDataProvider([
      'query' => $q
    ]);
    $provider->setSort([
      'defaultOrder' => ['created_at' => SORT_DESC, 'id' => SORT_DESC]
    ]);
    $provider->setPagination([
      'pageSize' => $size,
      'page' => $page - 1
    ]);
    $provider->prepare();

    return [
      "totalCount" => $provider->pagination->totalCount,
      "pageCount" => $provider->pagination->pageCount,
      "logs" => array_map(function ($i) {
        return $i->viewAttributes;
      }, $provider->models),
    ];
  }

  public function actionRevert($id) {
    $log = Log::findOne($id);
    
    if (!$log) {
      throw new HttpException(404, 'Log not found.');
    }

  	return $log->revert();
  }
}