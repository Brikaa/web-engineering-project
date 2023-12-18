<?php
require_once('landing_template.php');

$register_view = function () use (&$with_landing_template) {
  $with_landing_template(<<<HTML
  <div class="landing-content">
    <h1>Let's get started ✈️</h1>
      <form action="/" method="POST">
        <input class="input" type="text" name="action" value="perform_register" hidden>
        <input class="input" type="email" name="email" placeholder="Email" required>
        <input class="input" type="text" name="name" placeholder="Name" required>
        <input class="input" type="password" name="password" placeholder="Password" required>
        <input class="input" type="tel" name="telephone" placeholder="Telephone" required>
        <input type="submit" class="button secondary" value="Register">
      </form>
    </div>
  </div>
  HTML);
}
?>
