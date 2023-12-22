<?php

declare(strict_types=1);
session_start();
require_once 'controller.php';
require_once 'repo.php';
require_once 'request_handlers.php';
require_once 'views/home.php';
require_once 'views/error.php';
require_once 'views/not_found.php';
require_once 'views/login.php';
require_once 'views/signup.php';
require_once 'views/success.php';

const ACTION = "action";

function handle_action($action, $router, $success_view, $error_view, $not_found_view)
{
  try {
    if (!array_key_exists($action, $router)) {
      $not_found_view();
    } else {
      if (str_starts_with($action, "handle_")) {
        $res = $router[$action]();
        $success_view($res->success_message, $res->next_action);
      } else {
        $router[$action]();
      }
    }
  } catch (Error $e) {
    $error_view($e->getMessage());
    error_log($e->getTraceAsString());
  }
};

function with_db(Closure $fn)
{
  return function () use ($fn) {
    $con = mysqli_connect("wep-db", "user", "user123", "app");
    $con->begin_transaction();
    try {
      $res = $fn($con);
      $con->commit();
      return $res;
    } catch (Error $e) {
      $con->rollback();
      throw $e;
    } finally {
      $con->close();
    }
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
  "" => with_db(function (mysqli $con) use ($controller, $home_view) {
    $home_view($con, $controller);
  }),
  "login" => $login_view,
  "signup" => $signup_view,
  "handle_login" => with_db(function (mysqli $con) use ($controller, $handle_login) {
    return $handle_login($con, $controller);
  }),
  "handle_signup" => with_db(function (mysqli $con) use ($controller, $handle_signup) {
    return $handle_signup($con, $controller);
  }),
  "handle_register_passenger" => with_db(function (mysqli $con) use ($controller, $handle_register_passenger) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      NONE_ROLE,
      function ($ctx) use ($con, $controller, $handle_register_passenger) {
        return $handle_register_passenger($con, $controller, $ctx);
      }
    )();
  }),
  "handle_register_company" => with_db(function (mysqli $con) use ($controller, $handle_register_company) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      NONE_ROLE,
      function ($ctx) use ($con, $controller, $handle_register_company) {
        return $handle_register_company($con, $controller, $ctx);
      }
    )();
  }),
);
handle_action($action, $router, $success_view, $error_view, $not_found_view);
