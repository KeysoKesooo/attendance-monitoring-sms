<?php

require_once('includes/load.php');

// Check what level user has permission to view this page
page_require_level(2);

// Pull out all users from the database
$sections = displayCategoriesElem();

// Ensure unique elements
$sections = array_unique($sections, SORT_REGULAR);
?>

<?php include_once('layouts/header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="libs/css/department_dash.css" />
    <!-- Font Awesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>

<body>
    <!-- BODY -->
    <button class="back_button" onclick="window.history.back()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
            <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
        Back
    </button>

    <div class="job_cards_container">
        <?php foreach ($sections as $section): ?>
        <div class="job_card" data-grade="<?php echo htmlspecialchars($section['grade_level']); ?>">
            <div class="job_details">
                <div class="text">
                    <h2>
                        <a href="grade_level.php?grade_level=<?php echo htmlspecialchars($section['grade_level']); ?>">
                            GRADE <?php echo htmlspecialchars($section['grade_level']); ?>
                        </a>
                    </h2>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php include_once('layouts/footer.php'); ?>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const jobCards = document.querySelectorAll('.job_card');
        const seenGrades = new Set();

        jobCards.forEach(card => {
            const grade = card.getAttribute('data-grade');
            if (seenGrades.has(grade)) {
                card.remove(); // Remove duplicate card
            } else {
                seenGrades.add(grade);
            }
        });
    });
    </script>