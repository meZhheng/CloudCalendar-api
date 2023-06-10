<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  session_start();
  $redis = new Redis();

  try {
    $redis->pconnect('127.0.0.1');

    $user = json_decode(file_get_contents('php://input'), true);
    $username = $user['username'];
    $password = $user['password'];
    $captcha = $user['captcha'];

    if (isset($_SESSION['captchaID']) && !empty($_SESSION['captchaID']) && isset($_SESSION['captcha']) && !empty($_SESSION['captcha'])) {
      if (strtolower($captcha) != strtolower($_SESSION['captcha'])) {
        $tmp = $_SESSION['captcha'];
        $code = 503;
        $message = "验证码错误，请重试:$captcha $tmp";
      } elseif ($redis->exists("user:$username")) {
        $code = 503;
        $message = "用户名已存在";
      } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $redis->set("user:$username", $hashedPassword);

        $code = 200;
        $message = "注册成功";
        $token = bin2hex(openssl_random_pseudo_bytes(32));
      }

      unset($_SESSION['captcha']);
      unlink(dirname(__DIR__).'/CloudCalendar-frontend/build/image/captcha/'.$_SESSION['captchaID'].'.png');
      unset($_SESSION['captchaID']);

    } else {
      throw new RedisException();
    }

  } catch (RedisException $e) {
    $code = 503;
    $message = '服务出错，请稍后重试';
  }
  header('Content-Type: application/json');
  $response = [
    'code' => $code,
    'message' => $message,
    'token' => $token??null,
    'user' => $username
  ];
  echo json_encode($response);
}