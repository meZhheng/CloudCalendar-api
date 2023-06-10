<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  session_start();
  $redis = new Redis();

  try {
    $redis->pconnect('127.0.0.1');
    $redis->select(1);
    // 设置组信息
    $redis->hSet('mygroup:creator123', 'name', 'My Group');
    $redis->hSet('mygroup:creator123', 'description', 'This is my group');
    // 添加成员
    $redis->sAdd('mygroup:creator123:members', 'member1');
    $redis->sAdd('mygroup:creator123:members', 'member2');
    // 关闭Redis连接
    $redis->close();
  } catch (RedisException $e) {
    $code = 503;
    $message = '服务出错，请稍后重试';
  }
}
