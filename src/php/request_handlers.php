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

$handle_logout = function (DbController $c): HandlerResponse {
  $c->logout();
  return new HandlerResponse("Logging you out", "");
};

$handle_signup = function (mysqli $con, DbController $c): HandlerResponse {
  $c->signup($con, $_POST["email"], $_POST["name"], $_POST["password"], $_POST["telephone"]);
  return new HandlerResponse("Account created successfully, you can now log in 🥳", "");
};

function get_photo_temp_name(string $attribute_name): string
{
  return array_key_exists($attribute_name, $_FILES) ? $_FILES[$attribute_name]["tmp_name"] : "";
}

function update_user(mysqli $con, DbController $c, UserContext $ctx)
{
  $c->update_user(
    $con,
    $ctx,
    $_POST["email"],
    $_POST["name"],
    $_POST["password"],
    $_POST["telephone"],
    get_photo_temp_name("photo")
  );
}

$handle_update_passenger = function (mysqli $con, DbController $c, UserContext $ctx) {
  update_user($con, $c, $ctx);
  error_log($_FILES["passport_image"]["tmp_name"]);
  $c->update_passenger($con, $ctx, get_photo_temp_name("passport_image"));
  return new HandlerResponse("Updating your profile ✨", "profile");
};

$handle_register_passenger = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->register_passenger($con, $ctx, $_FILES["passport_image"]["tmp_name"]);
  return new HandlerResponse("Updating your profile", "");
};

$handle_update_company = function (mysqli $con, DbController $c, UserContext $ctx) {
  update_user($con, $c, $ctx);
  $c->update_company($con, $ctx, $_POST["bio"], $_POST["address"]);
  return new HandlerResponse("Updating your profile ✨", "profile");
};

$handle_register_company = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->register_company($con, $ctx, $_POST["bio"], $_POST["address"]);
  return new HandlerResponse("Updating your profile", "");
};

$handle_book_flight_cash = function (mysqli $con, DbController $c, UserContext $ctx) {
  $id = $_POST["id"];
  $c->book_flight($con, $ctx, true, $id);
  return new HandlerResponse("Booking your flight 🎉", "");
};

$handle_book_flight_credit = function (mysqli $con, DbController $c, UserContext $ctx) {
  $id = $_POST["id"];
  $c->book_flight($con, $ctx, false, $id);
  return new HandlerResponse("Booking your flight 🎉", "");
};

$handle_cancel_reservation = function (mysqli $con, DbController $c, UserContext $ctx) {
  $id = $_POST["id"];
  $c->cancel_reservation($con, $ctx, $id);
  return new HandlerResponse("Cancelling the reservation", "");
};

$handle_send_message = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->send_message($con, $ctx, $_POST["receiver_id"], $_POST["message"]);
  return new HandlerResponse("Sending the message ✉️", "messages");
};

$handle_add_flight = function (mysqli $con, DbController $c, UserContext $ctx) {
  $flight_cities = array();
  $city_names = $_POST["city_names"];
  $city_dates = $_POST["city_dates"];
  if (count($city_names) != count($city_dates))
    throw new Error("The number of city names and the number of dates in cities do not match");
  for ($i = 0; $i < count($city_names); ++$i) {
    $flight_cities[] = new FlightCity($city_names[$i], new DateTime($city_dates[$i]));
  }
  $c->add_flight(
    $con,
    $ctx,
    $_POST["name"],
    intval($_POST["max_passengers"]),
    floatval($_POST["price"]),
    $flight_cities
  );
  return new HandlerResponse("Creating the flight ✈️", "");
};

$handle_cancel_flight = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->cancel_flight($con, $ctx, $_POST["id"]);
  return new HandlerResponse("Cancelling the flight", "");
};

$handle_deposit = function (mysqli $con, DbController $c, UserContext $ctx) {
  $c->deposit_money($con, $ctx, floatval($_POST["amount"]));
  return new HandlerResponse("Depositing money", "");
};
