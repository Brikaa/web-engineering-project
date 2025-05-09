<?php
require_once 'primary_template.php';

$deposit_view = function () use ($with_primary_template) {
  $with_primary_template(
    "Deposit money",
    <<<HTML
    <form action="/" method="post">
      <input type="text" name="action" value="handle_deposit" hidden>
      <input class="input" type="number" name="amount" placeholder="Amount" step="0.01" min="0.01">
      <input type="submit" class="button primary" value="Deposit">
    </form>
    HTML
  );
};
