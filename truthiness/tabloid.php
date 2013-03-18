<?
require('firebaseLib.php');
$fb = new fireBase('https://more-real.firebaseio.com/tabloids');
$response = $fb->get($_GET['id']);
$tabloid = json_decode($response);
?>
<html>
<head>
</head>
<body>
<figure>
  <img src="<?php echo $tabloid->cover; ?>">
  <figcaption><?php echo $tabloid->headline; ?></figcaption>
</figure>
</body>
</html>
