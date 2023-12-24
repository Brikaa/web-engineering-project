<?php

declare(strict_types=1);

require_once "model.php";
require_once "repo.php";

const USER_ID = "user_id";
const PASSPORT_IMAGE = "passport";
const PROFILE_IMAGE = "profile";

class DbController
{
  private Repo $repo;
  private array $session;

  function __construct(Repo $repo, array &$session)
  {
    $this->repo = $repo;
    $this->session = &$session;
  }

  private function upload_image(string $temp_file_path, string $type, string $user_id): string
  {
    if (!getimagesize($temp_file_path)) {
      throw new Error("File is not an image");
    }
    $path = "cdn/$user_id-$type";
    if (!move_uploaded_file($temp_file_path, $path))
      throw new Error("Failed to upload the file");
    return $path;
  }

  public function signup(
    mysqli $con,
    string $email,
    string $name,
    string $password,
    string $telephone
  ): bool {
    if ($this->repo->select_user_by_name_or_email($con, $name, $email) != null) {
      throw new Exception("A user with this name or email already exists");
    }
    $req = $this->check_insert_user_business_rules(
      new InsertUserRequest($email, $name, $password, $telephone, "", 0.0)
    );
    return $this->repo->insert_user($con, $req);
  }

  public function login(
    mysqli $con,
    string $email,
    string $password
  ) {
    $user = $this->repo->select_user_by_email_and_password($con, $email, $password);
    if ($user == null)
      throw new Exception("Invalid email or password");
    $this->session[USER_ID] = $user->id;
  }

  public function logout()
  {
    $this->session = array();
  }

  private function check_insert_user_business_rules(InsertUserRequest $req)
  {
    if (strlen($req->password) < 8)
      throw new Error("Password must be at least 8 characters");
    if ($req->email === "" || $req->name === "" || $req->telephone === "")
      throw new Error("Email, name or phone can not be empty");
    return $req;
  }

  public function get_user_company(mysqli $con, UserContext $ctx): Company
  {
    return $this->repo->select_company_by_user_id($con, $ctx->id);
  }

  public function get_user_passenger(mysqli $con, UserContext $ctx): Passenger
  {
    return $this->repo->select_passenger_by_user_id($con, $ctx->id);
  }

  public function with_user_ctx(mysqli $con, array &$session, string $role, Closure $fn): Closure
  {
    if (!array_key_exists(USER_ID, $session)) {
      throw new Error("Permission denied");
    } else {
      $user_id = $session[USER_ID];
      $user_ctx = $this->repo->select_user_by_id($con, $user_id);
      if (($user_ctx->role !== $role && $role !== '*') || ($role === '*' && $user_ctx->role === NONE_ROLE)) {
        throw new Error("Permission Denied");
      } else {
        return function () use ($fn, $user_ctx) {
          return $fn($user_ctx);
        };
      }
    }
  }

  public function get_logged_in_user(mysqli $con): ?UserContext
  {
    if (!array_key_exists(USER_ID, $this->session))
      return null;
    $user_id = $this->session[USER_ID];
    return $this->repo->select_user_by_id($con, $user_id);
  }

  private function get_photo_url_from_temp_url(
    string $user_id,
    string $old_url,
    string $image_type,
    string $temp_url
  ): string {
    return $temp_url === ""
      ? $old_url
      : $this->upload_image($temp_url, $image_type, $user_id);
  }

  public function update_user(
    mysqli $con,
    UserContext $ctx,
    string $email,
    string $name,
    string $password,
    string $telephone,
    string $temp_photo_url
  ): bool {
    return $this->repo->update_user_by_id(
      $con,
      $ctx->id,
      $this->check_insert_user_business_rules(new InsertUserRequest(
        $email,
        $name,
        $password,
        $telephone,
        $this->get_photo_url_from_temp_url($ctx->id, $ctx->photo_url, PROFILE_IMAGE, $temp_photo_url),
        $ctx->money
      ))
    );
  }

  public function register_passenger(
    mysqli $con,
    UserContext $ctx,
    string $temp_passport_image_url
  ) {
    return $this->repo->insert_passenger_for_user_id(
      $con,
      $ctx->id,
      $this->upload_image($temp_passport_image_url, PASSPORT_IMAGE, $ctx->id)
    );
  }

  public function register_company(
    mysqli $con,
    UserContext $ctx,
    string $bio,
    string $address
  ): bool {
    if ($ctx->role != NONE_ROLE)
      throw new Error("You have already specified your account type");
    return $this->repo->insert_company_for_user_id($con, $ctx->id, $bio, $address);
  }

  public function update_passenger(
    mysqli $con,
    UserContext $ctx,
    string $temp_passport_image_url
  ): bool {
    $passenger = $this->repo->select_passenger_by_user_id($con, $ctx->id);
    return $this->repo->update_passenger_by_user_id(
      $con,
      $ctx->id,
      $this->get_photo_url_from_temp_url(
        $ctx->id,
        $passenger->passport_image_url,
        PASSPORT_IMAGE,
        $temp_passport_image_url
      )
    );
  }

  public function update_company(
    mysqli $con,
    UserContext $ctx,
    string $bio,
    string $address
  ): bool {
    return $this->repo->update_company_by_user_id($con, $ctx->id, $bio, $address);
  }

