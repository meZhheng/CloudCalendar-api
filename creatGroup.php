<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  session_start();

  $group = json_decode(file_get_contents('php://input'), true);
  $username = $group['username'];
  $groupname = $group['groupname'];
  $description = $group['description']??'无';

  // 验证用户名
  if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $code = 400;
    $message = "用户名不合法";
  }
  // 验证群组名
  elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $groupname)) {
    $code = 400;
    $message = "群组名不合法";
  }
  // 验证描述
  elseif (strlen($description) > 200) {
    $code = 400;
    $message = "描述过长";
  }
  else {
    // 在输入验证后连接到 Redis
    $redis = new Redis();
    try {
      $redis->connect('127.0.0.1');
      $redis->select(1);

      // 设置组信息
      $redis->hSet("$username:$groupname", 'name', "$groupname");
      $redis->hSet("$username:$groupname", 'description', "$description");
      // 关闭Redis连接
      $redis->close();
      
      $code = 200;
      $message = "创建成功";
    } catch (RedisException $e) {
      $code = 503;
      $message = '服务出错，请稍后重试';
    }
  }

  header('Content-Type: application/json');
  $response = [
    'code' => $code,
    'message' => $message
  ];
  echo json_encode($response);
}