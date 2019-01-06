<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "stamboom.marriages".
 * @property integer $id
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

  public function beforeDelete() {
    if (!parent::beforeDelete()) {
      return false;
    }

    foreach ($this->spouses as $spouse) {
      $spouse->delete();
    }
    
    return true;
  }

  public function getSpouses() {
    return $this->hasMany(Spouse::className(), ['marriage_id' => 'id']);
  }

  public function getPeople() {
    return $this->hasMany(Person::className(), ['id' => 'person_id'])->viaTable('spouses', ['marriage_id' => 'id']);
  }

  public function getMarriage() {
    return $this->hasOne(Moment::className(), ['id' => 'marriage_id']);
  }

  public function getDivorce() {
    return $this->hasOne(Moment::className(), ['id' => 'divorce_id']);
  }

  public function getViewAttributes($person_id) {
    $result = $this->getAttributes([
      'id'
    ]);

    $result['spouse'] = array_values(array_filter($this->people, function ($p) use ($person_id) {
      return $p->id !== $person_id;
    }))[0]->simpleAttributes ?? null;

    $result['marriage'] = $this->marriage ? $this->marriage->viewAttributes : null;
    $result['divorce'] = $this->divorce ? $this->divorce->viewAttributes : null;

    return $result;
  }
}
