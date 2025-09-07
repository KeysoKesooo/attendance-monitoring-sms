<?php
  $page_title = 'Sections';
  require_once('includes/load.php');
?>
<?php
// Checkin What level user has permission to view this page
 page_require_level(1);
//pull out all user form database
 $all_users = find_all_user();
 $students = join_student_table();
 $all_categories = find_all('categories');
 $groups = find_all('user_groups');
 $all_photo = find_all('media');
 $all_parents = find_by_sql("SELECT id, name, phone_number FROM users WHERE user_level = 3");
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
    <link rel="stylesheet" type="" href="libs/css/roles.css" />
</head>

<button class="back_button" style="top: 20px; position: absolute;" onclick="window.history.back()">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
        <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>
    Back
</button>

<div id="manageSections" class="content-section">
    <div class="action-buttons-container">

        <div class="search-bar-container">
            <input type="text" id="search-bar-sections" class="search-bar" placeholder="search...">
        </div>


        <div class="filter-wrapper">
            <button class="toggle-filter-btn" data-target="section-filter-container">Show Filters</button>
            <div id="section-filter-container" class="filter-container">
                <!-- Category Grade Level Filter -->
                <label for="category-grade-level-filter">Grade Level:</label>
                <div class="select">
                    <div class="selected" data-default="All">
                        <span id="category-grade-level-selected">All</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                            <path
                                d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                            </path>
                        </svg>
                    </div>
                    <div class="options">
                        <div title="all">
                            <input id="category-grade-level-all" name="category-grade-level-option" type="radio"
                                value="" checked />
                            <label class="option" for="category-grade-level-all">All</label>
                        </div>
                        <?php foreach (array_unique(array_map('remove_junk', array_column($all_categories, 'grade_level'))) as $grade_level): ?>
                        <div title="<?php echo htmlspecialchars($grade_level); ?>">
                            <input id="category-grade-level-<?php echo htmlspecialchars($grade_level); ?>"
                                name="category-grade-level-option" type="radio"
                                value="<?php echo htmlspecialchars($grade_level); ?>" />
                            <label class="option"
                                for="category-grade-level-<?php echo htmlspecialchars($grade_level); ?>"><?php echo htmlspecialchars($grade_level); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Category Section Filter -->
                <label for="category-section-filter">Section:</label>
                <div class="select">
                    <div class="selected" data-default="All">
                        <span id="category-section-selected">All</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                            <path
                                d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                            </path>
                        </svg>
                    </div>
                    <div class="options">
                        <div title="all">
                            <input id="category-section-all" name="category-section-option" type="radio" value=""
                                checked />
                            <label class="option" for="category-section-all">All</label>
                        </div>
                        <?php foreach (array_unique(array_map('remove_junk', array_column($all_categories, 'name'))) as $section): ?>
                        <div title="<?php echo htmlspecialchars($section); ?>">
                            <input id="category-section-<?php echo htmlspecialchars($section); ?>"
                                name="category-section-option" type="radio"
                                value="<?php echo htmlspecialchars($section); ?>" />
                            <label class="option"
                                for="category-section-<?php echo htmlspecialchars($section); ?>"><?php echo htmlspecialchars($section); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <a class="add_button" href="add_section.php">
            <svg aria-hidden="true" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="add_button__icon">
                <path stroke-width="2" stroke="#ffffff"
                    d="M13.5 3H12H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H11M13.5 3L19 8.625M13.5 3V7.625C13.5 8.17728 13.9477 8.625 14.5 8.625H19M19 8.625V11.8125"
                    stroke-linejoin="round" stroke-linecap="round"></path>
                <path stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="#ffffff"
                    d="M17 15V18M17 21V18M17 18H14M17 18H20"></path>
            </svg>
            <span class="add_button__text">Add Section</span>
        </a>
    </div>
    <div class="table">
        <div class="table-header">
            <div class="header__item"><a id="id" class="filter__link filter__link--number" href="#">No.</a>
            </div>
            <div class="header__item"><a id="name" class="filter__link" href="#">Section</a></div>
            <div class="header__item"><a id="grade_level" class="filter__link" href="#">Grade level</a>
            </div>
            <div class="header__item"><a id="grade_level" class="filter__link" href="#">Strand</a>
            </div>
            <div class="header__item"><a class="filter__link" href="#">Actions</a>
            </div>
        </div>
        <div class="table-content" id="table-content-sections">
            <?php foreach ($all_categories as $cat): ?>

            <div class="table-row" data-grade-level="<?php echo remove_junk((int)$cat['grade_level']); ?>"
                data-section="<?php echo remove_junk(ucfirst($cat['name'])); ?>">
                <!-- Adjusted for consistency -->
                <div class="table-data"><?php echo count_id(); ?></div>

                <div class="table-data"><?php echo remove_junk(ucfirst($cat['name'])); ?></div>

                <div class="table-data"><?php echo remove_junk((int)$cat['grade_level']); ?></div>

                <div class="table-data"><?php echo remove_junk(ucfirst($cat['strand'])); ?></div>


                <div class="table-data">
                    <a href="edit_categorie.php?id=<?php echo (int)$cat['id']; ?>" class="btn btn-xs btn-warning"
                        data-toggle="tooltip" title="Edit">
                        <i class="glyphicon glyphicon-pencil"></i>
                    </a>
                    <a href="delete_categorie.php?id=<?php echo (int)$cat['id']; ?>" class="btn btn-xs btn-danger"
                        data-toggle="tooltip" title="Remove" onclick="return confirmDelete();">
                        <i class="glyphicon glyphicon-remove"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
    </div>

</div>




<script>
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
        const gradeLevel = document.querySelector(`input[name="${gradeLevelName}"]:checked`)?.value || '';
        const section = document.querySelector(`input[name="${sectionName}"]:checked`)?.value || '';

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




    function filterUserTable() {
        const roleInputs = document.querySelectorAll(`input[name="user-role-option"]`);
        const statusInputs = document.querySelectorAll(`input[name="user-status-option"]`);

        const selectedRole = Array.from(roleInputs).find(input => input.checked)?.nextElementSibling
            .innerText || 'All Roles';
        const selectedStatus = Array.from(statusInputs).find(input => input.checked)?.nextElementSibling
            .innerText || 'All Statuses';

        document.getElementById('user-role-selected').innerText = selectedRole;
        document.getElementById('user-status-selected').innerText = selectedStatus;

        const role = document.querySelector(`input[name="user-role-option"]:checked`)?.value || '';
        const status = document.querySelector(`input[name="user-status-option"]:checked`)?.value || '';

        const rows = document.querySelectorAll(`#table-content-users .table-row`);
        rows.forEach(row => {
            const rowRole = row.getAttribute('data-role'); // Data attribute for role
            const rowStatus = row.getAttribute('data-status'); // Data attribute for status

            const matchesRole = !role || rowRole === role;
            const matchesStatus = !status || rowStatus === status;

            // Show or hide row based on matches
            row.style.display = matchesRole && matchesStatus ? '' : 'none';
        });
    }

    // Initialize filters for categories
    initializeDropdownFilters('category-grade-level-option', 'category-section-option',
        'category-grade-level-selected', 'category-section-selected', 'table-content-sections');

    // Initialize filters for users
    document.querySelectorAll(`input[name="user-role-option"], input[name="user-status-option"]`).forEach(
        input => {
            input.addEventListener('change', filterUserTable);
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
    document.getElementById("search-bar-sections").addEventListener("keyup", function() {
        searchTable("search-bar-sections", "table-content-sections");
    });


});
</script>


<?php include_once'layouts/footer.php'; ?>