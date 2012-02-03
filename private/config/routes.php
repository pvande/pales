<?php
  
  /*
    This file will contain mappings for URL patterns to controllers and actions.  Page requests
    will be matched against these routes, and directed appropriately; requests that don't match
    any of these routes are 404 errors.
    
    Routes should be provided in order of specificity -- more specific routes coming closer to
    the top of the file.  URLs will be matched against these routes in the order provided.
    
    Adding a route takes the form:
    
      Routing::add($url, $options);
    
    
    Where $options is an associative array of parameters to be passed to the action, like:
    
      $options = array('controller' => 'character', 'action' => 'list');
    
    And where $url is a either a static route like:
    
      $url = '/character/list';
    
    or a dynamic route like:
    
      $url = '/character/battle/:id';
    
    Dynamic parameters begin with ':', and will be passed through the param hash by name.
    Also worth noting are the two 'special' dynamic route parameters 'controller' and 'action' --
    these can be used to provide additional flexibility in the routing, by allowing the url to
    map directly to its controller and action directly.
    
    Every route must have a controller and an action, either explicitly in the $options, or
    inferrably in the $url.
  */
  
  Routing::add('/:controller/:action/:id');
  
?>