<?php
declare(strict_types=1);
require_once 'views/home.php';
require_once 'views/error.php';
require_once 'views/not_found.php';

const ACTION = "action";

$with_db = function($fn) {
  return function() {
    $con = mysqli_connect("wep-db", "user", "user123", "app");
    $fn($con);
  };
};

$with_auth = function($con, string $role, $fn) {
  return function() {
    $ctx = "something"; // TODO: generate context from permission
    $fn($con, $ctx);
  };
};

$handle_action = function($action, $router, $error_view, $not_found_view) {
  try {
    if (!array_key_exists($action, $router)) {
      $not_found_view();
    } else {
      $router[$action]();
    }
  } catch (Exception $e) {
    $error_view($e->get_message());
  }
};

if (!array_key_exists(ACTION, $_GET)) {
  $_GET[ACTION] = "";
}
$router = array("" => $home_view);
$handle_action($_GET[ACTION], $router, $error_view, $not_found_view);
?>
