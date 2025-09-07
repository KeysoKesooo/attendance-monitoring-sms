<?php
  $page_title = 'Students';
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
 if (isset($_POST['import_csv']) && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    // Validate the file extension
    $allowed_ext = ['csv'];
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);

    if (in_array($file_ext, $allowed_ext)) {
        // Open the file for reading
        if (($handle = fopen($file['tmp_name'], 'r')) !== FALSE) {
            // Skip the header row
            fgetcsv($handle);

            // Start the transaction
            $db->query("START TRANSACTION");

            // Initialize counters
            $success_count = 0;
            $error_count = 0;
            $duplicate_count = 0;
            $errors = [];

            // Loop through each row in the CSV file
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // Skip empty rows
                if (empty(array_filter($data))) {
                    continue;
                }

                $timestamp = $data[0];  // Timestamp value from CSV
                $student_name = trim(remove_junk($db->escape($data[1])));  // Student name
                $grade_level = remove_junk($db->escape($data[2]));  // Grade Level
                $section = remove_junk($db->escape($data[3]));  // Section (category name)
                $parent_name = remove_junk($db->escape($data[4]));  // Parent Name
                $gender = remove_junk($db->escape($data[5]));  // Gender
                $address = remove_junk($db->escape($data[6]));  // Address

                // Validate required fields
                if (empty($student_name)) {
                    $errors[] = "Row skipped: Student name is empty";
                    $error_count++;
                    continue;
                }

                // Check if student already exists in users table
                $existing_user = find_by_sql("SELECT id FROM users WHERE name = '{$student_name}' AND user_level = 4 LIMIT 1");
                if ($existing_user && count($existing_user) > 0) {
                    $duplicate_count++;
                    $errors[] = "Duplicate skipped: Student '{$student_name}' already exists";
                    continue;
                }

                // Convert the timestamp to the correct format (YYYY-MM-DD)
                $formatted_date = date("Y-m-d", strtotime($timestamp));

                // Get the parent ID (phone_id) by matching parent name from the users table
                $phone_id = NULL;
                if (!empty($parent_name)) {
                    $parent = find_by_sql("SELECT id FROM users WHERE name = '{$parent_name}' AND user_level = 3 LIMIT 1");
                    if ($parent && count($parent) > 0) {
                        $phone_id = (int)$parent[0]['id'];
                    }
                }

                // Get or create the category
                $categorie_id = NULL;
                if (!empty($section) && !empty($grade_level)) {
                    $category = find_by_sql("SELECT id FROM categories WHERE name = '{$section}' AND grade_level = '{$grade_level}' LIMIT 1");
                    
                    if ($category && count($category) > 0) {
                        $categorie_id = (int)$category[0]['id'];
                    } else {
                        $insert_category = "INSERT INTO categories (name, grade_level) VALUES ('{$section}', '{$grade_level}')";
                        if ($db->query($insert_category)) {
                            $categorie_id = $db->insert_id();
                        } else {
                            $errors[] = "Failed to create category '{$section}' for grade '{$grade_level}'";
                            $error_count++;
                            continue;
                        }
                    }
                }

                // Generate unique username - Improved version
                $name_parts = explode(' ', $student_name);
                $first_name = strtolower(trim($name_parts[0]));
                $last_name = '';
                
                if (count($name_parts) > 1) {
                    $last_name = strtolower(trim(end($name_parts)));
                }

                // Create base username - first letter of first name + full last name
                $base_username = substr($first_name, 0, 1) . (!empty($last_name) ? $last_name : substr($first_name, 1));
                $base_username = preg_replace('/[^a-z0-9]/', '', $base_username);

                // Ensure username is at least 3 characters
                if (strlen($base_username) < 3) {
                    $base_username .= 'user';
                }

                $username = $base_username;
                $counter = 1;

                // Ensure username is unique
                while (true) {
                    $user_check = find_by_sql("SELECT id FROM users WHERE username = '{$username}' LIMIT 1");
                    if (!$user_check || count($user_check) == 0) {
                        break;
                    }
                    $username = $base_username . $counter;
                    $counter++;
                }

                $password = 'student123'; // Default password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insert into users table
                $user_query = "INSERT INTO users (name, username, password, status, user_level) VALUES ";
                $user_query .= "('{$student_name}', '{$username}', '{$hashed_password}', 1, 4)";

                if ($db->query($user_query)) {
                    $user_id = $db->insert_id();

                    // Insert into student table
                    $student_query = "INSERT INTO student (name, student_username, student_password, categorie_id, date, phone_id, gender, address, user_level, user_id) VALUES (";
                    $student_query .= "'{$student_name}', '{$username}', '{$hashed_password}', ";
                    $student_query .= $categorie_id ? "'{$categorie_id}'" : "NULL";
                    $student_query .= ", '{$formatted_date}', ";
                    $student_query .= $phone_id ? "'{$phone_id}'" : "NULL";
                    $student_query .= ", '{$gender}', '{$address}', 4, '{$user_id}')";

                    if ($db->query($student_query)) {
                        $success_count++;
                    } else {
                        $errors[] = "Failed to add student '{$student_name}': " . $db->error();
                        $error_count++;
                        // Rollback the user insert if student insert fails
                        $db->query("DELETE FROM users WHERE id = '{$user_id}'");
                    }
                } else {
                    $errors[] = "Failed to create user account for '{$student_name}': " . $db->error();
                    $error_count++;
                }
            }

            fclose($handle);

            if ($error_count == 0) {
                $db->query("COMMIT");
                $session->msg('s', "Successfully imported {$success_count} students");
            } else {
                $db->query("ROLLBACK");
                $error_msg = "Import completed with issues:<br>";
                $error_msg .= "- Successfully processed: {$success_count}<br>";
                $error_msg .= "- Duplicates skipped: {$duplicate_count}<br>";
                $error_msg .= "- Errors encountered: {$error_count}<br>";
                $error_msg .= "Detailed errors:<br>" . implode("<br>", array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $error_msg .= "<br>...and " . (count($errors) - 5) . " more";
                }
                $session->msg('d', $error_msg);
            }
            
            redirect('management_student.php', false);
        } else {
            $session->msg('d', 'Failed to open the file.');
            redirect('management_student.php', false);
        }
    } else {
        $session->msg('d', 'Invalid file type. Please upload a CSV file.');
        redirect('management_student.php', false);
    }
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
    <link rel="stylesheet" type="" href="libs/css/roles.css" />
