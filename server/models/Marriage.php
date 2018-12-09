<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "stamboom.marriages".
 * @property integer $id
 * @property integer $male_id
 * @property integer $female_id
 * @property integer $marriage_id
 * @property integer $divorce_id
 * @property string $created_at
 * @property string $updated_at
 */
class Marriage extends ActiveRecord {

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
    return 'marriages';
  }

  public function beforeSave($insert) {
    $this->source = $this->source ?? '';
    
    return parent::beforeSave($insert);
  }

  public function getMale() {
    return $this->hasOne(Person::className(), ['id' => 'male_id']);
  }

  public function getFemale() {
    return $this->hasOne(Person::className(), ['id' => 'female_id']);
  }

  public function getMarriage() {
    return $this->hasOne(Moment::className(), ['id' => 'marriage_id']);
  }

  public function getDivorce() {
    return $this->hasOne(Moment::className(), ['id' => 'divorce_id']);
  }

  public function getViewAttributes() {
    $result = $this->getAttributes([
      'id'
    ]);

    $result['male'] = $this->male ? $this->male->simpleAttributes : null;
    $result['female'] = $this->female ? $this->female->simpleAttributes : null;

    $result['marriage'] = $this->marriage ? $this->marriage->viewAttributes : null;
    $result['divorce'] = $this->divorce ? $this->divorce->viewAttributes : null;

    return $result;
  }
}
