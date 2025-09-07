<?php
$page_title = 'Daily Attendance';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);
?>
<?php
$year  = date('Y');
$month = date('m');
$monthly_attendances = monthlyattendances($year);
$d_attendances = dailyattendances($year,$month);
$attendances = find_all_attendances(); // This function should include both QR code and regular attendances
$all_categories = find_all('categories');
?>
<?php include_once('layouts/header.php'); ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="libs/css/roles.css" />
</head>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<button class="back_button" style="top: 20px;position: absolute;" onclick="window.history.back()">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
        <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>
    Back
</button>

<div id="Daily_attendances" class="content-section active" style="display: block;">
    <div class="action-buttons-container">
        <div class="search-bar-container">
            <input type="text" id="search-bar-daily-report" class="search-bar" placeholder="Search Daily Attendance...">
        </div>

        <!-- Toggle button for Daily Attendance Filters -->
        <button class="toggle-filter-btn" data-target="daily-attendance-filter-container">Show Filters</button>

        <!-- Daily Attendance Filter Container -->
        <div id="daily-attendance-filter-container" class="filter-container">
            <!-- Grade Level Filter -->
            <label for="daily-grade-level-filter">Grade Level:</label>
            <div class="select">
                <div class="selected" data-default="All">
                    <span id="daily-grade-level-selected">All</span>
                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                        <path
                            d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                        </path>
                    </svg>
                </div>
                <div class="options">
                    <div title="all">
                        <input id="grade-level-all" name="grade-level-option" type="radio" value="" checked />
                        <label class="option" for="grade-level-all">All</label>
                    </div>
                    <?php foreach (array_unique(array_column($d_attendances, 'grade_level')) as $grade_level): ?>
                    <div title="<?php echo remove_junk($grade_level); ?>">
                        <input id="grade-level-<?php echo remove_junk($grade_level); ?>" name="grade-level-option"
                            type="radio" value="<?php echo remove_junk($grade_level); ?>" />
                        <label class="option"
                            for="grade-level-<?php echo remove_junk($grade_level); ?>"><?php echo remove_junk($grade_level); ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Section Filter -->
            <label for="daily-section-filter">Section:</label>
            <div class="select">
                <div class="selected" data-default="All">
                    <span id="daily-section-selected">All</span>
                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                        <path
                            d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                        </path>
                    </svg>
                </div>
                <div class="options">
                    <div title="all">
                        <input id="section-all" name="section-option" type="radio" value="" checked />
                        <label class="option" for="section-all">All</label>
                    </div>
                    <?php foreach (array_unique(array_column($d_attendances, 'categorie')) as $section): ?>
                    <div title="<?php echo remove_junk($section); ?>">
                        <input id="section-<?php echo remove_junk($section); ?>" name="section-option" type="radio"
                            value="<?php echo remove_junk($section); ?>" />
                        <label class="option"
                            for="section-<?php echo remove_junk($section); ?>"><?php echo remove_junk($section); ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <a class="export_button" id="export-attendance-btn">
            <i class="fa-solid fa-download"></i>
            <span class="export_button__text">Export</span>
        </a>
    </div>



    <div class="table">
        <div class="table-header">
            <div class="header__item ">No.</div>
            <div class="header__item name-header">Name</div>
            <div class="header__item">Strand</div>
            <div class="header__item">Grade Level</div>
            <div class="header__item">Section</div>
            <div class="header__item">Time In</div>
            <div class="header__item">Time Out</div>
            <div class="header__item">Late</div>
        </div>
        <div class="table-content" id="table-content-daily">
            <?php foreach ($d_attendances as $attendances): ?>
            <div class="table-row" data-grade-level="<?php echo remove_junk($attendances['grade_level']); ?>"
                data-section="<?php echo remove_junk($attendances['categorie']); ?>"
                data-date="<?php echo remove_junk($attendances['date']); ?>">
                <div class="table-data"><?php echo count_id(); ?></div>
                <div class="table-data name-column"><?php echo remove_junk($attendances['name']); ?></div>
                <div class="table-data"><?php echo remove_junk($attendances['strand']); ?></div>
                <div class="table-data"><?php echo remove_junk($attendances['grade_level']); ?></div>
                <div class="table-data"><?php echo remove_junk($attendances['categorie']); ?></div>
                <div class="table-data"><?php echo remove_junk($attendances['check_in_time']); ?></div>
                <div class="table-data"><?php echo remove_junk($attendances['check_out_time']); ?></div>
                <div class="table-data"><?php echo remove_junk($attendances['late']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('export-attendance-btn').addEventListener('click', exportAttendanceData);

    function exportAttendanceData() {
        // Get visible rows (excluding filtered out rows)
        const visibleRows = document.querySelectorAll('.table-row:not([style*="display: none"])');

        // CSV headers
        let csvContent = "No.,Name,Grade Level,Section,Gender,Time In,Time Out,Late";

        visibleRows.forEach(row => {
            const cells = row.querySelectorAll('.table-data');
            // Skip the Actions column if present
            const rowData = [
                cells[0].textContent.trim(), // No.
                cells[1].textContent.trim(), // Name
                cells[2].textContent.trim(), // Grade Level
                cells[3].textContent.trim(), // Section
                cells[4].textContent.trim(), // Gender
                cells[5].textContent.trim(), // Time In
                cells[6].textContent.trim(), // Time Out
                cells[7].textContent.trim(), // Late
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
    const tableContent = document.getElementById('table-content-daily');

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
</script>

<?php include_once('layouts/footer.php'); ?>