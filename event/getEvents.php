<?php

require_once "../checkAuth.php";
$authMiddleware = new AuthMiddleware();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  try{
    [$redis, $username] = $authMiddleware->handle();

    $redis->select(1);
    $groupIDs = $redis->sMembers("userGroup:$username");

    $events = [];

    foreach ($groupIDs as $groupID) {
      $redis->select(1);
      $groupEvents = $redis->sMembers("group:$groupID:events");
      foreach ($groupEvents as $groupEvent) {
        $redis->select(2);
        $event = [
          '_id' => $groupEvent,
          'title' => $redis->hGet("event:$groupEvent", "title"),
          'end' => $redis->hGet("event:$groupEvent", "end"),
          'start' => $redis->hGet("event:$groupEvent", "start"),
          'backgroundColor' => $redis->hGet("event:$groupEvent", "backgroundColor"),
          'textColor' => $redis->hGet("event:$groupEvent", "textColor"),
          'user' => $groupID
        ];
        $events[] = $event;
      }
    }
    $code = 200;
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
    'message' => $message??null,
    'events' => $events??null
  ];
  echo json_encode($response);
}
