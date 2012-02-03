<?php
  define('APP_ENV', 'test');
  require_once("config/loadConfig.php");
?>

<html>
  <head>
    <link rel="stylesheet" href="../styles/common.css" />
    <link rel="stylesheet" href="../styles/tests.css" />
    <title>Full Test Suite</title>
    <script>
      function toggle(e) {
        e = document.getElementById(e);
        e.style.display = e.style.display ? '' : 'none';
      }
    </script>
  </head>
  <body>
    <h2>Test Suite</h2>
<?php

__push_include_path('../lib/testing');
ini_set('assert.active', 0);
if (is_array($testCases = glob('tests/*.php'))) {
  foreach ($testCases as $testCase) {
    if ($_GET['test']) {
      $tests = "/(test)?(" . $_GET['test'] . ")/i";
      if (preg_match($tests, $testCase)) {
        require $testCase;
      }
    } else {
        require $testCase;
    }
  }
}

foreach (get_declared_classes() as $class) {
  if (preg_match("/^Test(?!Case)/", $class)) {
    $testCase = new $class();
    $testCase->__run();
  }
}

?>
  </body>
</head>