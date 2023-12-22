<?php

declare(strict_types=1);
require_once 'model.php';

class Repo
{
  private function with_prepared_statement(mysqli $con, string $sql, $fn)
  {
    $stmt = $con->prepare($sql);
    $res = $fn($stmt);
    $stmt->close();
    return $res;
  }

  private function with_executed_statement(mysqli $con, string $sql, string $binding, array $params, Closure $fn)
  {
    return $this->with_prepared_statement($con, $sql, function ($s) use (&$binding, &$params, $fn) {
      $s->bind_param($binding, ...$params);
      if ($s->execute()) {
        return $fn($s);
      }
      return null;
    });
  }

  private function execute_statement(mysqli $con, string $sql, string $binding, array $params): ?bool
  {
    return $this->with_executed_statement($con, $sql, $binding, $params, function ($s) {
      return $s != null && $s != false;
    });
  }

  private function select_one(mysqli $con, string $sql, string $binding, array $params): ?array
  {
    return $this->with_executed_statement($con, $sql, $binding, $params, function (mysqli_stmt $s) {
      if ($res = $s->get_result()) {
        return $res->fetch_array();
      }
      return null;
    });
  }

  private function select_all(mysqli $con, string $sql, string $binding, array $params): array
  {
    return $this->with_executed_statement($con, $sql, $binding, $params, function (mysqli_stmt $s) {
      $arr = array();
      while ($res = $s->get_result()) {
        if ($e = $res->fetch_array()) {
          $arr[] = $e;
        }
      }
      return $arr;
    });
  }

  private function select_user_by_condition(
    mysqli $con,
    string $condition,
    string $binding,
    array $params
  ): ?UserContext {
    $user = $this->select_one(
      $con,
      "SELECT
        User.id,
        User.email,
        User.name,
        User.telephone,
        User.photo_url,
        User.money,
        Passenger.id,
        Company.id
      FROM User
      LEFT JOIN Company ON Company.user_id = User.id
      LEFT JOIN Passenger ON Passenger.user_id = User.id
      WHERE $condition",
      $binding,
      $params
    );
    if ($user) {
      $role = NONE_ROLE;
      if ($user[6] == null && $user[7] == null) {
        $role = NONE_ROLE;
      } else if ($user[6] != null) {
        $role = PASSENGER_ROLE;
      } else {
        $role = COMPANY_ROLE;
      }
      return new UserContext($user[0], $user[1], $user[2], $user[3], $user[4], $user[5], $role);
    }
    return null;
  }

