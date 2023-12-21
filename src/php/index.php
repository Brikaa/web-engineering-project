<?php

declare(strict_types=1);
require_once 'controller.php';
require_once 'repo.php';
require_once 'request_handlers.php';
require_once 'views/home.php';
require_once 'views/error.php';
require_once 'views/not_found.php';
require_once 'views/login.php';
require_once 'views/register.php';

const ACTION = "action";

function handle_action($action, $router, $error_view, $not_found_view)
{
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

function with_db(Closure $fn)
{
  return function () use ($fn) {
    $con = mysqli_connect("wep-db", "user", "user123", "app");
    $con->begin_transaction();
    try {
      $fn($con);
      $con->commit();
    } catch (Exception $e) {
      $con->rollback();
      throw $e;
    }
    $con->close();
  };
}

$controller = new DbController(new Repo(), $_SESSION);
$action = "";
if (array_key_exists(ACTION, $_GET)) {
  $action = $_GET[ACTION];
} else if (array_key_exists(ACTION, $_POST)) {
  $action = $_POST[ACTION];
}
$router = array(
  "" => $home_view,
  "login" => $login_view,
  "register" => $register_view,
  "handle_login" => with_db(function (mysqli $con) use ($controller, $handle_login) {
    $handle_login($con, $controller);
  }),
  "handle_register" => with_db(function (mysqli $con) use ($controller, $handle_register) {
    $handle_register($con, $controller);
  }),
);
handle_action($action, $router, $error_view, $not_found_view);
