<?php
  require_once('includes/load.php');

/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/*--------------------------------------------------------------*/
function find_all($table) {
   global $db;
   if(tableExists($table))
   {
     return find_by_sql("SELECT * FROM ".$db->escape($table));
   }
}
/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
function find_by_sql($sql)
{
  global $db;
  $result = $db->query($sql);
  $result_set = $db->while_loop($result);
 return $result_set;
}
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/*--------------------------------------------------------------*/
function find_by_id($table,$id)
{
  global $db;
  $id = (int)$id;
    if(tableExists($table)){
          $sql = $db->query("SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
          if($result = $db->fetch_assoc($sql))
            return $result;
          else
            return null;
     }
}
/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------*/
function delete_by_id($table, $id)
{
  global $db;

  // Check if the table exists
  if (tableExists($table)) {
    // Check if the ID exists in the table before attempting to delete
    $idCheck = "SELECT id FROM " . $db->escape($table) . " WHERE id = " . $db->escape($id) . " LIMIT 1";
    $result = $db->query($idCheck);

    if ($result->num_rows > 0) {
      // Proceed with deletion if ID exists
      $sql = "DELETE FROM " . $db->escape($table);
      $sql .= " WHERE id = " . $db->escape($id);
      $sql .= " LIMIT 1";
      
      $db->query($sql);

      return ($db->affected_rows() === 1) ? true : false;
    } else {
      // ID doesn't exist in the table
      return false;
    }
  }

  // Return false if table doesn't exist
  return false;
}

/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/*--------------------------------------------------------------*/

function count_by_id($table){
  global $db;
  if(tableExists($table))
  {
    $sql    = "SELECT COUNT(id) AS total FROM ".$db->escape($table);
    $result = $db->query($sql);
     return($db->fetch_assoc($result));
  }
}
/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
function tableExists($table){
  global $db;
  $table_exit = $db->query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$db->escape($table).'"');
      if($table_exit) {
        if($db->num_rows($table_exit) > 0)
              return true;
         else
              return false;
      }
  }
 /*--------------------------------------------------------------*/
 /* Login with the data provided in $_POST,
 /* coming from the login form.
/*--------------------------------------------------------------*/
function authenticate($username = '', $password = '') {
  global $db;
  $username = $db->escape($username);
  
  $sql = sprintf("SELECT id, username, password, user_level FROM users WHERE username = '%s' LIMIT 1", $username);
  $result = $db->query($sql);
  
  if ($db->num_rows($result)) {
      $user = $db->fetch_assoc($result);
      
      // Secure password check
      if (password_verify($password, $user['password'])) {
          return $user['id'];  // Success
      }
  }

  return false;  // Fail
}


  /*--------------------------------------------------------------*/
  /* Login with the data provided in $_POST,
  /* coming from the login_v2.php form.
  /* If you used this method then remove authenticate function.
 /*--------------------------------------------------------------*/
   function authenticate_v2($username='', $password='') {
     global $db;
     $username = $db->escape($username);
     $password = $db->escape($password);
     $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
     $result = $db->query($sql);
     if($db->num_rows($result)){
       $user = $db->fetch_assoc($result);
       $password_request = sha1($password);
       if($password_request === $user['password'] ){
         return $user;
       }
     }
    return false;
   }


  /*--------------------------------------------------------------*/
  /* Find current log in user by session id
  /*--------------------------------------------------------------*/
  function current_user(){
      static $current_user;
      global $db;
      if(!$current_user){
         if(isset($_SESSION['user_id'])):
             $user_id = intval($_SESSION['user_id']);
             $current_user = find_by_id('users',$user_id);
        endif;
      }
    return $current_user;
  }
  /*--------------------------------------------------------------*/
  /* Find all user by
  /* Joining users table and user gropus table
  /*--------------------------------------------------------------*/
  function find_all_user(){
      global $db;
      $results = array();
      $sql = "SELECT u.id,u.image,u.name,u.username,u.email,u.user_level,u.phone_number,u.status,u.last_login,";
      $sql .="g.group_name ";
      $sql .="FROM users u ";
      $sql .="LEFT JOIN user_groups g ";
      $sql .="ON g.group_level=u.user_level ORDER BY u.name ASC";
      $result = find_by_sql($sql);
      return $result;
  }

  // sql.php
