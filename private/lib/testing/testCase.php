<?php

include 'assertions.php';

/**
 * TestCase - The base class from which all test cases descend.
 *
 * @see assertions.php
 */
abstract class TestCase {
  private $tests = array();
  private $failures = array();
  private $class;
  protected $database;
  
  // Finds all test methods.
  function __find_tests() {
    $this->tests = preg_grep("/^test/", get_class_methods($this));
    return $this->tests;
  }

  // Runs the full test case.
  function __run() {
    $this->class = get_class($this);
    
    // Find our tests, and guarantee they exist!
    if (!$this->__find_tests()) {
      $this->failures[] = "Found no tests to run!";
    }
    
    // Walk through the (sub-)classes methods, and run any tests.
    // A test is any method whose name begins with 'test'.
    $this->__setup_case();
    foreach ($this->tests as $test) {
      $this->__run_test($test);
    }
    $this->__teardown_case();
    
    return count($this->failures);
  }
  
  // Runs an individual test.
  function __run_test($test) {
    $this->__setup();
    try {      
      $this->$test();
      $this->pass($test);
    } catch (TestFailure $e) {
      $this->fail("$test: " . $e->getMessage());
    } catch (Exception $e) {
      $this->fail("$test: Test threw an error [$e]");
    }
    $this->__teardown();
  }
  
  // Perform setup for the entire test case.
  function __setup_case() {
    print "<h3 id='$this->class-title' onclick='toggle(\"$this->class\")'>$this->class</h3>\n";
    print "<div class='results' id='$this->class'>\n";
  }
  
  // Perform teardown for the entire test case.
  function __teardown_case() {
    $this->__printFailures();
    
    $tests = count($this->tests);
    $failures = count($this->failures);
    $successes = $tests - $failures;
    $display = $failures || $_GET['expanded'] ? '' : 'none';
    $results = $failures ? 'failed' : 'passed';
    print <<<SCRIPT
      </div>
      <script>
        document.getElementById('$this->class').style.display = '$display';
        var title = document.getElementById('$this->class-title');
        title.className = '$results';
        title.innerHTML = title.innerHTML + " ( $successes / $tests )"
      </script>
SCRIPT;
  }
  
  // Perform setup for each test.
  function __setup() {
    global $database;
    $this->database = $database;
    $this->database->_query("START TRANSACTION");
    
    $this->setup();
  }
  
  // Perform teardown for each test.
  function __teardown() {
    $this->database->_query("ROLLBACK");
  
    $this->teardown();
  }
  
  // Print an easily readable list of test failures.
  function __printFailures() {
    print "<ul>\n";
    foreach ($this->failures as $failure) {
      print "<li>$failure</li>\n";
    }
    print "</ul>\n";
    flush();
  }
  
  // Prints an indicator that the test was passed.
  function pass($test) {
    print "<span class='test passed' title='$test: passed'></span>\n";
    flush();
  }
  
  // Prints an indicator that the test was failed.
  function fail($message) {
    $this->failures[] = $message;
    $message = preg_replace('/"/', '\\"', $message);
    print "<span class='test failed' title=\"$message\"></span>\n";
    flush();
  }
  
  // To be overridden by subclasses
  function setup() {}
  function teardown() {}
}

?>