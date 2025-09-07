<?php
require_once('includes/load.php');
page_require_level(3);

// Check if 'grade_level' is set in the URL parameters
if (isset($_GET['grade_level'])) {
    $grade_level = $db->escape($_GET['grade_level']); // Sanitize the input

    // Query to fetch names associated with the grade level
    $sql = "SELECT id, name FROM categories WHERE grade_level = '{$grade_level}'";
    $result = $db->query($sql);
} else {
    // Redirect to the main page if 'grade_level' is not provided
    header("Location: index.php");
    exit();
}
?>
<?php include_once 'layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Names for Grade Level <?php echo htmlspecialchars($grade_level); ?></title>
    <link rel="stylesheet" href="libs/css/department_dash.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>

<body>

    <button class="back_button" onclick="window.history.back()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
            <path d="M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
        Back
    </button>
    <div class="job_cards_container">
        <?php if ($db->num_rows($result) > 0): ?>
        <?php while ($row = $db->fetch_assoc($result)): ?>
        <a href="sections.php?id=<?php echo urlencode($row['id']); ?>" class="job_card">
            <div class="job_details">
                <div class="text">
                    <h2><?php echo htmlspecialchars($row['name']); ?></h2>
                </div>
            </div>
        </a>
        <?php endwhile; ?>
        <?php else: ?>
        <p>No section found for this grade level.</p>
        <?php endif; ?>
    </div>
    <?php include_once 'layouts/footer.php'; ?>
</body>

</html>