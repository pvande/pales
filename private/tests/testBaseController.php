<?php

class TestBaseController extends TestCase {
  function testDispatch() {
    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array('foo' => 'bar'));
    assertEqual(FooController::$calls, array('action'));
  }
  
  function testDispatchWithoutRender() {
    $foo = new FooController();
    $data = $foo->dispatch('actionWithoutRender', array());
    assertFalse($data);
    assertEqual(FooController::$calls, array('actionWithoutRender'));
  }
  
  function testBeforeFilterPass() {
    FooController::beforeFilter('passFilter');
    
    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array('foo' => 'bar'));
    assertEqual(FooController::$calls, array('passFilter', 'action'));
  }
  
  function testBeforeFilterFail() {
    FooController::beforeFilter('failFilter');
    
    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array());
    assertEqual(FooController::$calls, array('failFilter'));
  }
  
  function testMultipleBeforeFilters() {
    FooController::beforeFilter('passFilter');
    FooController::beforeFilter('passFilter');
    FooController::beforeFilter('failFilter');
    FooController::beforeFilter('passFilter');
    
    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array());
    assertEqual(FooController::$calls, array('passFilter', 'passFilter', 'failFilter'));
  }
  
  function testBeforeFilterWithOnlyString() {
    FooController::beforeFilter('passFilter', array('only' => 'action'));
  
    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array('foo' => 'bar'));
    assertEqual(FooController::$calls, array('passFilter', 'action'));
    
    $foo = new FooController();
    $data = $foo->dispatch('actionWithoutRender', array());
    assertFalse($data);
    assertEqual(FooController::$calls, array('actionWithoutRender'));
  }
  
  function testBeforeFilterWithOnlyArray() {
    FooController::beforeFilter('passFilter', array('only' => array('action')));

    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array('foo' => 'bar'));
    assertEqual(FooController::$calls, array('passFilter', 'action'));
    
    $foo = new FooController();
    $data = $foo->dispatch('actionWithoutRender', array());
    assertFalse($data);
    assertEqual(FooController::$calls, array('actionWithoutRender'));
  }
  
  function testBeforeFilterWithExceptString() {
    FooController::beforeFilter('passFilter', array('except' => 'action'));
  
    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array('foo' => 'bar'));
    assertEqual(FooController::$calls, array('action'));
    
    $foo = new FooController();
    $data = $foo->dispatch('actionWithoutRender', array());
    assertFalse($data);
    assertEqual(FooController::$calls, array('passFilter', 'actionWithoutRender'));
  }
  
  function testBeforeFilterWithExceptArray() {
    FooController::beforeFilter('passFilter', array('except' => array('action')));

    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array('foo' => 'bar'));
    assertEqual(FooController::$calls, array('action'));
    
    $foo = new FooController();
    $data = $foo->dispatch('actionWithoutRender', array());
    assertFalse($data);
    assertEqual(FooController::$calls, array('passFilter', 'actionWithoutRender'));
  }
  
  function testBeforeFilterThatScansParameters() {
    FooController::beforeFilter('scanFilter');
    
    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertFalse($data);
    assertEqual(FooController::$calls, array('scanFilter'));
    
    $foo = new FooController();
    $data = $foo->dispatch('action', array('foo' => 'bar'));
    assertEqual($data, array('foo' => 'bar'));
    assertEqual(FooController::$calls, array('scanFilter', 'action'));
  }
  
  function testAfterFilterPass() {
    FooController::afterFilter('passFilter');
    
    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array('foo' => 'bar'));
    assertEqual(FooController::$calls, array('action', 'passFilter'));
  }
  
  function testAfterFilterFail() {
    FooController::afterFilter('failFilter');
    
    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array());
    assertEqual(FooController::$calls, array('action', 'failFilter'));
  }
  
  function testMultipleAfterFilters() {
    FooController::afterFilter('passFilter');
    FooController::afterFilter('passFilter');
    FooController::afterFilter('failFilter');
    FooController::afterFilter('passFilter');
    
    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array());
    assertEqual(FooController::$calls, array('action', 'passFilter', 'passFilter', 'failFilter'));
  }
  
  function testAfterFilterWithOnlyString() {
    FooController::afterFilter('passFilter', array('only' => 'action'));
  
    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array('foo' => 'bar'));
    assertEqual(FooController::$calls, array('action', 'passFilter'));
    
    $foo = new FooController();
    $data = $foo->dispatch('actionWithoutRender', array());
    assertFalse($data);
    assertEqual(FooController::$calls, array('actionWithoutRender'));
  }
  
  function testAfterFilterWithOnlyArray() {
    FooController::afterFilter('passFilter', array('only' => array('action')));

    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array('foo' => 'bar'));
    assertEqual(FooController::$calls, array('action', 'passFilter'));
    
    $foo = new FooController();
    $data = $foo->dispatch('actionWithoutRender', array());
    assertFalse($data);
    assertEqual(FooController::$calls, array('actionWithoutRender'));
  }
  
  function testAfterFilterWithExceptString() {
    FooController::afterFilter('passFilter', array('except' => 'action'));
  
    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array('foo' => 'bar'));
    assertEqual(FooController::$calls, array('action'));
    
    $foo = new FooController();
    $data = $foo->dispatch('actionWithoutRender', array());
    assertFalse($data);
    assertEqual(FooController::$calls, array('actionWithoutRender', 'passFilter'));
  }
  
  function testAfterFilterWithExceptArray() {
    FooController::afterFilter('passFilter', array('except' => array('action')));

    $foo = new FooController();
    $data = $foo->dispatch('action', array());
    assertEqual($data, array('foo' => 'bar'));
    assertEqual(FooController::$calls, array('action'));
    
    $foo = new FooController();
    $data = $foo->dispatch('actionWithoutRender', array());
    assertFalse($data);
    assertEqual(FooController::$calls, array('actionWithoutRender', 'passFilter'));
  }
  
  function testIsGet() {
    $foo = new FooController();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    assertTrue($foo->is_get());
    $_SERVER['REQUEST_METHOD'] = 'POST';
    assertFalse($foo->is_get());
    $_SERVER['REQUEST_METHOD'] = 'PUT';
    assertFalse($foo->is_get());
    $_SERVER['REQUEST_METHOD'] = 'DELETE';
    assertFalse($foo->is_get());
  }
  
  function testIsPost() {
    $foo = new FooController();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    assertFalse($foo->is_post());
    $_SERVER['REQUEST_METHOD'] = 'POST';
    assertTrue($foo->is_post());
    $_SERVER['REQUEST_METHOD'] = 'PUT';
    assertFalse($foo->is_post());
    $_SERVER['REQUEST_METHOD'] = 'DELETE';
    assertFalse($foo->is_post());
  }
  
  function testIsPut() {
    $foo = new FooController();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    assertFalse($foo->is_put());
    $_SERVER['REQUEST_METHOD'] = 'POST';
    assertFalse($foo->is_put());
    $_SERVER['REQUEST_METHOD'] = 'PUT';
    assertTrue($foo->is_put());
    $_SERVER['REQUEST_METHOD'] = 'DELETE';
    assertFalse($foo->is_put());
  }
  
  function testIsDelete() {
    $foo = new FooController();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    assertFalse($foo->is_delete());
    $_SERVER['REQUEST_METHOD'] = 'POST';
    assertFalse($foo->is_delete());
    $_SERVER['REQUEST_METHOD'] = 'PUT';
    assertFalse($foo->is_delete());
    $_SERVER['REQUEST_METHOD'] = 'DELETE';
    assertTrue($foo->is_delete());
  }
  
  function setup() {
    FooController::$filters = array();
  }
}

class FooController extends BaseController {
  static $calls;
  
  function __construct() {
    self::$calls = array();
  }
  
  function action($params, &$data) {
    self::$calls[] = 'action';
    $data['foo'] = 'bar';
  }
  
  function actionWithoutRender($params, &$data) {
    self::$calls[] = 'actionWithoutRender';
    $data['foo'] = 'bar';
    return STOP_RENDERING;
  }
  
  function passFilter() {
    self::$calls[] = 'passFilter';
    return TRUE;
  }
  
  function failFilter() {
    self::$calls[] = 'failFilter';
    return FALSE;
  }
  
  function scanFilter($params) {
    self::$calls[] = 'scanFilter';
    return array_key_exists('foo', $params);
  }
}

?>