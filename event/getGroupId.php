<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        require_once "../checkAuth.php";
        $authMiddleware = new AuthMiddleware();
        [$redis, $username] = $authMiddleware->handle();

        $redis->select(1);
        $userGroups = $redis->sMembers("userGroup:$username");
        $groupInfo = [];

        foreach ($userGroups as $groupID) {
            $groupName = $redis->hget("group:$groupID", "groupname");
            $groupOption = [
                'id' => $groupID,
                'name' => $groupName,
            ];
            $groupInfo[] = $groupOption;
        }

        $code = 200;
    } catch (RedisException $e) {
        $code = 503;
        $message = "服务出错，请稍后重试";
    } catch (AuthFailedException $e) {
        $code = 401;
        $message = $e->getMessage();
    }

    header('Content-Type: application/json');
    $response = [
        'code' => $code,
        'groupInfo' => $groupInfo ?? null,
    ];
    echo json_encode($response);
}


