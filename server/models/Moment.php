<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "stamboom.moments".
 *
 * @property integer $id
 * @property string $date
 * @property integer $location_id
 * @property string $source
 * @property string $created_at
 * @property string $updated_at
 */
class Moment extends ActiveRecord {

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
    return 'moments';
  }

  public function beforeSave($insert) {
    $this->source = $this->source ?? '';

    return parent::beforeSave($insert);
  }

  public function rules() {
    return [
      [['source'], 'string'],
      [['date'], 'date', 'format' => 'php:Y-m-d']
    ];
  }

  public function getLocation() {
    return $this->hasOne(Location::className(), ['id' => 'location_id']);
  }

  public function getOwner() {
    return $this->hasOne(Person::className(), ['birth_id' => 'id']) ??
           $this->hasOne(Person::className(), ['death_id' => 'id']);
  }

  public function getViewAttributes() {
    $result = $this->getAttributes([
      'id',
      'date',
      'source'
    ]);

    $result['location'] = $this->location ? $this->location->viewAttributes : null;

    return $result;
  }
}
