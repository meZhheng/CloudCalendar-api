<?php

class AuthFailedException extends Exception {}

class AuthMiddleware {
  /**
   * @throws RedisException
   * @throws AuthFailedException
   */
  public function handle() {
    if (isset($_SERVER['HTTP_AUTHORIZATION']) && isset($_SERVER['HTTP_USERNAME'])) {
      $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
      $username = $_SERVER['HTTP_USERNAME'];
      if (!preg_match('/^[0-9a-f]{64}$/', $token) || !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        throw new AuthFailedException("非法请求");
      } else {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        if ($token === $redis->get("userToken:$username")) {
          $redis->close();
          return [$redis, $username];
        } else {
          throw new AuthFailedException("非法请求");
        }
      }
    } else {
      throw new AuthFailedException("非法请求");
    }
  }
}

