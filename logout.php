<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  $user = json_decode(file_get_contents('php://input'), true);
  $username = $user['username'];

  // 验证用户名
  if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $code = 400;
    $message = "用户名不合法";
  }
  else {
    // Connect to Redis after input validation
    $redis = new Redis();
    try {
      $redis->connect('127.0.0.1');

      if($redis->del("userToken:$username")){
        $code = 200;
        $message = "用户：@$username 注销成功";
      } else {
        throw new RedisException();
      }
    } catch (RedisException $e) {
      $code = 503;
      $message = "userToken:$username";
    }
  }

  header('Content-Type: application/json');
  $response = [
    'code' => $code,
    'message' => $message
  ];
  echo json_encode($response);
}