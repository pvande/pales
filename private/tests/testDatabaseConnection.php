<?php

class TestDatabaseConnection extends TestCase {
  
  function setup() {
    mysql_query("CREATE TABLE test (name char(50), age integer)");
  }
  
  function teardown() {
    mysql_query("DROP TABLE test");
  }
  
  function testDatabaseConnectionExists() {
    assertTrue($this->database);
  }
  
  /* Insert Tests */
  
  function testInsertWithIndexedArray() {
    $phil = array('age' => 42, 'name' => 'Phil');
    $result = $this->database->insert('test', array('Phil', 42));
    assertNotEqual($result, -1, 'INSERT failed');
    
    $result = mysql_query("SELECT * FROM test");
    assertEqual(mysql_num_rows($result), 1, 'Wrong number of results!');
    assertEqual(mysql_fetch_assoc($result), $phil);
  }
  
  function testInsertWithAssociativeArray() {
    $phil = array('age' => 42, 'name' => 'Phil');
    $result = $this->database->insert('test', $phil);
    assertNotEqual($result, -1, 'INSERT failed');
    
    $result = mysql_query("SELECT * FROM test");
    assertEqual(mysql_num_rows($result), 1, 'Wrong number of results!');
    assertEqual(mysql_fetch_assoc($result), $phil);
  }
  
  function testInsertWithNestedArray() {
    $phil = array('age' => 42, 'name' => 'Phil');
    $bob = array('age' => 12, 'name' => 'Bob');
    $result = $this->database->insert('test', array($phil, $bob));
    assertEqual($result, 2, 'INSERT failed');
    
    $result = mysql_query("SELECT * FROM test");
    assertEqual(mysql_num_rows($result), 2, 'Wrong number of results!');
    assertEqual(mysql_fetch_assoc($result), $phil);
    assertEqual(mysql_fetch_assoc($result), $bob);
  }
  
  /* Update Tests */
  
  function testUpdateAll() {
    $this->database->insert('test', array('Phi', 41));
    $this->database->insert('test', array('s brother', 3));

    $result = $this->database->update('test', array('age' => 42));
    assertEqual($result, 2, 'UPDATE failed');
    
    $result = mysql_query("SELECT * FROM test");
    assertEqual(mysql_num_rows($result), 2, 'Wrong number of results!');
    assertEqual(mysql_result($result, 0, 'age'), 42);
    assertEqual(mysql_result($result, 1, 'age'), 42);
  }
  
  function testUpdateWithCondition() {
    $this->database->insert('test', array('Phi', 41));
    $this->database->insert('test', array('s brother', 3));

    $result = $this->database->update('test', array('name' => 'Phil'), "name = 'Phi'");
    assertEqual($result, 1, 'UPDATE failed');
    
    $result = mysql_query("SELECT * FROM test");
    assertEqual(mysql_num_rows($result), 2, 'Wrong number of results!');
    assertEqual(mysql_result($result, 0, 'name'), 'Phil');
    assertNotEqual(mysql_result($result, 1, 'name'), 'Phil');
  }
  
  /* Select Tests */
  
  function testSelectAll() {
    $this->database->insert('test', array('Phil', 42));
    $this->database->insert('test', array("Phil's brother", 3));
    $this->database->insert('test', array('Bob', 30));

    $result = $this->database->select('test', array('name'));
    assertTrue($result, 'SELECT failed');
    assertEqual(mysql_num_rows($result), 3, 'Wrong number of results!');
    
    $results = array();
    while ($row = mysql_fetch_assoc($result)) {
      array_push($results, $row);
    }
    assertSetsEqual(
      $results,
      array(
        array('name' => 'Phil'),
        array('name' => 'Bob'),
        array('name' => "Phil's brother")
      )
    );
  }
  
  function testSelectWhere() {
    $this->database->insert('test', array('Phil', 42));
    $this->database->insert('test', array("Phil's brother", 3));
    $this->database->insert('test', array('Bob', 30));

    $result = $this->database->select('test', array('name'), 'age > 18');
    assertTrue($result, 'SELECT failed');
    assertEqual(mysql_num_rows($result), 2, 'Wrong number of results!');
    
    $results = array();
    while ($row = mysql_fetch_assoc($result)) {
      array_push($results, $row);
    }
    assertSetsEqual(
      $results,
      array(
        array('name' => 'Phil'),
        array('name' => 'Bob'),
      )
    );
  }
  
  function testSelectOrdered() {
    $this->database->insert('test', array('Abbey', 42));
    $this->database->insert('test', array('Abbey', 3));
    $this->database->insert('test', array('Bob', 30));
    
    $result = $this->database->select('test', array('name', 'age'), '', array('age'));
    assertTrue($result, 'SELECT failed');
    assertEqual(mysql_num_rows($result), 3, 'Wrong number of results!');
    
    $results = array();
    while ($row = mysql_fetch_assoc($result)) {
      array_push($results, $row);
    }
    assertSetsEqual(
      $results,
      array(
        array('name' => 'Abbey', 'age' => 42),
        array('name' => 'Abbey', 'age' => 3),
        array('name' => 'Bob', 'age' => 42),
      )
    );
  }
  
  function testSelectGrouped() {
    $this->database->insert('test', array('Abbey', 42));
    $this->database->insert('test', array('Abbey', 3));
    $this->database->insert('test', array('Bob', 30));

    $result = $this->database->select('test', array('name', 'MAX(age) AS age'), '', '', array('name'));
    assertTrue($result, 'SELECT failed');
    assertEqual(mysql_num_rows($result), 2, 'Wrong number of results!');
    
    $results = array();
    while ($row = mysql_fetch_assoc($result)) {
      array_push($results, $row);
    }
    assertEqual(
      $results,
      array(
        array('name' => 'Abbey', 'age' => 42),
        array('name' => 'Bob', 'age' => 30),
      )
    );
  }
  
  /* Delete Tests */
  
  function testDelete() {
    $this->database->insert('test', array('Phil', 42));
    $this->database->insert('test', array("Phil's brother", 3));
    $this->database->insert('test', array('Bob', 30));

    $result = $this->database->delete('test', 'age < 35');
    assertEqual($result, 2, 'DELETE failed');
  }
  
  /* Find Tests are located in testBaseModel.php */
}

?>