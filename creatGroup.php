<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  session_start();
  $redis = new Redis();

  try {
    $redis->pconnect('127.0.0.1');
    $redis->select(1);

    $group = json_decode(file_get_contents('php://input'), true);
    $username = $group['username'];
    $groupname = $group['groupname'];
    $description = $group['description']??'无';

    // 设置组信息
    $redis->hSet("$username:$groupname", 'name', "$groupname");
    $redis->hSet("$username:$groupname", 'description', "$description");
//    // 添加成员
//    $redis->sAdd('mygroup:creator123:members', 'member1');
//    $redis->sAdd('mygroup:creator123:members', 'member2');
    // 关闭Redis连接
    $redis->close();
  } catch (RedisException $e) {
    $code = 503;
    $message = '服务出错，请稍后重试';
  }
}
