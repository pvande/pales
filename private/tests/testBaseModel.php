<?php

class TestBaseModel extends TestCase {
  function testInstantiation() {
    $person = new Person();
    $person->person_id = 1;
    assertFalse($person->inDatabase);
    assertEqual($person->tableName, 'people');
    assertEqual($person->__id(), 1);
  }
  
  function testCreate() {
    $model = new Person();
    $model->name  = 'Jimmy';
    $model->age = 24;
    
    assertFalse($model->inDatabase);
    assertFalse($model->wasCalled('before_create'));
    assertFalse($model->wasCalled('after_create'));
    $model->create();
    assertTrue($model->inDatabase);
    assertTrue($model->wasCalled('before_create'));
    assertTrue($model->wasCalled('after_create'));
    
    $result = $this->database->select('people');
    assertTrue($result, "Didn't #create properly");
    assertEqual(mysql_num_rows($result), 1, "Wrong number of results");
    assertEqual(mysql_fetch_row($result), array(1, 'Jimmy', 24), "Didn't INSERT data");
  }
  
  function testSaveAsCreate() {
    $model = new Person();
    $model->name  = 'Jimmy';
    $model->age = 24;
    
    assertFalse($model->inDatabase);
    assertFalse($model->wasCalled('before_save'));
    assertFalse($model->wasCalled('after_save'));
    assertFalse($model->wasCalled('before_create'));
    assertFalse($model->wasCalled('after_create'));
    $model->save();
    assertTrue($model->inDatabase);
    assertTrue($model->wasCalled('before_save'));
    assertTrue($model->wasCalled('after_save'));
    assertTrue($model->wasCalled('before_create'));
    assertTrue($model->wasCalled('after_create'));
    
    $result = $this->database->select('people');
    assertTrue($result, "Didn't #save properly");
    assertEqual(mysql_num_rows($result), 1, "Wrong number of results");
    assertEqual(mysql_fetch_row($result), array(1, 'Jimmy', 24), "Didn't INSERT data");
  }
  
  function testUpdate() {
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pi', 124)");
    $result = $this->database->_query("SELECT person_id, name, age FROM people");
    $model = mysql_fetch_object($result, 'Person');
    mysql_data_seek($result, 0);
    $model->_data = mysql_fetch_assoc($result);
    $model->inDatabase = true;
    
    assertFalse($model->hasChanges());
    $model->name = 'Jimmy';
    $model->age = 24;
    
    assertTrue($model->hasChanges());
    assertFalse($model->wasCalled('before_update'));
    assertFalse($model->wasCalled('after_update'));
    $model->update();
    assertTrue($model->wasCalled('before_update'));
    assertTrue($model->wasCalled('after_update'));
    assertFalse($model->hasChanges());
    
    $result = $this->database->select('people');
    assertTrue($result, "Didn't #update properly");
    assertEqual(mysql_num_rows($result), 1, "Wrong number of results");
    assertEqual(mysql_fetch_row($result), array(1, 'Jimmy', 24), "Didn't UPDATE data");
  }
  
  function testSaveAsUpdate() {
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pi', 124)");
    $result = $this->database->_query("SELECT person_id, name, age FROM people");
    $model = mysql_fetch_object($result, 'Person');
    mysql_data_seek($result, 0);
    $model->_data = mysql_fetch_assoc($result);
    $model->inDatabase = true;
    
    assertFalse($model->hasChanges());
    $model->name = 'Jimmy';
    $model->age = 24;
    
    assertTrue($model->hasChanges());
    assertFalse($model->wasCalled('before_save'));
    assertFalse($model->wasCalled('after_save'));
    assertFalse($model->wasCalled('before_update'));
    assertFalse($model->wasCalled('after_update'));
    $model->save();
    assertTrue($model->wasCalled('before_save'));
    assertTrue($model->wasCalled('after_save'));
    assertTrue($model->wasCalled('before_update'));
    assertTrue($model->wasCalled('after_update'));
    assertFalse($model->hasChanges());
    
    $result = $this->database->select('people');
    assertTrue($result, "Didn't #update properly");
    assertEqual(mysql_num_rows($result), 1, "Wrong number of results");
    assertEqual(mysql_fetch_row($result), array(1, 'Jimmy', 24), "Didn't UPDATE data");
  }
  
