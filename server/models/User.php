<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\web\IdentityInterface;
/**
 * This is the model class for table "stamboom.users".
 *
 * @property string $id
 * @property string $mail
 * @property integer $person_id
 * @property string $password
 * @property boolean $admin
 * @property integer $attempts
 * @property string $logged_at
 * @property string $updated_at
 * @property string $created_at
 */
class User extends ActiveRecord implements IdentityInterface {

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
    return 'users';
  }

  public function rules() {
    return [
      [['mail', 'password'], 'required'],
      [['mail', 'password'], 'string', 'max' => 100],
      [['attempts', 'person_id'], 'number'],
      [['admin'], 'boolean']      
    ];
  }

  public function beforeSave($insert) {
    $this->mail = strtolower($this->mail);

    return parent::beforeSave($insert);
  }

  public function getPerson() {
    return $this->hasOne(Person::className(), ['id' => 'person_id']);
  }

  public static function findIdentity($id) {
    return static::findOne($id);
  }
  
  public static function findByMail($mail) {
    return static::findOne(['mail' => $mail]);
  }
  
  public function getId() {
    return $this->id;
  }

  public function validatePassword($password) {
    return Yii::$app->security->validatePassword($password, $this->password);
  }

  public function setPassword($password) {
    if (strlen($password) < 8) {
      throw new \yii\web\HttpException(400, 'The password needs to have at least 8 characters.');
    }
    $this->password = Yii::$app->security->generatePasswordHash($password);
  }

  public static function findIdentityByAccessToken($token, $type = null) {
    return static::find()
      ->where(['token' => $token])
      ->andWhere(['<', 'TIMESTAMPDIFF(SECOND, logged_at, UTC_TIMESTAMP())', 900])
      ->one();
  }

  public function getAuthKey() {
    // Not needed, because enableAutologin is false
  }

  public function validateAuthKey($authKey) {
    // Not needed, because enableAutologin is false
  }
}