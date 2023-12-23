<?php

declare(strict_types=1);
require_once 'template.php';

$with_secondary_template = function (string $header, string $content, string $additional_head = "") use ($with_template) {
  $with_template(
    <<<HTML
    <div class="header">
      <a href="/">
        <h1>$header</h1>
      </a>
    </div>
    <div class="hero">
      $content
    </div>
    HTML,
    <<<HTML
    <link rel="stylesheet" href="/assets/css/secondary.css">
    $additional_head
    HTML
  );
};
