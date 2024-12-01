<?php
// (A) ALREADY SIGNED IN
if (isset($_SESSION["user"])) { $_CORE->redirect(); }

// (B) PAGE META & SCRIPTS
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."PAGE-wa-helper.js", "defer"],
  ["s", HOST_ASSETS."PAGE-login-wa.js", "defer"],
  
  ["s", HOST_ASSETS."PAGE-login.js", "defer"]
]];

// (C) HTML PAGE
require PATH_PAGES . "TEMPLATE-top.php";
?>
<?php if ($_CORE->error!="") { ?>
<!-- (C1) ERROR MESSAGE -->
<div class="p-2 mb-3 text-light bg-danger"><?=$_CORE->error?></div>  
<?php } ?>

<!-- (C2) LOGIN FORM -->
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-10 bg-white border">
      <div class="row">
        <div class="col-4" style="background:url('<?=HOST_ASSETS?>campus2.jpg') center;background-size:cover"></div>
        <div class="col-8 p-4">
          <h1 class="my-4">Student Attendance Management System</h1> <!-- Updated heading -->

          <form onsubmit="return login();">
            <!-- (C2-1) ROLE SELECTION -->
            <div class="form-floating mb-4">
              <select id="login-role" class="form-select" required>
                <option value="" disabled selected>Select Role</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
                <option value="admin">Admin</option>
              </select>
              <label for="login-role">Role</label>
            </div>

            <!-- (C2-2) EMAIL AND PASSWORD -->
            <div class="form-floating mb-4">
              <input type="email" id="login-email" class="form-control" required>
              <label for="login-email">Email</label>
            </div>

            <div class="form-floating mb-4">
              <input type="password" id="login-pass" class="form-control" required>
              <label for="login-pass">Password</label>
            </div>

            <!-- (C2-3) SUBMIT BUTTON -->
            <button type="submit" class="my-1 btn btn-primary d-flex-inline">
              <i class="ico-sm icon-enter"></i> Sign In
            </button>
          </form>

          <!-- (C2-4) FORGOT & NEW ACCOUNT -->
          <div class="text-secondary mt-3">
            <a href="<?=HOST_BASE?>forgot">Forgot Password</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>

