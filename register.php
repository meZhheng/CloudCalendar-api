<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  $data = json_decode(file_get_contents('php://input'), true);

  $test_username = "test";
  $test_password = "test";

  $username = $data['username'];
  $password = $data['password'];

  $hash = password_hash($test_password, PASSWORD_DEFAULT);

  // 在这里进行账号密码验证和处理逻辑
  // 例如，可以使用前面提到的 password_verify 函数验证密码

  if ($username === $test_username && password_verify($password, $hash)) {
    $response = [
      'success' => true,
      'message' => '登录成功'
    ];
  }else{
    $response = [
      'success' => false,
      'message' => '账号或密码错误'
    ];
  }

  header('Content-Type: application/json');
  echo json_encode($response);
}