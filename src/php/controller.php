<?php

declare(strict_types=1);

require_once "model.php";
require_once "repo.php";

const USER_ID = "user_id";

class DbController
{
  private Repo $repo;
  private array $session;

  function __construct(Repo $repo, array &$session)
  {
    $this->repo = $repo;
    $this->session = &$session;
  }

  public function signup(
    mysqli $con,
    string $email,
    string $name,
    string $password,
    string $telephone
  ) {
    if ($this->repo->select_user_by_name_or_email($con, $name, $email) != null) {
      throw new Exception("A user with this name or email already exists");
    }
    $req = new InsertUserRequest($email, $name, $password, $telephone, "", 0.0);
    $this->repo->insert_user($con, $req);
  }

  public function login(
    mysqli $con,
    string $email,
    string $password
  ) {
    $user = $this->repo->select_user_by_email_and_password($con, $email, $password);
    if ($user == null)
      throw new Exception("Invalid email or password");
    $this->session[USER_ID] = $user->id;
  }

  public function get_logged_in_user_context(mysqli $con)
  {
    $user_id = $this->session[USER_ID];
    return $this->repo->select_user_by_id($con, $user_id);
  }

  public function update_user(
    mysqli $con,
    UserContext $ctx,
    InsertUserRequest $req
  ) {
    $this->repo->update_user_by_id($con, $ctx->id, $req);
  }

  public function update_passenger(
    mysqli $con,
    UserContext $ctx,
    string $passport_image_url
  ) {
    $this->repo->update_passenger_by_user_id($con, $ctx->id, $passport_image_url);
  }

  public function update_company(
    mysqli $con,
    UserContext $ctx,
    string $bio,
    string $address
  ) {
    $this->repo->update_company_by_user_id($con, $ctx->id, $bio, $address);
  }
}
