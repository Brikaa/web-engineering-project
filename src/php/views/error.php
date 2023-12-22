<?php
require_once 'secondary_template.php';

$error_view = function (string $message) use ($with_secondary_template) {
  $with_secondary_template(
    "Woops, something's not right! 😬",
    "<p>$message</p>",
    <<<HTML
    <link rel="stylesheet" href="/assets/css/error.css">
    HTML
  );
};
