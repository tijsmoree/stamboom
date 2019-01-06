<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\components\AccessFilter;
use yii\web\Response;
use yii\web\HttpException;

use app\models\Person;
use app\models\Moment;
use app\models\Spouse;

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

    $attributes = Yii::$app->request->post();
    $person->setAttributes($attributes);

    if (!$person->save()) {
      throw new HttpException(422, json_encode($person->errors));
    }

    if ($attributes['birth'] ?? null) {
      if ($person->birth_id) {
        $birth = Moment::findOne($person->birth_id);
        if (!$birth) {
          throw new HttpException(404, 'Can\'t find the birth moment.');
        }
      } else {
        $birth = new Moment();
      }

      $birth->setAttributes($attributes['birth']);
      $birth->location_id = $attributes['birth']['location']['id'] ?? null;
      $birth->save();

      $person->birth_id = $birth->id;
      $person->save();
    } else {
      if ($person->birth_id) {
        $birth = Moment::findOne($person->birth_id);
        if (!$birth) {
          throw new HttpException(404, 'Can\'t find the birth moment.');
        }
        
        $person->birth_id = null;
        $person->save();
        
        $birth->delete();
      }
    }

    if ($attributes['death'] ?? null) {
      if ($person->death_id) {
        $death = Moment::findOne($person->death_id);
        if (!$death) {
          throw new HttpException(404, 'Can\'t find the death moment.');
        }
      } else {
        $death = new Moment();
      }

      $death->setAttributes($attributes['death']);
      $death->location_id = $attributes['death']['location']['id'] ?? null;
      $death->save();

      $person->death_id = $death->id;
      $person->save();
    } else {
      if ($person->death_id) {
        $death = Moment::findOne($person->death_id);
        if (!$death) {
          throw new HttpException(404, 'Can\'t find the death moment.');
        }
        
        $person->death_id = null;
        $person->save();
        
        $death->delete();
      }
    }

    $marriageIdsNow = Spouse::find()
      ->select(['marriage_id'])
      ->where(['person_id' => $person->id])
      ->all();
    $marriageIdsNow = array_map(function ($m) {
      return $m->marriage_id;
    }, $marriageIdsNow);
    $marriageIdsToBe = array_column(Yii::$app->request->post('marriages', []), 'id');
    foreach (array_diff($marriageIdsNow, $marriageIdsToBe) as $marriageId) {
      $marriage = Marriage::findOne($marriageId);
      if (!$marriage) {
        throw new HttpException(404, 'Can\'t find a marriage.');
      }
      $marriage->delete();
    }
    foreach (Yii::$app->request->post('marriages', []) as $marriageIn) {
      if ($marriageIn['id']) {
        $marriage = Marriage::findOne($marriageIn['id']);

        if ($marriageIn['marriage']) {
          if ($marriage->marriage_id) {
            $marriageMarriage = Moment::findOne($marriage->marriage_id);
            if (!$marriageMarriage) {
              throw new HttpException(404, 'Can\'t find the marriage moment.');
            }
          } else {
            $marriageMarriage = new Moment();
          }
    
          $marriageMarriage->setAttributes($marriageIn['marriage']);
          $marriageMarriage->location_id = $marriageIn['marriage']['location']['id'] ?? null;
          $marriageMarriage->save();
    
          $marriage->marriage_id = $marriageMarriage->id;
          $marriage->save();
        } else {
          if ($marriage->marriage_id) {
            $marriageMarriage = Moment::findOne($marriage->marriage_id);
            if (!$marriageMarriage) {
              throw new HttpException(404, 'Can\'t find the marriage moment.');
            }
            
            $marriageMarriage->delete();
          }
        }

        if ($marriageIn['divorce']) {
          if ($marriage->marriage_id) {
            $marriageDivorce = Moment::findOne($marriage->divorce_id);
            if (!$marriageDivorce) {
              throw new HttpException(404, 'Can\'t find the divorce moment.');
            }
          } else {
            $marriageDivorce = new Moment();
          }
    
          $marriageDivorce->setAttributes($marriageIn['divorce']);
          $marriageDivorce->location_id = $marriageIn['divorce']['location']['id'] ?? null;
          $marriageDivorce->save();
    
          $marriage->divorce_id = $marriageDivorce->id;
          $marriage->save();
        } else {
          if ($marriage->divorce_id) {
            $marriageDivorce = Moment::findOne($marriage->divorce_id);
            if (!$marriageDivorce) {
              throw new HttpException(404, 'Can\'t find the divorce moment.');
            }
            
            $marriageDivorce->delete();
          }
        }

        $spouse = array_values(array_filter($marriage->people, function ($p) use ($person) {
          return $p->id !== $person->id;
        }))[0] ?? null;
        if (!$spouse || $spouse->id != $marriageIn['spouse']['id']) {
          $spouses = Spouse::find()
            ->where(['marriage' => $marriage->id])
            ->andWhere(['not', 'person_id', $person->id])
            ->all();

          foreach($spouses as $s) {
            $s->delete();
          }

          $spouse = new Spouse();
          $spouse->person_id = $marriageIn['spouse']['id'];
          $spouse->marriage_id = $marriage->id;
          $spouse->save();
        }
      } else {
        $marriage = new Marriage();

        if ($marriageIn['marriage']) {
          $marriageMarriage = new Moment();
          $marriageMarriage->setAttributes($marriageIn['marriage']);
          $marriageMarriage->location_id = $marriageIn['marriage']['location']['id'] ?? null;
          $marriageMarriage->save();

          $marriage->marriage_id = $marriageMarriage->id;
        }

        if ($marriageIn['divorce']) {
          $marriageDivorce = new Moment();
          $marriageDivorce->setAttributes($marriageIn['divorce']);
          $marriageDivorce->location_id = $marriageIn['divorce']['location']['id'] ?? null;
          $marriageDivorce->save();

          $marriage->divorce_id = $marriageDivorce->id;
        }

        $ownSpouse = new Spouse();
        $ownSpouse->person_id = $person->id;
        $ownSpouse->marriage_id = $marriage->id;
        $ownSpouse->save();

        $otherSpouse = new Spouse();
        $otherSpouse->person_id = $marriage->spouse->id;
        $otherSpouse->marriage_id = $marriage->id;
        $otherSpouse->save();
      }

      $marriage->save();
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
