<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_SERVER['HTTP_AUTHORIZATION']) && isset($_SERVER['HTTP_USERNAME'])) {
    $token = $_SERVER['HTTP_AUTHORIZATION'];
    if (!empty($token)) {
      $token = str_replace('Bearer ', '', $token);
    }
    $username = $_SERVER['HTTP_USERNAME'];
    if (!preg_match('/^[0-9a-f]{64}$/', $token)) {
      $code = 400;
      $message = "非法请求";
    } else {
      session_start();
      $redis = new Redis();

      try {
        $redis->connect('127.0.0.1');
        if ($token === $redis->get("userToken:$username")) {
          $redis->select(1);

          $user = json_decode(file_get_contents('php://input'), true);
          $nickname = $user['nickname'];
          $location = $user['selectedOption'];
          $apartment = $user['ApartmentValue'];
          $position = $user['PositionValue'];
          $mobile = $user['PhoneValue'];
          $email = $user['EmailValue'];
          $description = $user['AboutValue'] ?? '本人很高冷，还没提供任何个人信息';

          if (strlen($description) > 200) {
            $response = [
              $code = 400,
              $message = "描述过长",
            ];
          } else {
            // 设置用户信息
            $hashKey = "user:$username";
            $redis->hSet($hashKey, 'nickname', $nickname);
            $redis->hSet($hashKey, 'location', $location);
            $redis->hSet($hashKey, 'apartment', $apartment);
            $redis->hSet($hashKey, 'position', $position);
            $redis->hSet($hashKey, 'mobile', $mobile);
            $redis->hSet($hashKey, 'email', $email);
            $redis->hSet($hashKey, 'description', $description);
            // 关闭Redis连接
            $redis->close();

            $response = [
              'code' => 200,
              'message' => '用户信息修改成功',
              'Nickname' => $nickname,
              'Location' => $location,
              'Apartment' => $apartment,
              'Position' => $position,
              'Mobile' => $mobile,
              'Email' => $email,
              'Description' => $description,
            ];
          }
        } else {
          $code = 401;
          $message = "非法请求";
        }
      } catch (RedisException $e) {
        $code = 503;
        $message = '服务出错，请稍后重试';
      }

      header('Content-Type: application/json');
      echo json_encode($response);
    }
  }
}