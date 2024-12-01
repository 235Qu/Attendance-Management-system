<?php
class Forgot extends Core {
  // (A) SETTINGS
  private $valid = 900; // request valid for 15 minutes
  private $plen = 10; // random password will be 10 characters
  private $hlen = 24; // hash will be 24 characters

  // (B) PASSWORD RESET REQUEST
  function request($email) {
    // (B1) ALREADY SIGNED IN
    if (isset($_SESSION["user"])) {
      $this->error = "You are already signed in.";
      return false;
    }

    // (B2) CHECK IF VALID USER
    $this->Core->load("Users");
    $user = $this->Users->get($email, "A");
    if (!is_array($user)) {
      $this->error = "The email address is not registered.";
      return false;
    }
    if (isset($user["hash_code"])) {
      $this->error = "The email address is associated with an inactive account.";
      return false;
    }
    if ($user["user_level"] == "S") {
      $this->error = "The email address is associated with an inactive account.";
      return false;
    }

    // (B3) CHECK PREVIOUS REQUEST (PREVENT SPAM)
    $req = $this->Users->hashGet($user["user_id"], "P");
    if (is_array($req)) {
      $expire = strtotime($req["hash_time"]) + $this->valid;
      $now = time();
      $left = $expire - $now;
      if ($left > 0) {
        $this->error = "Please wait another " . $left . " seconds before requesting a new password reset.";
        return false;
      }
    }

    // (B4) CREATE NEW RESET REQUEST
    $hash = $this->Core->random($this->hlen);
    $this->Users->hashAdd($user["user_id"], "P", $hash);

    // (B5) SEND EMAIL TO USER
    $this->Core->load("Mail");
    $emailSent = $this->Mail->send([
      "to" => $user["user_email"],
      "subject" => "Password Reset",
      "template" => PATH_PAGES . "MAIL-forgot-a.php",
      "vars" => [
        "link" => HOST_BASE . "forgot?i={$user["user_id"]}&h={$hash}"
      ]
    ]);

    if (!$emailSent) {
      $this->error = "Failed to send password reset email. Please try again later.";
      return false;
    }

    return true;
  }

  // (C) PROCESS PASSWORD RESET
  function reset($id, $hash) {
    // (C1) ALREADY SIGNED IN
    if (isset($_SESSION["user"])) {
      $this->error = "You are already signed in.";
      return false;
    }

    // (C2) CHECK REQUEST
    $this->Core->load("Users");
    $req = $this->Users->hashGet($id, "P");
    if (!is_array($req)) {
      $this->error = "Invalid password reset request.";
      return false;
    }

    // (C3) CHECK EXPIRATION TIME
    $expire = strtotime($req["hash_time"]) + $this->valid;
    $now = time();
    if ($now > $expire) {
      $this->error = "The password reset link has expired. Please request a new one.";
      return false;
    }

    // (C4) CHECK HASH
    if ($hash !== $req["hash_code"]) {
      $this->error = "Invalid password reset request.";
      return false;
    }

    // (C5) RESET PASSWORD
    $password = $this->Core->random($this->plen);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $success = $this->DB->update("users", ["user_password"], "`user_id`=?", [$hashedPassword, $id]);
    if (!$success) {
      $this->error = "Failed to reset the password. Please try again later.";
      return false;
    }

    // (C6) DELETE REQUEST
    $this->Users->hashDel($id, "P");

    // (C7) SEND EMAIL TO USER
    $user = $this->Users->get($id);
    $this->Core->load("Mail");
    $emailSent = $this->Mail->send([
      "to" => $user["user_email"],
      "subject" => "Password Reset Successful",
      "template" => PATH_PAGES . "MAIL-forgot-b.php",
      "vars" => [
        "password" => $password
      ]
    ]);

    if (!$emailSent) {
      $this->error = "Failed to send the password reset confirmation email. Please contact support.";
      return false;
    }

    return true;
  }
}

