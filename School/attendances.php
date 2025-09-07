<?php
$page_title = 'All attendance';
require_once('includes/load.php');
// Check if user has permission to view this page
page_require_level(2);



?>

<?php
// Check if the form is submitted
if (isset($_GET['filter'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    // Call the function to get the filtered attendances
    $attendances = find_all_attendances_with_date_range($start_date, $end_date);
} else {
    // Default to showing all attendances
    $attendances = find_all_attendances();
}
?>
<?php include_once('layouts/header.php'); ?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg(isset($msg) ? $msg : ''); ?>
    </div>
</div>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="libs/css/roles.css" />
</head>

<body>

    <button class="back_button" style="top: 20px;position: absolute;" onclick="window.history.back()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
            <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
        Back
    </button>

    <div class="action-buttons-container">
        <div class="search-bar-container">
            <input type="text" id="search-bar-attendance" class="search-bar" placeholder="search...">
        </div>

        <div class="filter-wrapper">
            <button class="toggle-filter-btn" data-target="custom-attendance-filter-container">Select Date</button>


            <!-- Attendance Filter Container -->
            <div id="custom-attendance-filter-container" class="filter-container">
                <form method="GET" action="" id="attendance-form">

                    <div class=" filter-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" class="date-input" />
                    </div>
                    <div class="filter-group">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" class="date-input" />
                    </div>
                    <button type="submit" name="filter">Filter Attendance</button>
                </form>
            </div>
        </div>




        <div class="filter-wrapper">
            <button class="toggle-filter-btn" data-target="attendance-filter-container">Show Filters</button>
            <!-- Grade Level Filter -->
            <div id="attendance-filter-container" class="filter-container">
                <label for="attendance-grade-level-filter">Grade Level:</label>
                <div class="select">
                    <div class="selected" data-default="All">
                        <span id="attendance-grade-level-selected">All</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                            <path
                                d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                            </path>
                        </svg>
                    </div>
                    <div class="options">
                        <div title="all">
                            <input id="attendance-grade-level-all" name="attendance-grade-level-option" type="radio"
                                value="" checked />
                            <label class="option" for="attendance-grade-level-all">All</label>
                        </div>
                        <?php foreach (array_unique(array_map('remove_junk', array_column($attendances, 'grade_level'))) as $grade_level): ?>
                        <div title="<?php echo htmlspecialchars($grade_level); ?>">
                            <input id="attendance-grade-level-<?php echo htmlspecialchars($grade_level); ?>"
                                name="attendance-grade-level-option" type="radio"
                                value="<?php echo htmlspecialchars($grade_level); ?>" />
                            <label class="option"
                                for="attendance-grade-level-<?php echo htmlspecialchars($grade_level); ?>"><?php echo htmlspecialchars($grade_level); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Section Filter -->
                <label for="attendance-section-filter">Section:</label>
                <div class="select">
                    <div class="selected" data-default="All">
                        <span id="attendance-section-selected">All</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                            <path
                                d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                            </path>
                        </svg>
                    </div>
                    <div class="options">
                        <div title="all">
                            <input id="attendance-section-all" name="attendance-section-option" type="radio" value=""
                                checked />
                            <label class="option" for="attendance-section-all">All</label>
                        </div>
                        <?php foreach (array_unique(array_map('remove_junk', array_column($attendances, 'category_name'))) as $section): ?>
                        <div title="<?php echo htmlspecialchars($section); ?>">
                            <input id="attendance-section-<?php echo htmlspecialchars($section); ?>"
                                name="attendance-section-option" type="radio"
                                value="<?php echo htmlspecialchars($section); ?>" />
                            <label class="option"
                                for="attendance-section-<?php echo htmlspecialchars($section); ?>"><?php echo htmlspecialchars($section); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <a class="export_button" id="export-attendance-btn">
            <i class=" fa-solid fa-download"></i>
            <span class="export_button__text">Export</span>
        </a>

        <a class="add_button" href="add_attendance.php">
            <svg aria-hidden="true" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="add_button__icon">
                <path stroke-width="2" stroke="#ffffff"
                    d="M13.5 3H12H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H11M13.5 3L19 8.625M13.5 3V7.625C13.5 8.17728 13.9477 8.625 14.5 8.625H19M19 8.625V11.8125"
                    stroke-linejoin="round" stroke-linecap="round"></path>
                <path stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="#ffffff"
                    d="M17 15V18M17 21V18M17 18H14M17 18H20"></path>
            </svg>
            <span class="add_button__text">Add Attendance</span>
        </a>

    </div>

    <div class="table">
        <div class="table-header">
            <div class="header__item"><a id="no" class="filter__link" href="#">No.</a></div>
            <div class="header__item name-header"><a id="name" class="filter__link" href="#">Name</a></div>
            <div class="header__item"><a id="strand" class="filter__link" href="#">Strand</a></div>
            <div class="header__item"><a id="grade_level" class="filter__link" href="#">Grade Level</a>
            </div>
            <div class="header__item"><a id="category_name" class="filter__link" href="#">Section</a>
            </div>
            <div class="header__item"><a id="gender" class="filter__link" href="#">Gender</a></div>
            <div class="header__item"><a id="timestamp_in" class="filter__link filter__link--number" href="#">Time
                    In</a>
            </div>
            <div class="header__item"><a id="timestamp_out" class="filter__link filter__link--number" href="#">Time
                    Out</a>
            </div>
            <div class="header__item">Late</div>
            <div class="header__item"><a id="date" class="filter__link filter__link--number" href="#">Date</a>
            </div>
            <?php if ($session->isUserLoggedIn(true)): ?>
            <?php if (isset($user['user_level']) && $user['user_level'] === '1'): ?>
            <div class="header__item">
                <a class="filter__link" href="#">Actions</a>
            </div>
            <?php else: ?>
            <?php endif; ?>
            <?php endif; ?>

        </div>
        <div class="table-content" id="table-content-attendance">
            <?php foreach ($attendances as $attendance): ?>
            <div class="table-row" data-grade-level="<?php echo (int)$attendance['grade_level']; ?>"
                data-section="<?php echo remove_junk($attendance['category_name']); ?>">
                <div class="table-data"><?php echo count_id(); ?></div>
                <div class="table-data name-column"><?php echo remove_junk($attendance['student_name']); ?></div>
                <div class="table-data"><?php echo remove_junk($attendance['strand']); ?></div>
                <div class="table-data"><?php echo (int)$attendance['grade_level']; ?></div>
                <div class="table-data"><?php echo remove_junk($attendance['category_name']); ?></div>
                <div class="table-data"><?php echo remove_junk($attendance['gender']); ?></div>
                <div class="table-data"><?php echo remove_junk($attendance['timestamp_in']); ?></div>
                <div class="table-data"><?php echo remove_junk($attendance['timestamp_out']); ?></div>
                <div class="table-data"><?php echo remove_junk($attendance['late']); ?></div>
                <div class="table-data"><?php echo remove_junk($attendance['date_in']); ?></div>

                <?php if ($session->isUserLoggedIn(true)): ?>
                <?php if (isset($user['user_level']) && $user['user_level'] === '1'): ?>
                <div class="table-data">
                    <a href="delete_attendance.php?id=<?php echo (int)$attendance['id']; ?>"
                        class="btn btn-xs btn-danger" data-toggle="tooltip" title="Remove"
                        onclick="return confirmDelete();">
                        <i class="glyphicon glyphicon-remove"></i>
                    </a>
                </div>
                <?php else: ?>
                <?php endif; ?>
                <?php endif; ?>


            </div>
            <?php endforeach; ?>
        </div>
    </div>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('export-attendance-btn').addEventListener('click', exportAttendanceData);

        function exportAttendanceData() {
            // Get visible rows (excluding filtered out rows)
            const visibleRows = document.querySelectorAll('.table-row:not([style*="display: none"])');

            // CSV headers
            let csvContent = "No.,Name,Grade Level,Section,Gender,Time In,Time Out,Late,Date\n";

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
                    cells[8].textContent.trim() // Date
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
            selectedSectionElementId, searchBarId, tableId) {
            // Set up grade level dropdown
            document.querySelectorAll(`input[name="${gradeLevelName}"]`).forEach(input => {
                input.addEventListener('change', () => {
                    const selectedText = document.querySelector(`label[for="${input.id}"]`)
                        .innerText;
                    document.getElementById(selectedGradeElementId).innerText = selectedText;
                    filterTable(gradeLevelName, sectionName, searchBarId, tableId);
                });
            });

            // Set up section dropdown
            document.querySelectorAll(`input[name="${sectionName}"]`).forEach(input => {
                input.addEventListener('change', () => {
                    const selectedText = document.querySelector(`label[for="${input.id}"]`)
                        .innerText;
                    document.getElementById(selectedSectionElementId).innerText = selectedText;
                    filterTable(gradeLevelName, sectionName, searchBarId, tableId);
                });
            });

            // Set up search bar
            document.getElementById(searchBarId).addEventListener('input', () => {
                filterTable(gradeLevelName, sectionName, searchBarId, tableId);
            });
        }

        function filterTable(gradeLevelName, sectionName, searchBarId, tableId) {
            const gradeLevel = document.querySelector(`input[name="${gradeLevelName}"]:checked`).value;
            const section = document.querySelector(`input[name="${sectionName}"]:checked`).value;
            const searchTerm = document.getElementById(searchBarId).value.toLowerCase();

            const rows = document.querySelectorAll(`#${tableId} .table-row`);
            rows.forEach(row => {
                const rowGradeLevel = row.getAttribute('data-grade-level');
                const rowSection = row.getAttribute('data-section');
                const rowData = row.textContent.toLowerCase();

                // Check if row matches selected filters and search term
                const matchesGradeLevel = !gradeLevel || rowGradeLevel === gradeLevel;
                const matchesSection = !section || rowSection === section;
                const matchesSearchTerm = rowData.includes(searchTerm);

                // Show or hide row based on all matches
                row.style.display = matchesGradeLevel && matchesSection && matchesSearchTerm ? '' :
                    'none';
            });
        }

        // Initialize filters for attendances
        initializeDropdownFilters('attendance-grade-level-option', 'attendance-section-option',
            'attendance-grade-level-selected', 'attendance-section-selected', 'search-bar-attendance',
            'table-content-attendance');
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
                    (!isNaN(numericValue) && !isNaN(filterNumeric) && numericValue ===
                        filterNumeric) // Numeric matching
                ) {
                    visible = true;
                    break; // Stop checking once a match is found
                }
            }
            rows[i].style.display = visible ? "" : "none"; // Show or hide row
        }
    }




    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("search-bar-attendance").addEventListener("keyup", function() {
            searchTable("search-bar-attendance", "table-content-attendance");
        });


    });

    document.addEventListener('DOMContentLoaded', () => {
        const headers = document.querySelectorAll('.header__item');
        const tableContent = document.getElementById('table-content-attendance');

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



    <?php include_once'layouts/footer.php'; ?>