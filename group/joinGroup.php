<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  session_start();
  $redis = new Redis();

  try {
    $redis->connect('127.0.0.1');
    $redis->select(1);
  } catch (RedisException $e) {
    $code = 503;
    $message = "服务出错，请稍后重试";
  }
}