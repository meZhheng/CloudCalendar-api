<?php

session_start();

$success = false;

if (isset($_SESSION['user_id'])) {
  $success = true;
}

$response = [
  'success' => $success
];

header('Content-Type: application/json');
echo json_encode($response);