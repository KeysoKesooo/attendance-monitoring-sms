<?php
  $page_title = 'Edit Attendance';
  require_once('includes/load.php');
  // Check what level user has permission to view this page
  page_require_level(1);
?>
<?php
// Find the attendance by ID
$attendance = find_by_id('attendances', (int)$_GET['id']);
if (!$attendance) {
  $session->msg("d", "Missing attendance ID.");
  redirect('edit_attendances.php');
}

$student = find_by_id('students', $attendance['student_id']);
?>
<?php

if (isset($_POST['update_attendance'])) {
    // Required fields for attendance update
    $req_fields = array('s_id', 'attendances', 'timestamp');
    validate_fields($req_fields);

    if (empty($errors)) {
        // Escape input data for security
        $student_id  = $db->escape((int)$_POST['s_id']); // Get student ID
        $attendances = $db->escape((int)$_POST['attendances']); // Number of attendances
        $timestamp   = $db->escape($_POST['timestamp']); // Get timestamp
        $formatted_timestamp = date("Y-m-d H:i:s", strtotime($timestamp)); // Format the timestamp to standard format

        // Update SQL query for attendances table
        $sql  = "UPDATE attendances SET";
        $sql .= " student_id= '{$student_id}', attendances={$attendances}, time_stamp='{$formatted_timestamp}'";
        $sql .= " WHERE id ='{$attendance['id']}'"; // Use the correct attendance ID
        
        $result = $db->query($sql);

        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "Attendance updated successfully.");
            redirect('attendances.php?id=' . $attendance['id'], false); // Redirect to the report page with attendance ID
        } else {
            $session->msg('d', 'Sorry, failed to update attendance!');
            redirect('attendances.php', false);
        }
    } else {
        // Handle validation errors
        $session->msg("d", $errors);
        redirect('attendances.php?id=' . (int)$attendance['id'], false);
    }
}
?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
    <div class="col-md-6">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<button class="back_button" onclick="window.history.back()">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
        <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>
    Back
</button>

<div class="row">
    <div class="col-md-12">
        <div class="editpopup_container">
            <div class="editpopup_form_area">
                <form method="post" action="edit_attendance.php?id=<?php echo (int)$attendance['id']; ?>">
                    <div class="editpopup_form_group">
                        <label class="editpopup_sub_title" for="s_id">Student ID</label>
                        <input name="s_id" placeholder="Student ID" class="editpopup_form_style" type="text"
                            value="<?php echo (int)$attendance['student_id']; ?>" required>
                    </div>

                    <div class="editpopup_form_group">
                        <label class="editpopup_sub_title" for="timestamp">Timestamp</label>
                        <input name="timestamp" class="editpopup_form_style" type="datetime-local"
                            value="<?php echo date('Y-m-d\TH:i', strtotime($attendance['time_stamp'])); ?>" required>
                    </div>
                    <div>
                        <button type="submit" name="update_attendance" class="editpopup_btn">UPDATE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>