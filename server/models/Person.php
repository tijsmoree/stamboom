<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "stamboom.persons".
 *
 * @property integer $id
 * @property string $first_name
 * @property string $nickname
 * @property string $last_name
 * @property string $sex
 * @property integer $birth_id
 * @property integer $death_id
 * @property integer $father_id
 * @property integer $mother_id
 * @property string $comments
 * @property string $created_at
 * @property string $updated_at
 */
class Person extends ActiveRecord {

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
    return 'persons';
  }

  public function beforeSave($insert) {
    $this->first_name = $this->first_name ?? '';
    $this->nickname = $this->nickname ?? '';
    $this->last_name = $this->last_name ?? '';
    $this->comments = $this->comments ?? '';

    // throw new \yii\web\HttpException(400, json_encode($this->birth));
    
    return parent::beforeSave($insert);
  }

  public function beforeDelete() {
    if (!parent::beforeDelete()) {
      return false;
    }

    if ($this->birth) $this->birth->delete();
    if ($this->death) $this->death->delete();
    foreach ($this->marriages as $marriage) {
      $marriage->delete();
    }
    
    return true;
  }

  public function rules() {
    return [
      [['first_name', 'nickname', 'last_name', 'comments'], 'string'],
      ['sex', 'in', 'range' => ['m', 'f', 'u']]
    ];
  }

  public function getFather() {
    return $this->hasOne(Person::className(), ['id' => 'father_id']);
  }

  public function getMother() {
    return $this->hasOne(Person::className(), ['id' => 'mother_id']);
  }

  public function getBirth() {
    return $this->hasOne(Moment::className(), ['id' => 'birth_id']);
  }

  public function getDeath() {
    return $this->hasOne(Moment::className(), ['id' => 'death_id']);
  }

  public function getMarriages() {
    return $this->hasMany(Marriage::className(), ['id' => 'marriage_id'])->viaTable('spouses', ['person_id' => 'id']);
  }

  public function getChildren() {
    return $this->hasMany(Person::className(), [($this->sex == 'm' ? 'father_id' : 'mother_id') => 'id']);
  }

  public function getUser() {
    return $this->hasOne(User::className(), ['person_id' => 'id']);
  }

  public function getAge() {
    if ($this->birth && $this->birth->date) {
      $latest = ($this->death && $this->death->date) ? $this->death->date : 'today';
      return date_diff(date_create($this->birth_date), date_create($latest))->y;
    } else {
      return null;
    }
  }

  public function getName() {
    $first = $this->nickname ? $this->nickname : $this->first_name;
    
    return trim($first . ' ' . $this->last_name);
  }

  public function getViewAttributes() {
    $result = $this->getAttributes([
      'id',
      'first_name',
      'nickname',
      'last_name',
      'sex',
      'comments'
    ]);

    $result['birth'] = $this->birth ? $this->birth->viewAttributes : null;
    $result['death'] = $this->death ? $this->death->viewAttributes : null;

    $result['father'] = $this->father ? $this->father->simpleAttributes : null;
    $result['mother'] = $this->mother ? $this->mother->simpleAttributes : null;

    $person_id = $this->id;
    $result['marriages'] = array_map(function ($m) use ($person_id) {
      return $m->getViewAttributes($person_id);
    }, $this->marriages);

    $result['children'] = array_map(function ($p) {
      return $p->simpleAttributes;
    }, $this->children);

    return $result;
  }

  public function getSimpleAttributes() {
    $result = $this->getAttributes([
      'id',
      'first_name',
      'nickname',
      'last_name',
      'sex'
    ]);

    $result['birth'] = $this->birth ? $this->birth->viewAttributes : null;
    $result['death'] = $this->death ? $this->death->viewAttributes : null;

    return $result;
  }
}
