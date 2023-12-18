<?php

declare(strict_types=1);

const PASSENGER_ROLE = 'passenger';
const COMPANY_ROLE = 'company';
const NONE_ROLE = 'none';

final class InsertUserRequest
{
  public string $email;
  public string $name;
  public string $password;
  public string $telephone;
  public string $photo_url;
  public float $money;

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

final class UserContext
{
  public string $id;
  public string $name;
  public string $role;

  public function __construct(string $id, string $name, string $role)
  {
    $this->id = $id;
    $this->name = $name;
    $this->role = $role;
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

final class City
{
  public string $name;
  public DateTime $date_in_city;
}

class BareCity {
  public string $id;
  public string $name;
  public string $company_name;
  public float $price;
}

final class FlightSummary extends BareCity
{
  public City $source;
  public City $destination;

  public function __construct(
    string $id,
    string $name,
    string $company_name,
    float $price,
    City $source,
    City $destination
  ) {
    $this->id = $id;
    $this->name = $name;
    $this->company_name = $company_name;
    $this->price = $price;
    $this->source = $source;
    $this->destination = $destination;
  }
}

final class FlightDetail extends BareCity
{
  public array $cities;

  public function __construct(
    string $id,
    string $name,
    string $company_name,
    float $price,
    array $cities
  ) {
    $this->id = $id;
    $this->name = $name;
    $this->company_name = $company_name;
    $this->price = $price;
    $this->cities = $cities;
  }
}
