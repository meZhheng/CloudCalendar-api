<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  $redis = new Redis();

  try {
    $redis->pconnect('127.0.0.1');

    $user = json_decode(file_get_contents('php://input'), true);
    $username = $user['username'];

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
  header('Content-Type: application/json');
  $response = [
    'code' => $code,
    'message' => $message
  ];
  echo json_encode($response);
}