  function testDestroy() {
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pi', 124)");
    $result = $this->database->_query("SELECT person_id, name, age FROM people");
    $model = mysql_fetch_object($result, 'Person');
    mysql_data_seek($result, 0);
    $model->_data = mysql_fetch_assoc($result);
    $model->inDatabase = true;
    
    assertFalse($model->wasCalled('before_destroy'));
    assertFalse($model->wasCalled('after_destroy'));
    $model->destroy();
    assertTrue($model->wasCalled('before_destroy'));
    assertTrue($model->wasCalled('after_destroy'));
    
    $result = $this->database->select('people');
    assertTrue($result, "Didn't #destroy properly");
    assertEqual(mysql_num_rows($result), 0, "Didn't DELETE data");
  }
  
  function testReset() {
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pi', 20)");
    $person = $this->database->find(Person, 1);
    
    assertFalse($person->hasChanges());
    $person->name = "foo";
    assertTrue($person->hasChanges());
    $person->reset();
    assertFalse($person->hasChanges());
    assertEqual($person->name, 'pi');
  }
  
  function testFind() {
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pi', 20)");
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pie', 40)");
    $this->database->_query("INSERT INTO people (name, age) VALUES ('py', 80)");
    
    $person = $this->database->find(Person, 2);
    assertEqual($person->name, 'pie');
    assertEqual($person->age, 40);
  }
  
  function testFindWhere() {
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pi', 20)");
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pie', 40)");
    $this->database->_query("INSERT INTO people (name, age) VALUES ('py', 80)");
    
    $people = $this->database->findWhere(Person, 'age > 25');
    assertEqual(count($people), 2);
    assertEqual($people[0]->name, 'pie');
    assertEqual($people[0]->age, 40);
    assertEqual($people[1]->name, 'py');
    assertEqual($people[1]->age, 80);
  }
  
  function testFindByName() {
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pi', 20)");
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pie', 40)");
    $this->database->_query("INSERT INTO people (name, age) VALUES ('py', 80)");
    
    $people = $this->database->findByName(Person, 'pie');
    assertEqual(count($people), 1);
    assertEqual($people[0]->name, 'pie');
    assertEqual($people[0]->age, 40);
  }
  
  function testFindByPersonIdAndName() {
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pi', 20)");
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pie', 40)");
    $this->database->_query("INSERT INTO people (name, age) VALUES ('py', 80)");
    
    $people = $this->database->findByPersonIdAndName(Person, 2, 'pie');
    assertEqual(count($people), 1);
    assertEqual($people[0]->name, 'pie');
    assertEqual($people[0]->age, 40);
  }
  
  function testFindAndHasChangesPlayNicely() {
    $this->database->_query("INSERT INTO people (name, age) VALUES ('pi', 20)");
    $person = $this->database->find(Person, 1);
    assertTrue($person);
    
    assertFalse($person->hasChanges());
    assertTrue($person->inDatabase);
    $person->name = 'Jimmy';
    assertTrue($person->hasChanges());
    $person->save();
    assertFalse($person->hasChanges());
  }
  
  function testHasOneRelationship() {
    Person::$relations = array();
    Person::hasOne('house');
    $this->database->_query("INSERT INTO people (name, age) VALUES ('Jimmy', 24)");
    
    $person = $this->database->find(Person, 1);
    assertEqual($person->house, null);
    
    $this->database->_query("INSERT INTO tbl_houses (address, person_id) VALUES ('5000 NE 72nd Ave', 1)");
    $house = $person->house;
    assertTrue($house);
    assertEqual(
      $house->values(), 
      array('house_id' => 1, 'address' => '5000 NE 72nd Ave', 'zip_code' => null, 'person_id' => 1)
    );
  }
  
  function testHasOneRelationshipWithAlternateName() {
    Person::$relations = array();
    Person::hasOne('dwelling', array('className' => House));
    $this->database->_query("INSERT INTO people (name, age) VALUES ('Jimmy', 24)");
    
    $person = $this->database->find(Person, 1);
    assertEqual($person->dwelling, null);
    
    $this->database->_query("INSERT INTO tbl_houses (address, person_id) VALUES ('5000 NE 72nd Ave', 1)");
    $house = $person->dwelling;
    assertTrue($house);
    assertEqual(
      $house->values(), 
      array('house_id' => 1, 'address' => '5000 NE 72nd Ave', 'zip_code' => null, 'person_id' => 1)
    );
  }
  
