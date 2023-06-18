<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once "../checkAuth.php";
  $authMiddleware = new AuthMiddleware();
  try{
    [$redis, $username] = $authMiddleware->handle();
    $event = json_decode(file_get_contents('php://input'), true);
    $eventCalendar = $event['eventCalendar'];
    if($groupID = $eventCalendar['selectedOptions'] and $groupID != '') {
      $redis->select(2);
      $eventID = $redis->incr("event_id");
      $redis->hmset("event:$eventID", $eventCalendar);
      $redis->set("event:$eventID:creator", $username);

      $redis->select(1);
      $redis->sAdd("group:$groupID:events", $eventID);
      $code = 200;
      $message = "日程创建成功";
      $schedule = [
        '_id' => $eventID,
        'title' => $eventCalendar['title'],
        'start' => $eventCalendar['start'] ,
        'endStr' => $eventCalendar['end'] ,
      ];
    } else {
      $code = 400;
      $message = "请选择一个小组";
    }
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
    'schedule' => $schedule??null
  ];
  echo json_encode($response);
}