/**
 * Retrieves student information and their attendance records
 * 
 * @param int $user_id The ID of the user (from users table)
 * @return array Associative array containing student info and attendance records
 */
function get_student_info_and_attendance($user_id) {
  global $db;
  
  // Validate and sanitize input
  if (!is_numeric($user_id)) {
      return false;
  }
  $escaped_user_id = (int)$user_id;
  
  // First get student information (single record)
  $student_sql = "SELECT 
                  s.id AS student_id,
                  u.name, u.username, 
                  c.name AS section_name, 
                  c.grade_level, 
                  s.gender, 
                  s.phone_id, 
                  s.address, 
                  s.student_image AS image
              FROM users u 
              JOIN student s ON s.user_id = u.id 
              LEFT JOIN categories c ON s.categorie_id = c.id 
              WHERE u.id = {$escaped_user_id}
              LIMIT 1";
  
  $student_info = find_by_sql($student_sql);
  
  if (empty($student_info)) {
      return false;
  }
  
  // Then get attendance records separately
  $attendance_sql = "SELECT 
                      DATE(a.timestamp_in) AS date,
                      TIME_FORMAT(a.timestamp_in, '%h:%i %p') AS time_in,
                      TIME_FORMAT(a.timestamp_out, '%h:%i %p') AS time_out,
                      a.late_in_hours_minutes AS late_status
                  FROM attendances a
                  WHERE a.student_id = {$student_info[0]['student_id']}
                  ORDER BY a.timestamp_in DESC
                  LIMIT 30"; // Limit to recent 30 records for performance
  
  $attendance_records = find_by_sql($attendance_sql);
  
  // Combine results
  return [
      'student_info' => $student_info[0],
      'attendance_records' => $attendance_records
  ];
}



  


  
function find_faculty_categories() {
  global $db;

  $sql = "
      SELECT 
    fc.id AS faculty_category_id,
    u.name AS faculty_name,
    c.id AS categorie_id, 
    c.grade_level AS grade_level,
    c.name AS section,
    u.username,
    u.phone_number,
    u.email,
    u.image,
    u.status,
    u.last_login
FROM faculty_categories fc
JOIN users u ON fc.user_id = u.id
JOIN categories c ON fc.categorie_id = c.id
WHERE u.user_level = 2
ORDER BY c.grade_level, c.name, u.name;";

  return find_by_sql($sql);
}
  /*--------------------------------------------------------------*/
  /* Function to update the last log in of a user
  /*--------------------------------------------------------------*/

 function updateLastLogIn($user_id)
	{
		global $db;
    $date = make_date();
    $sql = "UPDATE users SET last_login='{$date}' WHERE id ='{$user_id}' LIMIT 1";
    $result = $db->query($sql);
    return ($result && $db->affected_rows() === 1 ? true : false);
	}

  /*--------------------------------------------------------------*/
  /* Find all Group name
  /*--------------------------------------------------------------*/
  function find_by_groupName($val)
  {
    global $db;
    $sql = "SELECT group_name FROM user_groups WHERE group_name = '{$db->escape($val)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Find group level
  /*--------------------------------------------------------------*/
  function find_by_groupLevel($level) {
    global $db;
    $sql = "SELECT * FROM user_groups WHERE group_level = '{$db->escape($level)}' LIMIT 1";
    $result = $db->query($sql);
    
    // Check if the query was successful
    if ($result) {
        // If there are any rows, return the first row as an associative array
        if ($db->num_rows($result) > 0) {
            return $result->fetch_assoc();
        }
    }

    // Return false if no matching group is found
    return false;
}
  /*--------------------------------------------------------------*/
  /* Function for cheaking which user level has access to page
  /*--------------------------------------------------------------*/
  function page_require_level($require_level){
    global $session;
    $current_user = current_user();
    $login_level = find_by_groupLevel($current_user['user_level']);
    //if user not login
    if (!$session->isUserLoggedIn(true)):
           $session->msg('d','Please login...');
           redirect('index.php', false);
     //if Group status Deactive
    elseif($login_level['group_status'] === '0'):
          $session->msg('d','This level user has been band!');
          redirect('index.php',false);
     //cheackin log in User level and Require level is Less than or equal to
    elseif($current_user['user_level'] <= (int)$require_level):
             return true;
     else:
           $session->msg("d", "Sorry! you dont have permission to view the page.");
           redirect('index.php', false);
       endif;

    }


function countAllStudents() {
  global $db; // Assuming $db is your database connection variable

  // SQL query to count all students
  $query = "SELECT COUNT(*) as total FROM student";
  
  // Execute the query
  $result = $db->query($query);
  
  // Fetch the result
  if ($result) {
      $row = $result->fetch_assoc();
      return $row['total']; // Return the count of students
  }

  return 0; // Return 0 if no students found or query fails
}
function getGenderCounts() {
    global $db;

    // Query to count gender from the student table
    $query = "SELECT gender, COUNT(*) as count FROM student GROUP BY gender";
    $result = $db->query($query);

    $genderCounts = [
        'Male' => 0,
        'Female' => 0
    ];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $gender = ucfirst(strtolower($row['gender'])); // Ensure consistent formatting
            if (isset($genderCounts[$gender])) {
                $genderCounts[$gender] = (int) $row['count'];
            }
        }
    }

    return $genderCounts;
}

