<?php

declare(strict_types=1);
require_once 'secondary_template.php';
require_once 'landing_template.php';
require_once 'main_template.php';
require_once __DIR__ . '/../model.php';

$generate_flight_cards_from_flights = function (array $flights) {
  $res = "";
  foreach ($flights as $flight) {
    $date = $flight->source->date_in_city->format("jS F Y");
    $source = $flight->source->name;
    $destination = $flight->destination->name;
    $res .= <<<HTML
    <a href="?action=flight&id=$flight->id">
      <div class="flight-card">
        <h2>$source to $destination</h2>
        <p>$date - $flight->company_name - $flight->price$</p>
      </div>
    </a>
    HTML;
  }
  if ($res == "") {
    return "<p>None</p>";
  }
  return $res;
};

$home_view = function (
  mysqli $con,
  DbController $c
) use ($with_landing_template, $with_main_template, $with_secondary_template, $generate_flight_cards_from_flights) {
  $user = $c->get_logged_in_user($con);
  if ($user && $user->role != NONE_ROLE) {
    $html = "";
    $profile_image_url = $user->photo_url === "" ? "/assets/images/avatar.png" : $user->photo_url;
    if ($user->role == PASSENGER_ROLE) {
      $upcoming_flights = $generate_flight_cards_from_flights($c->get_upcoming_flights($con, $user));
      $available_flights = $generate_flight_cards_from_flights($c->get_available_flights($con, $user));
      $completed_flights = $generate_flight_cards_from_flights($c->get_completed_flights($con, $user));
      $html = <<<HTML
        <div class="section">
          <h1>Upcoming flights</h1>
          <div class="flights">$upcoming_flights</div>
        </div>
        <div class="section">
          <h1>Available flights</h1>
          <form action="/" method="POST">
            <input class="input" type="text" name="from" placeholder="From" required>
            <input class="input" type="text" name="to" placeholder="To" required>
            <input class="button secondary" type="submit" value="Go">
          </form>
          <div class="flights">$available_flights</div>
        </div>
        <div class="section">
          <h1>Completed flights</h1>
          <div class="flights">$completed_flights</div>
        </div>
      HTML;
    } else {
      $company_flights = $generate_flight_cards_from_flights($c->list_company_flights($con, $user));
      $html = <<<HTML
      <div class="section">
        <h1>Company flights</h1>
        <div class="flights">$company_flights</div>
      </div>
      HTML;
    }
    $with_main_template(
      "Home",
      $html,
      <<<HTML
      <div class="profile-area">
        <a class="user-info" href="/?action=profile">
          <div class="avatar-icon" style="background-image: url('$profile_image_url')"></div>
          <h2>$user->name</h2>
        </a>
        <div class="actions">
          <a href="/?action=messages"><img src="/assets/images/envelope.svg" /></a>
          <a href="/?action=handle_logout"><img src="/assets/images/sign-out.svg" /></a>
        </div>
      </div>
      HTML
    );
  } else if ($user) {
    $with_secondary_template(
      "Let's finish your profile ✨",
      <<<HTML
        <div>
          <h1>I'm a...</h1>
          <div class="choices">
            <h2 id="passenger-choice">Passenger</h2>
            <h2 id="company-choice">Company</h2>
          </div>
        </div>
        <form id="passenger-form" action="/" method="POST" enctype="multipart/form-data">
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
      HTML,
      <<<HTML
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
