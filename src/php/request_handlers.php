<?php

declare(strict_types=1);
require_once "controller.php";

class HandlerResponse
{
  public string $success_message;
  public string $next_action;

  public function __construct(string $success_message, string $next_action)
  {
    $this->success_message = $success_message;
    $this->next_action = $next_action;
  }
}

$handle_login = function (mysqli $con, DbController $c): HandlerResponse {
  $c->login($con, $_POST["email"], $_POST["password"]);
  return new HandlerResponse("Logging you in", "");
};

$handle_signup = function (mysqli $con, DbController $c): HandlerResponse {
  $c->signup($con, $_POST["email"], $_POST["name"], $_POST["password"], $_POST["telephone"]);
  return new HandlerResponse("Account created successfully, you can now log in 🥳", "");
};

function update_user(mysqli $con, DbController $c, UserContext $ctx)
{
  $c->update_user(
    $con,
    $ctx,
    $_POST["email"],
    $_POST["name"],
    $_POST["password"],
    $_POST["telephone"],
    $_FILES["photo"]["tmp_name"]
  );
}

$handle_update_passenger = function (mysqli $con, DbController $c, UserContext $ctx) {
  update_user($con, $c, $ctx);
  $c->update_passenger($con, $ctx, $_FILES["passport_image"]["tmp_name"]);
};

$handle_register_passenger = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->register_passenger($con, $ctx, $_FILES["passport_image"]["tmp_name"]);
  return new HandlerResponse("Updating your profile", "");
};

$handle_update_company = function (mysqli $con, DbController $c, UserContext $ctx) {
  update_user($con, $c, $ctx);
  $c->update_company($con, $ctx, $_POST["bio"], $_POST["address"]);
};

$handle_register_company = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->register_company($con, $ctx, $_POST["bio"], $_POST["address"]);
  return new HandlerResponse("Updating your profile", "");
};

$handle_book_flight = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->book_flight($con, $ctx, $_POST["cash"], $_POST["id"]);
};

$handle_cancel_reservation = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->cancel_reservation($con, $ctx, $_POST["id"]);
};

$handle_send_message = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->send_message($con, $ctx, $_POST["receiver_id"], $_POST["message"]);
};

$handle_add_flight = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->add_flight($con, $ctx, $_POST["name"], $_POST["max_passengers"], $_POST["price"]);
};

$handle_add_flight_city = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->add_flight_city($con, $ctx, $_POST["flight_id"], $_POST["name"], $_POST["date_in_city"]);
};

$handle_cancel_flight = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->cancel_flight($con, $ctx, $_POST["id"]);
};
