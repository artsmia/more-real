<?php
  /* https://github.com/carsonmcdonald/direct-browser-s3-upload-example/blob/master/signput.php */
  require 's3.config.php';
  $S3_BUCKET='/more-real-tabloid'; // Make sure to leave the / on the front of the bucket here

  $EXPIRE_TIME=(60 * 5); // 5 minutes
  $S3_URL='http://s3.amazonaws.com';

  $objectName='/' . $_GET['name'];

  $mimeType=$_GET['type'];
  $expires = time() + $EXPIRE_TIME;
  $amzHeaders= "x-amz-acl:public-read";
  $stringToSign = "PUT\n\n$mimeType\n$expires\n$amzHeaders\n$S3_BUCKET$objectName";
  if(function_exists('mhash')) {
    $sig = urlencode(base64_encode(mhash(MHASH_SHA1, $stringToSign, $S3_SECRET)));
  } else {
    $sig = urlencode(base64_encode(hash_hmac('sha1', $stringToSign, $S3_SECRET, true)));
  }

  $url = urlencode("$S3_URL$S3_BUCKET$objectName?AWSAccessKeyId=$S3_KEY&Expires=$expires&Signature=$sig");

  echo $url;
?>
