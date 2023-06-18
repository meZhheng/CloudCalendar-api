<?php

require_once "../checkAuth.php";
$authMiddleware = new AuthMiddleware();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    [$redis, $username] = $authMiddleware->handle();

    $event = json_decode(file_get_contents('php://input'), true);
    $eventID= $event['id'];
    $groupID= $event['groupID'];

    $redis->select(2);
    $groupIDs = $redis->del("event:$eventID");
    $groupIDs = $redis->del("event:$eventID:creator");
    $redis->select(1);
    $redis->sRem("group:$groupID:events", $eventID);

    $code= 200;
    $message = "删除日程成功";

  } catch (RedisException $e) {
    $code = 503;
    $message = "服务出错，请稍后重试";
  } catch (AuthFailedException $e) {
    $code = 401;
    $message = $e->getMessage();
  }
  header('Content-Type: application/json');
  $response = [
    'code' => $code,
    'message' => $message,
  ];
  echo json_encode($response);
}