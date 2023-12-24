<?php

declare(strict_types=1);
require_once __DIR__ . '/../model.php';

function format_date(DateTime $date): string
{
  return $date->format("jS F Y");
}
