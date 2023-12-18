<?php
$not_found_view = function() {
  http_response_code(404);
  echo "The page you are looking for was not found";
};
?>
