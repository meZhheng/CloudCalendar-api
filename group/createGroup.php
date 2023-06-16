<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $group = json_decode(file_get_contents('php://input'), true);
  $groupName = $group['groupname'];
  $description = $group['description'];

  if (strlen($description) > 100) {
      $code = 400;
      $message = "描述过长";
  } elseif (isset($_SERVER['HTTP_AUTHORIZATION']) && isset($_SERVER['HTTP_USERNAME'])) {
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
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
          if ($redis->exists("groupCreator:$username")) {
            $code = 400;
            $message = "暂时只支持一人创建一个小组";
          } else {
            $uuid = uniqid();

            $newGroupID = $redis->incr("current_groupid");

            $redis->sAdd("userGroup:$username", $newGroupID);
            $redis->sAdd("group:$newGroupID:members", $username);
            $redis->incr("group:$newGroupID:num_members");

            $redis->set("groupCode:$uuid", $newGroupID);
            $redis->set("groupCreator:$username", $newGroupID);

            $hashKey = "group:$newGroupID";
            $redis->hset($hashKey, 'creator', $username);
            $redis->hset($hashKey, 'groupname', $groupName);
            $redis->hset($hashKey, 'description', $description);

            $code = 200;
            $message = '组群创建成功';
          }
        } else {
          $code = 401;
          $message = "非法请求";
        }
      } catch (RedisException $e) {
        $code = 503;
        $message = '服务出错，请稍后重试';
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