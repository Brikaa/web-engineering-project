<?php

declare(strict_types=1);
require_once 'views/home.php';
require_once 'views/error.php';
require_once 'views/not_found.php';
require_once 'views/login.php';
require_once 'views/register.php';

const ACTION = "action";

$handle_action = function ($action, $router, $error_view, $not_found_view) {
  try {
    if (!array_key_exists($action, $router)) {
      $not_found_view();
    } else {
      $router[$action]();
    }
  } catch (Exception $e) {
    $error_view($e->getMessage());
  }
};

$action = "";
if (array_key_exists(ACTION, $_GET)) {
  $action = $_GET[ACTION];
} else if (array_key_exists(ACTION, $_POST)) {
  $action = $_POST[ACTION];
}
$router = array("" => $home_view, "login" => $login_view, "register" => $register_view);
$handle_action($action, $router, $error_view, $not_found_view);
