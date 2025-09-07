<?php
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(3);
?>
<?php
  $d_attendance = find_by_id('attendances',(int)$_GET['id']);
  if(!$d_attendance){
    $session->msg("d","Missing student id.");
    redirect('attendances.php');
  }
?>
<?php
  $delete_id = delete_by_id('attendances',(int)$d_attendance['id']);
  if($delete_id){
      $session->msg("s","Attendance deleted.");
      redirect('attendances.php');
  } else {
      $session->msg("d","Attendance  deletion failed.");
      redirect('attendances.php');
  }
?>