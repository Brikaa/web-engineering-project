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
require_once 'views/profile.php';
require_once 'views/deposit.php';
require_once 'views/flight.php';
require_once 'views/add_flight.php';
require_once 'views/send_message.php';
require_once 'views/messages.php';

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
  } catch (Throwable $e) {
    $error_view($e->getMessage());
    error_log($e->getTraceAsString());
    error_log(strval($e->getFile()));
    error_log(strval($e->getLine()));
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
    } catch (Throwable $e) {
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
  "handle_logout" => function () use ($controller, $handle_logout) {
    return $handle_logout($controller);
  },
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
  "profile" => with_db(function (mysqli $con) use ($controller, $profile_view) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      '*',
      function ($ctx) use ($con, $controller, $profile_view) {
        return $profile_view($con, $controller, $ctx);
      }
    )();
  }),
  "handle_update_passenger" => with_db(function (mysqli $con) use ($controller, $handle_update_passenger) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      PASSENGER_ROLE,
      function ($ctx) use ($con, $controller, $handle_update_passenger) {
        return $handle_update_passenger($con, $controller, $ctx);
      }
    )();
  }),
  "handle_update_company" => with_db(function (mysqli $con) use ($controller, $handle_update_company) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      COMPANY_ROLE,
      function ($ctx) use ($con, $controller, $handle_update_company) {
        return $handle_update_company($con, $controller, $ctx);
      }
    )();
  }),
  "deposit" => with_db(function (mysqli $con) use ($controller, $deposit_view) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      "*",
      function ($ctx) use ($con, $controller, $deposit_view) {
        return $deposit_view($con, $controller, $ctx);
      }
    )();
  }),
  "handle_deposit" => with_db(function (mysqli $con) use ($controller, $handle_deposit) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      "*",
      function ($ctx) use ($con, $controller, $handle_deposit) {
        return $handle_deposit($con, $controller, $ctx);
      }
    )();
  }),
  "flight" => with_db(function (mysqli $con) use ($controller, $flight_view) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      "*",
      function ($ctx) use ($con, $controller, $flight_view) {
        return $flight_view($con, $controller, $ctx);
      }
    )();
  }),
  "handle_book_flight_cash" => with_db(function (mysqli $con) use ($controller, $handle_book_flight_cash) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      PASSENGER_ROLE,
      function ($ctx) use ($con, $controller, $handle_book_flight_cash) {
        return $handle_book_flight_cash($con, $controller, $ctx);
      }
    )();
  }),
  "handle_book_flight_credit" => with_db(function (mysqli $con) use ($controller, $handle_book_flight_credit) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      PASSENGER_ROLE,
      function ($ctx) use ($con, $controller, $handle_book_flight_credit) {
        return $handle_book_flight_credit($con, $controller, $ctx);
      }
    )();
  }),
  "handle_cancel_reservation" => with_db(function (mysqli $con) use ($controller, $handle_cancel_reservation) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      PASSENGER_ROLE,
      function ($ctx) use ($con, $controller, $handle_cancel_reservation) {
        return $handle_cancel_reservation($con, $controller, $ctx);
      }
    )();
  }),
  "handle_cancel_flight" => with_db(function (mysqli $con) use ($controller, $handle_cancel_flight) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      COMPANY_ROLE,
      function ($ctx) use ($con, $controller, $handle_cancel_flight) {
        return $handle_cancel_flight($con, $controller, $ctx);
      }
    )();
  }),
  "handle_add_flight" => with_db(function (mysqli $con) use ($controller, $handle_add_flight) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      COMPANY_ROLE,
      function ($ctx) use ($con, $controller, $handle_add_flight) {
        return $handle_add_flight($con, $controller, $ctx);
      }
    )();
  }),
  "add_flight" => with_db(function (mysqli $con) use ($controller, $add_flight_view) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      COMPANY_ROLE,
      function ($_ctx) use ($add_flight_view) {
        return $add_flight_view();
      }
    )();
  }),
  "handle_send_message" => with_db(function (mysqli $con) use ($controller, $handle_send_message) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      "*",
      function ($ctx) use ($con, $controller, $handle_send_message) {
        return $handle_send_message($con, $controller, $ctx);
      }
    )();
  }),
  "send_message" => with_db(function (mysqli $con) use ($controller, $send_message_view) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      "*",
      function ($_ctx) use ($send_message_view) {
        return $send_message_view();
      }
    )();
  }),
  "messages" => with_db(function (mysqli $con) use ($controller, $messages_view) {
    return $controller->with_user_ctx(
      $con,
      $_SESSION,
      "*",
      function ($ctx) use ($con, $controller, $messages_view) {
        return $messages_view($con, $controller, $ctx);
      }
    )();
  }),
);
handle_action($action, $router, $success_view, $error_view, $not_found_view);
