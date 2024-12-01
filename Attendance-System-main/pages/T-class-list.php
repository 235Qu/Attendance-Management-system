<?php
// (A) GET CLASSES FOR TEACHER
$_POST["uid"] = $_SESSION["user"]["user_id"];
$classes = $_CORE->autoCall("Classes", "getByTeacher");

// (B) DRAW CLASSES LIST
if (is_array($classes)) { foreach ($classes as $id=>$c) { ?>
<div class="d-flex align-items-center border p-2">
  <div class="flex-grow-1">
    <strong><?=$c["cd"]?></strong><br>
    [<?=$c["course_code"]?>] <?=$c["course_name"]?><br>
    <small><?=$c["class_desc"]?></small>
  </div>
  <div class="dropdown">
    <button type="button" class="btn btn-primary p-3 ico-sm icon-arrow-right" type="button" data-bs-toggle="dropdown"></button>
    <ul class="dropdown-menu dropdown-menu-dark">
      <li class="dropdown-item" onclick="attend.show(<?=$id?>)">
        <i class="text-secondary ico-sm icon-users"></i> Attendance
      </li>
      <li><a class="dropdown-item" target="_blank" href="<?=HOST_BASE?>TA/classqr?c=<?=$id?>">
        <i class="text-secondary ico-sm icon-qrcode"></i> QR Code
      </a></li>
    </ul>
  </div>
</div>
<?php }} else { ?>
<div class="d-flex align-items-center border p-2">No classes found.</div>
<?php }

// (C) PAGINATION
$_CORE->load("Page");
$_CORE->Page->draw("classes.goToPage");