  public function get_completed_flights(
    mysqli $con,
    UserContext $ctx,
  ): array {
    return $this->repo->select_completed_flights_summaries_for_passenger($con, $ctx->id);
  }

  public function get_upcoming_flights(
    mysqli $con,
    UserContext $ctx,
  ): array {
    return $this->repo->select_upcoming_flights_summaries_for_passenger($con, $ctx->id);
  }

  public function get_available_flights(
    mysqli $con,
    UserContext $ctx,
  ): array {
    return $this->repo->select_available_flights_summaries_for_passenger($con, $ctx->id);
  }

  public function get_flight_details(
    mysqli $con,
    string $flight_id
  ): FlightDetail {
    $res = $this->repo->select_flight_details_by_flight_id($con, $flight_id);
    if (!$res)
      throw new Error("A flight with this id does not exist");
    return $res;
  }

  public function get_flights_by_source_and_destination(
    mysqli $con,
    UserContext $ctx,
    string $src,
    string $dest
  ) {
    return $this->repo->select_available_flights_summaries_for_passenger_by_src_dest($con, $ctx->id, $src, $dest);
  }

  public function book_flight(
    mysqli $con,
    UserContext $ctx,
    bool $cash,
    string $flight_id
  ): bool {
    $flight = $this->repo->select_flight_details_by_flight_id($con, $flight_id);
    if (!$flight)
      throw new Error("A flight with this id does not exist");
    if (new DateTime() > $flight->cities[0]->date_in_city)
      throw new Error("Can't book a flight that starts in the past");
    if ($this->repo->select_flight_reservation_id_by_user_id_and_flight_id($con, $ctx->id, $flight_id) != null)
      throw new Error("You already have a reservation for this flight");
    if ($flight->max_passengers <= $flight->registered_passengers)
      throw new Error("This flight is fully reserved");
    if (!$cash && $ctx->money < $flight->price)
      throw new Error("You don't have enough money to book this flight");
    if (!$cash) {
      $this->repo->change_money_for_user($con, $ctx->id, -$flight->price);
    }
    return
      $this->repo->insert_flight_reservation_for_user($con, $ctx->id, $flight_id)
      && $this->repo->change_money_for_user($con, $flight->company_user_id, $flight->price);
  }

  public function get_flight_reservation_id_for_flight(
    mysqli $con,
    UserContext $ctx,
    string $flight_id
  ): ?string {
    return $this->repo->select_flight_reservation_id_by_user_id_and_flight_id($con, $ctx->id, $flight_id);
  }

  public function cancel_reservation(
    mysqli $con,
    UserContext $ctx,
    string $flight_reservation_id
  ): bool {
    $flight_detail = $this->repo->select_flight_details_by_reservation_id($con, $flight_reservation_id);
    if (!$flight_detail) {
      throw new Error("Such flight reservation does not exist");
    }
    if ($flight_detail->cities[0]->date_in_city < new DateTime())
      throw new Error("Can't cancel a past flight reservation");
    if (!$this->repo->delete_flight_reservation_for_user($con, $ctx->id, $flight_reservation_id)) {
      throw new Error("Couldn't cancel this reservation");
    }
    return
      $this->repo->change_money_for_user($con, $ctx->id, $flight_detail->price)
      && $this->repo->change_money_for_user($con, $flight_detail->company_user_id, -$flight_detail->price);
  }

  public function send_message(
    mysqli $con,
    UserContext $ctx,
    string $receiver_id,
    string $message
  ): bool {
    return $this->repo->insert_message($con, $ctx->id, $receiver_id, $message);
  }

  public function get_messages(mysqli $con, UserContext $ctx): array
  {
    return $this->repo->select_messages_for_user($con, $ctx->id);
  }

  public function list_company_flights(mysqli $con, UserContext $ctx): array
  {
    return $this->repo->select_flights_summaries_for_company($con, $ctx->id);
  }

  public function add_flight(
    mysqli $con,
    UserContext $ctx,
    string $name,
    int $max_passengers,
    float $price,
    array $flight_cities
  ): bool {
    if ($max_passengers <= 0)
      throw new Error("The maximum number of passengers must be a positive number");
    if ($price < 0)
      throw new Error("The price must be a positive number");
    if ($flight_cities < 2)
      throw new Error("At least two cities are required");
    if (!$this->repo->insert_flight_for_company($con, $ctx->id, $name, $max_passengers, $price))
      throw new Error("Could not add this flight");
    $flight_id = $this->repo->select_flight_id_by_flight_name($con, $name);
    return $this->repo->insert_flight_cities_for_flight($con, $flight_id, $flight_cities);
  }

  public function cancel_flight(
    mysqli $con,
    UserContext $ctx,
    string $flight_id,
  ): bool {
    $flight = $this->repo->select_flight_details_by_flight_id($con, $flight_id);
    if (!$this->repo->delete_flight($con, $ctx->id, $flight_id))
      throw new Error("Can't cancel this flight");
    return
      $this->repo->change_money_for_registered_passengers($con, $flight_id, $flight->price)
      && $this->repo->change_money_for_user(
        $con,
        $flight->company_user_id,
        -$flight->price * $flight->registered_passengers
      );
  }

  public function deposit_money(mysqli $con, UserContext $ctx, float $amount): bool
  {
    if ($amount <= 0)
      throw new Error("Invalid amount to deposit");
    return $this->repo->change_money_for_user($con, $ctx->id, $amount);
  }
}
