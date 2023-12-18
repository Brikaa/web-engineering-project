<?php
require_once 'template.php';

$with_landing_template = function (string $html) use (&$with_template) {
  $with_template(<<<HTML
  <div class="home">
    $html
  </div>
  HTML);
};
?>