  private function select_flights_summaries_by_condition(
    mysqli $con,
    string $from,
    string $condition,
    string $bindings,
    array $params,
  ): array {
    $arr = $this->select_all(
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
        new FlightCity($row[4], new DateTime($row[5])),
        new FlightCity($row[6], new DateTime($row[7]))
      );
    }
    return $res;
  }

  public function insert_user(
    mysqli $con,
    InsertUserRequest $user
  ): bool {
    return $this->execute_statement(
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
  ): bool {
    return $this->execute_statement(
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
  ): bool {
    return $this->execute_statement(
      $con,
      "INSERT INTO Company(`user_id`, `bio`, `address`) values(?, ?, ?)",
      "sss",
      [$user_id, $bio, $address]
    );
  }

  public function update_user_by_id(
    mysqli $con,

    string $id,
    InsertUserRequest $user
  ): bool {
    return $this->execute_statement(
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
  ): bool {
    return $this->execute_statement(
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
  ): bool {
    return $this->execute_statement(
      $con,
      "UPDATE Company SET bio=?, `address`=? WHERE user_id=?",
      "sss",
      [$bio, $address, $user_id]
    );
  }

  public function select_user_by_id(mysqli $con, string $id)
  {
    return $this->select_user_by_condition($con, "User.id=?", "s", [$id]);
  }

  public function select_user_by_name_or_email(mysqli $con, string $name, string $email)
  {
    return $this->select_user_by_condition($con, "User.name=? OR User.email=?", "ss", [$name, $email]);
  }

  public function select_user_by_email_and_password(mysqli $con, string $email, string $password)
  {
    return $this->select_user_by_condition($con, "User.email = ? AND User.password = ?", "ss", [$email, $password]);
  }

  public function select_passenger_by_user_id(mysqli $con, string $user_id): ?Passenger
  {
    $passenger = $this->select_one(
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
    $company = $this->select_one(
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

  public function select_upcoming_flights_summaries_for_passenger(mysqli $con, string $user_id): array
  {
    return $this->select_flights_summaries_by_condition(
      $con,
      "FlightReservation LEFT JOIN Flight ON FlightReservation.flight_id = Flight.id",
      "FlightReservation.passenger_user_id = ? AND StartCity.date_in_city > NOW()",
      "s",
      [$user_id]
    );
  }

  public function select_completed_flights_summaries_for_passenger(mysqli $con, string $user_id): array
  {
    return $this->select_flights_summaries_by_condition(
      $con,
      "FlightReservation LEFT JOIN Flight ON FlightReservation.flight_id = Flight.id",
      "FlightReservation.passenger_user_id = ? AND EndCity.date_in_city < NOW()",
      "s",
      [$user_id]
    );
  }

  public function select_available_flights_summaries_for_passenger(mysqli $con, string $user_id): array
  {
    return $this->select_flights_summaries_by_condition(
      $con,
      "Flight",
      "StartCity.date_in_city > NOW()
      AND Flight.id NOT IN (
        SELECT FlightReservation.flight_id FROM FlightReservation WHERE FlightReservation.passenger_user_id = ?
      )",
      "s",
      [$user_id]
    );
  }

  public function select_available_flights_summaries_for_passenger_by_src_dest(
    mysqli $con,
    string $user_id,
    string $src,
    string $dest
  ): array {
    $now = date("Y-m-d H:i:s");
    return $this->select_flights_summaries_by_condition(
      $con,
      "Flight",
      "StartCity.date_in_city > $now
      AND Flight.id NOT IN (
        SELECT FlightReservation.flight_id FROM FlightReservation WHERE FlightReservation.passenger_user_id = ?
      )
      AND StartCity.name LIKE %?%
      AND EndCity.name LIKE %?%",
      "sss",
      [$user_id, $src, $dest]
    );
  }

  public function select_flights_summaries_for_company(mysqli $con, string $user_id)
  {
    return $this->select_flights_summaries_by_condition(
      $con,
      "Flight",
      "Company.user_id = ?",
      "s",
      [$user_id]
    );
  }

  public function select_flight_reservation_id_by_user_id_and_flight_id(
    mysqli $con,
    string $user_id,
    string $flight_id
  ): ?string {
    $res = $this->select_one(
      $con,
      "SELECT FlightReservation.flight_id FROM FlightReservation
      WHERE
        FlightReservation.passenger_user_id = ?
        AND FlightReservation.flight_id = ?",
      "ss",
      [$user_id, $flight_id]
    );
    if ($res)
      return $res[0];
    else
      return null;
  }

  private function select_flight_details_by_condition(
    mysqli $con,
    string $condition,
    string $bindings,
    array $params
  ): ?FlightDetail {
    $flights_with_cities = $this->select_all(
      $con,
      "SELECT
        Flight.id,
        Flight.name,
        Flight.price,
        Company.user_id,
        Company.name,
        Flight.max_passengers,
        COUNT(FlightReservation.id),
        City.name,
        City.date_in_city
      FROM Flight
      LEFT JOIN Company ON Flight.company_user_id = Company.user_id
      LEFT JOIN FlightCity ON FlightCity.flight_id = Flight.id
      LEFT JOIN FlightReservation ON FlightReservation.flight_id = Flight.id
      WHERE $condition
      GROUP BY City.name
      ORDER BY City.date_in_city",
      $bindings,
      $params
    );
    if (count($flights_with_cities) < 1) {
      return null;
    } else {
      $cities = array();
      foreach ($flights_with_cities as $row) {
        $cities[] = new FlightCity($row[7], new DateTime($row[8]));
      }
      return new FlightDetail(
        $flights_with_cities[0][0],
        $flights_with_cities[0][1],
        $flights_with_cities[0][2],
        $flights_with_cities[0][3],
        $flights_with_cities[0][4],
        $flights_with_cities[0][5],
        $flights_with_cities[0][6],
        $cities,
      );
    }
  }

  public function select_flight_details_by_flight_id(mysqli $con, string $flight_id): ?FlightDetail
  {
    return $this->select_flight_details_by_condition($con, "Flight.id = ?", "s", [$flight_id]);
  }

  public function select_flight_details_by_flight_id_and_company_user_id(
    mysqli $con,
    string $flight_id,
    string $user_id
  ): ?FlightDetail {
    return $this->select_flight_details_by_condition(
      $con,
      "Flight.id = ? AND Company.user_id = ?",
      "ss",
      [$flight_id, $user_id]
    );
  }

  public function select_flight_details_by_reservation_id(mysqli $con, string $reservation_id): ?FlightDetail
  {
    return $this->select_flight_details_by_condition($con, "FlightReservation.id = ?", "s", [$reservation_id]);
  }

  public function insert_flight_reservation_for_user(mysqli $con, string $user_id, string $flight_id): bool
  {
    return $this->execute_statement(
      $con,
      "INSERT INTO FlightReservation(passenger_user_id, flight_id) VALUES (?, ?)",
      "ss",
      [$user_id, $flight_id]
    );
  }

  public function delete_flight_reservation_for_user(mysqli $con, string $user_id, string $flight_reservation_id): bool
  {
    return $this->execute_statement(
      $con,
      "DELETE FROM FlightReservation WHERE id = ? AND passenger_user_id = ?",
      "ss",
      [$flight_reservation_id, $user_id]
    );
  }

  private function change_money_by_condition(
    mysqli $con,
    string $condition,
    string $bindings,
    array $params,
    float $delta
  ): bool {
    array_unshift($params, $delta);
    return $this->execute_statement(
      $con,
      "UPDATE User SET User.money = User.money + ? WHERE $condition",
      "d" . $bindings,
      $params
    );
  }

  public function change_money_for_user(mysqli $con, string $user_id, float $delta): bool
  {
    return $this->change_money_by_condition($con, "User.id = ?", "s", [$user_id], $delta);
  }

  public function change_money_for_registered_passengers(mysqli $con, string $flight_id, float $delta)
  {
    return $this->change_money_by_condition(
      $con,
      "User.id IN (
        SELECT FlightReservation.passenger_user_id FROM FlightReservation WHERE FlightReservation.flight_id = ?
      )",
      "s",
      [$flight_id],
      $delta
    );
  }

  public function insert_message(mysqli $con, string $sender_id, string $receiver_id, string $message)
  {
    return $this->execute_statement(
      $con,
      "INSERT INTO `Message` (sender_user_id, receiver_user_id, `message`) VALUES (?, ?, ?)",
      "sss",
      [$sender_id, $receiver_id, $message]
    );
  }

  public function insert_flight_for_company(
    mysqli $con,
    string $user_id,
    string $name,
    int $max_passengers,
    float $price
  ) {
    return $this->execute_statement(
      $con,
      "INSERT INTO Flight (`company_user_id`, `name`, max_passengers, price) VALUES (?, ?, ?, ?)",
      "ssid",
      [$user_id, $name, $max_passengers, $price]
    );
  }

  public function insert_flight_city_for_flight(
    mysqli $con,
    string $flight_id,
    string $city_name,
    DateTime $date_in_city
  ) {
    return $this->execute_statement(
      $con,
      "INSERT INTO FlightCity (flight_id, `name`, date_in_city) VALUES (?, ?, ?)",
      "sss",
      [$flight_id, $city_name, $date_in_city]
    );
  }

  public function delete_flight(
    mysqli $con,
    string $user_id,
    string $flight_id
  ) {
    return $this->execute_statement(
      $con,
      "DELETE FROM Flight WHERE Flight.id = ? AND Flight.company_user_id = ?",
      "ss",
      [$flight_id, $user_id]
    );
  }

  public function select_messages_for_user(mysqli $con, string $user_id, string $first_party, string $second_party)
  {
    $rows = $this->select_all(
      $con,
      "SELECT Message.id, Message.message, User.name
      FROM `Message`
      LEFT JOIN User ON `Message`." . $second_party . "_user_id = User.id
      WHERE Message." . $first_party . "_user_id = ?",
      "s",
      [$user_id]
    );
    $res = array();
    foreach ($rows as $row) {
      $res[] = new Message($row[0], $row[1], $row[2]);
    }
    return $res;
  }

  public function select_received_messages_for_user(mysqli $con, string $user_id)
  {
    return $this->select_messages_for_user($con, $user_id, "receiver", "sender");
  }

  public function select_sent_messages_for_user(mysqli $con, string $user_id)
  {
    return $this->select_messages_for_user($con, $user_id, "sender", "receiver");
  }
}
