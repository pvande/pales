<?php

function show_errors_for($obj) {
  if ($obj->errors()) {
    $errors = '<div class="errors">Please fix the following issues:<ul><li>';
    $errors .= join("</li><li>", $obj->errors());
    $errors .= '</li></ul></div>';
    return $errors;
  }
}

function url_for(array $map) {
  return Routing::findRoute($map);
}

function link_to($link, array $map, array $options = array()) {
  $url = url_for($map);
  $attributes = '';
  foreach ($options as $key => $val) {
    $attributes .= "$key='$val' ";
  }
  return "<a href='$url' $attributes>$link</a>";
}

function img_tag($img) {
  global $URL_BASE;
  return "$URL_BASE/images/$img";
}

function include_stylesheet($stylesheet) {
  global $__STYLESHEETS__;
  array_push($__STYLESHEETS__, $stylesheet);
}

function include_javascript($javascript) {
  global $__JAVASCRIPTS__;
  array_push($__JAVASCRIPTS__, $javascript);
}

function stylesheets() {
  global $__STYLESHEETS__, $URL_BASE;
  $output = "";
  foreach ($__STYLESHEETS__ as $stylesheet) {
    $output .= "<link rel='stylesheet' href='$URL_BASE/styles/$stylesheet' />\n";
  }
  return $output;
}

function javascripts() {
  global $__JAVASCRIPTS__, $URL_BASE;
  $output = "";
  foreach ($__JAVASCRIPTS__ as $javascript) {
    $output .= "<script src='$URL_BASE/scripts/$javascript'></script>\n";
  }
  return $output;
}

?>