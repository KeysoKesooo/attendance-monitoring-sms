<?php
$page_title = 'Faculty Dashboard';
require_once('includes/load.php');
page_require_level(2);

$now = time();
$reset_time = isset($_SESSION['last_reset']) ? $_SESSION['last_reset'] : 0;
$time_difference = $now - $reset_time;

if ($time_difference > 86400) { // Reset attendance counts after 24 hours
    $_SESSION['last_reset'] = $now;
    $_SESSION['present_count'] = 0;
    $_SESSION['absent_count'] = 0;
    $_SESSION['late_count'] = 0; // Reset late count
}

// Fetch the faculty's sections
$user_id = $_SESSION['user_id']; // Assuming the faculty's user ID is stored in the session
$sections = get_faculty_sections($user_id);

// Initialize counters
$total_present = 0;
$total_absent = 0;
$total_late = 0; // Initialize late counter
$total_students = 0;

foreach ($sections as $section) {
    // Get total students in the section
    $students_in_section = get_students_in_section($section['section_id']);
    $total_students_in_section = count($students_in_section); // Get the number of students in this section

    // Get attendance data for the section
    $attendance = get_section_attendance_late($section['section_id']);
    $present_count = 0;
    $late_count = 0; // Initialize late count for this section

    foreach ($attendance as $entry) {
        if ($entry['timestamp_in']) {
            $present_count++;

            // Check if the student was late
            $timestamp_in = strtotime($entry['timestamp_in']);
            $today_start = strtotime(date('Y-m-d 06:30:00')); // Start of today (6:30 AM)
            $today_end = strtotime(date('Y-m-d 19:00:00'));   // End of today (7:00 PM)

            if ($timestamp_in > $today_start && $timestamp_in <= $today_end) {
                $late_count++; // Increment late count
            }
        }
    }

    // Calculate absent count
    $absent_count = $total_students_in_section - $present_count;

    // Update total counts
    $total_present += $present_count;
    $total_absent += $absent_count;
    $total_late += $late_count; // Add late count for this section
    $total_students += $total_students_in_section; // Add total students for this section
}
if (!empty($sections)) {
    // Get the first section (or modify for multiple sections)
    $faculty_section = $sections[0]['section_id'];

    // Set default date range (last 7 days)
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d', strtotime('-7 days'));
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');

    // Fetch attendance data
    $attendanceData = get_section_attendance_chart($faculty_section, $start_date, $end_date);
    $presentData = $attendanceData['present'];
    $absentData = $attendanceData['absent'];
    $totalStudents = count(get_students_in_section($faculty_section)); // Total students in section

    // Convert PHP arrays to JSON for JavaScript
    $dates = json_encode(array_keys($presentData));
    $presentCounts = json_encode(array_values($presentData));
    $absentCounts = json_encode(array_values($absentData));
} else {
    // Handle case when faculty has no sections
    $presentData = $absentData = $dates = $presentCounts = $absentCounts = json_encode([]);
    $totalStudents = 0;
}

?>

