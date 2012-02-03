<?php

class TestUtilityFunctions extends TestCase {
  function testIsAssociativeArray() {
    assertTrue(is_associative_array(array('a' => 1)));
    assertTrue(is_associative_array(array('a' => 1, 0 => 2)));
    assertFalse(is_associative_array(array(1, 2, 3)));
  }
  
  function testArrayRejectKeys() {
    $arr = array(
      'foo' => 'bar',
      'bar' => 'baz',
      'baz' => 'qux',
      'qux' => 'joo',
      'joo' => 'foo'
    );
    assertEqual(array_keys(array_reject_keys($arr, array('bar', 'baz'))), array('foo', 'qux', 'joo'));
    assertEqual(array_keys(array_reject_keys($arr, array())), array('foo', 'bar', 'baz', 'qux', 'joo'));
  }
  
  function testMassageDBValue() {
    $number = 12;
    $string = 'foo';
    $null = null;
    
    assertEqual(massage_db_value($number), '12');
    assertEqual(massage_db_value($string), "'foo'");
    assertEqual(massage_db_value($null), 'NULL');
  }
}

?>