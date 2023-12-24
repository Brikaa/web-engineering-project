<?php

declare(strict_types=1);

const PASSENGER_ROLE = 'passenger';
const COMPANY_ROLE = 'company';
const NONE_ROLE = 'none';

class BareUser
{
  public string $email;
  public string $name;
  public string $telephone;
  public string $photo_url;
  public float $money;
}

final class InsertUserRequest extends BareUser
{
  public string $password;

  public function __construct(
    string $email,
    string $name,
    string $password,
    string $telephone,
    string $photo_url,
    float $money
  ) {
    $this->email = $email;
    $this->name = $name;
    $this->password = $password;
    $this->telephone = $telephone;
    $this->photo_url = $photo_url;
    $this->money = $money;
  }
}

final class UserContext extends BareUser
{
  public string $id;
  public string $role;

  public function __construct(
    string $id,
    string $email,
    string $name,
    string $telephone,
    string $photo_url,
    float $money,
    string $role,
  ) {
    $this->id = $id;
    $this->email = $email;
    $this->name = $name;
    $this->telephone = $telephone;
    $this->photo_url = $photo_url;
    $this->role = $role;
    $this->money = $money;
  }
}

final class Company
{
  public string $user_id;
  public string $bio;
  public string $address;

  public function __construct(string $user_id, string $bio, string $address)
  {
    $this->user_id = $user_id;
    $this->bio = $bio;
    $this->address = $address;
  }
}

final class Passenger
{
  public string $user_id;
  public string $passport_image_url;

  public function __construct(string $user_id, string $passport_image_url)
  {
    $this->user_id = $user_id;
    $this->passport_image_url = $passport_image_url;
  }
}

final class FlightCity
{
  public string $name;
  public DateTime $date_in_city;

  public function __construct(string $name, DateTime $date_in_city)
  {
    $this->name = $name;
    $this->date_in_city = $date_in_city;
  }
}

class BareFlight
{
  public string $id;
  public string $name;
  public string $company_name;
  public float $price;
}

final class FlightSummary extends BareFlight
{
  public FlightCity $source;
  public FlightCity $destination;

  public function __construct(
    string $id,
    string $name,
    string $company_name,
    float $price,
    FlightCity $source,
    FlightCity $destination
  ) {
    $this->id = $id;
    $this->name = $name;
    $this->company_name = $company_name;
    $this->price = $price;
    $this->source = $source;
    $this->destination = $destination;
  }
}

final class FlightDetail extends BareFlight
{
  public array $cities;
  public string $company_user_id;
  public int $max_passengers;
  public int $registered_passengers;

  public function __construct(
    string $id,
    string $name,
    float $price,
    string $company_user_id,
    string $company_name,
    int $max_passengers,
    int $registered_passengers,
    array $cities,
  ) {
    $this->id = $id;
    $this->name = $name;
    $this->company_name = $company_name;
    $this->price = $price;
    $this->cities = $cities;
    $this->max_passengers = $max_passengers;
    $this->registered_passengers = $registered_passengers;
    $this->company_user_id = $company_user_id;
  }
}

final class Message
{
  public string $id;
  public string $message;
  public string $sender_user_id;
  public string $sender_name;
  public string $sender_photo_url;
  public string $receiver_user_id;
  public string $receiver_name;
  public string $receiver_photo_url;

  public function __construct(
    string $id,
    string $message,
    string $sender_user_id,
    string $sender_name,
    string $sender_photo_url,
    string $receiver_user_id,
    string $receiver_name,
    string $receiver_photo_url,
  ) {
    $this->id = $id;
    $this->message = $message;
    $this->sender_user_id = $sender_user_id;
    $this->sender_name = $sender_name;
    $this->sender_photo_url = $sender_photo_url;
    $this->receiver_user_id = $receiver_user_id;
    $this->receiver_name = $receiver_name;
    $this->receiver_photo_url = $receiver_photo_url;
  }
}
