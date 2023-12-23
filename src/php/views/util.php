<?php

declare(strict_types=1);
require_once __DIR__ . '/../model.php';

function get_profile_image_url(UserContext $user)
{
  return $user->photo_url === "" ? "/assets/images/user.svg" : $user->photo_url;
}
