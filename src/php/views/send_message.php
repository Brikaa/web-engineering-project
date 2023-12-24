<?php

declare(strict_types=1);
require_once 'primary_template.php';

$send_message_view = function () use ($with_primary_template) {
  if (!array_key_exists("receiver_id", $_GET))
    throw new Error("A receiver id was not provided");
  $receiver_id = $_GET["receiver_id"];
  $with_primary_template(
    "Send message",
    <<<HTML
    <form action="/" method="post">
      <input type="text" name="action" value="handle_send_message" hidden>
      <input type="text" name="receiver_id" value="$receiver_id" hidden>
      <textarea class="input" name="message" id="" cols="30" rows="10" placeholder="Message"></textarea>
      <input class="button primary" type="submit" value="Send">
    </form>
    HTML
  );
};
