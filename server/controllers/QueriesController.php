<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Exception;
use yii\web\HttpException;
use yii\web\Response;
use app\components\AccessFilter;

class QueriesController extends Controller {

  public function beforeAction($action) {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $this->enableCsrfValidation = false;
    return parent::beforeAction($action);
  }

  public function behaviors() {
    return [
      'access' => [
        'class' => AccessFilter::className(),
        'admin' => ['try', 'info']
      ]
    ];
  }

  public function actionTry() {
    $query = Yii::$app->request->post('query', null);
    if ($query == null) {
      return [];
    }

    try {
      $queryObject = Yii::$app->db->createCommand($query);

      $reader = $queryObject->query();
      $reader->setFetchMode(\PDO::FETCH_NAMED);

      try {
        $data = $reader->readAll();
      } catch (\PDOException $e) {
        return ['resultExpected' => false]; 
      }
      $columnCount = $reader->getColumnCount();

      $columns = [];
      if (count($data) > 0) {
        $columns = array_keys($data[0]);
      }

      return [
        'resultExpected' => true,
        'columnCount' => $columnCount,
        'columns' => $columns,
        'data' => $data
      ];
    } catch (Exception $e) {
      throw new HttpException(400, $e->getMessage());
    }
  }

  function actionInfo() {
    $tables = ["persons", "marriages", "moments", "locations", "logs", "users"];

    $results = [];
    foreach ($tables as $tableName) {
      $columns = Yii::$app->db->createCommand("SHOW COLUMNS FROM `" . $tableName . "`")->queryAll();
      foreach ($columns as $i => $column) {
        if (in_array($column['Field'], ['updated_at', 'created_at'])) {
          unset($columns[$i]);
        }
      }
      $results[] = [
        'name' => $tableName,
        'columns' => $columns
      ];
    }

    return $results;
  }
}
