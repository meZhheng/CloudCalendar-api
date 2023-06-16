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
                    // 获取用户所属的组id列表
                    $groupIds = $redis->smembers("userGroup:$username");

                    $result = [];
                    // 遍历每个组，获取组的 ID，并获取对应的用户
                    foreach ($groupIds as $groupID) {

                        // 从 'group:$id:members' 中获取组的成员列表
                        $groupName = $redis->hget("group:$groupID", "groupname");
                        $groupMembers = $redis->smembers("group:$groupID:members");
                        $groupMemberNum = count($groupMembers);
                        $groupCode = $redis->hget("group:$groupID", "code");
                        $groupData = [
                            'groupName' => $groupName,
                            'groupCode' => $groupCode,
                            'groupMemberNum' => $groupMemberNum,
                            'groupMembers' => $groupMembers,
                        ];

                        // 添加组数据到结果数组
                        $result[] = $groupData;
                    }

                    $code = 200; // 成功获取数据
                    $message = "成功获取数据";
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

    $jsonData = json_encode($result ?? []); // 默认为空数组
    header('Content-Type: application/json');
    $response = [
        'code' => $code,
        'message' => $message ?? null,
        'userInfo' => $result ?? null,
    ];
    echo json_encode($response);
}

