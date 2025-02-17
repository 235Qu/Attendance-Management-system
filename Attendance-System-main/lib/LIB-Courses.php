<?php


class Courses extends Core {
  // (A) ADD OR UPDATE COURSE
  //  $code : course code
  //  $name : course name
  //  $start : start date
  //  $end : end date
  //  $desc : course description
  //  $ocode : old course code (edit only)
  function save ($code, $name, $start, $end, $desc=null, $ocode=null) {
    // (A1) DATA SETUP
    if (strtotime($end) < strtotime($start)) {
      $this->error = "End date cannot be earlier than start";
      return false;
    }
    $fields = ["course_code", "course_name", "course_start", "course_end", "course_desc"];
    $data = [$code, $name, $start, $end, $desc];

    // (A2) ADD/UPDATE COURSE
    if ($ocode==null) {
      $this->DB->insert("courses", $fields, $data);
    } else {
      $data[] = $ocode;
      $this->DB->update("courses", $fields, "`course_code`=?", $data);
    }
    return true;
  }

  // (B) IMPORT COURSE (OVERRIDES OLD ENTRY)
  //  $code : course code
  //  $name : course name
  //  $start : start date
  //  $end : end date
  //  $desc : course description
  function import ($code, $name, $start, $end, $desc=null) {
    // (B1) GET COURSE
    $course = $this->get($code);

    // (B2) UPDATE OR INSERT
    $this->save($code, $name, $start, $end, $desc, is_array($course)?$course["course_code"]:null);
    return true;
  }

  // (C) DELETE COURSE
  //  $code : course code
  function del ($code) {
    $this->DB->start();
    $this->DB->query("DELETE `attendance` FROM `attendance` LEFT JOIN `classes` USING (`class_id`) WHERE `course_code`=?", [$code]);
    $this->DB->delete("classes", "`course_code`=?", [$code]);
    $this->DB->delete("courses_users", "`course_code`=?", [$code]);
    $this->DB->delete("courses", "`course_code`=?", [$code]);
    $this->DB->end();
    return true;
  }

  // (D) GET COURSE
  //  $code : course code
  function get ($code) {
    return $this->DB->fetch(
      "SELECT * FROM `courses` WHERE `course_code`=?",
      [$code]
    );
  }

  // (E) GET ALL OR SEARCH COURSES
  //  $search : optional, course code or name
  //  $page : optional, current page number
  function getAll ($search=null, $page=null) {
    // (E1) PARITAL SQL + DATA
    $sql = "FROM `courses`";
    $data = null;
    if ($search != null) {
      $sql .= " WHERE `course_code` LIKE ? OR `course_name` LIKE ?";
      $data = ["%$search%", "%$search%"];
    }

    // (E2) PAGINATION
    if ($page != null) {
      $this->Core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
      $sql .= $this->Core->page["lim"];
    }
// Define D_SHORT constant
//define('D_SHORT', 'Y-m-d');
    // (E3) RESULTS
    return $this->DB->fetchAll(
      "SELECT *, DATE_FORMAT(`course_start`, '".D_SHORT."') `sd`, DATE_FORMAT(`course_end`, '".D_SHORT."') `ed` $sql",
       $data, "course_code"
    );
  }

  // (F) ADD USER TO COURSE
  //  $code : course code
  //  $uid : user id or email
  function addUser ($code, $uid) {
    // (F1) VERIFY VALID USER
    $this->Core->load("Users");
    $user = $this->Users->get($uid);
    if (!is_array($user) || $user["user_level"]=="S") {
      $this->error = "Invalid user";
      return false;
    }

    // (F2) ADD TO COURSE
    $this->DB->replace("courses_users", ["course_code", "user_id"], [$code, $user["user_id"]]);
    return true;
  }

  // (G) DELETE USER FROM COURSE
  //  $code : course code
  //  $uid : user id or email
  function delUser ($code, $uid) {
    $this->DB->delete("courses_users", "`course_code`=? AND `user_id`=?", [$code, $uid]);
    return true;
  }

  // (H) GET ALL USERS IN COURSE
  //  $code : course code
  //  $page : optional, current page number
  function getUsers ($code, $page=null) {
    // (H1) PARITAL SQL + DATA
    $sql = "FROM `courses_users` cu
            JOIN `users` u USING (`user_id`)
            WHERE cu.`course_code`=? AND u.`user_level`!='S'";
    $data = [$code];

    // (H2) PAGINATION
    if ($page != null) {
      $this->Core->paginator(
        $this->DB->fetchCol("SELECT COUNT(*) $sql", $data), $page
      );
    }

    // (H3) "MAIN SQL"
    $sql .= " ORDER BY FIELD(`user_level`, 'A','T','U'), `user_name`";
    if ($page != null) { $sql .= $this->Core->page["lim"]; }

    // (H4) RESULTS
    return $this->DB->fetchAll("SELECT * $sql", $data, "user_id");
  }

  // (I) GET TEACHERS IN COURSE
  //  $code : course code
  function getTeachers ($code) {
    return $this->DB->fetchAll(
      "SELECT u.`user_id`, u.`user_name`, u.`user_email`
       FROM `courses_users` c
       JOIN `users` u USING (`user_id`)
       WHERE u.`user_level` IN ('A', 'T')
       AND c.`course_code`=?
       ORDER BY `user_name` ASC",
       [$code], "user_id"
    );
  }
}