// Example function to get gender counts for a specific date range
function getGenderCountsByDateRange($startDate, $endDate) {
  global $db;

  // Validate and format dates
  try {
      $today = new DateTime();
      $startDateObj = new DateTime($startDate);
      $endDateObj = new DateTime($endDate);
      
      // Adjust end date if it's in the future
      if ($endDateObj > $today) {
          $endDate = $today->format('Y-m-d');
          $endDateObj = new DateTime($endDate);
      }
      
      // Ensure start date isn't after end date
      if ($startDateObj > $endDateObj) {
          $startDate = $endDate;
      }
  } catch (Exception $e) {
      error_log("Invalid date format: " . $e->getMessage());
      return ['Male' => 0, 'Female' => 0];
  }

  // Use DATE() function to properly compare dates with timestamps
  $sql = "SELECT s.gender, COUNT(DISTINCT a.student_id) AS count
          FROM attendances a
          JOIN student s ON a.student_id = s.id
          WHERE DATE(a.timestamp_in) BETWEEN '{$db->escape($startDate)}' AND '{$db->escape($endDate)}'
          GROUP BY s.gender";

  $result = $db->query($sql);
  
  if (!$result) {
      error_log("Query failed: " . $db->error);
      return ['Male' => 0, 'Female' => 0];
  }

  $genderCounts = ['Male' => 0, 'Female' => 0];
  
  while ($row = $result->fetch_assoc()) {
      $gender = ucfirst(strtolower($row['gender'])); // Normalize gender case
      if (array_key_exists($gender, $genderCounts)) {
          $genderCounts[$gender] = (int)$row['count'];
      }
  }

  return $genderCounts;
}


/*--------------------------------------------------------------*/
/*  Get total present students for a specific month and year
/*--------------------------------------------------------------*/
/*--------------------------------------------------------------*/
/*  Get total present students for a specific date range
/*--------------------------------------------------------------*/
function getTotalStudentsByDateRange($startDate, $endDate) {
  global $db;
  
  // Adjust end date to include the entire day
  $endDate .= ' 23:59:59';
  
  // Query to get the total students present per date in the range
  $sql = "SELECT DATE(timestamp_in) AS date, COUNT(DISTINCT student_id) AS total_students
          FROM attendances
          WHERE timestamp_in >= '{$db->escape($startDate)}' 
            AND timestamp_in <= '{$db->escape($endDate)}'
          GROUP BY DATE(timestamp_in)";
  
  $result = $db->query($sql);
  $attendanceData = [];
  
  while ($row = $result->fetch_assoc()) {
      $attendanceData[$row['date']] = $row['total_students'];
  }
  
  return $attendanceData;
}


