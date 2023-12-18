<?php

declare(strict_types=1);

require_once "model.php";

const USER_ID = "user_id";

$with_db = function (Closure $fn) {
  $con = mysqli_connect("wep-db", "user", "user123", "app");
  $fn($con);
};

$with_auth = function (mysqli $con, string $role, $fn) {
  $ctx = "something"; // TODO: generate context from permission
  $fn($con, $ctx);
};

$signup = function (
  Closure $insert_user,
  Closure $select_user_by_name_or_email,
  string $email,
  string $name,
  string $password,
  string $telephone
) use ($with_db) {
  $with_db(function (mysqli $con) use (
    $insert_user,
    $select_user_by_name_or_email,
    $email,
    $name,
    $password,
    $telephone,
  ) {
    if ($select_user_by_name_or_email != null) {
      throw new Exception("A user with this name or email already exists");
    }
    $req = new InsertUserRequest($email, $name, $password, $telephone, "", 0.0);
    $insert_user($con, $req);
  });
};

$login = function (
  array $session,
  Closure $select_user_by_email_and_password,
  string $email,
  string $password
) use ($with_db) {
  $with_db(function (mysqli $con) use ($session, $select_user_by_email_and_password, $email, $password) {
    $user = $select_user_by_email_and_password($con, $email, $password);
    if ($user == null)
      throw new Exception("Invalid email or password");
    $session[USER_ID] = $user->id;
  });
};
