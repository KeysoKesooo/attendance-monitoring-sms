<?php
  $page_title = 'Custom Attendance';
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
    <link rel="stylesheet" type="" href="libs/css/roles.css" />
</head>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>


<div id="Custom_attendances" class="content-section">
    <div class="action-buttons-container">
        <button class="toggle-filter-btn" data-target="custom-attendance-filter-container">Select Date</button>

        <!-- Attendance Filter Container -->
        <div id="custom-attendance-filter-container" class="filter-container">
            <form id="attendance-form">
                <div class="filter-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" class="date-input" />
                </div>
                <div class="filter-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" class="date-input" />
                </div>
                <button type="submit">Filter Attendance</button>
            </form>
        </div>

        <a class="download-button" id="download-custom-button" href="export_excel.php?type=custom">
            <span class="download-button__text">Download</span>
            <span class="download-button__icon">
                <svg class="download-svg" data-name="Layer 2" viewBox="0 0 35 35" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M17.5,22.131a1.249,1.249,0,0,1-1.25-1.25V2.187a1.25,1.25,0,0,1,2.5,0V20.881A1.25,1.25,0,0,1,17.5,22.131Z">
                    </path>
                    <path
                        d="M17.5,22.693a3.189,3.189,0,0,1-2.262-.936L8.487,15.006a1.249,1.249,0,0,1,1.767-1.767l6.751,6.751a.7.7,0,0,0,.99,0l6.751-6.751a1.25,1.25,0,0,1,1.768,1.767l-6.752,6.751A3.191,3.191,0,0,1,17.5,22.693Z">
                    </path>
                    <path
                        d="M31.436,34.063H3.564A3.318,3.318,0,0,1,.25,30.749V22.011a1.25,1.25,0,0,1,2.5,0v8.738a.815.815,0,0,0,.814.814H31.436a.815.815,0,0,0,.814-.814V22.011a1.25,1.25,0,1,1,2.5,0v8.738A3.318,3.318,0,0,1,31.436,34.063Z">
                    </path>
                </svg>
            </span>
        </a>
    </div>

    <div class="table">
        <div class="table-header" id="table-content-custom-report">
            <div class="header__item name-header">Name</div>
            <div class="header__item">Grade Level</div>
            <div class="header__item">Section</div>
            <div class="header__item">Time In</div>
            <div class="header__item">Time Out</div>
            <div class="header__item">Late</div>
            <div class="header__item">Date</div>

        </div>
        <div class="table-content" id="custom-attendance_info"></div>
    </div>
    <div class="pagination" id="pagination-controls-custom"></div>
</div>






<?php include_once('layouts/footer.php'); ?>
<script>
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

    // Initialize filters for daily attendance
    initializeDropdownFilters('grade-level-option', 'section-option', 'daily-grade-level-selected',
        'daily-section-selected', 'daily-attendance-table');

    // Initialize filters for monthly attendance
    initializeDropdownFilters('monthly-grade-level-option', 'monthly-section-option',
        'monthly-grade-level-selected', 'monthly-section-selected', 'monthly-attendance-table');
});





document.addEventListener('DOMContentLoaded', () => {
    const entriesPerPage = 7; // Number of entries per page

    function paginateTable(contentId, paginationId) {
        const tableContent = document.getElementById(contentId);
        const rows = Array.from(tableContent.getElementsByClassName('table-row'));
        const paginationControls = document.getElementById(paginationId);
        const totalPages = Math.ceil(rows.length / entriesPerPage);

        let currentPage = 1;

        function renderPage(page) {
            // Hide all rows
            rows.forEach((row) => {
                row.style.display = 'none';
            });

            // Show rows for the current page
            const start = (page - 1) * entriesPerPage;
            const end = start + entriesPerPage;
            rows.slice(start, end).forEach((row) => {
                row.style.display = 'flex'; // Adjust to match your table layout
            });

            // Highlight the active page in pagination controls
            Array.from(paginationControls.children).forEach((btn, index) => {
                btn.classList.toggle('active', index === page);
            });
        }

        function renderPagination() {
            paginationControls.innerHTML = '';

            // Add Previous button
            const prevBtn = document.createElement('button');
            prevBtn.textContent = 'Previous';
            prevBtn.disabled = currentPage === 1;
            prevBtn.addEventListener('click', () => {
                currentPage -= 1;
                renderPage(currentPage);
            });
            paginationControls.appendChild(prevBtn);

            // Add page numbers
            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.textContent = i;
                pageBtn.className = currentPage === i ? 'active' : '';
                pageBtn.addEventListener('click', () => {
                    currentPage = i;
                    renderPage(currentPage);
                });
                paginationControls.appendChild(pageBtn);
            }

            // Add Next button
            const nextBtn = document.createElement('button');
            nextBtn.textContent = 'Next';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.addEventListener('click', () => {
                currentPage += 1;
                renderPage(currentPage);
            });
            paginationControls.appendChild(nextBtn);
        }

        renderPagination();
        renderPage(currentPage);
    }

    // Initialize pagination for multiple tables
    paginateTable('daily-attendance-table', 'pagination-controls-daily');
    paginateTable('monthly-attendance-table', 'pagination-controls-monthly');
    paginateTable('custom-attendance-table', 'pagination-controls-custom');

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
    searchTable('search-bar-monthly-report', 'monthly-attendance-table');
});

// Filter for daily attendance
document.getElementById('search-bar-daily-report').addEventListener('keyup', function() {
    searchTable('search-bar-daily-report', 'daily-attendance-table');
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


document.getElementById('download-custom').addEventListener('click', function() {
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    if (start_date && end_date) {
        window.location.href = `export_excel.php?type=custom&start_date=${start_date}&end_date=${end_date}`;
    } else {
        alert('Please select a start and end date');
    }
});
</script>