<?php

declare(strict_types=1);

require_once "model.php";
require_once "repo.php";

const USER_ID = "user_id";

$with_auth = function (mysqli $con, string $role, $fn) {
  $ctx = "something"; // TODO: generate context from permission
  $fn($con, $ctx);
};

class DbController
{
  private mysqli $con;
  private Repo $repo;
  private array $session;

  function __construct(Repo $repo, array &$session)
  {
    $this->con = mysqli_connect("wep-db", "user", "user123", "app");
    $this->repo = $repo;
    $this->session = &$session;
  }

  function __destruct()
  {
    $this->con->close();
  }

  public function signup(
    string $email,
    string $name,
    string $password,
    string $telephone
  ) {
    if ($this->repo->select_user_by_name_or_email($this->con, $name, $email) != null) {
      throw new Exception("A user with this name or email already exists");
    }
    $req = new InsertUserRequest($email, $name, $password, $telephone, "", 0.0);
    $this->repo->insert_user($this->con, $req);
  }

  public function login(
    string $email,
    string $password
  ) {
    $user = $this->repo->select_user_by_email_and_password($this->con, $email, $password);
    if ($user == null)
      throw new Exception("Invalid email or password");
    $this->session[USER_ID] = $user->id;
  }

  public function get_logged_in_user_context()
  {
    $user_id = $this->session[USER_ID];
    return $this->repo->select_user_by_id($this->con, $user_id);
  }

  public function update_user(
    UserContext $ctx,
    InsertUserRequest $req
  ) {
    $this->repo->update_user_by_id($this->con, $ctx->id, $req);
  }

  public function update_passenger(
    UserContext $ctx,
    string $passport_image_url
  ) {
    $this->repo->update_passenger_by_user_id($this->con, $ctx->id, $passport_image_url);
  }

  public function update_company(
    UserContext $ctx,
    string $bio,
    string $address
  ) {
    $this->repo->update_company_by_user_id($this->con, $ctx->id, $bio, $address);
  }
}

$create_db_controller_constructor = function (Repo $repo, array &$session): Closure {
  return function () use ($repo, &$session): DbController {
    return new DbController($repo, $session);
  };
};
