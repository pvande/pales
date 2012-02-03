<?php header("Pragma: no-cache", TRUE, 404); ?>
<html>
<body>
<h1>404 Page Not Found</h1>
<p>
<?php
  if ($message) {
    echo $message;
  } else {
    echo "The server was unable to find the requested file.";
  }
?>
</p>
</body>
</html>