</head>

<button class="back_button" style="top: 20px; position: absolute;" onclick="window.history.back()">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
        <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>
    Back
</button>

<div id="manageStudents" class="content-section">
    <div class="action-buttons-container">


        <div class="search-bar-container">
            <input type="text" id="search-bar-students" class="search-bar" placeholder="search...">
        </div>


        <a class="export_button" id="download-btn">
            <i class="fa-solid fa-download"></i>
            <span class="export_button__text">Export</span>
        </a>



        <a class="export_button" id="openPopup">
            <i class="fa-solid fa-upload"></i>
            <span class="export_button__text">Import</span>
        </a>

        <!-- Popup Form -->
        <div id="popupForm" class="popup-form">
            <div class="editpopup_form_area">
                <span id="closePopup" class="close-btn">&times;</span>

                <form method="post" action="management_student.php" enctype="multipart/form-data">
                    <div class="editpopup_form_group">
                        <label class="editpopup_sub_title" for="csv_file">Choose CSV File</label>
                        <input type="file" name="csv_file" class="editpopup_form_style" required>
                    </div>
                    <div>
                        <button type="submit" name="import_csv" class="editpopup_btn" style="margin-left: 180px">Import
                            Users</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="filter-wrapper">
            <button class="toggle-filter-btn" data-target="student-filter-container">Show Filters</button>

            <!-- Filters -->
            <div id="student-filter-container" class="filter-container">
                <!-- Grade Level Filter -->
                <div class="filter-item">
                    <label for="student-grade-level-filter">Student Grade Level:</label>
                    <div class="select">
                        <div class="selected" data-default="All">
                            <span id="student-grade-level-selected">All</span>
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                                <path
                                    d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                                </path>
                            </svg>
                        </div>
                        <div class="options">
                            <div title="all">
                                <input id="student-grade-level-all" name="student-grade-level-option" type="radio"
                                    value="" checked />
                                <label class="option" for="student-grade-level-all">All</label>
                            </div>
                            <!-- PHP-generated grade levels -->
                            <?php foreach (array_unique(array_column($students, 'grade_level')) as $grade_level): ?>
                            <div title="<?php echo htmlspecialchars($grade_level); ?>">
                                <input id="student-grade-level-<?php echo htmlspecialchars($grade_level); ?>"
                                    name="student-grade-level-option" type="radio"
                                    value="<?php echo htmlspecialchars($grade_level); ?>" />
                                <label class="option"
                                    for="student-grade-level-<?php echo htmlspecialchars($grade_level); ?>"><?php echo htmlspecialchars($grade_level); ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Section Filter -->
                <div class="filter-item">
                    <label for="student-section-filter">Student Section:</label>
                    <div class="select">
                        <div class="selected" data-default="All">
                            <span id="student-section-selected">All</span>
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                                <path
                                    d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                                </path>
                            </svg>
                        </div>
                        <div class="options">
                            <div title="all">
                                <input id="student-section-all" name="student-section-option" type="radio" value=""
                                    checked />
                                <label class="option" for="student-section-all">All</label>
                            </div>
                            <!-- PHP-generated sections -->
                            <?php foreach (array_unique(array_column($students, 'categorie')) as $section): ?>
                            <div title="<?php echo htmlspecialchars($section); ?>">
                                <input id="student-section-<?php echo htmlspecialchars($section); ?>"
                                    name="student-section-option" type="radio"
                                    value="<?php echo htmlspecialchars($section); ?>" />
                                <label class="option"
                                    for="student-section-<?php echo htmlspecialchars($section); ?>"><?php echo htmlspecialchars($section); ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <a class="add_button" href="add_student.php">
            <svg aria-hidden="true" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="add_button__icon">
                <path stroke-width="2" stroke="#ffffff"
                    d="M13.5 3H12H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H11M13.5 3L19 8.625M13.5 3V7.625C13.5 8.17728 13.9477 8.625 14.5 8.625H19M19 8.625V11.8125"
                    stroke-linejoin="round" stroke-linecap="round"></path>
                <path stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="#ffffff"
                    d="M17 15V18M17 21V18M17 18H14M17 18H20"></path>
            </svg>
            <span class="add_button__text">Add Student</span>
        </a>
    </div>
    <div class="table">
        <div class="table-header">
            <div class="header__item"><a id="no" class="filter__link" href="#">No.</a></div>
            <div class="header__item"><a id="student_photo" class="filter__link" href="#">Photo</a></div>

            <div class="header__item"><a id="name" class="filter__link" href="#">Name</a></div>
            <div class="header__item"><a id="strand" class="filter__link" href="#">Strand</a></div>
            <div class="header__item"><a id="grade_level" class="filter__link" href="#">Grade Level</a>
            </div>
            <div class="header__item"><a id="section" class="filter__link" href="#">Section</a></div>
            <div class="header__item"><a id="gender" class="filter__link" href="#">Sex</a></div>
            <div class="header__item"><a id="p_number" class="filter__link" href="#">Parent Phone
                    Number</a></div>
            <div class="header__item address-header"><a id="address" class="filter__link" href="#">Address</a></div>
            <!-- New column -->
            <div class="header__item"><a id="date" class="filter__link filter__link--number" href="#">Created at</a>
            </div>
            <div class="header__item"><a class="filter__link" href="#">Actions</a></div>
        </div>
        <div class="table-content" id="table-content-students">
            <?php foreach ($students as $student): ?>
            <div class="table-row" data-grade-level="<?php echo remove_junk((int)$student['grade_level']); ?>"
                data-section="<?php echo remove_junk($student['categorie']); ?>">
                <div class="table-data"><?php echo count_id(); ?></div>
                <div class="table-data">
                    <?php if($student['student_image'] === '0'): ?>
                    <img class="img-avatar img-circle" src="/test1/image/defualt.png" alt="">
                    <?php else: ?>
                    <img class="img-avatar img-circle" src="uploads/student/<?php echo $student['student_image']; ?>"
                        alt="">
                    <?php endif; ?>
                </div>

                <div class="table-data"><?php echo remove_junk($student['name']); ?></div>
                <div class="table-data"><?php echo remove_junk(ucfirst($student['strand'])); ?></div>
                <div class="table-data"><?php echo remove_junk((int)$student['grade_level']); ?></div>
                <div class="table-data"><?php echo remove_junk($student['categorie']); ?></div>
                <div class="table-data"><?php echo remove_junk($student['gender']); ?></div>
                <div class="table-data"><?php echo remove_junk($student['phone_number']); ?></div>
                <div class="table-data address-column"><?php echo remove_junk($student['address']); ?></div>
                <!-- New column -->
                <div class="table-data"><?php echo read_date($student['date']); ?></div>
                <div class="table-data">
                    <a href="edit_student.php?id=<?php echo (int)$student['id'];?>" class="btn btn-xs btn-warning"
                        data-toggle="tooltip" title="Edit">
                        <i class="glyphicon glyphicon-pencil"></i>
                    </a>
                    <a href="delete_student.php?id=<?php echo (int)$student['id'];?>" class="btn btn-xs btn-danger"
                        data-toggle="tooltip" title="Remove" onclick="return confirmDelete();">
                        <i class="glyphicon glyphicon-remove"></i>
                    </a>
                    <a href="generate_qr.php?id=<?php echo (int)$student['id']; ?>" id="generate-qr"
                        class="btn btn-xs btn-danger" data-toggle="tooltip" title="Generate QR Code">
                        <svg xmlns="glyphicon glyphicon-qrcode" height="12" width="13" viewBox="0 0 448 512">
                            <path
                                d="M0 80C0 53.5 21.5 32 48 32h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80zM64 96v64h64V96H64zM0 336c0-26.5 21.5-48 48-48h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V336zm64 16v64h64V352H64zM304 32h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48h-96c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48zm64 64v64h64V96h-64zM304 336h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48h-96c-26.5 0-48-21.5-48-48V336c0-26.5 21.5-48 48-48zm64 64v64h64v-64h-64z" />
                        </svg>
                    </a>
                </div>
            </div>
            <?php endforeach;?>
        </div>
    </div>

