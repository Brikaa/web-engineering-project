<?php
declare(strict_types=1);

const PASSENGER_ROLE = 'passenger';
const COMPANY_ROLE = 'company';
const NONE_ROLE = 'none';

final class InsertUserRequest {
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

final class UserContext {
  public string $id;
  public string $name;
  public string $role;

  public function __construct(string $id, string $name, string $role) {
    $this->id = $id;
    $this->name = $name;
    $this->role = $role;
  }
}

?>
