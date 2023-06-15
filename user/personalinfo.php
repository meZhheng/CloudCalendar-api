<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $redis = new Redis();

    try {
        $redis->connect('127.0.0.1');
        $redis->select(1);

        $user = json_decode(file_get_contents('php://input'), true);
        $username = $user['username'];
        $nickname = $user['nickname'];
        $location = $user['selectedOption'];
        $apartment = $user['ApartmentValue'];
        $position = $user['PositionValue'];
        $mobile = $user['PhoneValue'];
        $email = $user['EmailValue'];
        $description = $user['AboutValue']??'本人很高冷，还没提供任何个人信息';


        // 验证用户名
        if ($redis->hexists("username:$username")) {
            $response = [
                'code' => 400,
                'message' => '用户名已存在'
            ];
        }
        // 验证描述
        elseif (strlen($description) > 200) {
            $response = [
                $code = 400,
                $message = "描述过长",
            ];
        }
        else {
            // 设置用户信息
            $hashKey = "user:$username";
            $redis->hset($hashKey, 'nickname', $nickname);
            $redis->hset($hashKey, 'location', $location);
            $redis->hset($hashKey, 'apartment', $apartment);
            $redis->hset($hashKey, 'position', $position);
            $redis->hset($hashKey, 'mobile', $mobile);
            $redis->hset($hashKey, 'email', $email);
            $redis->hset($hashKey, 'description', $description);
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
    } catch (RedisException $e) {
        $code = 503;
        $message = '服务出错，请稍后重试';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}