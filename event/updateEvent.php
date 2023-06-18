<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once "../checkAuth.php";
  $authMiddleware = new AuthMiddleware();
  try {
    [$redis, $username] = $authMiddleware->handle();

    $event = json_decode(file_get_contents('php://input'), true);
    $eventCalendar = $event['eventCalendar'];
    $eventID = $eventCalendar['_id'];
    $redis->select(2);
    $redis->hmset("event:$eventID", $eventCalendar);

    $code = 200;
    $message = "日程修改成功";

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