<?php include_once('layouts/header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>


<div class="dashboard-container">
    <div class="date-time">
        <span><?php echo date("F j, Y, g:i a"); ?></span>
    </div>
    <!-- Cards for Total Present, Absent, and Students -->
    <div class="dashboard">
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="card-title">Total Present <i class="fas fa-user-check"></i> </div>
                <p><?php echo $total_present; ?></p>
            </div>
            <div class="dashboard-card">
                <div class="card-title">Total Absent <i class="fas fa-user-times"></i> </div>
                <p><?php echo $total_absent; ?></p>
            </div>
            <div class="dashboard-card">
                <div class="card-title">Late <i class="fas fa-clock"></i> </div>
                <p><?php echo $total_late; ?></p>
            </div>
            <div class="dashboard-card">
                <div class="card-title">Total Students <i class="fas fa-users"></i> </div>
                <p><?php echo $total_students; ?></p>
            </div>
        </div>
    </div>

    <!-- Display Sections and Attendance Tables -->
    <div class="dashboard">
        <!-- Date Filter Form -->

        <!-- Flex container for chart and table -->
        <div class="chart-table-container">

            <!-- Left Side: Chart -->
            <div class="chart-container">
                <h2>
                    Attendance Report
                    <?php if (!empty($sections)) : ?>
                    of <?= $sections[0]['section_name'] ?>
                    <?php else: ?>
                    (No section assigned)
                    <?php endif; ?>
                </h2>

                <form method="POST">
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>" required>

                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>" required>

                    <button type="submit">Filter</button>
                </form>
                <canvas id="attendanceChart"></canvas>
            </div>

            <!-- Right Side: Attendance Table -->
            <div class="table-container">
                <?php if (!empty($sections)): ?>
                <?php foreach ($sections as $section): 
            // Get today's attendance records for this section
            $attendance = get_recent_section_attendance($section['section_id']);

            // Check if there is any attendance data for today
            $has_attendance_today = false;
            foreach ($attendance as $entry) {
                if ($entry['timestamp_in']) {
                    $has_attendance_today = true;
                    break;
                }
            }

            // Get total students in the section
            $students_in_section = get_students_in_section($section['section_id']);
            $total_students_in_section = count($students_in_section);
        ?>
                <div class="section-container">
                    <div class="attendance">
                        <div class="table-title">Today Attendance</div>
                        <a class="view-details"
                            href="sections.php?section_id=<?php echo $section['section_id']; ?>">View Details</a>
                        <div class="table">
                            <div class="table-header">
                                <div class="header__item">Student Name</div>
                                <div class="header__item">Time In</div>
                                <div class="header__item">Time Out</div>
                                <div class="header__item">Date</div>
                                <div class="header__item">Status</div>
                            </div>
                            <div class="table-content"
                                id="table-content-sections-<?php echo $section['section_id']; ?>">
                                <?php if (!$has_attendance_today): ?>
                                <div class="table-row">
                                    <div class="table-data" colspan="5">No attendance today</div>
                                </div>
                                <?php else: ?>
                                <?php foreach ($attendance as $entry): ?>
                                <?php if ($entry['timestamp_in']): ?>
                                <div class="table-row">
                                    <div class="table-data"><?php echo $entry['student_name']; ?></div>
                                    <div class="table-data">
                                        <?php echo date('g:i A', strtotime($entry['timestamp_in'])); ?>
                                    </div>
                                    <div class="table-data">
                                        <?php echo $entry['timestamp_out'] ? date('g:i A', strtotime($entry['timestamp_out'])) : 'N/A'; ?>
                                    </div>
                                    <div class="table-data">
                                        <?php echo date('F d, Y', strtotime($entry['timestamp_in'])); ?>
                                    </div>
                                    <div class="table-data">
                                        <?php
                                    $timestamp_in = strtotime($entry['timestamp_in']);
                                    $today_start = strtotime(date('Y-m-d 06:30:00'));
                                    $today_end = strtotime(date('Y-m-d 19:00:00'));

                                    if ($timestamp_in > $today_start && $timestamp_in <= $today_end) {
                                        echo 'Late (' . $entry['late_in_hours_minutes'] . ')';
                                    } elseif ($timestamp_in <= $today_start) {
                                        echo 'Present';
                                    }
                                    ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="section-container">
                    <div class="attendance">
                        <div class="table-title">Today Attendance</div>
                        <div class="table">
                            <div class="table-row">
                                <div class="table-data" colspan="5">No section assigned</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>



        </div>
    </div>
</div>


<style>
body {
    overflow: auto;
    /* Allow scrolling if content overflows */
    font-family: Arial, sans-serif;
    /* Ensure a consistent font */
    background-color: #f8f9fa;
    /* Light background for the page */
}

/* Pagination Styles */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    /* Increased margin for better spacing */
    gap: 10px;
}

