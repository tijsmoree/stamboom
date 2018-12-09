<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "members.logs".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $model_ids
 * @property string $model_class
 * @property string $change_type
 * @property string $changes
 * @property string $created_at
 */
class Log extends ActiveRecord {
  
  public function behaviors() {
    return [
      [
        "class" => TimestampBehavior::className(),
        "attributes" => [
          ActiveRecord::EVENT_BEFORE_INSERT => ["created_at", false],
          ActiveRecord::EVENT_BEFORE_UPDATE => false,
        ],
        "value" => new Expression("UTC_TIMESTAMP()"),
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName() {
    return "logs";
  }

  public function beforeSave($insert) {
    if ($this->changes != "[]") {
      if (!$this->user_id) {
        $this->user_id = Yii::$app->user->identity->id ?? null;
      }

      return parent::beforeSave($insert);
    } else {
      return false;
    }
  } 

  /**
   * @return array validation rules for model attributes.
   */
  public function rules() {
    return [
      [["change_type", "model_class", "model_ids", "changes"], "required"],
      [["user_id"], "integer"],
      [["change_type", "model_class", "model_ids", "changes"], "string"]
    ];
  }

  public function revert() {
    if (!$this->revertable) {
      return false;
    }

    if ($this->change_type == "delete") {
      $modelPath = "app\models\\" . $this->model_class;
      $object = new $modelPath;

      foreach ($this->getChanges() as $attribute) {
        $key = $attribute["key"];
        $object->$key = $attribute["old"];
      }

      return $object->save();
    } elseif ($this->change_type == "create") {
      return $this->getObject()->delete();
    } elseif ($this->change_type == "update") {
      $object = $this->getObject();

      foreach ($this->getChanges() as $attribute) {
        $key = $attribute["key"];
        $object->$key = $attribute["old"];
      }

      return $object->save();
    }

    return false;
  }

  public function getObject() {
    $modelPath = "app\models\\" . $this->model_class;

    return $modelPath::findOne(json_decode($this->model_ids, true));
  }

  public function getChanges() {
    return json_decode($this->changes, true);
  }

  public function getMessage() {
    $cudMessage = [
      "create" => " is aangemaakt door ",
      "update" => " is gewijzigd door ",
      "delete" => " is verwijderd door "
    ];

    $ids = json_decode($this->model_ids, true);

    switch($this->model_class) {
      case 'Person':
        $person = Person::findOne($ids);
        if ($person) {
          $message = 'De persoon ' . $person->name;
        } else {
          $message = 'Een persoon';
        }
        break;
      case 'Moment':
        $person = $this->getObject()->owner;
        if ($person) {
          $message = 'Een gebeurtenis van ' . $person->name;
        } else {
          $message = 'Een gebeurtenis';
        }
        break;
      case 'Location':
        $location = Location::findOne($ids);
        if ($location) {
          $message = 'De locatie ' . $location->name;
        } else {
          $message = 'Een locatie';
        }
        break;
      case 'User':
        $user = User::findOne($ids);
        if ($user) {
          $message = 'De gebruiker ' . $user->person->name;
        } else {
          $message = 'Een gebruiker';
        }
        break;
      case 'Marriage':
        $person_1 = $this->getObject()->male;
        $person_2 = $this->getObject()->female;
        if ($person_1 && !$person_2) {
          $message = 'Een huwelijk van ' . $person_1->name;
        } elseif (!$person_1 && $person_2) {
          $message = 'Een huwelijk van ' . $person_2->name;
        } elseif (!$person_1 && !$person_2) {
          $message = 'Een huwelijk';
        } elseif ($person_1 && $person_2) {
          $message = 'Het huwelijk van ' .
            $person_1->name . ' en ' .
            $person_2->name;
        }
        break;
    }

    $user = User::findOne($this->user_id)->person->name ?? 'een onbekende';

    return $message . $cudMessage[$this->change_type] . $user;
  }

  public function getRevertable() {
    if ($this->change_type == "delete" && $this->getObject() ||
      $this->change_type != "delete" && !$this->getObject()) {
      return false;
    }

    return true;
  }

  public function getDisplayTime() {
    // Need to change timezone, this is done using date("Z")
    return date("j-n-Y G:i", strtotime($this->created_at) + date("Z"));
  }

  public function getLinks() {
    $ids = json_decode($this->model_ids, true);

    switch($this->model_class) {
      case 'Person':
        $person = Person::findOne($ids);
        return $person ? [
          [
            'name' => $person->name,
            'link' => 'person/' . $ids['id']
          ]
        ] : [];
        break;
      case 'User':
        $user = User::findOne($ids);
        return $user ? [
          [
            'name' => $user->person->name,
            'link' => 'user/' . $ids['id']
          ]
        ] : [];
        break;
      case 'Location':
        $location = Location::findOne($ids);
        return $location ? [
          [
            'name' => $location->name,
            'link' => 'location/' . $ids['id']
          ]
        ] : [];
      case 'Moment':
        $moment = Moment::findOne($ids);
        return ($moment && $moment->owner) ? [
          [
            'name' => $moment->owner->name,
            'link' => 'person/' . $moment->owner->id
          ]
        ] : [];
      case 'Marriage':
        $marriage = Marriage::findOne($ids);
        return ($marriage && $marriage->owner) ? [
          [
            'name' => $marriage->male->name,
            'link' => 'person/' . $marriage->male->id
          ],
          [
            'name' => $marriage->female->name,
            'link' => 'person/' . $marriage->female->id
          ]
        ] : [];
    }

    throw new HttpException(404, 'This model cannot be found and can therefore not be shown.');
  }

  public function getSearch($query) {
    return strpos(strtolower($this->message . json_encode($this->changes)), strtolower($query)) !== false;
  }

  public function getViewAttributes() {
    $result = $this->getAttributes([
      "id",
      "change_type",
      "changes",
      "message",
      "links",
      "revertable",
      "displayTime"
    ]);

    $result["changes"] = $this->getChanges();

    return $result;
  }
}