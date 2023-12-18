<?php
require_once 'landing_template.php';

$home_view = function () use (&$with_landing_template) {
  $html = <<<HTML
  <div class="landing-content">
    <h1>Fly anywhere, anytime</h1>
    <div class="buttons">
      <a href="?action=login" class="button primary">Login</a>
      <a href="?action=register" class="button secondary">Register</a>
    </div>
  </div>
  HTML;
  $with_landing_template($html);
};
