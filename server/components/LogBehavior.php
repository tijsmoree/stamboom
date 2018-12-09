<?php

namespace app\components;

use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use app\models\Log;
use yii\web\HttpException;

class LogBehavior extends Behavior {

  public $log;

  /**
   * @inheritdoc
   */
  public function attach($owner) {
    parent::attach($owner);
    
    if(!$this->owner instanceof ActiveRecord) {
      throw new HttpException(404, "This behavior is applicable only on classes that belong to or extend ActiveRecord");
    }
  }

  public function events() {
    return [
      ActiveRecord::EVENT_BEFORE_UPDATE => "logBeforeUpdate",
      ActiveRecord::EVENT_AFTER_UPDATE => "logAfterUpdate",
      ActiveRecord::EVENT_BEFORE_DELETE => "logBeforeDelete",
      ActiveRecord::EVENT_AFTER_DELETE => "logAfterDelete",
      ActiveRecord::EVENT_AFTER_INSERT => "logAfterInsert",
    ];
  }

  public function logBeforeUpdate(Event $event) {
    $this->log = new Log();

    $this->log->model_class = $this->owner->formName();
    $this->log->model_ids = $this->setIds($this->owner);
    $this->log->change_type = "update";
    $this->log->changes = $this->setChanges($this->owner, "update");
    $this->log->save();

    return true;
  }

  public function logAfterUpdate(Event $event) {
    return $this->log->save();
  }

  public function logBeforeDelete(Event $event) {
    $this->log = new Log();

    $this->log->model_class = $this->owner->formName();
    $this->log->model_ids = $this->setIds($this->owner);
    $this->log->change_type = "delete";
    $this->log->changes = $this->setChanges($this->owner, "delete");

    return true;
  }

  public function logAfterDelete(Event $event) {
    return $this->log->save();
  }

  public function logAfterInsert(Event $event) {
    $this->log = new Log();

    $this->log->model_class = $this->owner->formName();
    $this->log->model_ids = $this->setIds($this->owner);
    $this->log->change_type = "create";
    $this->log->changes = $this->setChanges($this->owner, "create");
    return $this->log->save();
  }

  private function setIds($model) {
    switch ($model->formName()) {
      case 'Person':
      case 'Moment':
      case 'Location':
      case 'User':
      case 'Marriage':
        return json_encode(['id' => $model->id]);
        break;
    }

    throw new HttpException(404, 'This model cannot be found and is therefore not loggable.');
  }

  private function setChanges($model, $change_type) {
    $except = ["created_at", "updated_at", "logged_at", "token"];
    $modelAttributes = $model->getAttributes(null, $except);
    if ($change_type == "update") {
      $keys = array_diff(array_keys($model->getDirtyAttributes()), $except);

      $values = array_map(function($x) use ($model) {
        if ($x === "password") {
          return [
            "key" => "password",
            "old" => "Niet getoond",
            "new" => "Niet getoond"
          ];
        }
        return [
          "key" => $x,
          "old" => $model->getOldAttributes()[$x],
          "new" => $model->getDirtyAttributes()[$x]
        ];
      }, $keys);
    } elseif ($change_type == "create") {
      $keys = array_keys($modelAttributes);
      $values = array_map(function($x) use ($modelAttributes) {
        if ($x === "password") {
          return [
            "key" => "password",
            "new" => "Niet getoond"
          ];
        }
        return [
          "key" => $x,
          "new" => $modelAttributes[$x]
        ];
      }, $keys);
    } elseif ($change_type == "delete") {
      $keys = array_keys($modelAttributes);
      $values = array_map(function($x) use ($modelAttributes) {
        if ($x === "password") {
          return [
            "key" => "password",
            "old" => "Niet getoond"
          ];
        }
        return [
          "key" => $x,
          "old" => $modelAttributes[$x]
        ];
      }, $keys);
    } else {
      return false;
    }

    return json_encode($values);
  }
}