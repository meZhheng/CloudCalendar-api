<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  session_start();
  $redis = new Redis();

  try {
    $redis->connect('127.0.0.1');
    $redis->select(1);

    $group = json_decode(file_get_contents('php://input'), true);
    $groupid = $redis->get('current_groupid');
    $username = $group['username'];
    $groupName = $group['groupname'];
    $description = $group['description']??'无';

    if ($groupid === false) {
        $groupid = 0;
    }
    // 验证群组名
   if ($redis->exists("group:$groupName")) {
       $response = [
           'code' => 400,
           'message' => '同名小组已存在'
       ];
    }
    // 验证描述
    elseif (strlen($description) > 200) {
        $response = [
            $code = 400,
            $message = "描述过长",
        ];
    }
   //目前只支持一人创建一个小组
   elseif($redis->exists("groupCreator:$username")){
       $response = [
           'code' => 400,
           'message' => '暂时支持一人一组',
       ];
   }
    else {
        $uuid = uniqid();

        // 递增组群号并更新到 Redis
        $newGroupId = $redis->incr('current_groupid');

        $redis->set("groupCode:$uuid", $newGroupId);
        $redis->set("groupCreator:$username",$groupName);


        // 设置组信息
        $hashKey = "group:$newGroupId";
        $redis->hset($hashKey, 'creator', $username);
        $redis->hset($hashKey, 'groupname', $groupName);
        $redis->hset($hashKey, 'description', $description);
      // 关闭Redis连接
      $redis->close();

      $response = [
          'code' => 200,
          'message' => '组群创建成功',
          'groupid' => $newGroupId,
          'groupcode' => $uuid
      ];
    }
  } catch (RedisException $e) {
    $code = 503;
    $message = '服务出错，请稍后重试';
  }

    header('Content-Type: application/json');
    echo json_encode($response);
}