<?php
require_once('landing_template.php');

$login_view = function () use (&$with_landing_template) {
  $with_landing_template(<<<HTML
  <div class="landing-content">
    <h1>Welcome back ✈️</h1>
      <form action="/" method="POST">
        <input class="input" type="text" name="action" value="perform_login" hidden>
        <input class="input" type="email" name="email" placeholder="Email" required>
        <input class="input" type="password" name="password" placeholder="Password" required>
        <input type="submit" class="button primary" value="Login">
      </form>
    </div>
  </div>
  HTML);
}
?>
