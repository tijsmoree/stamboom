<?php

use yii\db\Migration;

class m181209_011534_init extends Migration {

  public function up() {
    $this->createTable('persons', [
      'id'          => $this->primaryKey(),
      'first_name'  => $this->string(100)->notNull(),
      'nickname'    => $this->string(100)->notNull(),
      'prefix'      => $this->string(100)->notNull(),
      'last_name'   => $this->string(100)->notNull(),
      'sex'         => "ENUM('m', 'f', 'u')",
      'birth_id'    => $this->integer(11),
      'death_id'    => $this->integer(11),
      'father_id'   => $this->integer(11),
      'mother_id'   => $this->integer(11),
      'comments'    => $this->text(),
      'created_at'  => $this->dateTime()->null(),
      'updated_at'  => $this->dateTime()->null()
    ]);

    $this->createTable('moments', [
      'id'          => $this->primaryKey(),
      'date'        => $this->date(),
      'location_id' => $this->integer(11),
      'source'      => $this->text(),
      'created_at'  => $this->dateTime()->null(),
      'updated_at'  => $this->dateTime()->null()
    ]);

    $this->createTable('locations', [
      'id'          => $this->primaryKey(),
      'name'        => $this->string(100)->notNull(),
      'state'       => $this->string(100)->notNull(),
      'country'     => $this->string(100)->notNull(),
      'longitude'   => $this->decimal(11, 8),
      'latitude'    => $this->decimal(11, 8),
      'created_at'  => $this->dateTime()->null(),
      'updated_at'  => $this->dateTime()->null()
    ]);

    $this->createTable('marriages', [
      'id'          => $this->primaryKey(),
      'male_id'     => $this->integer(11)->notNull(),
      'female_id'   => $this->integer(11)->notNull(),
      'marriage_id' => $this->integer(11),
      'divorce_id'  => $this->integer(11),
      'created_at'  => $this->dateTime()->null(),
      'updated_at'  => $this->dateTime()->null()
    ]);

    $this->createTable('users', [
      'id'          => $this->primaryKey(),
      'person_id'   => $this->integer(11)->notNull(),
      'mail'        => $this->string(100)->notNull(),
      'password'    => $this->string(200)->notNull(),
      'admin'       => $this->boolean()->notNull()->defaultValue(false),
      'attempts'    => $this->integer(1)->defaultValue(0),
      'token'       => $this->string(64)->null(),
      'logged_at'   => $this->dateTime()->null(),
      'created_at'  => $this->dateTime()->null(),
      'updated_at'  => $this->dateTime()->null()
    ]);

    $this->createTable('logs', [
      'id'          => $this->primaryKey(),
      'user_id'     => $this->integer(11),
      'model_ids'   => $this->string(100)->notNull(),
      'model_class' => $this->string(20)->notNull(),
      'change_type' => $this->string(20)->notNull(),
      'changes'     => $this->text(),
      'created_at'  => $this->dateTime()->null()
    ]);

    $this->addForeignKey('person_has_birth',
      'persons',
      'birth_id',
      'moments', 'id',
      'RESTRICT', 'RESTRICT'
    );
    $this->addForeignKey('person_has_death',
      'persons',
      'death_id',
      'moments', 'id',
      'RESTRICT', 'RESTRICT'
    );

    $this->addForeignKey('moment_has_location',
      'moments',
      'location_id',
      'locations', 'id',
      'RESTRICT', 'RESTRICT'
    );

    $this->addForeignKey('marriage_has_male',
      'marriages',
      'male_id',
      'persons', 'id',
      'RESTRICT', 'RESTRICT'
    );
    $this->addForeignKey('marriage_has_female',
      'marriages',
      'female_id',
      'persons', 'id',
      'RESTRICT', 'RESTRICT'
    );
    $this->addForeignKey('marriage_has_marriage',
      'marriages',
      'marriage_id',
      'moments', 'id',
      'RESTRICT', 'RESTRICT'
    );
    $this->addForeignKey('marriage_has_divorce',
      'marriages',
      'divorce_id',
      'moments', 'id',
      'RESTRICT', 'RESTRICT'
    );

    $this->addForeignKey('user_has_person',
      'users',
      'person_id',
      'persons', 'id',
      'RESTRICT', 'RESTRICT'
    );

    $this->addForeignKey('log_has_user',
      'logs',
      'user_id',
      'users', 'id',
      'RESTRICT', 'RESTRICT'
    );
  }

  public function down() {
    $this->dropTable('marriages');
    $this->dropTable('locations');
    $this->dropTable('moments');
    $this->dropTable('logs');
    $this->dropTable('users');
    $this->dropTable('persons');
  }
}
