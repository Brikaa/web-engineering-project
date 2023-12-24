<?php

declare(strict_types=1);
require_once 'primary_template.php';

function create_submit(string $action, string $id, string $button_class, string $button_text): string
{
  return <<<HTML
  <form action="/" method="POST">
    <input type="text" name="action" value="$action" hidden>
    <input type="text" name="id" value="$id" hidden>
    <input type="submit" class="button $button_class" value="$button_text">
  </form>
  HTML;
}

$flight_view = function (mysqli $con, DbController $c, UserContext $ctx) use ($with_primary_template) {
  if (!array_key_exists("id", $_GET)) {
    throw new Error("Flight id was not provided");
  }
  $flight = $c->get_flight_details($con, $_GET["id"]);
  $cities = "";
  foreach ($flight->cities as $city) {
    $date = format_date($city->date_in_city);
    $cities .= "<li>$city->name - $date</li>";
  }
  $actions_html = "";
  $flight_reservation_id = $c->get_flight_reservation_id_for_flight($con, $ctx, $flight->id);
  if (
    $ctx->role === PASSENGER_ROLE &&
    $flight->max_passengers > $flight->registered_passengers
    && $flight_reservation_id == NULL
  ) {
    $actions_html = create_submit("handle_book_flight_cash", $flight->id, "secondary", "Book cash");
    if ($ctx->money > $flight->price) {
      $actions_html .= create_submit("handle_book_flight_credit", $flight->id, "primary", "Book credit");
    }
  } else if ($ctx->role === COMPANY_ROLE) {
    $actions_html = create_submit("handle_cancel_flight", $flight->id, "danger", "Cancel flight");
  } else if ($flight_reservation_id != NULL) {
    $actions_html = create_submit("handle_cancel_reservation", $flight_reservation_id, "danger", "Cancel reservation");
  }
  $with_primary_template(
    "Flight " . $flight->name,
    <<<HTML
    <div class="flight-info">
      <h1>Price</h1>
      <p>$flight->price$</p>
      <h1>Company</h1>
      <a href="/?action=send_message&receiver_id=$flight->company_user_id">
        <p>$flight->company_name</p>
      </a>
      <h1>Capacity</h1>
      <p>$flight->registered_passengers / $flight->max_passengers</p>
      <h1>Cities</h1>
      <ol>
        $cities
      </ol>
      <div class="buttons">
        $actions_html
      </div>
    </div>
    HTML,
    "<script src='/assets/js/confirm_before_submitting.js' defer></script>"
  );
};
