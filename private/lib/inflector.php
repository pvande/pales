<?php

global $__inflections;
$__inflections = array(
  'person' => 'people',
  'child'  => 'children',
  'ox'     => 'oxen',
);

/**
 * Turns the given string (a single noun is expected) into its plural form.
 */
function pluralize($str) {
  global $__inflections;
  $__inflections = array_change_key_case($__inflections);
  $str = singularize($str);
  if ($plural = $__inflections[strtolower($str)]) {
    return strtolower($plural);
  } elseif (preg_match("/^[A-Z].*ese$|(fish|(o|it)is|sheep|deer|pox)$/", $str)) {
    return $str;
  } elseif (preg_match("/(man|[lm]ouse|tooth|goose|foot|[csx]is)$/", $str)) {
    $plural = $str;
    $plural = preg_replace("/man$/", 'men', $plural);
    $plural = preg_replace("/ouse$/", 'ice', $plural);
    $plural = preg_replace("/tooth$/", 'teeth', $plural);
    $plural = preg_replace("/goose$/", 'geese', $plural);
    $plural = preg_replace("/foot$/", 'feet', $plural);
    $plural = preg_replace("/is$/", 'es', $plural);
    return $plural;
  } elseif (preg_match("/(ex|um|on|a)$/", $str)) {
    $plural = $str;
    $plural = preg_replace("/ex$/", 'ices', $plural);
    $plural = preg_replace("/a$/", 'ae', $plural);
    $plural = preg_replace("/(um|on)$/", 'a', $plural);
    return $plural;
  } elseif (preg_match("/([cs]h|ss)$/", $str)) {
    $plural = $str;
    $plural = preg_replace("/h$/", 'hes', $plural);
    $plural = preg_replace("/ss$/", 'sses', $plural);
    return $plural;
  } elseif (preg_match("/([aeo]lf|[^d]eaf|arf)$/", $str)) {
    return preg_replace("/f$/", 'ves', $str);
  } elseif (preg_match("/([nlw]ife)$/", $str)) {
    return preg_replace("/fe$/", 'ves', $str);
  } elseif (preg_match("/^[^A-Z].*[^aeiou]y$/", $str)) {
    return preg_replace("/y$/", 'ies', $str);
  } elseif (preg_match("/[^aeiou]o$/", $str)) {
    return preg_replace("/o$/", 'oes', $str);
  } else {
    return "{$str}s";
  }
}

/**
 * Turns the given string (a single noun is expected) into its singular form.
 */
function singularize($str) {
  global $__inflections;
  $__inflections = array_change_key_case($__inflections);
  $reverse_inflections = array_flip($__inflections);
  if ($singular = $reverse_inflections[strtolower($str)]) {
    return strtolower($singular);
  } elseif (preg_match("/([cs]h|ss)es$/", $str)) {
    return preg_replace("/es$/", '', $str);
  } elseif (preg_match("/([aeo]lves|[^d]eaves|arves)$/", $str)) {
    return preg_replace("/ves$/", 'f', $str);
  } elseif (preg_match("/([nlw]ives)$/", $str)) {
    return preg_replace("/ves$/", 'fe', $str);
  } elseif (preg_match("/(ices|ae?)$/", $str)) {
    $singular = $str;
    $singular = preg_replace("/ices$/", 'ex', $singular);
    $singular = preg_replace("/ia$/", 'ion', $singular);
    $singular = preg_replace("/([^b]r|b[^r]|[^b][^r])a$/", '\1um', $singular);
    $singular = preg_replace("/ae$/", 'a', $singular);
    return $singular;
  } elseif (preg_match("/(men|[lm]ice|teeth|geese|feet|[csx]es)$/", $str)) {
    $singular = $str;
    $singular = preg_replace("/men$/", 'man', $singular);
    $singular = preg_replace("/ice$/", 'ouse', $singular);
    $singular = preg_replace("/teeth$/", 'tooth', $singular);
    $singular = preg_replace("/geese$/", 'goose', $singular);
    $singular = preg_replace("/feet$/", 'foot', $singular);
    $singular = preg_replace("/es$/", 'is', $singular);
    return $singular;
  } elseif (preg_match("/(o|it|x)is$/", $str)) {
    return $str;
  } elseif (preg_match("/ies$/", $str)) {
    return preg_replace("/ies$/", 'y', $str);
  } elseif (preg_match("/[^aeiou]oes$/", $str)) {
    return preg_replace("/oes$/", 'o', $str);
  } else {
    return preg_replace("/([^s])s$/", '\1', $str);
  }
}

function underscorize($str) {
  $result = $str;
  $result = trim(preg_replace("/([A-Z])/", ' \1', $result));
  $result = preg_replace("/\s+/", '_', $result);
  $result = strtolower($result);
  return $result;
}
?>