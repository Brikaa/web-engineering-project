<?php

$with_template = function (string $html, string $additional_head="") {
  echo <<<HTML
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/global.css">
    $additional_head
    <title>Flight booking system</title>
  </head>
  <body>
    $html
  </body>
  </html>
  HTML;
};
