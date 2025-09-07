<?php
$page_title = 'Section Details';
require_once('includes/load.php');
page_require_level(2);

if (!isset($_GET['section_id'])) {
    redirect('faculty_dashboard.php'); // Redirect back if no section_id is provided
}

$section_id = $_GET['section_id'];

// Fetch section details and attendance
$section = get_section_by_id($section_id); // Fetch section details
$attendance = get_section_attendance($section_id); // Fetch attendance data
$students_in_section = get_students_in_section($section_id); // Fetch students in the section
$total_students = count($students_in_section); // Count total students

// Calculate present and absent counts
$present_count = 0;
foreach ($attendance as $entry) {
    if ($entry['timestamp_out']) {
        $present_count++;
    }
}
$absent_count = $total_students - $present_count;
?>

<?php include_once('layouts/header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="libs/css/roles.css" />
</head>

<button class="back_button" style="top: 20px;position: absolute;" onclick="window.history.back()">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
        <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>
    Back
</button>

<div class="section-container">
    <div class="action-buttons-container">
        <div class="search-bar-container">
            <input type="text" id="search-bar-sections" class="search-bar" placeholder="search..."
                style="margin-top: 30px; ">
        </div>

        <a class="export_button" id="export-attendance-btn" style="margin-top: 30px;">
            <i class="fa-solid fa-download"></i>
            <span class="export_button__text">Export</span>
        </a>

    </div>
    <div class="attendance">
        <?php foreach ($section as $sec): ?>
        <h2>Attendance for Section: <?php echo $sec['section_name']; ?></h2>
        <?php endforeach; ?>
        <div class="table">
            <div class="table-header">
                <div class="header__item">No.</div>
                <div class="header__item">Student Name</div>
                <div class="header__item">Time In</div>
                <div class="header__item">Time Out</div>
                <div class="header__item">Date</div>
                <div class="header__item">Status</div>
            </div>
            <div class="table-content" id="table-content-sections">

                <?php foreach ($attendance as $entry): ?>
                <div class="table-row">
                    <div class="table-data"><?php echo count_id(); ?></div>
                    <div class="table-data"><?php echo $entry['student_name']; ?></div>

                    <!-- Time In -->
                    <div class="table-data">
                        <?php 
                    if ($entry['timestamp_in']) {
                        echo date('g:i A', strtotime($entry['timestamp_in']));
                    } else {
                        echo 'N/A'; 
                    }
                    ?>
                    </div>

                    <!-- Time Out -->
                    <div class="table-data">
                        <?php 
                    echo $entry['timestamp_out'] ? date('g:i A', strtotime($entry['timestamp_out'])) : 'N/A'; 
                    ?>
                    </div>

                    <!-- Date -->
                    <div class="table-data">
                        <?php 
                    echo $entry['timestamp_in'] ? date('F d, Y', strtotime($entry['timestamp_in'])) : 'N/A'; 
                    ?>
                    </div>

                    <!-- Status -->
                    <div class="table-data">
                        <?php 
                    $timestamp_in = strtotime($entry['timestamp_in']);
                    $today_start = strtotime(date('Y-m-d 06:30:00')); // Start of today
                    $today_end = strtotime(date('Y-m-d 19:00:00'));   // End of today

                    if ($entry['timestamp_in']) {
                        // Check if student is late (after today_start)
                        if ($timestamp_in > $today_start && $timestamp_in <= $today_end) {
                            echo 'Late (' . $entry['late_in_hours_minutes'] . ')';
                        } elseif ($timestamp_in <= $today_start) {
                            echo 'Present';
                        }
                    } else {
                        echo 'Absent';
                    }
                    ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>


    </div>

    <div class="mobile">
        <?php foreach ($section as $sec): ?>
        <h2>Attendance for <?php echo $sec['section_name']; ?></h2>
        <?php endforeach; ?>

        <div class="children-cards">
            <?php foreach ($attendance as $entry): ?>
            <div class="child-card">
                <!-- Avatar Placeholder -->
                <img class="child-card__avatar" src="/test1/image/default.png" alt="Student Image">

                <!-- Student Name -->
                <div class="child-card__title"><?php echo ucfirst($entry['student_name']); ?></div>

                <!-- Section (optional: from $sec or add to $entry if needed) -->
                <div class="child-card__subtitle">
                    Time In:
                    <?php 
                        echo $entry['timestamp_in'] ? date('g:i A', strtotime($entry['timestamp_in'])) : 'N/A'; 
                    ?>
                </div>

                <!-- Grade Level as Time Out -->
                <div class="child-card__info">
                    Time Out:
                    <?php 
                        echo $entry['timestamp_out'] ? date('g:i A', strtotime($entry['timestamp_out'])) : 'N/A'; 
                    ?>
                </div>

                <!-- Date -->
                <div class="child-card__info">
                    Date:
                    <?php 
                        echo $entry['timestamp_in'] ? date('F d, Y', strtotime($entry['timestamp_in'])) : 'N/A'; 
                    ?>
                </div>

                <!-- Attendance Status -->
                <div class="child-card__info">
                    Status:
                    <?php 
                        $timestamp_in = strtotime($entry['timestamp_in']);
                        $today_start = strtotime(date('Y-m-d 06:30:00'));
                        $today_end = strtotime(date('Y-m-d 19:00:00'));

                        if ($entry['timestamp_in']) {
                            if ($timestamp_in > $today_start && $timestamp_in <= $today_end) {
                                echo 'Late (' . $entry['late_in_hours_minutes'] . ')';
                            } elseif ($timestamp_in <= $today_start) {
                                echo 'Present';
                            }
                        } else {
                            echo 'Absent';
                        }
                    ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="pagination" id="pagination-controls-sections"></div>


</div>
<!-- Attendance Table -->



</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('export-attendance-btn').addEventListener('click', exportAttendanceData);

    function exportAttendanceData() {
        // Get visible rows (excluding filtered out rows)
        const visibleRows = document.querySelectorAll('.table-row:not([style*="display: none"])');

        // CSV headers
        let csvContent = "No.,Student Name,Time In,Time Out,Date, Status\n";

        visibleRows.forEach(row => {
            const cells = row.querySelectorAll('.table-data');
            // Skip the Actions column if present
            const rowData = [
                cells[0].textContent.trim(), // No.
                cells[1].textContent.trim(), // Name
                cells[2].textContent.trim(), // Time In
                cells[3].textContent.trim(), // Time Out
                cells[4].textContent.trim(), // Date
                cells[5].textContent.trim() // Status
            ];

            // Proper CSV escaping
            csvContent += rowData.map(data => `"${data.replace(/"/g, '""')}"`).join(',') + '\n';
        });

        // Create and trigger download
        const blob = new Blob([csvContent], {
            type: 'text/csv;charset=utf-8;'
        });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `attendance_${new Date().toISOString().slice(0,10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
});
document.addEventListener('DOMContentLoaded', () => {
    const headers = document.querySelectorAll('.header__item');
    const tableContent = document.getElementById('table-content-sections');

    headers.forEach((header, index) => {
        header.addEventListener('click', () => {
            const rows = Array.from(tableContent.querySelectorAll('.table-row'));
            const isAscending = header.classList.toggle('asc');

            rows.sort((a, b) => {
                const cellA = a.children[index].textContent.trim()
                    .toLowerCase();
                const cellB = b.children[index].textContent.trim()
                    .toLowerCase();

                // Check if it's a number (e.g., "faculty 10" and "faculty 2")
                const numberA = parseInt(cellA.replace(/\D/g, ''));
                const numberB = parseInt(cellB.replace(/\D/g, ''));

                if (!isNaN(numberA) && !isNaN(numberB)) {
                    // Sort numerically if it's a number
                    return isAscending ? numberA - numberB : numberB -
                        numberA;
                } else {
                    // Sort alphabetically if it's not a number
                    return isAscending ? cellA.localeCompare(cellB) : cellB
                        .localeCompare(cellA);
                }
            });

            rows.forEach(row => tableContent.appendChild(row));
        });
    });
});



function searchTable(searchBarId, tableContentId) {
    var input, filter, table, rows, i, j, txtValue, visible;
    input = document.getElementById(searchBarId);
    filter = input.value.trim().toLowerCase(); // Use trim to avoid leading/trailing spaces
    table = document.getElementById(tableContentId);
    rows = table.getElementsByClassName("table-row");

    for (i = 0; i < rows.length; i++) {
        visible = false;
        columns = rows[i].getElementsByClassName("table-data");

        for (j = 0; j < columns.length; j++) {
            txtValue = columns[j].textContent || columns[j].innerText;

            // Check if the txtValue can be parsed to an integer
            var numericValue = parseFloat(txtValue); // Use parseFloat for date as well
            var filterNumeric = parseFloat(filter); // Convert filter to a number for comparison

            // Check if filter matches a numeric value or text
            if (
                (txtValue.toLowerCase().includes(filter)) || // Text matching
                (txtValue === filter) || // Exact match for numbers
                (!isNaN(numericValue) && !isNaN(filterNumeric) && numericValue === filterNumeric) // Numeric matching
            ) {
                visible = true;
                break; // Stop checking once a match is found
            }
        }
        rows[i].style.display = visible ? "" : "none"; // Show or hide row
    }
}



document.addEventListener("DOMContentLoaded", function() {
    // Event listeners for search bars
    document.getElementById("search-bar-sections").addEventListener("keyup", function() {
        searchTable("search-bar-sections", "table-content-sections");
    });


});
</script>
<?php include_once('layouts/footer.php'); ?>