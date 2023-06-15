<?php

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
  $token = $_SERVER['HTTP_AUTHORIZATION'];
}