</div>






<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click event to download button
    document.getElementById('download-btn').addEventListener('click', downloadFilteredStudentData);

    function downloadFilteredStudentData() {
        // Get all visible rows (filtered rows)
        const visibleRows = document.querySelectorAll('.table-row:not([style*="display: none"])');

        // Prepare CSV headers
        let csvContent =
            "No.,Name,Grade Level,Section,Gender,Parent Phone Number,Address,Created at\n";

        visibleRows.forEach(row => {
            const columns = row.querySelectorAll('.table-data');
            const rowData = [
                columns[0].textContent.trim(),
                columns[1].textContent.trim(),
                columns[2].textContent.trim(),
                columns[3].textContent.trim(),
                columns[4].textContent.trim(),
                columns[5].textContent.trim(),
                columns[6].textContent.trim(),
                columns[7].textContent.trim()
            ];

            // Escape quotes and add to CSV
            csvContent += rowData.map(data => `"${data.replace(/"/g, '""')}"`).join(',') + '\n';
        });

        // Create download link
        const blob = new Blob([csvContent], {
            type: 'text/csv;charset=utf-8;'
        });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `students_${new Date().toISOString().slice(0,10)}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const headers = document.querySelectorAll('.header__item');
    const tableContent = document.getElementById('table-content-students');

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

    // Initialize filters for students
    initializeDropdownFilters('student-grade-level-option', 'student-section-option',
        'student-grade-level-selected', 'student-section-selected', 'table-content-students');

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
    document.getElementById("search-bar-attendance").addEventListener("keyup", function() {
        searchTable("search-bar-attendance", "table-content-attendance");
    });


});


document.getElementById("search-bar-students").addEventListener("keyup", function() {
    searchTable("search-bar-students", "table-content-students");
});
</script>


<?php include_once'layouts/footer.php'; ?>