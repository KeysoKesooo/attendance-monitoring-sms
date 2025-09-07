<?php
require_once('includes/load.php');
require_once('vendor/autoload.php'); // Ensure the Composer autoload file is included
// Fetch daily attendance records
function get_daily_attendance($year, $month) {
    return dailyattendances($year, $month); // Using dailyattendances function
}

// Fetch monthly attendance records
function get_monthly_attendance($year) {
    return monthlyattendances($year); // Using monthlyattendances function
}

function custom_attendance($start_date, $end_date) {
    return find_all_attendances_with_date_range($start_date, $end_date); // Using monthlyattendances function
}

function all_user () {
    return find_all_user();
}

function all_student() {
    return join_student_table();
}

function student_monthly_attendance($student_id, $month, $year) {
    return get_student_monthly_attendance($student_id, $month, $year);
}



// Check the type of report to download
if (isset($_GET['type'])) {
    $year = date("Y"); // Set current year
    $month = date("m"); // Set current month

    if ($_GET['type'] == 'daily') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="daily_attendance_report.csv"');

        $output = fopen('php://output', 'w');

        // Add header row for daily attendance (without Total Attendance)
        fputcsv($output, ['ID', 'Name', 'Grade Level', 'Section', 'Time in', 'Time out', 'Late (Mins)', 'Date']);

        // Fetch daily attendance records
        $daily_attendances = get_daily_attendance($year, $month);
        $count = 1; // Initialize row counter

        foreach ($daily_attendances as $attendance) {
            fputcsv($output, [
                $count++,  // Row number
                $attendance['name'],         // Name
                $attendance['grade_level'],  // Grade Level
                $attendance['categorie'],    // Section
                $attendance['check_in_time'],    // Check in
                $attendance['check_out_time'],    // Check out
                $attendance['late'],   
                $attendance['date'],         // Date
            ]);
        }

        fclose($output);
        exit;
    }

    if ($_GET['type'] == 'monthly') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="monthly_attendance_report.csv"');

        $output = fopen('php://output', 'w');

        // Add header row for monthly attendance (including Total Attendance)
        fputcsv($output, ['No', 'Name', 'Grade Level', 'Section', 'Total Attendance', 'Month']);

        // Fetch monthly attendance records
        $monthly_attendances = get_monthly_attendance($year);
        $count = 1; // Initialize row counter


        foreach ($monthly_attendances as $attendance) {
            fputcsv($output, [
                $count++,  // Row number
                $attendance['name'],              // Name
                $attendance['grade_level'],       // Grade Level
                $attendance['categorie'],         // Section
                $attendance['total_records'],     // Total Attendance
                $attendance['month'],             // Month
            ]);
        }

        fclose($output);
        exit;
    }


    if ($_GET['type'] == 'all_attendances') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="all_attendance_report.csv"');
    
        $output = fopen('php://output', 'w');
        fputcsv($output, ['No', 'Name', 'Grade Level', 'Section', 'Time In', 'Time Out', 'Late (Mins)', 'Date']);
    
        $all_attendances = find_all_attendances(); // Make sure this function exists
        $count = 1; // Initialize row counter

        foreach ($all_attendances as $attendance) {
            fputcsv($output, [
                $count++,  // Row number
                $attendance['student_name'],
                $attendance['grade_level'],
                $attendance['category_name'],
                $attendance['timestamp_in'],
                $attendance['timestamp_out'],
                $attendance['late'],
                $attendance['date_in']
            ]);
        }
    
        fclose($output);
        exit;
    }

    if ($_GET['type'] == 'custom') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="custom_attendance_report.csv"');

        $output = fopen('php://output', 'w');

        // Add header row for custom attendance
        fputcsv($output, ['No', 'Name', 'Grade Level', 'Section', 'Time In', 'Time Out', 'Late (Mins)', 'Date']);



        // Fetch custom attendance records
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $custom_attendances = custom_attendance($start_date, $end_date);
        $count = 1; // Initialize row counter


        foreach ($custom_attendances as $attendance) {
            fputcsv($output, [
                $count++,  // Row number
                $attendance['student_name'],         // Name
                $attendance['grade_level'],  // Grade Level
                $attendance['category_name'],    // Section
                $attendance['timestamp_in'],    // Check in
                $attendance['timestamp_out'],    // Check out
                $attendance['late'],   
                $attendance['date_in'],         // Date
            ]);
        }

        fclose($output);
        exit;
    }

    if ($_GET['type'] == 'user') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="user_list.csv"');
    
        $output = fopen('php://output', 'w');
    
        // Add header row for custom attendance
        fputcsv($output, ['No', 'Name', 'Username', 'Email', 'Phone Number', 'User Role', 'Status', 'Last Login']);
    
        $all_user = all_user();
        $count = 1; // Initialize row counter

    
        foreach ($all_user as $user) {
            // Format last_login if it's a timestamp
            $last_login = date('Y-m-d H:i:s', strtotime($user['last_login']));
            
            // Ensure phone_number is treated as text in Excel
            $phone_number = "'" . $user['phone_number']; 
    
            // Write each user data as a CSV row
            fputcsv($output, [
                $count++,  // Row number
                $user['name'],
                $user['username'],
                $user['email'],
                $phone_number,  // formatted as text
                $user['group_name'],
                $user['status'],
                $last_login,  // formatted date
            ]);
        }
    
        fclose($output);
        exit;
    }
    

    if ($_GET['type'] == 'student') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="student_attendance_report.csv"');

        $output = fopen('php://output', 'w');

        // Add header row for custom attendance
        fputcsv($output, ['No', 'Name', 'Gender', 'Grade Level', 'Section', 'Guardian Phone Number', 'Address' , 'Date Created']);

        $all_student = all_student();
        $count = 1; // Initialize row counter



        foreach ($all_student as $student) {
            fputcsv($output, [
                $count++,  // Row number
                $student['name'],  
                $student['gender'],  
                $student['grade_level'],      
                $student['categorie'],      
                $student['phone_number'],  
                $student['address'],          
                $student['date'],                  


            ]);
        }

        fclose($output);
        exit;
    }

    


    if (isset($_GET['type']) && $_GET['type'] == 'view_attendance' && isset($_GET['section_id'])) {
        $section_id = $_GET['section_id'];
        
        // Fetch attendance data
        $attendance = get_section_attendance($section_id);
    
        if (!$attendance) {
            die("No attendance records found.");
        }
    
        // Set headers to force download as an Excel file
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=attendance_section_$section_id.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
    
        // Output column headers
        echo "No.\tStudent Name\tTime In\tTime Out\tDate\tStatus\n";
    
        $count = 1;
        foreach ($attendance as $entry) {
            $time_in = $entry['timestamp_in'] ? date('g:i A', strtotime($entry['timestamp_in'])) : 'N/A';
            $time_out = $entry['timestamp_out'] ? date('g:i A', strtotime($entry['timestamp_out'])) : 'N/A';
            $date = $entry['timestamp_in'] ? date('F d, Y', strtotime($entry['timestamp_in'])) : 'N/A';
    
            $status = 'Absent';
            if ($entry['timestamp_in']) {
                $timestamp_in = strtotime($entry['timestamp_in']);
                $today_start = strtotime(date('Y-m-d 06:30:00')); 
                $today_end = strtotime(date('Y-m-d 19:00:00'));   
    
                if ($timestamp_in > $today_start && $timestamp_in <= $today_end) {
                    $status = 'Late (' . $entry['late_in_hours_minutes'] . ')';
                } elseif ($timestamp_in <= $today_start) {
                    $status = 'Present';
                }
            }
    
            echo "$count\t{$entry['student_name']}\t$time_in\t$time_out\t$date\t$status\n";
            $count++;
        }
    
        exit();
    }

    if (isset($_GET['type']) && $_GET['type'] == 'student_attendance' && isset($_GET['student_id'])) {
        $studentId = $_GET['student_id'];
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
    
        $attendance = get_student_attendance($studentId, $startDate, $endDate);
    
        if (!$attendance) {
            die("No attendance records found for this student.");
        }
    
        // Set headers
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=student_attendance.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
    
        echo "No.\tStudent Name\tTime In\tTime Out\tDate\tStatus\n";
    
        $count = 1;
        foreach ($attendance as $entry) {
            $time_in = $entry['timestamp_in'] ? date('g:i A', strtotime($entry['timestamp_in'])) : 'N/A';
            $time_out = $entry['timestamp_out'] ? date('g:i A', strtotime($entry['timestamp_out'])) : 'N/A';
            $date = $entry['timestamp_in'] ? date('F d, Y', strtotime($entry['timestamp_in'])) : 'N/A';
    
            $status = 'Absent';
            if ($entry['timestamp_in']) {
                $timestamp_in = strtotime($entry['timestamp_in']);
                $today_start = strtotime(date('Y-m-d 06:30:00', $timestamp_in)); 
                $today_end = strtotime(date('Y-m-d 19:00:00', $timestamp_in));   
    
                if ($timestamp_in > $today_start && $timestamp_in <= $today_end) {
                    $status = 'Late (' . $entry['late_in_hours_minutes'] . ')';
                } elseif ($timestamp_in <= $today_start) {
                    $status = 'Present';
                }
            }
    
            echo "$count\t{$entry['student_name']}\t$time_in\t$time_out\t$date\t$status\n";
            $count++;
        }
    
        exit();
    }

if (isset($_GET['id']) && isset($_GET['month'])) {
    $student_id = (int)$_GET['id'];
    $month_full = $_GET['month'];
    
    // Split month and year
    $parts = explode('-', $month_full);
    $month = $parts[0];
    $year = $parts[1];

    $records = student_monthly_attendance($student_id, $month, $year);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="monthly_attendance_'.$month.'_'.$year.'.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'Strand', 'Grade Level', 'Section', 'Time In', 'Time Out', 'Date']);

    foreach ($records as $row) {
        fputcsv($output, [
            $row['name'],
            $row['strand'],
            $row['grade_level'],
            $row['section'],
            $row['check_in_time'],
            $row['check_out_time'],
            $row['date'],
        ]);
    }

    fclose($output);
    exit;
}
    
}
?>