  function testHasOneRelationshipCanBeSet() {
    Person::$relations = array();
    Person::hasOne('house');
    $this->database->_query("INSERT INTO people (name, age) VALUES ('Jimmy', 24)");
    $this->database->_query("INSERT INTO tbl_houses (address) VALUES ('5000 NE 72nd Ave')");
    
    $person = $this->database->find(Person, 1);
    assertFalse($person->house);
    
    $house = $this->database->find(House, 1);
    $person->house = $house;
    assertTrue($person->house);
  }
  
  function testHasOneRelationshipAndSaveAsUpdatePlayNicely() {
    Person::$relations = array();
    Person::hasOne('house');
    $this->database->_query("INSERT INTO people (name, age) VALUES ('Jimmy', 24)");
    $this->database->_query("INSERT INTO tbl_houses (address) VALUES ('5000 NE 72nd Ave')");

    $person = $this->database->find(Person, 1);
    $house = $this->database->find(House, 1);
    $person->house = $house;
    $house->zip_code = '98661';
    assertTrue($house->hasChanges());
    assertTrue($person->house->hasChanges());
    assertTrue($person->hasChanges());
    
    $person->save();
    assertFalse($person->house->hasChanges());

    $person = $this->database->find(Person, 1);
    assertTrue($person->house->zip_code, '98661');
    
    $house = $this->database->find(House, 1);
    assertTrue($house->person_id, 1);
    assertTrue($house->zip_code, '98661');
  }
  
  function testHasOneRelationshipAndSaveAsCreatePlayNicely() {
    Person::$relations = array();
    Person::hasOne('house');
    
    $house = new House();
    $house->address = "231 Greensborough Lane";
    $person = new Person();
    $person->name = "Phil";
    $person->age = 42;
    $person->house = $house;
    
    $person->save();
    assertFalse($person->hasChanges());
    assertFalse($house->hasChanges());
    
    $found = $this->database->find(Person, 1);
    assertEqual($found->house->address, $house->address);
  }
  
  function testHasManyRelationship() {
    Person::$relations = array();
    Person::hasMany('pets');
    
    $this->database->_query("INSERT INTO people (name, age) VALUES ('Jimmy', 24)");
    $person = $this->database->find(Person, 1);
    assertTrue(is_array($person->pets));
    assertEqual(count($person->pets), 0);
    
    $this->database->_query("INSERT INTO pets (name, person_id) VALUES ('Romulus', 1)");
    $person = $this->database->find(Person, 1);
    $pets = $person->pets;
    assertTrue(is_array($pets));
    assertEqual(count($pets), 1);
    assertEqual($pets[0]->values(), array('pet_id' => 1, 'name' => 'Romulus', 'person_id' => 1));
    
    $this->database->_query("INSERT INTO pets (name, person_id) VALUES ('Reimus', 1)");
    $person = $this->database->find(Person, 1);
    $pets = $person->pets;
    assertTrue(is_array($pets));
    assertEqual(count($pets), 2);
    assertEqual($pets[0]->values(), array('pet_id' => 1, 'name' => 'Romulus', 'person_id' => 1));
    assertEqual($pets[1]->values(), array('pet_id' => 2, 'name' => 'Reimus', 'person_id' => 1));
  }
  
  function testHasManyRelationshipWithAlternateName() {
    Person::$relations = array();
    Person::hasMany('companions', array('className' => Pet));
    
    $this->database->_query("INSERT INTO people (name, age) VALUES ('Jimmy', 24)");
    $person = $this->database->find(Person, 1);
    assertTrue(is_array($person->companions));
    assertEqual(count($person->companions), 0);
    
    $this->database->_query("INSERT INTO pets (name, person_id) VALUES ('Romulus', 1)");
    $person = $this->database->find(Person, 1);
    $companions = $person->companions;
    assertTrue(is_array($companions));
    assertEqual(count($companions), 1);
    assertEqual($companions[0]->values(), array('pet_id' => 1, 'name' => 'Romulus', 'person_id' => 1));
    
    $this->database->_query("INSERT INTO pets (name, person_id) VALUES ('Reimus', 1)");
    $person = $this->database->find(Person, 1);
    $companions = $person->companions;
    assertTrue(is_array($companions));
    assertEqual(count($companions), 2);
    assertEqual($companions[0]->values(), array('pet_id' => 1, 'name' => 'Romulus', 'person_id' => 1));
    assertEqual($companions[1]->values(), array('pet_id' => 2, 'name' => 'Reimus', 'person_id' => 1));
  }
  
