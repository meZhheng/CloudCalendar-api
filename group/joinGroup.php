<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_SERVER['HTTP_AUTHORIZATION']) && isset($_SERVER['HTTP_USERNAME'])) {
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
    $username = $_SERVER['HTTP_USERNAME'];
    if (!preg_match('/^[0-9a-f]{64}$/', $token)) {
      $code = 400;
      $message = "非法请求";
    } else {
      $groupCode = json_decode(file_get_contents('php://input'), true);
      $groupCode = $groupCode['groupCode'];
      $redis = new Redis();
      try {
        $redis->connect('127.0.0.1');
        if ($token === $redis->get("userToken:$username")) {
          $redis->select(1);
          if ($groupID = $redis->get("groupCode:$groupCode")) {
            if ($redis->sAdd("group:$groupID:members", $username) && $redis->sAdd("userGroup:$username", $groupID)) {
              $code = 200;
              $message = "加入组成功";
            } else {
              $code = 400;
              $message = "您已在该小组中";
            }
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
    'message' => $message
  ];
  echo json_encode($response);
}