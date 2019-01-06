<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "stamboom.locations".
 *
 * @property integer $id
 * @property string $name
 * @property string $state
 * @property string $country
 * @property string $longitude
 * @property string $latitude
 * @property string $created_at
 * @property string $updated_at
 */
class Location extends ActiveRecord {

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
    return 'locations';
  }

  public function beforeSave($insert) {
    $this->state = $this->state ?? '';
    $this->country = $this->country ?? '';
    
    return parent::beforeSave($insert);
  }

  public function rules() {
    return [
      [['name'], 'required'],
      [['name', 'state', 'country'], 'string'],
      [['longitude', 'latitude'], 'number']
    ];
  }

  public function getViewAttributes() {
    return $this->getAttributes([
      'id',
      'name',
      'state',
      'country',
      'longitude',
      'latitude'
    ]);
  }
}