  function testHasManyRelationshipCanBeSet() {
    Person::$relations = array();
    Person::hasMany('pets');
    
    $this->database->_query("INSERT INTO people (name, age) VALUES ('Jimmy', 24)");
    $this->database->_query("INSERT INTO pets (name) VALUES ('Romulus')");
    $this->database->_query("INSERT INTO pets (name) VALUES ('Reimus')");
    
    $person = $this->database->find(Person, 1);
    assertTrue(is_array($person->pets));
    assertEqual(count($person->pets), 0);
    $person->pets = array($this->database->find(Pet, 1), $this->database->find(Pet, 2));
    
    $pets = $person->pets;
    assertTrue(is_array($pets));
    assertEqual(count($pets), 2);
    assertEqual($pets[0]->values(), array('pet_id' => 1, 'name' => 'Romulus', 'person_id' => 1));
    assertEqual($pets[1]->values(), array('pet_id' => 2, 'name' => 'Reimus', 'person_id' => 1));
  }
  
  function testHasManyRelationshipAndSaveAsUpdatePlayNicely() {
    Person::$relations = array();
    Person::hasMany('pets');
    
    $this->database->_query("INSERT INTO people (name, age) VALUES ('Jimmy', 24)");
    $this->database->_query("INSERT INTO pets (name) VALUES ('Romulus')");
    $this->database->_query("INSERT INTO pets (name) VALUES ('Reimus')");
    
    $person = $this->database->find(Person, 1);
    assertTrue(is_array($person->pets));
    assertEqual(count($person->pets), 0);
    $pets = array($this->database->find(Pet, 1), $this->database->find(Pet, 2));
    $person->pets = $pets;
    assertTrue($pets[0]->hasChanges());
    assertTrue($pets[1]->hasChanges());
    assertTrue($person->pets[0]->hasChanges());
    assertTrue($person->pets[1]->hasChanges());
    assertTrue($person->hasChanges());
    
    $person->save();
    
    $person = $this->database->find(Person, 1);
    $pets = $person->pets;
    assertTrue(is_array($pets));
    assertEqual(count($pets), 2);
    assertEqual($pets[0]->values(), array('pet_id' => 1, 'name' => 'Romulus', 'person_id' => 1));
    assertEqual($pets[1]->values(), array('pet_id' => 2, 'name' => 'Reimus', 'person_id' => 1));
  }
  
  function testHasManyRelationshipAndSaveAsCreatePlayNicely() {
    Person::$relations = array();
    Person::hasMany('pets');
    
    $pet = new Pet();
    $pet->name = 'Fuzzbutt';
    $person = new Person();
    $person->name = "Phil";
    $person->age = 42;
    $person->pets = array($pet);
    
    $person->save();
    assertFalse($person->hasChanges());
    assertFalse($pet->hasChanges());
    
    $found = $this->database->find(Person, 1);
    assertEqual($found->pets[0]->name, $pet->name);
  }
  
  function testBelongsToRelationship() {
    Person::$relations = array();
    Pet::$relations = array();
    Pet::belongsTo('person');
    
    $this->database->_query("INSERT INTO pets (name) VALUES ('Romulus')");
    $pet = $this->database->find(Pet, 1);
    assertFalse($pet->person);
    
    $this->database->_query("INSERT INTO people (name, age) VALUES ('Jimmy', 24)");
    $this->database->_query("UPDATE pets SET person_id = 1");
    $pet = $this->database->find(Pet, 1);
    assertTrue($pet->person);
    assertEqual($pet->person->name, 'Jimmy');
  }
  
  function testBelongsToRelationshipWithAlternateName() {
    Person::$relations = array();
    Pet::$relations = array();
    Pet::belongsTo('owner', array('className' => Person));
    
    $this->database->_query("INSERT INTO pets (name) VALUES ('Romulus')");
    $pet = $this->database->find(Pet, 1);
    assertFalse($pet->owner);
    
    $this->database->_query("INSERT INTO people (name, age) VALUES ('Jimmy', 24)");
    $this->database->_query("UPDATE pets SET person_id = 1");
    $pet = $this->database->find(Pet, 1);
    assertTrue($pet->owner);
    assertEqual($pet->owner->name, 'Jimmy');
  }
  
