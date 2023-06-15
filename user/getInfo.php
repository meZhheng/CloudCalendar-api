<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (isset($_SERVER['HTTP_AUTHORIZATION']) && isset($_SERVER['HTTP_USERNAME'])) {
    $token = $_SERVER['HTTP_AUTHORIZATION'];
    if (!empty($token)) {
      $token = str_replace('Bearer ', '', $token);
    }
    $username = $_SERVER['HTTP_USERNAME'];

    if (!preg_match('/^[0-9a-f]{64}$/', $token)) {
      $code = 400;
      $message = "非法请求";
    } else {
      $redis = new Redis();
      try {
        $redis->connect('127.0.0.1');
        if ($token === $redis->get("userToken:$username")) {
          $redis->select(1);
          if ($userInfo = $redis->hgetall("user:$username")) {
            $code = 200;
          } else {
            throw new RedisException();
          }
        } else {
          $code = 401;
          $message = "非法请求";
        }
      } catch (RedisException $e) {
        $code = 503;
        $message = "服务出错，请稍后重试";
      }
    }
  } else {
    $code = 401;
    $message = "非法请求";
  }
  header('Content-Type: application/json');
  $response = [
    'code' => $code,
    'message' => $message??null,
    'userInfo' => $userInfo??null,
  ];
  echo json_encode($response);
}