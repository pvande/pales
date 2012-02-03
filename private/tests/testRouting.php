<?php

class TestRouting extends TestCase {
  function testAddRoute() {
    Routing::$routes = array();
    Routing::add('/:controller/:action/:id');
    assertEqual(
      Routing::$routes,
      array(
        array('/:controller/:action/:id', array())
      )
    );
    
    Routing::add('/foo/bar', array('controller' => 'Dog', 'action' => 'bark'));
    assertEqual(
      Routing::$routes,
      array(
        array('/:controller/:action/:id', array()),
        array('/foo/bar', array('controller' => 'Dog', 'action' => 'bark'))
      )
    );
  }
  
  function testRoutesToForStaticRoutes() {
    assertRoutesTo('/', array('controller' => 'application', 'action' => 'index'));
    assertRoutesTo('/genius', array('controller' => 'apple', 'action' => 'genius'));
    assertRoutesTo('/routed/fool', array('controller' => 'application', 'action' => 'stocks'));
  }
  
  function testRoutesToForDynamicRoutes() {
    assertRoutesTo('/routed/barn', array('controller' => 'application', 'action' => 'route', 'img' => 'barn'));
    assertRoutesTo('/images/image1.gif', array('controller' => 'images', 'action' => 'loadImage', 'gif' => 'image1.gif'));
    assertRoutesTo('/application', array('controller' => 'application', 'action' => 'index'));
    assertRoutesTo('/application/index', array('controller' => 'application', 'action' => 'index'));
    assertRoutesTo('/character/show/1', array('controller' => 'character', 'action' => 'show', 'id' => '1'));
    assertRoutesTo('/character/show/login', array('controller' => 'character', 'action' => 'show', 'id' => 'login'));
  }
  
  function testFindRouteForStaticRoutes() {
    assertFindRoute(array('controller' => 'application', 'action' => 'index'), '/');
    assertFindRoute(array('controller' => 'apple', 'action' => 'genius'), '/genius');
    assertFindRoute(array('controller' => 'application', 'action' => 'stocks'), '/routed/fool');
  }
  
  function testFindRouteForDynamicRoutes() {
    assertFindRoute(array('controller' => 'application', 'action' => 'route', 'img' => 'barn'), '/routed/barn');
    assertFindRoute(array('controller' => 'images', 'action' => 'loadImage', 'gif' => 'image1.gif'), '/images/image1.gif');
    assertFindRoute(array('controller' => 'character', 'action' => 'show', 'id' => '1'), '/character/show/1');
    assertFindRoute(array('controller' => 'character', 'action' => 'show', 'id' => 'login'), '/character/show/login');
  }
  
  function setup() {
    Routing::$routes = array();
    Routing::add('/', array('controller' => 'application', 'action' => 'index'));
    Routing::add('/genius', array('controller' => 'apple', 'action' => 'genius'));
    Routing::add('/routed/fool', array('controller' => 'application', 'action' => 'stocks'));
    Routing::add('/routed/:img', array('controller' => 'application', 'action' => 'route'));
    Routing::add('/images/:gif', array('controller' => 'images', 'action' => 'loadImage'));
    Routing::add('/:controller/:action/:id');
  }
}

function assertRoutesTo($url, $result) {
  $route = Routing::routesTo($url);
  assertEqual($route, array_merge($result, $_REQUEST),
    "Route '$url' incorrectly mapped to " . print_r($route, 1)
  );
}

function assertFindRoute($map, $url) {
  $route = Routing::findRoute($map);
  assertEqual($route, $url, "Map " . print_r($map, 1) . " incorrectly mapped to '$route'");
}

?>