  function testBelongsToRelationshipCanBeSet() {
    Person::$relations = array();
    Pet::$relations = array();
    Pet::belongsTo('person');
    
    $this->database->_query("INSERT INTO pets (name) VALUES ('Romulus')");
    $this->database->_query("INSERT INTO people (name, age) VALUES ('Jimmy', 24)");
    $pet = $this->database->find(Pet, 1);
    assertFalse($pet->person);
    
    $person = $this->database->find(Person, 1);
    $pet->person = $person;
    assertTrue($pet->person);
    assertEqual($pet->person, $person);
  }
  
  function testBelongsToRelationshipAndSaveAsUpdatePlayNicely() {
    Pet::$relations = array();
    Pet::belongsTo('person');
    
    $this->database->_query("INSERT INTO pets (name) VALUES ('Romulus')");
    $this->database->_query("INSERT INTO people (name, age) VALUES ('Jimmy', 24)");
    
    $pet = $this->database->find(Pet, 1);
    $person = $this->database->find(Person, 1);
    $pet->person = $person;
    assertTrue($pet->hasChanges());
    assertFalse($person->hasChanges());
    
    $pet->save();
    
    $pet = $this->database->find(Pet, 1);
    $person = $pet->person;
    assertTrue($person);
    assertEqual($person->name, 'Jimmy');
  }
  
  function testBelongsToRelationshipAndSaveAsCreatePlayNicely() {
    Pet::$relations = array();
    Pet::belongsTo('person');
    
    $person = new Person();
    $person->name = "Phil";
    $person->age = 42;
    $person->save();
    
    $pet = new Pet();
    $pet->name = 'Fuzzbutt';
    $pet->person = $person;
    $pet->save();
    assertFalse($pet->hasChanges());
    
    $found = $this->database->find(Pet, 1);
    assertEqual($found->person->name, $person->name);
  }
  
  function testRequiredValidation() {
    Pet::$validators = array();
    Pet::validate('required', 'name', array('message' => 'REQUIRED'));
    
    $pet = new Pet();
    assertFalse($pet->save());
    assertTrue($pet->errorOn('name'));
    assertEqual($pet->errors(), array('REQUIRED'));
    
    $pet = new Pet();
    $pet->name = "Fuzzbutt";
    assertTrue($pet->save());
    assertFalse($pet->errorOn('name'));
    assertEqual($pet->errors(), array());
  }
  
  function testUniqueValidation() {
    Pet::$validators = array();
    Pet::validate('unique', 'name', array('message' => 'UNIQUE'));
    
    $pet = new Pet();
    $pet->name = "Fuzzbutt";
    assertTrue($pet->save());
    assertFalse($pet->errorOn('name'));
    assertEqual($pet->errors(), array());
    
    $pet = new Pet();
    $pet->name = "Fuzzbutt";
    assertFalse($pet->save());
    assertTrue($pet->errorOn('name'));
    assertEqual($pet->errors(), array('UNIQUE'));
  }
  
  function setup() {
    $people = <<<PEOPLE
CREATE TABLE people (
  person_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(32),
  age INT
)
PEOPLE;

    $houses = <<<HOUSES
CREATE TABLE tbl_houses (
  house_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  address TEXT,
  zip_code VARCHAR(12),
  person_id INT
)
HOUSES;

    $pets = <<<PETS
CREATE TABLE pets (
  pet_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name TEXT,
  person_id INT
)
PETS;

    $this->database->_query($people);
    $this->database->_query($houses);
    $this->database->_query($pets);
  }
  
  function teardown() {
    $this->database->_query('DROP TABLE pets');
    $this->database->_query('DROP TABLE tbl_houses');
    $this->database->_query('DROP TABLE people');
  }
}

class Person extends BaseModel {
  private $callback;
  function beforeCreate()  { $this->callback['before_create'] = true;  }
  function beforeUpdate()  { $this->callback['before_update'] = true;  }
  function beforeSave()    { $this->callback['before_save'] = true;    }
  function beforeDestroy() { $this->callback['before_destroy'] = true; }
  function afterCreate()   { $this->callback['after_create'] = true;   }
  function afterUpdate()   { $this->callback['after_update'] = true;   }
  function afterSave()     { $this->callback['after_save'] = true;     }
  function afterDestroy()  { $this->callback['after_destroy'] = true;  }
  
  function wasCalled($name) { return $this->callback[$name]; }
}

class House extends BaseModel {
  function __construct() {
    parent::__construct();
    $this->tableName = 'tbl_houses';
  }
}

class Pet extends BaseModel { }

?>