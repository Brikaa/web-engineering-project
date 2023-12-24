<?php

declare(strict_types=1);
require_once 'primary_template.php';

$add_flight_view = function () use ($with_primary_template) {
  if (array_key_exists("no_cities", $_GET)) {
    $no_cities = intval($_GET["no_cities"]);
    $cities = "";
    for ($i = 0; $i < $no_cities; ++$i) {
      $cities .= <<<HTML
      <div class="space-between">
        <input class="input" type="text" name="city_names[]" placeholder="City name">
        <input class="input" type="datetime-local" name="city_dates[]" placeholder="Date in city">
      </div>
      HTML;
    }
    $with_primary_template(
      "Add flight",
      <<<HTML
      <form action="/" method="post">
        <input class="input" type="text" name="action" value="handle_add_flight" hidden>
        <input class="input" type="text" name="name" placeholder="Name">
        <input class="input" type="number" name="max_passengers" placeholder="Capacity" min="1">
        <input class="input" type="number" name="price" step="0.01" min="0" placeholder="Price">
        $cities
        <input class="button primary" type="submit" value="Add flight">
      </form>
      HTML
    );
  } else {
    $with_primary_template(
      "Number of cities",
      <<<HTML
      <form action="/" method="get">
      <input class="input" type="text" name="action" value="add_flight" hidden>
        <input class="input" type="number" name="no_cities" placeholder="Number of cities" min="2">
        <input type="submit" class="button primary" value="Initialize flight">
      </form>
      HTML
    );
  }
};
