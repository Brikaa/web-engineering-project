<?php
require_once 'template.php';

$home_view = function () use (&$with_template) {
  $html = <<<HTML
  <div class="home">
    <div class="hero">
      <h1>Fly wherever, whenever</h1>
      <div class="buttons">
        <a href="?action=login" class="button primary">Login</a>
        <a href="?action=register" class="button secondary">Register</a>
      </div>
    </div>
  </div>
  HTML;
  $with_template($html);
};
