<?php

/**
  * assertTrue
  *   Used to prove the 'truthiness' of a value.
 */
function assertTrue($value, $message = false) {
  $value_r = print_r($value, 1);
  $message = $message ? $message : "'$value_r' is not true";
  if (!$value) {
    $trace = debug_backtrace();
    do {
      $line = array_shift($trace);
    } while (count($trace) && !preg_match("/^test/", basename($line['file'])));
    $file = basename($line['file']);
    $line = $line['line'];
    throw new TestFailure("$message ($file:$line)");
  }
}

/**
  * assertFalse
  *   Used to prove the 'falsiness' of a value.
 */
function assertFalse($value, $message = false) {
  $value_r = print_r($value, 1);
  $message = $message ? $message : "'$value_r' is unexpectedly true";
  assertTrue(!$value, $message);
}

/**
  * assertEqual
  *   Used to prove that a given value is equal to an expected value.
 */
function assertEqual($actual, $expected, $message = false) {
  $actual_r = print_r($actual, 1);
  $expected_r = print_r($expected, 1);
  $message = $message ? $message : "Got '$actual_r', but expected '$expected_r'";
  assertTrue($actual == $expected, $message);
}

function assertNotEqual($actual, $expected, $message = false) {
  $actual_r = print_r($actual, 1);
  $message = $message ? $message : "Got '$actual_r', but expected something else";
  assertTrue($actual != $expected, $message);
}

/**
  * assertSetsEqual
  *   Used to prove that a given set is equal to an expected set.
 */
function assertSetsEqual($actual, $expected, $message = false) {
  $actual_r = print_r($actual, 1);
  $expected_r = print_r($expected, 1);
  $message = $message ? $message : "Set '$actual_r' is not equal to '$expected_r'";
  assertFalse(
    array_diff(
      array_unique(array_values($actual)),
      array_unique(array_values($expected))
    ),
    $message
  );
}

?>