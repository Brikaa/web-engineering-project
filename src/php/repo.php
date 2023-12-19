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

function with_executed_statement(mysqli $con, string $sql, string $binding, array $params, Closure $fn)
{
  return with_prepared_statement($con, $sql, function ($s) use (&$binding, &$params, $fn) {
    $s->bind_param($binding, ...$params);
    if ($s->execute()) {
      return $fn($s);
    }
    return null;
  });
}

function execute_statement(mysqli $con, string $sql, string $binding, array $params): ?mysqli_stmt
{
  return with_executed_statement($con, $sql, $binding, $params, function ($s) {
    return $s;
  });
}

function select_one(mysqli $con, string $sql, string $binding, array $params): ?array
{
  return with_executed_statement($con, $sql, $binding, $params, function (mysqli_stmt $s) {
    if ($res = $s->get_result()) {
      return $res->fetch_array();
    }
    return null;
  });
}

function select_all(mysqli $con, string $sql, string $binding, array $params): array
{
  return with_executed_statement($con, $sql, $binding, $params, function (mysqli_stmt $s) {
    $arr = array();
    while ($res = $s->get_result()) {
      if ($e = $res->fetch_array()) {
        $arr[] = $e;
      }
    }
    return $arr;
  });
}

function select_user_by_condition(mysqli $con, string $condition, string $binding, array $params): ?UserContext
{
  $user = select_one(
    $con,
    "SELECT User.id, User.name, Passenger.id, Company.id FROM User
    LEFT JOIN Company ON Company.user_id = User.user_id
    LEFT JOIN Passenger ON Passenger.user_id = User.user_id
    WHERE $condition",
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

function select_flights_summaries_by_condition(
  mysqli $con,
  string $from,
  string $condition,
  string $bindings,
  array $params,
): array {
  $arr = select_all(
    $con,
    "SELECT
      Flight.id,
      Flight.name,
      Company.name,
      Flight.price,
      StartCity.name,
      StartCity.date_in_city,
      EndCity.name,
      EndCity.date_in_city
    FROM $from
      LEFT JOIN
        (SELECT C.name, C.date_in_city FROM FlightCity AS C ORDER BY C.date_in_city ASC LIMIT 1)
      AS StartCity ON StartCity.flight_id = Flight.id
      LEFT JOIN
        (SELECT C.name, C.date_in_city FROM FlightCity AS C ORDER BY C.date_in_city DESC LIMIT 1)
      AS EndCity ON EndCity.flight_id = Flight.id
      LEFT JOIN Company ON Company.user_id = Flight.company_user_id
    WHERE $condition",
    $bindings,
    $params
  );
  $res = array();
  foreach ($arr as $row) {
    $res[] = new FlightSummary(
      $row[0],
      $row[1],
      $row[2],
      $row[3],
      new FlightCity($row[4], $row[5]),
      new FlightCity($row[6], $row[7])
    );
  }
  return $res;
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

  public function select_upcoming_flights_summaries_for_user(mysqli $con, string $user_id): array
  {
    $now = date("Y-m-d H:i:s");
    return select_flights_summaries_by_condition(
      $con,
      "FlightReservation LEFT JOIN Flight ON FlightReservation.flight_id = Flight.id",
      "FlightReservation.passenger_user_id = ? AND StartCity.date_in_city > $now",
      "s",
      [$user_id]
    );
  }

  public function select_completed_flights_summaries_for_user(mysqli $con, string $user_id): array
  {
    $now = date("Y-m-d H:i:s");
    return select_flights_summaries_by_condition(
      $con,
      "FlightReservation LEFT JOIN Flight ON FlightReservation.flight_id = Flight.id",
      "FlightReservation.passenger_user_id = ? AND EndCity.date_in_city < $now",
      "s",
      [$user_id]
    );
  }

  public function select_available_flights_summaries_for_user(mysqli $con, string $user_id): array
  {
    $now = date("Y-m-d H:i:s");
    return select_flights_summaries_by_condition(
      $con,
      "Flight",
      "StartCity.date_in_city > $now
      AND Flight.id NOT IN (
        SELECT FlightReservation.flight_id FROM FlightReservation WHERE FlightReservation.passenger_user_id = ?
      )",
      "s",
      [$user_id]
    );
  }

  public function flight_reservation_by_user_id_and_flight_id_exists(
    mysqli $con,
    string $user_id,
    string $flight_id
  ): bool {
    return select_one(
      $con,
      "SELECT FlightReservation.flight_id FROM FlightReservation
      WHERE
        FlightReservation.passenger_user_id = ?
        AND FlightReservation.flight_id = ?",
      "ss",
      [$user_id, $flight_id]
    ) != null;
  }

  public function select_flight_details_for_flight_id(mysqli $con, string $flight_id): ?FlightDetail
  {
    $flights_with_cities = select_all(
      $con,
      "SELECT Flight.id, Flight.name, Flight.price, Company.name, City.name, City.date_in_city
      FROM Flight
      LEFT JOIN
        Company ON Flight.company_user_id = Company.user_id
      LEFT JOIN
        FlightCity ON FlightCity.flight_id = Flight.id
      WHERE Flight.id = ?",
      "s",
      [$flight_id]
    );
    if (count($flights_with_cities) < 1) {
      return null;
    } else {
      $cities = array();
      foreach ($flights_with_cities as $row) {
        $cities[] = new FlightCity($row[4], new DateTime($row[5]));
      }
      return new FlightDetail(
        $flights_with_cities[0][0],
        $flights_with_cities[0][1],
        $flights_with_cities[0][3],
        $flights_with_cities[0][2],
        $cities
      );
    }
  }
}