/*--------------------------------------------------------------*/
/*  Get total absent students for a specific month and year
/*--------------------------------------------------------------*/
/*--------------------------------------------------------------*/
/*  Get total absent students for a specific date range
/*--------------------------------------------------------------*/
function getAbsentStudentsByDateRange($startDate, $endDate) {
    global $db;
    $absenceData = [];

    // Get the total number of students
    $totalStudentsQuery = "SELECT COUNT(*) AS total_students FROM student";
    $totalStudentsResult = $db->query($totalStudentsQuery);
    $totalStudents = $totalStudentsResult->fetch_assoc()['total_students'];

    // Loop through each day in the date range
    $startDateObj = new DateTime($startDate);
    $endDateObj = new DateTime($endDate);
    $today = new DateTime(); // Get today's date

    $currentDate = $startDateObj;

    while ($currentDate <= $endDateObj) {
        $dateStr = $currentDate->format('Y-m-d');

        if ($currentDate > $today) {
            // If the date is in the future, assume no data
            $absenceData[$dateStr] = 0;
        } else {
            $sql = "SELECT COUNT(*) AS absent_students
                    FROM student s
                    LEFT JOIN attendances a ON s.id = a.student_id 
                    AND DATE(a.timestamp_in) = '{$db->escape($dateStr)}'
                    WHERE a.student_id IS NULL";

            $result = $db->query($sql);
            $absentCount = $result->fetch_assoc()['absent_students'];

            $absenceData[$dateStr] = $absentCount;
        }

        $currentDate->modify('+1 day');
    }

    return $absenceData;
}



/*--------------------------------------------------------------*/
/*  Total students who attended today
/*--------------------------------------------------------------*/

function getTotalStudentsToday() {
  global $db;

  // Query to count students with a `timestamp_in` record for today
  $query = "SELECT COUNT(*) as present_count 
            FROM attendances 
            WHERE DATE(timestamp_in) = CURDATE()";
  $result = $db->query($query);
  $row = $result->fetch_assoc();

  return (int)$row['present_count']; // Return present count
}




/*--------------------------------------------------------------*/
/*  Total students who absent today
/*--------------------------------------------------------------*/

function getAbsentStudentsToday() {
  global $db;

  // Total students
  $totalStudents = countAllStudents();
  // Total present students
  $presentStudents = getTotalStudentsToday();

  // Calculate absent students
  return $totalStudents - $presentStudents; // Return calculated absent students
}
/*--------------------------------------------------------------*/
/*  online users
/*--------------------------------------------------------------*/ 
function count_online_users() {
  global $db;
  
  // Set threshold to 5 minutes ago
  $threshold_time = date("Y-m-d H:i:s", strtotime("-5 minutes"));
  
  // SQL to count users whose last_login is within the threshold
  $sql = "SELECT COUNT(*) AS online_count FROM users WHERE last_login >= '{$threshold_time}'";
  
  // Execute the query
  $result = $db->query($sql);
  $online_count = $result->fetch_assoc()['online_count'];
  
  return $online_count;
}

