<?php
$page_title = 'Add Attendance';
require_once('includes/load.php');
// Check if user has permission to view this page
page_require_level(2);

if (isset($_POST['add_attendance'])) {
    $req_fields = array('s_id', 'timestamp_in', 'timestamp_out'); 
    validate_fields($req_fields);

    if (empty($errors)) {
        $p_id = $db->escape((int)$_POST['s_id']);
        $timestamp_in = $db->escape($_POST['timestamp_in']); 
        $timestamp_out = $db->escape($_POST['timestamp_out']); 

        // Set late threshold time (6:30 AM)
        $late_threshold = '06:30:00';

        // Convert both the timestamp_in and late_threshold to Unix timestamps
        $time_in = strtotime($timestamp_in);
        $late_time = strtotime($late_threshold);

        // Calculate the difference in minutes
        $late_minutes = 0;

        if ($time_in > $late_time) {
            $time_difference = $time_in - $late_time;
            $late_minutes = round($time_difference / 60); // Convert seconds to minutes
        }

        // Insert into the attendances table, including the 'late' minutes
        $sql = "INSERT INTO attendances (student_id, timestamp_in, timestamp_out, late) ";
        $sql .= "VALUES ('{$p_id}', '{$timestamp_in}', '{$timestamp_out}', '{$late_minutes}')";

        if ($db->query($sql)) {
            $session->msg('s', "Attendance added.");
            redirect('add_attendance.php', false);
        } else {
            $session->msg('d', 'Sorry failed to add!');
            redirect('add_attendance.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_attendance.php', false);
    }
}
?>



<?php include_once('layouts/header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sale</title>
    <link rel="stylesheet" href="libs/css/roles.scss" />
</head>


<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<button class="back_button" style="top: 20px; z-index:1; position: absolute;" onclick="window.history.back()">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
        <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>
    Back
</button>

<div class="col-md-12">
    <div class="action-buttons-container">
        <div class="search-bar-container" id="sug-form" style="left:0px; position:relative;">
            <form method="post" action="add_attendance_ajax.php" autocomplete="off">
                <input type="text" name="title" class="search-bar" placeholder="search..." id="sug_input">
                <div id="result" class="list-group"></div>
            </form>
        </div>
    </div>
    <form method="post" action="add_attendance.php">
        <div class="table">
            <div class="table-header">
                <!-- Existing table headers -->
                <div class="header__item"><a id="name" class="filter__link" href="#">Name</a></div>
                <div class="header__item"><a id="section" class="filter__link" href="#">Section</a></div>
                <div class="header__item"><a id="grade_level" class="filter__link" href="#">Grade Level</a></div>
                <div class="header__item"><a id="gender" class="filter__link" href="#">Gender</a></div>
                <div class="header__item"><a id="timestamp_in" class="filter__link filter__link--number"
                        href="#">Timestamp in</a></div>
                <div class="header__item"><a id="timestamp_out" class="filter__link filter__link--number"
                        href="#">Timestamp out</a></div>
                <div class="header__item"><a id="late" class="filter__link filter__link--number" href="#">Late</a></div>
                <div class="header__item"><a class="filter__link" href="#">Actions</a></div>


            </div>
        </div>

        <div class="table-content" id="student_info"></div>
    </form>
</div>



<?php include_once('layouts/footer.php'); ?>