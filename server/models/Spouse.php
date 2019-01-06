<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "stamboom.spouses".
 * @property integer $id
 * @property integer $person_id
 * @property integer $marriage_id
 * @property string $created_at
 * @property string $updated_at
 */
class Spouse extends ActiveRecord {

  public function behaviors() {
    return [
      'timestamp' => [
        'class' => TimestampBehavior::className(),
        'value' => new Expression('UTC_TIMESTAMP()'),
      ],
      'logs' => [
        'class' => 'app\components\LogBehavior'
      ]
    ];
  }

  public static function tableName() {
    return 'spouses';
  }

  public function getPerson() {
    return $this->hasMany(Person::className(), ['id' => 'person_id']);
  }

  public function getMarriage() {
    return $this->hasOne(Marriage::className(), ['id' => 'marriage_id']);
  }
}
