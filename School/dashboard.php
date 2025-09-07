<?php 
$page_title = 'Dashboard';
require_once('includes/load.php');
page_require_level(1);

// Get gender data
$genderData = getGenderCounts();
$maleCount = $genderData['Male'] ?? 0;
$femaleCount = $genderData['Female'] ?? 0;

// Handle the selected start and end date
$selectedStartDate = isset($_GET['start_date']) ? date('Y-m-d', strtotime($_GET['start_date'])) : date('Y-m-01');
$selectedEndDate = isset($_GET['end_date']) ? date('Y-m-d', strtotime($_GET['end_date'])) : date('Y-m-t');

// Get gender data for the selected date range
$genderData = getGenderCountsByDateRange($selectedStartDate, $selectedEndDate);
$maleCountbyDate = $genderData['Male'] ?? 0;
$femaleCountbyDate = $genderData['Female'] ?? 0;

// Get the attendance and absence data for the selected date range
$attendanceData = getTotalStudentsByDateRange($selectedStartDate, $selectedEndDate);
$absenceData = getAbsentStudentsByDateRange($selectedStartDate, $selectedEndDate);

// Generate complete date range
$start = new DateTime($selectedStartDate);
$end = new DateTime($selectedEndDate);
$end->modify('+1 day'); // Include end date
$interval = new DateInterval('P1D');
$dateRange = new DatePeriod($start, $interval, $end);

// Initialize arrays with all dates set to 0
$allDates = [];
foreach ($dateRange as $date) {
    $dateStr = $date->format('Y-m-d');
    $allDates[$dateStr] = 0;
}

// Merge with actual data (present and absent)
$presentCounts = array_merge($allDates, $attendanceData);
$absentCounts = array_merge($allDates, $absenceData);

// Sort by date
ksort($presentCounts);
ksort($absentCounts);

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
<style>
.dashboard-container {
    margin: 0 auto;
}

.dashboard {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    margin-bottom: 30px;
}

/* Style for the date/time display */
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

.dashboard-card {
    background: #690B22;
    color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
    transition: transform 0.3s ease;
    min-width: 200px;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.dashboard-card h2 {
    font-weight: bold;
    color: var(--main-bg-color);
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
}

.dashboard-card h3 {
    color: var(--main-bg-color);
    font-size: 2rem;
    font-weight: bold;
}

.filter-form {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-bottom: 10px;
}

.filter-form select {
    padding: 5px 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    transition: box-shadow 0.3s ease;
}

.filter-form select:hover,
.filter-form select:focus {
    box-shadow: 0 0 5px #A3D1C6;
    outline: none;
}

.charts {
    height: 380px;
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

.chart-container h2 {
    font-size: 1.5rem;
    margin-bottom: 15px;
    text-align: center;
}

canvas {
    width: 100%;
    max-height: 280px;
}

@media (max-width: 768px) {
    .date-time {
        text-align: center;
        font-size: 1rem;
    }

    .dashboard {
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }

    .dashboard-card {
        width: 90%;
        min-width: unset;
    }

    .filter-form {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }

    .charts {
        flex-direction: column;
        gap: 20px;
        height: auto;
        width: 100%;
        align-items: center;
    }

    .chart-container {
        width: 90%;
        min-width: unset;
        padding: 15px;
    }

    canvas {
        width: 100% !important;
        height: 250px !important;
        max-height: 300px;
    }

    .date-time {
        display: none;
    }
}
</style>


<body>
    <div class="dashboard-container">
        <div class="date-time">
            <span><?php echo date("F j, Y, g:i a"); ?></span>
        </div>
        <!-- Dashboard Summary -->

        <div class="dashboard">

            <div class="dashboard-card">
                <h2>Present Students Today <i class="fas fa-user-check"></i></h2>
                <h3><?php echo getTotalStudentsToday(); ?></h3>
            </div>
            <div class="dashboard-card">
                <h2>Absent Students Today <i class="fas fa-user-times"></i></h2>
                <h3><?php echo getAbsentStudentsToday(); ?></h3>
            </div>
            <div class="dashboard-card">
                <h2>Male Students <i class="fas fa-male"></i> </h2>
                <h3><?php echo $maleCount; ?></h3>
            </div>
            <div class="dashboard-card">
                <h2>Female Students <i class="fas fa-female"></i> </h2>
                <h3><?php echo $femaleCount; ?></h3>
            </div>
            <div class="dashboard-card">
                <h2>Total Students <i class="fas fa-users"></i> </h2>
                <h3><?php echo countAllStudents(); ?></h3>
            </div>
        </div>

        <!-- Charts Section: Bar Chart and Pie Chart -->
        <div class="charts">
            <!-- Attendance Chart -->
            <div class="chart-container">
                <div class="chart-header">
                    <h2>Attendance from <?php echo date('F j, Y', strtotime($selectedStartDate)); ?> to
                        <?php echo date('F j, Y', strtotime($selectedEndDate)); ?></h2>
                    <form method="GET" class="filter-form" id="attendanceFilterForm">
                        <label for="start_date">From:</label>
                        <input type="date" id="start_date" name="start_date" value="<?= $selectedStartDate ?>"
                            onchange="this.form.submit()">

                        <label for="end_date">To:</label>
                        <input type="date" id="end_date" name="end_date" value="<?= $selectedEndDate ?>"
                            onchange="this.form.submit()">
                    </form>

                </div>
                <canvas id="attendanceChart"></canvas>
            </div>

            <!-- Gender Chart -->
            <div class="chart-container">
                <h2>Gender Distribution from <?php echo date('F j, Y', strtotime($selectedStartDate)); ?> to
                    <?php echo date('F j, Y', strtotime($selectedEndDate)); ?></h2>
                <canvas id="genderChart"></canvas>
            </div>
        </div>
    </div>

    <script>
    // Attendance Chart
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(attendanceCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($presentCounts)) ?>,
            datasets: [{
                    label: 'Present Students',
                    data: <?= json_encode(array_values($presentCounts)) ?>,
                    backgroundColor: '#7886C7',
                    borderColor: '#2D336B',
                    borderWidth: 1
                },
                {
                    label: 'Absent Students',
                    data: <?= json_encode(array_values($absentCounts)) ?>,
                    backgroundColor: '#F7374F',
                    borderColor: '#88304E',
                    borderWidth: 1
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
                    max: <?= countAllStudents() ?>
                }
            }
        }
    });

    // Gender Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    const genderChart = new Chart(genderCtx, {
        type: 'pie',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                data: [<?= $maleCountbyDate?>, <?= $femaleCountbyDate?>],
                backgroundColor: ['#4335A7', '#FFCFEF'],
                hoverOffset: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allow size adjustments
            aspectRatio: 1, // This ensures the pie chart remains circular
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
    </script>
</body>

</html>

<?php include_once('layouts/footer.php'); ?>