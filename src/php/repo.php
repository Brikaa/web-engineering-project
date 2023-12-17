<?php
declare(strict_types=1);
require_once 'model.php';

function with_prepared_statement($con, string $sql, $fn) {
  $stmt = $con->prepare($sql);
  $res = $fn(stmt);
  $s->close();
  return $res;
}

function execute_statement($con, string $sql, string $binding, array $params) {
  return with_prepared_statement($con, $sql, function($s) {
    $s->bind_param($binding, ...$params);
    return $s->execute();
  });
}

function select_one($con, string $sql, string $binding, array $params) {
  return with_prepared_statement($con, $sql, function($s) {
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

$insert_user = function(
  $con, InsertUserRequest $user
) {
  return execute_statement(
    $con,
    "INSERT INTO User(`email`, `name`, `password`, `telephone`, `photo_url`, `money`) values(?, ?, ?, ?, ?, ?)",
    "sssssd",
    [$user->email, $user->name, $user->password, $user->telephone, $user->photo_url, $user->money]
  );
};

$update_user_by_id = function(
  $con,
  string $id,
  InsertUserRequest $user,
  float $money
) {
  return execute_statement(
    $con,
    "UPDATE User SET `email`=?, `name`=?, `password`=?, `telephone`=?, `photo_url`=?, `money`=? WHERE id=?",
    "sssssds",
    [$user->email, $user->name, $user->password, $user->telephone, $user->photo_url, $user->money, $user_id]
  );
};

function select_user_by_condition($con, string $condition, string $binding, array $params): UserContext {
  $user = select_one(
    $con,
    "SELECT User.id, User.name, Passenger.id, Company.id FROM User WHERE $condition
    LEFT JOIN Company ON Company.user_id = User.user_id
    LEFT JOIN Passenger ON Passenger.user_id = User.user_id",
    $binding,
    $params
  );
  if ($user) {
    $role;
    if ($user[2] == null && user[3] == null) {
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

$select_user_by_id = function($con, string $id): UserContext {
  return select_user_by_condition($con, "User.id=?", "s", [$id]);
};

$select_user_by_name_or_email = function($con, string $name, string $email): UserContext {
  return select_user_by_condition($con, "User.name=? OR User.email=?", "ss", [$id, $email]);
};

$select_user_by_email_and_password = function($con, string $email, string $password): UserContext {
  return select_user_by_condition($con, "User.email = ? AND User.password = ?", "ss", [$email, $password]);
};

?>
