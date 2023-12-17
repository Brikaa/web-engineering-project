<?php
require_once "model.php";

const USER_ID = "user_id";

$signup = function(
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
};

$login = function($con, array $session, $select_user_by_email_and_password, string $email, string $password) {
  $user = $select_user_by_email_and_password($con, $email, $password);
  if ($user == null)
    throw new Exception("Invalid email or password");
  $session[USER_ID] = $user->id;
};
?>
