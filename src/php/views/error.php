<?php
require_once 'template.php';

$error_view = function (string $message) use ($with_template) {
  $with_template(
    <<<HTML
    <div class="header">
      <h1>Woops, something's not right! 😬</h1>
    </div>
    <p>$message</p>
    HTML,
    "<link rel='stylesheet' href='/assets/css/secondary.css'>"
  );
};
