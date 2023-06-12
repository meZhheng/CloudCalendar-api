<?php
// 定义路由规则
$routes = [
  '/captcha.php' => '/captcha.php',
  '/login.php' => '/user/login.php',
  '/logout.php' => '/user/logout.php',
  '/register.php' => '/user/register.php',
  '/createGroup.php' => '/group/createGroup.php',
];

// 获取当前请求的路径
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// 根据路由规则匹配处理对应的后端文件
if (isset($routes[$path])) {
  $file = __DIR__ . $routes[$path];

  // 执行对应的后端文件
  if (file_exists($file)) {
    require $file;
    exit;
  }
}

// 如果没有匹配的路由规则，则返回404错误
header('Content-Type: application/json');
$response = [
  'code' => 404,
  'message' => '没有找到该页面',
];
echo json_encode($response);
