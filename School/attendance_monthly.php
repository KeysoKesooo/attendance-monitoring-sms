<?php
  $page_title = 'Monthly Attendance';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
   page_require_level(2);
?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $student_id = $_POST['student_id'];
  $month      = $_POST['month'];

  // Format for SQL query
  $monthYear = date('Y-m', strtotime($month));

  $records = find_by_sql("SELECT DATE(timestamp_in) AS date, timestamp_in, timestamp_out
                          FROM attendances 
                          WHERE student_id = '{$student_id}'
                          AND DATE_FORMAT(timestamp_in, '%Y-%m') = '{$monthYear}'
                          ORDER BY timestamp_in ASC");

  // Send CSV headers
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="attendance_' . $monthYear . '.csv"');

  $output = fopen('php://output', 'w');
  fputcsv($output, ['Date', 'Time In', 'Time Out']);

  foreach ($records as $row) {
    fputcsv($output, [$row['date'], $row['timestamp_in'], $row['timestamp_out']]);
  }

  fclose($output);
  exit;
}

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
    <link rel="stylesheet" type="" href="libs/css/roles.css" />
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

<div id="Monthly_attendances" class="content-section">
    <div class="action-buttons-container">
        <div class="search-bar-container">
            <input type="text" id="search-bar-monthly-report" class="search-bar"
                placeholder="Search Monthly Attendance...">
        </div>
        <!-- Toggle button for Monthly Attendance Filters -->
        <button class="toggle-filter-btn" data-target="monthly-attendance-filter-container">Show Filters</button>

        <!-- Monthly Attendance Filter Container -->
        <div id="monthly-attendance-filter-container" class="filter-container">
            <!-- Grade Level Filter -->
            <label for="monthly-grade-level-filter">Grade Level:</label>
            <div class="select">
                <div class="selected" data-default="All">
                    <span id="monthly-grade-level-selected">All</span>
                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                        <path
                            d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                        </path>
                    </svg>
                </div>
                <div class="options">
                    <div title="all">
                        <input id="monthly-grade-level-all" name="monthly-grade-level-option" type="radio" value=""
                            checked />
                        <label class="option" for="monthly-grade-level-all">All</label>
                    </div>
                    <?php foreach (array_unique(array_map('remove_junk', array_column($monthly_attendances, 'grade_level'))) as $grade_level): ?>
                    <div title="<?php echo htmlspecialchars($grade_level); ?>">
                        <input id="monthly-grade-level-<?php echo htmlspecialchars($grade_level); ?>"
                            name="monthly-grade-level-option" type="radio"
                            value="<?php echo htmlspecialchars($grade_level); ?>" />
                        <label class="option"
                            for="monthly-grade-level-<?php echo htmlspecialchars($grade_level); ?>"><?php echo htmlspecialchars($grade_level); ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Section Filter -->
            <label for="monthly-section-filter">Section:</label>
            <div class="select">
                <div class="selected" data-default="All">
                    <span id="monthly-section-selected">All</span>
                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                        <path
                            d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                        </path>
                    </svg>
                </div>
                <div class="options">
                    <div title="all">
                        <input id="monthly-section-all" name="monthly-section-option" type="radio" value="" checked />
                        <label class="option" for="monthly-section-all">All</label>
                    </div>
                    <?php foreach (array_unique(array_map('remove_junk', array_column($monthly_attendances, 'categorie'))) as $section): ?>
                    <div title="<?php echo htmlspecialchars($section); ?>">
                        <input id="monthly-section-<?php echo htmlspecialchars($section); ?>"
                            name="monthly-section-option" type="radio"
                            value="<?php echo htmlspecialchars($section); ?>" />
                        <label class="option"
                            for="monthly-section-<?php echo htmlspecialchars($section); ?>"><?php echo htmlspecialchars($section); ?></label>
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
            <div class="header__item">No.</div>
            <div class="header__item">Name</div>
            <div class="header__item">Strand</div>
            <div class="header__item">Grade Level</div>
            <div class="header__item">Section</div>
            <div class="header__item">Total Attendance</div>
            <div class="header__item">Date</div>
            <div class="header__item">Action</div>
        </div>

        <div class="table-content" id="table-content-monthly">
            <?php foreach ($monthly_attendances as $attendances): ?>
            <div class="table-row" data-grade-level="<?= remove_junk($attendances['grade_level']); ?>"
                data-section="<?= remove_junk($attendances['categorie']); ?>"
                data-month="<?= remove_junk($attendances['month']); ?>">
                <div class="table-data"><?= count_id(); ?></div>
                <div class="table-data"><?= remove_junk($attendances['name']); ?></div>
                <div class="table-data"><?= remove_junk($attendances['strand']); ?></div>
                <div class="table-data"><?= remove_junk($attendances['grade_level']); ?></div>
                <div class="table-data"><?= remove_junk($attendances['categorie']); ?></div>
                <div class="table-data"><?= remove_junk($attendances['total_records']); ?></div>
                <div class="table-data"><?= remove_junk($attendances['month']); ?></div>
                <div class="table-data">
                    <a class="btn btn-sm btn-info" onclick="toggleDropdown(this)"><i class="fa-solid fa-eye"></i></a>
                    <a class="btn btn-sm btn-primary"
                        href="export_excel.php?type=student_monthly&id=<?= $attendances['student_id']; ?>&month=<?= urlencode($attendances['month']); ?>">
                        <i class="fa-solid fa-download"></i>
                    </a>
                </div>
            </div>

            <!-- Dropdown Detail Table -->
            <div class="dropdown-container" style="display:none;">
                <table class="nested-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Strand</th>
                            <th>Grade Level</th>
                            <th>Section</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                    $records = get_student_attendance_by_month($attendances['student_id'], $attendances['month']);
                    foreach ($records as $rec): ?>
                        <tr>
                            <td><?= remove_junk($attendances['name']); ?></td>
                            <td><?= remove_junk($attendances['strand']); ?></td>
                            <td><?= remove_junk($attendances['grade_level']); ?></td>
                            <td><?= remove_junk($attendances['categorie']); ?></td>
                            <td><?= date('h:i A', strtotime($rec['timestamp_in'])); ?></td>
                            <td><?= $rec['timestamp_out'] ? date('h:i A', strtotime($rec['timestamp_out'])) : 'N/A'; ?>
                            </td>
                            <td><?= date('F d, Y', strtotime($rec['timestamp_in'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        </div>
    </div>



</div>








<?php include_once('layouts/footer.php'); ?>
<script>
function toggleDropdown(button) {
    const row = button.closest('.table-row');
    const next = row.nextElementSibling;
    if (next && next.classList.contains('dropdown-container')) {
        next.style.display = next.style.display === 'none' ? 'block' : 'none';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('export-attendance-btn').addEventListener('click', exportAttendanceData);

    function exportAttendanceData() {
        // Get visible rows (excluding filtered out rows)
        const visibleRows = document.querySelectorAll('.table-row:not([style*="display: none"])');

        // CSV headers
        let csvContent = "No.,Name,Grade Level,Section,Total Attendance,Date\n";

        visibleRows.forEach(row => {
            const cells = row.querySelectorAll('.table-data');
            // Skip the Actions column if present
            const rowData = [
                cells[0].textContent.trim(), // No.
                cells[1].textContent.trim(), // Name
                cells[2].textContent.trim(), // Grade Level
                cells[3].textContent.trim(), // Section 
                cells[4].textContent.trim(), // Total Attendance
                cells[5].textContent.trim() // Date
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
    const tableContent = document.getElementById('table-content-monthly');

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


document.querySelectorAll('.toggle-filter-btn').forEach(button => {
    button.addEventListener('click', function() {
        // Get the target filter container ID from the data-target attribute
        const filterContainerId = this.getAttribute('data-target');
        const filterContainer = document.getElementById(filterContainerId);

        // Toggle the 'open' class to show or hide the filter container
        filterContainer.classList.toggle('open');

        // Update button text based on the container's state
        if (filterContainer.classList.contains('open')) {
            this.textContent = 'Hide Filters'; // Change button text to 'Hide Filters'
        } else {
            this.textContent = 'Show Filters'; // Change button text to 'Show Filters'
        }
    });
});
document.addEventListener('DOMContentLoaded', function() {
    function initializeDropdownFilters(gradeLevelName, sectionName, selectedGradeElementId,
        selectedSectionElementId, tableId) {
        // Set up grade level dropdown
        document.querySelectorAll(`input[name="${gradeLevelName}"]`).forEach(input => {
            input.addEventListener('change', () => {
                const selectedText = document.querySelector(`label[for="${input.id}"]`)
                    .innerText;
                document.getElementById(selectedGradeElementId).innerText = selectedText;
                filterTable(gradeLevelName, sectionName, tableId);
            });
        });

        // Set up section dropdown
        document.querySelectorAll(`input[name="${sectionName}"]`).forEach(input => {
            input.addEventListener('change', () => {
                const selectedText = document.querySelector(`label[for="${input.id}"]`)
                    .innerText;
                document.getElementById(selectedSectionElementId).innerText = selectedText;
                filterTable(gradeLevelName, sectionName, tableId);
            });
        });
    }

    function filterTable(gradeLevelName, sectionName, tableId) {
        const gradeLevel = document.querySelector(`input[name="${gradeLevelName}"]:checked`).value;
        const section = document.querySelector(`input[name="${sectionName}"]:checked`).value;

        const rows = document.querySelectorAll(`#${tableId} .table-row`);
        rows.forEach(row => {
            const rowGradeLevel = row.getAttribute('data-grade-level');
            const rowSection = row.getAttribute('data-section');

            // Check if row matches selected filters
            const matchesGradeLevel = !gradeLevel || rowGradeLevel === gradeLevel;
            const matchesSection = !section || rowSection === section;

            // Show or hide row based on matches
            row.style.display = matchesGradeLevel && matchesSection ? '' : 'none';
        });
    }


    // Initialize filters for monthly attendance
    initializeDropdownFilters('monthly-grade-level-option', 'monthly-section-option',
        'monthly-grade-level-selected', 'monthly-section-selected', 'table-content-monthly');
});


function searchTable(searchBarId, tableContentId) {
    var input, filter, table, rows, i, j, txtValue, visible;
    input = document.getElementById(searchBarId);
    filter = input.value.trim().toLowerCase(); // Use trim to avoid leading/trailing spaces
    table = document.getElementById(tableContentId);
    rows = table.getElementsByClassName("table-row");

    for (i = 0; i < rows.length; i++) {
        visible = false;
        let columns = rows[i].getElementsByClassName("table-data");

        for (j = 0; j < columns.length; j++) {
            txtValue = columns[j].textContent || columns[j].innerText;

            if (txtValue.toLowerCase().includes(filter)) { // Text matching
                visible = true;
                break; // Stop checking once a match is found
            }
        }
        rows[i].style.display = visible ? "" : "none"; // Show or hide row
    }
}

// Filter for monthly attendance
document.getElementById('search-bar-monthly-report').addEventListener('keyup', function() {
    searchTable('search-bar-monthly-report', 'table-content-monthly');
});




// Function to download table data
function downloadTable(tableId, filename) {
    const rows = document.querySelectorAll(`#${tableId} .table-row`);
    const wb = XLSX.utils.book_new();
    const ws_data = [];

    // Add table headers
    ws_data.push(['ID', 'Name', 'Grade Level', 'Section', 'Total Attendance', 'Date']);

    // Add table data
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const rowData = [];
            row.querySelectorAll('.table-data').forEach(cell => {
                rowData.push(cell.textContent.trim());
            });
            ws_data.push(rowData);
        }
    });

    const ws = XLSX.utils.aoa_to_sheet(ws_data);
    XLSX.utils.book_append_sheet(wb, ws, 'Attendance');
    XLSX.writeFile(wb, filename);
}

// Download for monthly attendance
document.getElementById('download-monthly').addEventListener('click', function() {
    downloadTable('table-content-monthly', 'Monthly_Attendance_Report.xlsx');
});
</script>