<?
$url = 'https://more-real.firebaseio.com/tabloids/' . $_GET['id'] . '.json';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
$response = curl_exec($ch);
curl_close($ch);

preg_match('/"cover"."(.*)","headline"."(.*)"/', $response, $matches);
$src = $matches[1];
$headline = $matches[2];
?>
<html>
  <head>
    <link rel="stylesheet" href="/more-real/css/reset.css">
    <link rel="stylesheet" href="/more-real/css/morereal.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/3.0.2/css/font-awesome.min.css">
    <script type="text/javascript" src="/more-real/js/modernizr.custom.js"></script>
    <script type="text/javascript" src="/more-real/js/css3-mediaqueries.js"></script>
    <script type="text/javascript" src="http://use.typekit.com/vft0zxk.js"></script>
    <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
    <script type="text/javascript" src="/more-real/js/foutbgone.js" ></script>
    <script type="text/javascript">fbg.hideFOUT('asap', 400);</script>
    <link rel="stylesheet" href="/more-real/css/tabloid.css">

    <title>Truthiness! - More Real - Minneapolis Institute of Arts</title>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.firebase.com/v0/firebase.js"></script>
  </head>
<body>
  <figure id="gallery">
    <img src="<?php echo $src; ?>">
    <figcaption><?php echo $headline; ?></figcaption>
  </figure>
  <aside>
    <img src="/more-real/images/MR-title.png" alt="More Real? Art in the Age of Truthiness" />
    <nav class="row clearfix" id="nav">
      <ul class="navL">
        <li><a href="/more-real/index.html#about">About the Exhibition</a></li>
        <li><a href="/more-real/truthiness/">Tabloid Gallery</a></li>
        <li><a href="/more-real/index.html#tabloid">Write your own headline</a></li>
      </ul>
    </nav>
  </aside>
</body>
</html>
