<?php header("Pragma: no-cache", TRUE, 500); ?>
<html>
<body>
<h1>500 Server Error</h1>
<p>
<?php
  if ($message) {
    echo $message;
  } else {
    echo "The server was unable to complete your request.  Please contact the site administrator.";
  }
?>
</p>
</body>
</html>