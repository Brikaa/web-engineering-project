<?php

$with_template = function (string $html) {
  echo <<<HTML
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/style.css">
    <title>Flight booking system</title>
    </head>
    <body>
      $html
    </body>
    </html>
  HTML;
};
