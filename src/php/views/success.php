<?php
require_once 'secondary_template.php';

$success_view = function (string $message, string $next_action) use ($with_secondary_template) {
  $with_secondary_template($message, <<<HTML
  <meta http-equiv="refresh" content="1; url='/?action=$next_action'" />
  HTML);
};
