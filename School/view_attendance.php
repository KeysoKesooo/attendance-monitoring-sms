<?php
$page_title = 'Attendance';

require_once('includes/load.php');

if (!isset($_SESSION['user_id'])) {
    die("Please log in as a parent to view children's attendance.");
}

$parentId = $_SESSION['user_id'];

if (!isset($_GET['student_id'])) {
    die("Please select a child to view their attendance.");
}

$studentId = $_GET['student_id'];

// Validate parent's access to child
$childCheckSql = "SELECT s.id AS student_id, s.name AS student_name FROM student s
JOIN users u ON s.phone_id = u.id
WHERE u.user_level = 3 AND u.id = $parentId AND s.id = $studentId";
$childCheckResult = $db->query($childCheckSql);

if ($childCheckResult->num_rows === 0) {
    die("You are not authorized to view this child's attendance.");
}

$child = $childCheckResult->fetch_assoc();
$studentName = $child['student_name'];

// Get selected date range (defaults to current month)
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Query to fetch attendance records in the selected range
$attendanceSql = "SELECT DATE(a.timestamp_in) AS date,
TIME_FORMAT(a.timestamp_in, '%h:%i %p') AS time_in,
TIME_FORMAT(a.timestamp_out, '%h:%i %p') AS time_out,
a.late_in_hours_minutes
FROM attendances a
WHERE a.student_id = $studentId
AND DATE(a.timestamp_in) BETWEEN '$startDate' AND '$endDate'
ORDER BY a.timestamp_in DESC";

$attendanceResult = $db->query($attendanceSql);

// Calculate attendance counts for each date
$attendanceCounts = [];
$dates = [];
$lateCount = 0;
$presentCount = 0;
$absentCount = 0;

// Get all dates in the range
$period = new DatePeriod(
    new DateTime($startDate),
    new DateInterval('P1D'),
    new DateTime($endDate)
);

$allDates = [];
foreach ($period as $date) {
    $allDates[] = $date->format('Y-m-d');
}

// Initialize attendance counts for all dates with 0
$attendanceCounts = [];
foreach ($allDates as $date) {
    $attendanceCounts[$date] = 0;
}

// Process attendance records and track only one attendance type (Present, Late, or Absent) per day
while ($attendance = $attendanceResult->fetch_assoc()) {
    $date = $attendance['date'];

    if (!empty($attendance['late_in_hours_minutes']) && $attendance['late_in_hours_minutes'] !== "00:00") {
        $attendanceCounts[$date] = 2; // Late
        $lateCount++;
    } elseif ($attendanceCounts[$date] == 0) {
        $attendanceCounts[$date] = 1; // Present
        $presentCount++;
    }
}

// Prepare data for JavaScript
$dates = json_encode(array_keys($attendanceCounts));
$attendanceData = json_encode(array_map(fn($count) => $count == 1 ? 1 : 0, $attendanceCounts)); // Present data
$lateData = json_encode(array_map(fn($count) => $count == 2 ? 1 : 0, $attendanceCounts)); // Late data
$absentData = json_encode(array_map(fn($count) => $count == 0 ? 1 : 0, $attendanceCounts)); // Absent data

// Calculate absent counts
foreach ($allDates as $date) {
    if ($attendanceCounts[$date] == 0) {
        $absentCount++;
    }
}

// Calculate the total number of records
$totalRecords = $lateCount + $presentCount + $absentCount;

// Calculate percentages only if there are records
if ($totalRecords > 0) {
    $latePercentage = ($lateCount / $totalRecords) * 100;
    $presentPercentage = ($presentCount / $totalRecords) * 100;
    $absentPercentage = ($absentCount / $totalRecords) * 100;
} else {
    // If there are no records, set all percentages to 0
    $latePercentage = 0;
    $presentPercentage = 0;
    $absentPercentage = 0;
}

