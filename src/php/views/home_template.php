<?php
require_once 'primary_template.php';

$with_home_template = function (
  string $title,
  string $left_html,
  string $right_html,
  string $additional_head = ""
) use ($with_primary_template) {
  $with_primary_template(
    $title,
    <<<HTML
    <div class="main">
      <div class="side">
        $left_html
      </div>
      <div class="side">
        $right_html
      </div>
    </div>
    HTML,
    <<<HTML
    <link rel="stylesheet" href="/assets/css/home.css">
    $additional_head
    HTML
  );
};