.pagination button {
    padding: 8px 16px;
    /* Slightly larger padding for better clickability */
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f9f9f9;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
    font-size: 14px;
    /* Consistent font size */
}

.pagination button.active {
    background-color: #2a2f3b;
    color: white;
    font-weight: bold;
}

.pagination button:disabled {
    background-color: #e0e0e0;
    cursor: not-allowed;
    color: #999;
    /* Gray out disabled buttons */
}

/* Dashboard Container */
.dashboard-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    /* Center the container */
    padding: 20px;
    /* Add padding for better spacing */
}

h2 {
    text-decoration: none;
    color: #333;
    font-size: 24px;
    margin-bottom: 20px;
    font-weight: 600;
    /* Slightly bolder heading */
}

/* Dashboard Layout */
.dashboard {
    display: flex;
    flex-direction: column;
    gap: 20px;
    /* Add consistent spacing between sections */
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.dashboard-card {
    background: #690B22;
    color: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    cursor: pointer;
    text-align: center;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    /* Enhanced shadow on hover */
}

.card-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

.view-details {
    margin: 10px 0px 10px 0px;
    padding: 8px 15px;
    float: right;
    border: 2px solid #690B22;
    border-radius: 4px;
    font-weight: 700;
    font-size: 12px;
    color: #F8FAFF;
    background: #690B22;
    text-transform: uppercase;
    transition: all 0.3s;
    text-decoration: none;
}

.view-details:hover {
    background: #F8FAFF;
    color: #690B22;
    text-decoration: none;
}



.table-title {
    text-decoration: none;
    margin: 10px 0px 10px 0px;
    padding: 8px 15px;
    font-size: 15px;
    font-weight: bold;
    float: left;
}

/* Table Styles */
.table {
    margin: 20px 0;
    /* Increased margin for better spacing */
    width: 100%;
    border: 2px solid var(--color-form-highlight);
    border-collapse: collapse;
    background-color: #fff;
    /* White background for better contrast */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.table-header {
    display: flex;
    width: 100%;
    background: var(--secondary-color);
    color: #fff;
    font-weight: bold;
    font-size: 16px;
    text-transform: uppercase;
    justify-content: center;
    align-items: center;
}

.header__item {
    flex: 1;
    text-align: center;
    padding: 12px 15px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.table-row {
    display: flex;
    width: 100%;
    padding: 12px 0;
    transition: background-color 0.3s ease;
}

.table-row:nth-of-type(odd) {
    background-color: var(--color-form-highlight, #f2f2f2);
}

.table-row:hover {
    background-color: #EEF5FF
        /* Highlight on hover */
}

.table-data,
.header__item {
    flex: 1;
    padding: 12px 15px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

.table-data.email-column,
.table-data.address-column,
.table-data.name-column,
.table-data.date-column,
.header__item.email-header,
.header__item.address-header,
.header__item.name-header,
.header__item.date-header {
    flex: 2;
    text-align: left;
    white-space: normal;
}

.table-data:first-child,
.header__item:first-child {
    border-left: none;
}

.table-content {
    max-height: 500px;
    overflow-y: auto;
    /* Allow vertical scrolling if needed */
}

.table-content::-webkit-scrollbar {
    width: 10px;
}

.table-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 5px;
}

.table-content::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Date/Time Display */
.date-time {
    text-align: right;
    margin-bottom: 20px;
    font-size: 1.2rem;
    color: #444;
}

.date-time span {
    margin-left: 10px;
    font-weight: bold;
}

/* Chart and Table Container */
.chart-table-container {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.chart-container {
    flex: 1;
    background-color: #fff;
    /* White background for the chart */
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.table-container {
    flex: 1;
    overflow-x: auto;
    /* Allow horizontal scrolling for the table */
    background-color: #fff;
    /* White background for the table */
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Responsive Styles */
@media (max-width: 768px) {
    .dashboard {
        gap: 15px;
    }

    .dashboard-cards {
        grid-template-columns: 1fr;
    }

    .dashboard-card {
        padding: 15px;
        font-size: 14px;
    }

    .card-title {
        font-size: 16px;
    }

    .view-details {
        margin: 10px auto;
        padding: 8px 16px;
        font-size: 12px;
    }

    /* Make buttons smaller */
    .pagination button {
        padding: 6px 12px;
        font-size: 12px;
    }

    .chart-table-container {
        flex-direction: column;
    }

    .chart-container,
    .table-container {
        width: 100%;
        padding: 15px;
    }

    .date-time {
        display: none;
    }

    .pagination {
        flex-wrap: wrap;
        gap: 5px;
    }

    .pagination button {
        flex: 1 1 auto;
        min-width: 40px;
        font-size: 12px;
        padding: 6px 10px;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById("attendanceChart").getContext("2d");

    const attendanceChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: <?= $dates ?>,
            datasets: [{
                    label: "Present",
                    backgroundColor: "#7886C7",
                    borderColor: "#2D336B",
                    borderWidth: 1,
                    data: <?= $presentCounts ?>
                },
                {
                    label: "Absent",
                    backgroundColor: "#F7374F",
                    borderColor: "#88304E",
                    borderWidth: 1,
                    data: <?= $absentCounts ?>
                }
            ]
        },
        options: {
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Number of Students'
                    },
                    beginAtZero: true,
                    max: <?= $totalStudents ?> // Adjusted to total students in section
                }
            }
        }
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const entriesPerPage = 3; // Number of entries per page

    function paginateTable(contentId, paginationId) {
        const tableContent = document.getElementById(contentId);
        let rows = Array.from(tableContent.getElementsByClassName('table-row'));
        const paginationControls = document.getElementById(paginationId);
        const totalPages = Math.ceil(rows.length / entriesPerPage);

        let currentPage = 1;

        function renderPage(page) {
            rows.forEach((row) => {
                row.style.display = 'none';
            });

            const start = (page - 1) * entriesPerPage;
            const end = start + entriesPerPage;
            rows.slice(start, end).forEach((row) => {
                row.style.display = 'flex';
            });

            Array.from(paginationControls.children).forEach((btn, index) => {
                btn.classList.toggle('active', index === page);
            });
        }

        function renderPagination() {
            paginationControls.innerHTML = '';

            const prevBtn = document.createElement('button');
            prevBtn.textContent = 'Previous';
            prevBtn.disabled = currentPage === 1;
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage -= 1;
                    renderPage(currentPage);
                    renderPagination();
                }
            });
            paginationControls.appendChild(prevBtn);

            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.textContent = i;
                pageBtn.className = currentPage === i ? 'active' : '';
                pageBtn.addEventListener('click', () => {
                    currentPage = i;
                    renderPage(currentPage);
                    renderPagination();
                });
                paginationControls.appendChild(pageBtn);
            }

            const nextBtn = document.createElement('button');
            nextBtn.textContent = 'Next';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage += 1;
                    renderPage(currentPage);
                    renderPagination();
                }
            });
            paginationControls.appendChild(nextBtn);
        }

        function updatePagination() {
            rows = Array.from(tableContent.getElementsByClassName('table-row'));
            currentPage = 1;
            renderPagination();
            renderPage(currentPage);
        }

        renderPagination();
        renderPage(currentPage);

        return updatePagination;
    }

    // Initialize pagination for each section's table
    <?php foreach ($sections as $section): ?>
    const updatePagination<?php echo $section['section_id']; ?> = paginateTable(
        'table-content-sections-<?php echo $section['section_id']; ?>',
        'pagination-controls-sections-<?php echo $section['section_id']; ?>'
    );
    <?php endforeach; ?>
});
</script>

<?php include_once('layouts/footer.php'); ?>