<?
require_once 'template.php';

$success_view = function (string $message, string $next_action) use ($with_template) {
  $with_template(<<<HTML
  <p>$message</p>
  <meta http-equiv="refresh" content="2; url='/?action=$next_action'" />
  HTML);
};