/*--------------------------------------------------------------*/
/* Function for Finding all students name
/* JOIN with categories and media database table
/*--------------------------------------------------------------*/
function join_student_table() {
  global $db;
  $sql  = "SELECT p.id, p.name, p.address, p.student_image, p.date, p.gender,";
  $sql .= " c.name AS categorie, c.grade_level, c.strand,";
  $sql .= " u.phone_number AS phone_number";
  $sql .= " FROM student p";
  $sql .= " LEFT JOIN users u ON u.id = p.phone_id";
  $sql .= " LEFT JOIN categories c ON c.id = p.categorie_id";
  $sql .= " ORDER BY p.id DESC";
  return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
/*  Function to find the specific student by ID:
/* JOIN with categories and media database table
/*--------------------------------------------------------------*/

function find_student_by_id($id) {
  global $db;
  
  // Escape the ID to prevent SQL injection
  $id = $db->escape($id);

  // Construct the SQL query
  $sql  = "SELECT p.id, p.name, p.student_image, p.date, p.gender, ";
  $sql .= "c.name AS categorie, c.grade_level, c.strand ";
  $sql .= "FROM student p ";
  $sql .= "LEFT JOIN categories c ON c.id = p.categorie_id ";
  $sql .= "WHERE p.id = '{$id}' ";
  $sql .= "LIMIT 1";

  // Execute the query and fetch the result
  $result = find_by_sql($sql);

  // Return the student data or false if no data found
  return !empty($result) ? $result[0] : false;
}

/*--------------------------------------------------------------*/
/* Function for Finding all product names with category info
/* Request coming from ajax.php for auto suggest
/*--------------------------------------------------------------*/
function find_student_by_title($student_name){
  global $db;
  $p_name = remove_junk($db->escape($student_name));
  $sql = "SELECT name FROM student WHERE name like '%$p_name%' LIMIT 5";
  $result = find_by_sql($sql);
  return $result;
}

/*--------------------------------------------------------------*/
/* Function for Finding all product info by product title
/* Request coming from ajax.php
/*--------------------------------------------------------------*/
function find_all_student_info_by_title($title) {
  global $db;
  $title = remove_junk($db->escape($title));
  $sql = "SELECT p.id, p.name,p.gender, c.name AS category_name, c.grade_level AS grade_level
          FROM student p
          LEFT JOIN categories c ON c.id = p.categorie_id
          WHERE p.name = '{$title}'
          LIMIT 1";
  return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
/* Function for find all attendances
/*--------------------------------------------------------------*/
function find_all_attendances() {
  global $db;

  // Construct the SQL query
  $sql = "SELECT 
              a.id,
              a.late_in_hours_minutes AS late,  -- Use the new column with formatted time
              DATE_FORMAT(a.timestamp_in, '%h:%i %p') AS timestamp_in,  -- 12-hour format with AM/PM
              DATE_FORMAT(a.timestamp_out, '%h:%i %p') AS timestamp_out,  -- 12-hour format with AM/PM
              DATE_FORMAT(a.timestamp_in, '%M-%d-%Y') AS date_in,
              s.gender AS gender,
              s.name AS student_name, 
              c.name AS category_name, 
              c.grade_level AS grade_level,
              c.strand
          FROM attendances a
          LEFT JOIN student s ON a.student_id = s.id
          LEFT JOIN categories c ON s.categorie_id = c.id
          ORDER BY a.timestamp_in DESC";

  // Execute the query and return the results
  return find_by_sql($sql);
}
  
function get_student_attendance($studentId, $startDate, $endDate) {
  global $db;

  $sql = "SELECT 
              s.name AS student_name,
              a.timestamp_in,
              a.timestamp_out,
              a.late_in_hours_minutes
          FROM attendances a
          JOIN student s ON s.id = a.student_id
          WHERE a.student_id = {$studentId}
          AND DATE(a.timestamp_in) BETWEEN '{$startDate}' AND '{$endDate}'
          ORDER BY a.timestamp_in DESC";

  $result = $db->query($sql);

  $attendance = [];
  while ($row = $result->fetch_assoc()) {
      $attendance[] = $row;
  }

  return $attendance;
}


/*--------------------------------------------------------------*/
/* Function for Generate Daily attendances Report
/*--------------------------------------------------------------*/
function dailyattendances($year, $month) {
  global $db;
  $sql = "SELECT DATE_FORMAT(s.timestamp_in, '%M-%d-%Y') AS date, s.id, s.late_in_hours_minutes AS late, p.name, p.gender, c.name AS categorie, c.grade_level AS grade_level,c.strand AS strand, ";
  $sql .= "DATE_FORMAT(s.timestamp_in, '%h:%i %p') AS check_in_time, DATE_FORMAT(s.timestamp_out, '%h:%i %p') AS check_out_time, ";  // 12-hour format with AM/PM
  $sql .= "COUNT(s.student_id) AS total_records ";
  $sql .= "FROM attendances s ";
  $sql .= "LEFT JOIN student p ON s.student_id = p.id ";
  $sql .= "LEFT JOIN categories c ON p.categorie_id = c.id ";
  $sql .= "WHERE DATE_FORMAT(s.timestamp_in, '%Y-%m') = '{$year}-{$month}' ";
  $sql .= "GROUP BY DATE_FORMAT(s.timestamp_in, '%M-%d-%Y'), p.id";
  return find_by_sql($sql);
}


/*--------------------------------------------------------------*/
/* Function for Generate Monthly attendances Report
/*--------------------------------------------------------------*/
function monthlyattendances($year) {
  global $db;
  $sql = "SELECT 
            DATE_FORMAT(s.timestamp_in, '%M-%Y') AS month,
            s.student_id,
            p.name,
            p.gender,
            c.name AS categorie,
            c.grade_level,
            c.strand,
            COUNT(s.student_id) AS total_records,
            GROUP_CONCAT(DATE(s.timestamp_in) ORDER BY s.timestamp_in ASC) AS dates
          FROM attendances s
          LEFT JOIN student p ON s.student_id = p.id
          LEFT JOIN categories c ON p.categorie_id = c.id
          WHERE DATE_FORMAT(s.timestamp_in, '%Y') = '{$year}'
          GROUP BY DATE_FORMAT(s.timestamp_in, '%M-%Y'), s.student_id";
  return find_by_sql($sql);
}


function get_student_attendance_by_month($student_id, $month_year) {
    global $db;
    $sql = "SELECT timestamp_in, timestamp_out FROM attendances 
            WHERE student_id = '{$student_id}' 
              AND DATE_FORMAT(timestamp_in, '%M-%Y') = '{$month_year}' 
            ORDER BY timestamp_in ASC";
    return find_by_sql($sql);
}


function get_student_monthly_attendance($student_id, $month, $year) {
    global $db;

    $sql  = "SELECT p.name, c.strand, c.grade_level, c.name AS section, ";
    $sql .= "DATE_FORMAT(s.timestamp_in, '%h:%i %p') AS check_in_time, ";
    $sql .= "DATE_FORMAT(s.timestamp_out, '%h:%i %p') AS check_out_time, ";
    $sql .= "DATE_FORMAT(s.timestamp_in, '%Y-%m-%d') AS date ";
    $sql .= "FROM attendances s ";
    $sql .= "LEFT JOIN student p ON s.student_id = p.id ";
    $sql .= "LEFT JOIN categories c ON p.categorie_id = c.id ";
    $sql .= "WHERE s.student_id = '{$student_id}' ";
    $sql .= "AND DATE_FORMAT(s.timestamp_in, '%M-%Y') = '{$month}-{$year}' ";
    $sql .= "ORDER BY s.timestamp_in ASC";

    return find_by_sql($sql);
}


/*--------------------------------------------------------------*/
/* Function for find all attendances with date range
/*--------------------------------------------------------------*/
function find_all_attendances_with_date_range($start_date, $end_date) {
  global $db;

  $sql = "SELECT 
              a.id, 
              a.late,
              DATE_FORMAT(a.timestamp_in, '%h:%i %p') AS timestamp_in,
              DATE_FORMAT(a.timestamp_out, '%h:%i %p') AS timestamp_out,
              DATE_FORMAT(a.timestamp_in, '%M-%d-%Y') AS date_in,
              s.gender AS gender,
              s.name AS student_name, 
              c.name AS category_name, 
              c.grade_level AS grade_level
          FROM attendances a
          LEFT JOIN student s ON a.student_id = s.id
          LEFT JOIN categories c ON s.categorie_id = c.id
          WHERE a.timestamp_in >= '{$start_date} 00:00:00' 
            AND a.timestamp_in <= '{$end_date} 23:59:59'
          ORDER BY a.timestamp_in DESC";

  return find_by_sql($sql);
}



// SQL function to get the faculty's registered sections
function get_faculty_sections($user_id) {
  global $db;
  $sql = "SELECT c.id AS section_id, c.name AS section_name 
          FROM categories c
          JOIN faculty_categories fc ON fc.categorie_id = c.id
          WHERE fc.user_id = $user_id";
  return find_by_sql($sql); // Assuming find_by_sql is a predefined function to execute the query
}

// SQL function to get student attendance for a section
function get_section_attendance($section_id) {
  global $db;
  $today_start = date('Y-m-d 00:00:00'); // Start of today (6:30 AM)
  $today_end = date('Y-m-d 19:00:00');   // End of today (3:00 PM)

  // Get attendance records for students in the section, including those without time_in
  $sql = "SELECT s.id AS student_id, s.name AS student_name, a.timestamp_in, a.timestamp_out, a.late_in_hours_minutes
          FROM student s
          LEFT JOIN attendances a 
            ON s.id = a.student_id AND a.timestamp_in BETWEEN '$today_start' AND '$today_end'
          WHERE s.categorie_id = $section_id
          ORDER BY s.name";
  
  return find_by_sql($sql); // Assuming find_by_sql is a predefined function to execute the query
}

function get_section_attendance_late($section_id) {
  global $db;

  // Define today's date range
  $today_start = date('Y-m-d 00:00:00'); // Start of today (midnight)
  $today_end = date('Y-m-d 23:59:59');   // End of today (just before midnight)

  // Fetch attendance records for today, including late students
  $sql = "SELECT s.id AS student_id, s.name AS student_name, 
                 a.timestamp_in, a.timestamp_out, a.late_in_hours_minutes
          FROM student s
          LEFT JOIN attendances a 
            ON s.id = a.student_id 
            AND a.timestamp_in BETWEEN '$today_start' AND '$today_end'
          WHERE s.categorie_id = $section_id
            AND a.late_in_hours_minutes IS NOT NULL
          ORDER BY a.timestamp_in DESC";

  return find_by_sql($sql); // Assuming find_by_sql is a predefined function to execute the query
}

function get_recent_section_attendance($section_id) {
    global $db;

    // Define today's date range
    $today_start = date('Y-m-d 00:00:00'); // Start of today (midnight)
    $today_end = date('Y-m-d 23:59:59');   // End of today (just before midnight)

    // Fetch attendance records for today
    $sql = "SELECT s.id AS student_id, s.name AS student_name, 
                   a.timestamp_in, a.timestamp_out, a.late_in_hours_minutes
            FROM student s
            LEFT JOIN attendances a 
              ON s.id = a.student_id 
              AND a.timestamp_in BETWEEN '$today_start' AND '$today_end'
            WHERE s.categorie_id = $section_id
            ORDER BY a.timestamp_in DESC";

    return find_by_sql($sql); // Assuming find_by_sql is a predefined function to execute the query
}


function get_section_by_id($faculty_id) {
  global $db;
  // This query joins faculty_categories with categories to fetch sections assigned to the faculty
  $sql = "SELECT c.id AS section_id, c.name AS section_name
          FROM faculty_categories fc
          JOIN categories c ON c.id = fc.categorie_id
          WHERE fc.user_id = $faculty_id";
  return find_by_sql($sql); // Assuming find_by_sql is a predefined function to execute the query
}

// SQL function to get total students in a section
function get_students_in_section($section_id) {
  global $db;

  // Query to get all students in the section (using the categorie_id to filter students in that section)
  $sql = "SELECT id 
          FROM student
          WHERE categorie_id = $section_id";
  
  return find_by_sql($sql); // Assuming find_by_sql is a predefined function to execute the query
}

// Function to fetch all faculty members
function get_faculty_members() {
  global $db;
  $sql = "SELECT id, name FROM users WHERE user_level = 2"; // Faculty user_level = 2
  return find_by_sql($sql);
}

// Function to fetch all categories (sections)
function get_all_categories() {
  global $db;
  $sql = "SELECT id, name FROM categories";
  return find_by_sql($sql);
}

// Function to fetch categories that already have assigned faculty
function get_assigned_categories() {
  global $db;
  $sql = "SELECT DISTINCT categorie_id FROM faculty_categories";
  $assigned_categories = find_by_sql($sql);
  
  // Extract just the category IDs into an array
  $category_ids = [];
  foreach ($assigned_categories as $category) {
      $category_ids[] = $category['categorie_id'];
  }
  return $category_ids;
}

// Function to fetch faculty members already assigned to sections
function get_assigned_faculty() {
  global $db;
  $sql = "SELECT DISTINCT user_id FROM faculty_categories";
  $assigned_faculty = find_by_sql(sql: $sql);
  
  // Extract just the faculty IDs into an array
  $faculty_ids = [];
  foreach ($assigned_faculty as $faculty) {
      $faculty_ids[] = $faculty['user_id'];
  }
  return $faculty_ids;
}
function get_section_attendance_chart($section_id, $start_date, $end_date) {
  global $db;

  // Get total students in the section
  $sql_total = "SELECT COUNT(id) AS total_students FROM student WHERE categorie_id = $section_id";
  $totalStudentsResult = find_by_sql($sql_total);
  $totalStudents = $totalStudentsResult[0]['total_students'] ?? 0;

  // Query Present Students
  $sql_present = "SELECT DATE(a.timestamp_in) AS date, COUNT(DISTINCT s.id) AS present_count
  FROM student s
  LEFT JOIN attendances a ON s.id = a.student_id 
  WHERE s.categorie_id = $section_id 
    AND DATE(a.timestamp_in) >= '$start_date' 
    AND DATE(a.timestamp_in) <= '$end_date'
  GROUP BY DATE(a.timestamp_in)";
  $presentRecords = find_by_sql($sql_present);

  // Convert results to associative array
  $presentData = [];
  $absentData = [];
  
  foreach ($presentRecords as $row) {
      $date = $row['date'];
      $presentData[$date] = $row['present_count'];
      $absentData[$date] = $totalStudents - $row['present_count']; // Absent = Total - Present
  }

  return ['present' => $presentData, 'absent' => $absentData];
}