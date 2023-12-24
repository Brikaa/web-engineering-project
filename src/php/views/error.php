<?php
require_once 'primary_template.php';

$error_view = function (string $message) use ($with_primary_template) {
  $with_primary_template(
    "Woops, something's not right! 😬",
    "<p>$message</p>",
    <<<HTML
    <link rel="stylesheet" href="/assets/css/error.css">
    HTML
  );
};
