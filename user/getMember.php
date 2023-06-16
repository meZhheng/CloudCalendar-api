<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
            $redis = new Redis();
            try {
                $redis->connect('127.0.0.1');
                if ($token === $redis->get("userToken:$username")) {
                    $redis->select(1);
                    // 从 'userGroup:$username' 中获取用户所属的组列表
                    $userGroups = $redis->get("userGroup:$username");
                    $groups = explode(',', $userGroups);

                    // 遍历每个组，获取组的 ID，并获取对应的用户
                    foreach ($groups as $groupId) {
                        // 组键的形式为 'group:id'
                        $groupKey = "group:$groupId";

                        // 从 'group:$id:members' 中获取组的成员列表
                        $groupMembersKey = "group:$groupId:members";
                        $groupMembers = $redis->get($groupMembersKey);

                        // 处理组的成员列表
                        $members = explode(',', $groupMembers);

                        // 输出结果
                        echo "Group ID: $groupId\n";
                        echo "Group Members: " . implode(', ', $members) . "\n";}
                } else {
                    $code = 401;
                    $message = "非法请求";
                }
            } catch (RedisException $e) {
                $code = 503;
                $message = "服务出错，请稍后重试";
            }
        }
    } else {
        $code = 401;
        $message = "非法请求";
    }
    header('Content-Type: application/json');
    $response = [
        'code' => $code,
        'message' => $message??null,
        'userInfo' => $userInfo??null,
    ];
    echo json_encode($response);
}
