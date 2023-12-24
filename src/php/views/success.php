<?php
require_once 'primary_template.php';

$success_view = function (string $message, string $next_action) use ($with_primary_template) {
  $with_primary_template($message, <<<HTML
  <meta http-equiv="refresh" content="1; url='/?action=$next_action'" />
  HTML);
};
