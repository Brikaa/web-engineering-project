<?php

declare(strict_types=1);
require_once 'primary_template.php';

function generate_send_message_link(string $id, string $name)
{
  return "<a href='/?action=send_message&receiver_id=$id'>$name</a>";
}

$messages_view = function (mysqli $con, DbController $c, UserContext $ctx) use ($with_primary_template) {
  $messages = $c->get_messages($con, $ctx);
  $messages_html = "";
  $no_messages = count($messages);
  if ($no_messages === 0)
    $messages_html = "You do not have any messages yet.";
  for ($i = 0; $i < $no_messages; ++$i) {
    // I am receiver
    $message = $messages[$i];
    $additional_class = "";
    $photo_url = $message->sender_photo_url;
    $name = generate_send_message_link($message->sender_user_id, $message->sender_name);
    // I am sender
    if ($message->sender_user_id === $ctx->id) {
      $additional_class = "sender";
      $photo_url = $ctx->photo_url;
      $name = "You -> " . generate_send_message_link($message->receiver_user_id, $message->receiver_name);
    }
    $additional_id = $i == $no_messages - 1 ? "last" : "";
    $messages_html .= <<<HTML
    <div class="message $additional_class" id="$additional_id">
      <div>
        <div class="profile-icon" style="background-image: url('$photo_url')"></div>
      </div>
      <div class="message-body">
        <div class="message-header"><h2>$name</h2></div>
        <div class="message-content"><p>$message->message</p></div>
      </div>
    </div>
    HTML;
  }
  $with_primary_template(
    "Messages",
    <<<HTML
    <div class="messages">
      $messages_html
    </div>
    HTML,
    "<link rel='stylesheet' href='/assets/css/message.css'>"
  );
};
