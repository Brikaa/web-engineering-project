<?php
require_once 'template.php';
require_once 'landing_template.php';
require_once __DIR__ . '/../model.php';

$home_view = function (mysqli $con, DbController $c) use (&$with_landing_template, $with_template) {
  $user = $c->get_logged_in_user($con);
  if ($user && $user->role != NONE_ROLE) {
    echo $user->role;
  } else if ($user) {
    $with_template(
      <<<HTML
      <div class="header">
        <h1>Let's finish your profile ✨</h1>
      </div>
      <div class="hero">
        <div>
          <h1>I'm a...</h1>
          <div class="choices">
            <h2 id="passenger-choice">Passenger</h2>
            <h2 id="company-choice">Company</h2>
          </div>
        </div>
        <form id="passenger-form" action="/" method="POST">
          <input class="input" type="text" name="action" value="handle_register_passenger" hidden>
          <label for="passport-image">Passport image</label>
          <input class="input" type="file" name="passport_image" id="passport-image" accept=".png,.jpg,.jpeg" required>
          <input type="submit" class="button secondary" value="Register as a passenger">
        </form>
        <form id="company-form" action="/" method="POST">
          <input class="input" type="text" name="action" value="handle_register_company" hidden>
          <textarea class="input" name="bio" placeholder="Bio" required></textarea>
          <input class="input" type="text" name="address" placeholder="Address" required>
          <input type="submit" class="button secondary" value="Register as a company">
        </form>
      </div>
      HTML,
      <<<HTML
      <link rel="stylesheet" href='/assets/css/secondary.css'>
      <script src='/assets/js/account_type.js' defer></script>
      <script src='/assets/css/account_type.css'></script>
      HTML
    );
  } else {
    $with_landing_template(<<<HTML
    <div class="landing-content">
      <h1>Fly anywhere, anytime</h1>
      <div class="buttons">
        <a href="?action=login" class="button primary">Login</a>
        <a href="?action=signup" class="button secondary">Sign up</a>
      </div>
    </div>
    HTML);
  }
};
