<?php

include_once 'inflector.php';

class TestInflector extends TestCase {
  function testUserDefinedInflections() {
    global $__inflections;
    $__inflections['from'] = 'to';
    assertPluralizations($__inflections);
    assertSingularizations($__inflections);
    assertPluralizations(array('FROM' => 'to'));
    assertSingularizations(array('FROM' => 'to'));
  }
  
  function testUninflectedWords() {
    $plurals = array(
      'fish'      => 'fish',
      'Iroquois'  => 'Iroquois',
      'sheep'     => 'sheep',
      'deer'      => 'deer',
      'smallpox'  => 'smallpox',
      'Japanese'  => 'Japanese',
      'arthritis' => 'arthritis'
    );
    assertPluralizations($plurals);
    assertSingularizations($plurals);
  }
  
  function testIrregularInflectionsForCommonSuffixes() {
    $plurals = array(
      'madman' => 'madmen',
      'mouse'  => 'mice',
      'tooth'  => 'teeth',
      'goose'  => 'geese',
      'foot'   => 'feet',
      'axis'   => 'axes'
    );
    assertPluralizations($plurals);
    assertSingularizations($plurals);
  }
  
  function testStandardClassicalInflections() {
    $plurals = array(
      'index'     => 'indices',
      'datum'     => 'data',
      'criterion' => 'criteria',
      'vertebra'  => 'vertebrae'
    );
    assertPluralizations($plurals);
    assertSingularizations($plurals);
  }
  
  function testCommonEsPlurals() {
    $plurals = array(
      'church' => 'churches',
      'hash'   => 'hashes',
      'class'  => 'classes'
    );
    assertPluralizations($plurals);
    assertSingularizations($plurals);
  }
  
  function testCommonVesPlurals() {
    $plurals = array(
      'wolf' => 'wolves',
      'leaf' => 'leaves',
      'wife' => 'wives',
      'life' => 'lives',
    );
    assertPluralizations($plurals);
    assertSingularizations($plurals);
  }
  
  function testWordsEndingInY() {
    $plurals = array(
      'candy' => 'candies',
      'gray'  => 'grays',
      'Tony'  => 'Tonys' 
    );
    assertPluralizations($plurals);
    assertSingularizations($plurals);
  }
  
  function testWordsEndingInO() {
    $plurals = array(
      'potato' => 'potatoes',
      'loo'    => 'loos'
    );
    assertPluralizations($plurals);
    assertSingularizations($plurals);
  }
  
  function testGeneralNouns() {
    $plurals = array(
      'cat'    => 'cats',
      'tree'   => 'trees',
      'line'   => 'lines'
    );
    assertPluralizations($plurals);
    assertSingularizations($plurals);
  }

  function testUnderscorize() {
    $names = array(
      'Person' => 'person',
      'ArmorClass' => 'armor_class',
      'Armor Class' => 'armor_class',
      'armor_class' => 'armor_class',
      'TestCase' => 'test_case',
      'NovaRPG' => 'nova_r_p_g',
    );
    assertUnderscorizations($names);
  }
}

function assertPluralizations($pairs) {
  foreach ($pairs as $singular => $plural) {
    assertEqual(strtolower(pluralize($singular)), strtolower($plural));
    assertEqual(strtolower(pluralize($plural)), strtolower($plural));
  }
}

function assertSingularizations($pairs) {
  foreach ($pairs as $singular => $plural) {
    assertEqual(strtolower(singularize($plural)), strtolower($singular));
    assertEqual(strtolower(singularize($singular)), strtolower($singular));
  }
}

function assertUnderscorizations($pairs) {
  foreach ($pairs as $proper => $underscored) {
    assertEqual(underscorize($proper), $underscored);
    assertEqual(underscorize($underscored), $underscored);
  }
}
?>