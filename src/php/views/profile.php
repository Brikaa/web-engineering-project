<?php
require_once __DIR__ . '/../model.php';
require_once 'primary_template.php';
require_once 'util.php';

$profile_view = function (mysqli $con, DbController $c, UserContext $ctx) use ($with_primary_template) {
  $additional_html = "";
  if ($ctx->role === PASSENGER_ROLE) {
    $passenger = $c->get_user_passenger($con, $ctx);
    $additional_html = <<<HTML
    <label for="passport-image">
      <div class="avatar-icon" style="background-image: url('$passenger->passport_image_url')"></div>
    </label>
    <label for="passport-image">Passport image</label>
    <input class="input" type="file" name="passport_image" id="passport-image" accept=".png,.jpg,.jpeg">
    HTML;
  } else {
    $company = $c->get_user_company($con, $ctx);
    $additional_html = <<<HTML
    <textarea class="input" name="bio" placeholder="Bio" required>$company->bio</textarea>
    <input class="input" type="text" name="address" placeholder="Address" value="$company->address" required>
    HTML;
  }
  $form_action = $ctx->role === PASSENGER_ROLE ? "handle_update_passenger" : "handle_update_company";
  $image_url = get_profile_image_url($ctx);
  $with_primary_template(
    "Profile",
    <<<HTML
    <form class="profile" method="POST" action="/" enctype="multipart/form-data">
      <input type="text" name="action" value="$form_action" hidden>
      <label for="photo">
        <div class="avatar-icon" style="background-image: url('$image_url')"></div>
      </label>
      <input class="input" type="file" name="photo" id="photo" accept=".png,.jpg,.jpeg">
      <input class="input" type="email" name="email" placeholder="Email" value="$ctx->email" required>
      <input class="input" type="text" name="name" placeholder="Name" value="$ctx->name" required>
      <input class="input" type="password" name="password" placeholder="Password" required>
      <input class="input" type="tel" name="telephone" placeholder="Telephone" value="$ctx->telephone" required>
      $additional_html
      <input class="button primary" type="submit" value="Update profile" required>
    </form>
    HTML
  );
};
