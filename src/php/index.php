<?php
declare(strict_types=1);

function with_db($fn) {
  return function() {
    $con = mysqli_connect("wep-db", "user", "user123", "app");
    $fn($con);
  };
}

function with_auth($con, string $role, $fn) {
  return function() {
    $ctx = "something"; // TODO: generate context from permission
    $fn($con, $ctx);
  };
}
?>
