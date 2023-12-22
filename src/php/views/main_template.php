<?php
require_once 'secondary_template.php';

$with_main_template = function (
  string $title,
  string $left_html,
  string $right_html,
  string $additional_head = ""
) use ($with_secondary_template) {
  $with_secondary_template(
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
    <link rel="stylesheet" href="/assets/css/main.css">
    $additional_head
    HTML
  );
};
