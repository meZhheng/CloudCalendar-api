<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  session_start();
  $redis = new Redis();

  try {
    $redis->connect('127.0.0.1');

    $user = json_decode(file_get_contents('php://input'), true);
    $username = $user['username'];
    $password = $user['password'];
    $captcha = $user['captcha'];

    // 验证用户名
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
      $code = 400;
      $message = "用户名不合法：用户名只能包含大小写字母、数字和下划线，长度为3-20个字符";
    }
    // 验证密码
    elseif (!preg_match('/^[a-zA-Z0-9]{3,20}$/', $password)) {
      $code = 400;
      $message = "密码不合法：密码只能包含大小写字母和数字，长度为3-20个字符";
    }
    // 验证验证码
    elseif (!preg_match('/^[a-zA-Z]{4}$/', $captcha)) {
      $code = 400;
      $message = "验证码不合法";
    }
    elseif (isset($_SESSION['captchaID']) && !empty($_SESSION['captchaID']) && isset($_SESSION['captcha']) && !empty($_SESSION['captcha'])) {
      if (strtolower($captcha) != strtolower($_SESSION['captcha'])) {
        $tmp = $_SESSION['captcha'];
        $code = 503;
        $message = "验证码错误";
      } elseif ($redis->exists("user:$username")) {
        $code = 503;
        $message = "用户名已存在";
      } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $redis->set("user:$username", $hashedPassword);

        $code = 200;
        $message = "注册成功";
        $token = bin2hex(openssl_random_pseudo_bytes(32));
        $redis->set("userToken:$username", $token);

        $redis->select(1);
          $hashKey = "user:$username";
          $redis->hset($hashKey, 'nickname', "Create nickname here");
          $redis->hset($hashKey, 'location', "shanghai");
          $redis->hset($hashKey, 'apartment', "Create apartment here");
          $redis->hset($hashKey, 'position', "Create position here");
          $redis->hset($hashKey, 'mobile', "Create mobile here");
          $redis->hset($hashKey, 'email', "Create email here");
          $redis->hset($hashKey, 'description', "Create description here");
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