$attendanceResult->data_seek(0);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" type="text/css" href="libs/css/main_dash.css" />
    <style>
    body {
        overflow: auto;
        /* Allow scrolling if content overflows */
        font-family: Arial, sans-serif;
        /* Ensure a consistent font */
        background-color: #f8f9fa;
        /* Light background for the page */
        margin: 0;
        padding: 0;

    }

    .attendance-container {
        margin: -20px;
        font-family: "Poppins", sans-serif;
    }

    .attendance-container h2 {
        margin-bottom: 20px;
        font-size: 1.5em;
        color: #333;
        display: inline-block;
    }

    h2 {
        background-color: #ffffff;
        color: #000;
        font-size: 24px;
        font-weight: bold;
        padding: 15px 25px;
        border: 2px solid #ccc;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: inline-block;
    }




    #percentageChart {
        margin-left: 140px;
    }

    .charts {
        height: 400px;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }

    .chart-container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
        flex: 1 1 calc(50% - 20px);
        min-width: 300px;
    }

    .attendance-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .select-month-container {
        display: inline-block;
        padding: 0px 10px;
        position: relative;
    }

    .select-month-container input {
        width: 100%;
        padding: 10px 10px;
        font-size: 1em;
        border-radius: 4px;
        border: none;
        background-color: #2a2f3b;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .select-month-container input:focus,
    .select-month-container input:hover {
        background-color: #333;
    }

    .attendance-records {
        margin-top: 10px;
    }

    .attendance-date h2 {
        margin: 15px 0 10px;
        font-size: 1.2em;
        color: #444;
    }

    .attendance-entry {
        display: flex;
        gap: 15px;
        margin-bottom: 10px;
    }

    .card {
        flex: 1;
        padding: 15px;
        border-radius: 8px;
        background-color: #ffffff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: all 0.3s ease;
    }

    .card span {
        display: block;
        font-size: 1em;
        color: #555;
    }

    .card strong {
        display: block;
        margin-top: 5px;
        font-size: 1.2em;
        color: #333;
    }

    .time-in {
        border: 2px solid #4caf50;
        background-color: #e8f5e9;
    }

    .time-out {
        border: 2px solid #f44336;
        background-color: #fdecea;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    .no-attendance {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 40vh;
        text-align: center;
        font-size: 1.2em;
        color: #666;
    }

    @media screen and (max-width: 768px) {
        .attendance-container {
            padding: 10px;
            margin: 0;
        }

        .attendance-container h2,
        .attendance-date h2 {
            font-size: 1.2rem;
            text-align: center;
            padding: 10px 15px;
        }

        .charts {
            flex-direction: column;
            height: auto;
            gap: 20px;
        }

        #percentageChart {
            margin-left: 0;
        }

        .chart-container {
            min-width: 100%;
            padding: 15px;
        }

        .attendance-header {
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .select-month-container {
            display: block;
            width: 100%;
            padding: 0;
            top: auto;
            right: auto;
        }

        .select-month-container input {
            width: 100%;
            font-size: 1rem;
            margin: 10px 0;
            padding: 10px;
        }

        .attendance-entry {
            flex-direction: column;
            gap: 10px;
        }

        .card {
            width: 100%;
        }

        .no-attendance {
            font-size: 1rem;
            height: auto;
            padding: 20px;
        }
    }
    </style>
</head>
<?php include_once 'layouts/header.php'; ?>

<body>

    <button class="back_button" onclick="window.history.back()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
            <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
        Back
    </button>

    <div class="attendance-container">
        <h2><?php echo remove_junk(ucfirst($studentName)); ?> Attendance</h2>
        <div class="charts">
            <!-- Date Range Filter Form -->
            <div class="chart-container">
                <form method="get">
                    <h3>Attendance from <?php echo date('F j, Y', strtotime($startDate)); ?> to
                        <?php echo date('F j, Y', strtotime($endDate)); ?></h3>
                    <input type="hidden" name="student_id" value="<?php echo $studentId; ?>">
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" value="<?php echo $startDate; ?>">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" value="<?php echo $endDate; ?>">
                    <button type="submit">Filter</button>
                </form>
                <canvas id="attendanceChart"></canvas>
            </div>

            <div class="chart-container">
                <canvas id="percentageChart"></canvas>
            </div>
        </div>

        <!-- Attendance Records -->
        <?php if ($attendanceResult->num_rows > 0): ?>
        <div class="attendance-records">

            <a class="export_button"
                href="export_excel.php?type=student_attendance&student_id=<?php echo $studentId; ?>&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>">
                <i class="fa-solid fa-download"></i>
                <span class="export_button__text">Export</span>
            </a>

            <?php 
                $currentDate = '';
                while ($attendance = $attendanceResult->fetch_assoc()): 
                    if ($currentDate != $attendance['date']):
                        $currentDate = $attendance['date'];
            ?>
            <div class="attendance-date">

                <h2><?php echo date('F d, Y', strtotime($currentDate)); ?></h2>
            </div>
            <?php endif; ?>
            <div class="attendance-entry">
                <div class="card time-in">
                    <span>In:</span>
                    <strong><?php echo $attendance['time_in']; ?></strong>
                </div>
                <div class="card time-out">
                    <span>Out:</span>
                    <strong><?php echo $attendance['time_out']; ?></strong>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="no-attendance">No attendance records found for the selected date.</div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById("attendanceChart").getContext("2d");
        var attendanceChart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: <?php echo $dates; ?>,
                datasets: [{
                        label: "Present",
                        data: <?php echo $attendanceData; ?>,
                        backgroundColor: "#7886C7"
                    },
                    {
                        label: "Absent",
                        data: <?php echo $absentData; ?>,
                        backgroundColor: "#F7374F"
                    },
                    {
                        label: "Late",
                        data: <?php echo $lateData; ?>,
                        backgroundColor: "#FFD95F"
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Pie Chart for Percentages
        const percentageCtx = document.getElementById('percentageChart').getContext('2d');
        const percentageData = {
            labels: ['Late', 'Present', 'Absent'],
            datasets: [{
                data: [<?php echo $latePercentage; ?>, <?php echo $presentPercentage; ?>,
                    <?php echo $absentPercentage; ?>
                ],
                backgroundColor: [
                    '#FFD95F',
                    '#7886C7',
                    '#F7374F'

                ],
                borderColor: [
                    '#FFEFC8',
                    '#2D336B',
                    '#88304E'
                ],
                borderWidth: 1
            }]
        };

        new Chart(percentageCtx, {
            type: 'pie',
            data: percentageData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.raw !== null) {
                                    label += context.raw.toFixed(2) + '%';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
    </script>
</body>

<?php include_once('layouts/footer.php'); ?>

</html>