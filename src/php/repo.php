<?php

declare(strict_types=1);
require_once 'model.php';

function with_prepared_statement(mysqli $con, string $sql, $fn)
{
  $stmt = $con->prepare($sql);
  $res = $fn($stmt);
  $stmt->close();
  return $res;
}

function execute_statement(mysqli $con, string $sql, string $binding, array $params)
{
  return with_prepared_statement($con, $sql, function ($s) use (&$binding, &$params) {
    $s->bind_param($binding, ...$params);
    return $s->execute();
  });
}

function select_one(mysqli $con, string $sql, string $binding, array $params)
{
  return with_prepared_statement($con, $sql, function ($s) use (&$binding, &$params) {
    $s->bind_param($binding, ...$params);
    if ($s->execute()) {
      $res = $s->get_result();
      if ($res) {
        return $res->fetch_array();
      }
    }
    return null;
  });
}

function select_user_by_condition(mysqli $con, string $condition, string $binding, array $params): ?UserContext
{
  $user = select_one(
    $con,
    "SELECT User.id, User.name, Passenger.id, Company.id FROM User WHERE $condition
    LEFT JOIN Company ON Company.user_id = User.user_id
    LEFT JOIN Passenger ON Passenger.user_id = User.user_id",
    $binding,
    $params
  );
  if ($user) {
    $role = NONE_ROLE;
    if ($user[2] == null && $user[3] == null) {
      $role = NONE_ROLE;
    } else if ($user[2] != null) {
      $role = PASSENGER_ROLE;
    } else {
      $role = COMPANY_ROLE;
    }
    return new UserContext($user[0], $user[1], $role);
  }
  return null;
}

class Repo
{
  public function insert_user(
    mysqli $con,
    InsertUserRequest $user
  ) {
    return execute_statement(
      $con,
      "INSERT INTO User(`email`, `name`, `password`, `telephone`, `photo_url`, `money`) values(?, ?, ?, ?, ?, ?)",
      "sssssd",
      [$user->email, $user->name, $user->password, $user->telephone, $user->photo_url, $user->money]
    );
  }

  public function insert_passenger_for_user_id(
    mysqli $con,
    string $user_id,
    string $passport_image_url
  ) {
    return execute_statement(
      $con,
      "INSERT INTO Passenger(`user_id`, `passport_image_url`) values(?, ?)",
      "ss",
      [$user_id, $passport_image_url]
    );
  }

  public function insert_company_for_user_id(
    mysqli $con,
    string $user_id,
    string $bio,
    string $address
  ) {
    return execute_statement(
      $con,
      "INSERT INTO Passenger(`user_id`, `bio`, `address`) values(?, ?, ?)",
      "sss",
      [$user_id, $bio, $address]
    );
  }

  public function update_user_by_id(
    mysqli $con,
    string $id,
    InsertUserRequest $user
  ) {
    return execute_statement(
      $con,
      "UPDATE User SET `email`=?, `name`=?, `password`=?, `telephone`=?, `photo_url`=?, `money`=? WHERE id=?",
      "sssssds",
      [$user->email, $user->name, $user->password, $user->telephone, $user->photo_url, $user->money, $id]
    );
  }

  public function update_passenger_by_user_id(
    mysqli $con,
    string $user_id,
    string $passport_image_url
  ) {
    return execute_statement(
      $con,
      "UPDATE Passenger SET passport_image_url=? WHERE user_id=?",
      "ss",
      [$passport_image_url, $user_id]
    );
  }

  public function update_company_by_user_id(
    mysqli $con,
    string $user_id,
    string $bio,
    string $address
  ) {
    return execute_statement(
      $con,
      "UPDATE Company SET bio=?, `address`=? WHERE user_id=?",
      "sss",
      [$bio, $address, $user_id]
    );
  }

  public function select_user_by_id(mysqli $con, string $id)
  {
    return select_user_by_condition($con, "User.id=?", "s", [$id]);
  }

  public function select_user_by_name_or_email(mysqli $con, string $name, string $email)
  {
    return select_user_by_condition($con, "User.name=? OR User.email=?", "ss", [$name, $email]);
  }

  public function select_user_by_email_and_password(mysqli $con, string $email, string $password)
  {
    return select_user_by_condition($con, "User.email = ? AND User.password = ?", "ss", [$email, $password]);
  }

  public function select_passenger_by_user_id(mysqli $con, string $user_id): ?Passenger
  {
    $passenger = select_one(
      $con,
      "SELECT Passenger.user_id, Passenger.passport_image_url FROM Passenger WHERE Passenger.user_id = ?",
      "s",
      [$user_id]
    );
    if ($passenger) {
      return new Passenger($passenger[0], $passenger[1]);
    }
    return null;
  }

  public function select_company_by_user_id(mysqli $con, string $user_id): ?Company
  {
    $company = select_one(
      $con,
      "SELECT Company.user_id, Company.bio, Company.address FROM Company WHERE Company.user_id = ?",
      "s",
      [$user_id]
    );
    if ($company) {
      return new Company($company[0], $company[1], $company[2]);
    }
    return null;
  }
}
