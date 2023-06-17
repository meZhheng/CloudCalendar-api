<?php

function generateCaptcha() {
  $width = 108;
  $height = 36;
  $length = 4; // 验证码长度
  $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $code = '';
  $font = realpath('./font/Allure.ttf');

  for ($i = 0; $i < $length; $i++) {
    $code .= $characters[mt_rand(0, strlen($characters) - 1)];
  }
  $imageID = uniqid();

  $_SESSION['captcha'] = $code;
  $_SESSION['captchaID'] = $imageID;
  $imagePath = "image/captcha/$imageID.png";

  $image = imagecreatetruecolor($width, $height);
  $bgColor = imagecolorallocate($image, 255, 255, 255);
  $textColor = imagecolorallocate($image, 0, 0, 0);
  imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
  imagettftext($image, 24, 0, 6, 30, $textColor, $font, $code);

  imagepng($image, dirname(__DIR__).'/CloudCalendar-frontend/build/'.$imagePath);
  imagedestroy($image);

  header('Content-Type: application/json');
  $response = [
    'code' => 200,
    'captcha_img' => $imagePath
  ];
  echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET'){
  session_start();
  if (isset($_SESSION['captchaID']) && !empty($_SESSION['captchaID']) && isset($_SESSION['captcha']) && !empty($_SESSION['captcha'])) {
    unlink(dirname(__DIR__).'/CloudCalendar-frontend/build/image/captcha/'.$_SESSION['captchaID'].'.png');
  }
  generateCaptcha();
}
