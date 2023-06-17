<?php

require_once "checkAuth.php";
$authMiddleware = new AuthMiddleware();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try{
    $username = $authMiddleware->handle();


  } catch (RedisException $e) {
    $code = 503;
    $message = "服务出错，请稍后重试";
  } catch (AuthFailedException $e) {
    $code = 401;
    $message = $e->getMessage();
  }
}
