<?php
function with_db($fn) {
  $con = mysqli_connect("wep-db", "user", "user123", "app");
  return $fn($con);
}

?>
