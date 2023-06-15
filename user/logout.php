<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  $redis = new Redis();

  try {
    $redis->connect('127.0.0.1');

    $user = json_decode(file_get_contents('php://input'), true);
    $username = $user['username'];
    $token = $user['token'];

    // 验证用户名
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
      $code = 400;
      $message = "非法请求";
    } elseif(!preg_match('/^[0-9a-f]{64}$/', $token)) {
      $code = 400;
      $message = "非法请求";
    }
    elseif($token === $redis->get("userToken:$username")) {
      if($redis->del("userToken:$username")){
        $code = 200;
        $message = "用户：@$username 注销成功";
      } else {
        throw new RedisException();
      }
    }else {
      $code = 401;
      $message = "非法请求";
    }
  } catch (RedisException $e) {
    $code = 503;
    $message = "服务出错，请稍后重试";
  }
  header('Content-Type: application/json');
  $response = [
    'code' => $code,
    'message' => $message
  ];
  echo json_encode($response);
}