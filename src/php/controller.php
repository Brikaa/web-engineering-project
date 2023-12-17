<?php
require "model.php";

const USER_ID = "user_id";

function signup(
  $con,
  $insert_user,
  $select_user_by_name_or_email,
  string $email,
  string $name,
  string $password,
  string $telephone
) {
  if ($select_user_by_name_or_email != null)
    throw new Exception("A user with this name or email already exists");
  $req = new InsertUserRequest($email, $name, $password, $telephone, "", 0.0);
  $insert_user($con, $req);
}

function login($con, array $session, $select_user_by_email_and_password, string $email, string $password) {
  $user = $select_user_by_email_and_password($email, $password);
  if ($user == null)
    throw new Exception("Invalid email or password");
  $session[USER_ID] = $user->id;
}
?>
