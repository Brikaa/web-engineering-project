<?php
require_once 'template.php';
require_once 'landing_template.php';
require_once '../model.php';

$home_view = function (mysqli $con, DbController $c) use (&$with_landing_template, $with_template) {
  $user = $c->get_logged_in_user($con);
  if ($user && $user->role != NULL) {
  } else if ($user) {
    $with_template(
      <<<HTML
      <div class="header">
        <h1>Let's finish your profile</h1>
      </div>
      <div class="hero">
        <h1>I'm a</h1>
        <div class="choices">
          <a id="passenger-choice">Passenger</a>
          <a id="company-choice">Company</a>
        </div>
        <form id="passenger-form" action="/" method="POST">
          <input class="input" type="text" name="action" value="handle_register_passenger" hidden>
          <input class="input" type="file" name="passport_image" required>
          <input type="submit" class="button secondary" value="Register as a passenger">
        </form>
        <form id="company-form" action="/" method="POST">
          <input class="input" type="text" name="action" value="handle_register_company" hidden>
          <textarea class="input" name="bio" placeholder="Bio" required>
          <input class="input" type="text" name="address" placeholder="Address" required>
          <input type="submit" class="button secondary" value="Register as a company">
        </form>
      </div>
      HTML,
      <<<HTML
      <script src='/assets/js/account_type.js'></script>
      <script src='/assets/css/account_type.css'></script>
      HTML
    );
  } else {
    $with_landing_template(<<<HTML
    <div class="landing-content">
      <h1>Fly anywhere, anytime</h1>
      <div class="buttons">
        <a href="?action=login" class="button primary">Login</a>
        <a href="?action=register" class="button secondary">Register</a>
      </div>
    </div>
    HTML);
  }
};
