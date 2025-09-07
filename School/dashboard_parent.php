<?php
require_once('includes/load.php'); // Assuming your database connection is loaded here
$page_title = 'Dashboard';

// Check if user is logged in and is a parent (user_level = 3)
if (isset($_SESSION['user_id'])) {
    $parentId = $_SESSION['user_id']; // Get the logged-in user's ID

    // Query to get all children for the parent along with their latest attendance
    $childQuery = "
        SELECT 
            s.id AS student_id,
            s.name AS student_name,s.student_image AS image,
            c.name AS section_name,
            c.grade_level,
            MAX(a.timestamp_in) AS latest_time_in
        FROM student s
        JOIN users u ON s.phone_id = u.id
        JOIN categories c ON s.categorie_id = c.id
        LEFT JOIN attendances a ON s.id = a.student_id
        WHERE u.user_level = 3 AND u.id = '{$parentId}'
        GROUP BY s.id, s.name, c.name, c.grade_level";

    $childResult = $db->query($childQuery);

    // Get attendance summary
    $attendanceQuery = "
        SELECT 
            COUNT(DISTINCT s.id) AS total_children,
            SUM(CASE WHEN a.timestamp_in IS NOT NULL THEN 1 ELSE 0 END) AS present,
            SUM(CASE WHEN a.timestamp_in IS NULL THEN 1 ELSE 0 END) AS absent,
            SUM(CASE WHEN a.late_in_hours_minutes IS NOT NULL THEN 1 ELSE 0 END) AS total_late
        FROM student s
        JOIN users u ON s.phone_id = u.id
        LEFT JOIN attendances a ON s.id = a.student_id AND DATE(a.timestamp_in) = CURDATE()
        WHERE u.user_level = 3 AND u.id = '{$parentId}'";
    
    $attendanceResult = $db->query($attendanceQuery);
    $attendanceData = $attendanceResult->fetch_assoc();
} else {
    echo "Please log in as a parent to view children's attendance.";
    exit;
}
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


<!-- Dashboard Content -->
<div class="children-container">
    <div class="date-time">
        <span><?php echo date("F j, Y, g:i a"); ?></span>
    </div>
    <div class="dashboard">
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="card-title">Total Present <i class="fas fa-user-check"></i> </div>
                <p><?php echo $attendanceData['present']; ?></p>
            </div>
            <div class="dashboard-card">
                <div class="card-title">Total Absent <i class="fas fa-user-times"></i> </div>
                <p><?php echo $attendanceData['absent']; ?></p>
            </div>
            <div class="dashboard-card">
                <div class="card-title">Late <i class="fas fa-clock"></i> </div>
                <p><?php echo $attendanceData['total_late']; ?></p>
            </div>
            <div class="dashboard-card">
                <div class="card-title">Total Children <i class="fas fa-users"></i> </div>
                <p><?php echo $attendanceData['total_children']; ?></p>
            </div>
        </div>
    </div>
    <h2>Children's Attendance</h2>
    <div class="children-cards">
        <?php if ($childResult->num_rows > 0): ?>
        <?php while ($child = $childResult->fetch_assoc()): ?>
        <div class="child-card">
            <?php if($child['image'] === '0'): ?>
            <img class="child-card__avatar" src="/test1/image/defualt.png" alt="">
            <?php else: ?>
            <img class="child-card__avatar" src="uploads/student/<?php echo $child['image']; ?>" alt="">
            <?php endif; ?>
            <div class="child-card__title"><?php echo ucfirst($child['student_name']); ?></div>
            <div class="child-card__subtitle">Section: <?php echo $child['section_name']; ?></div>
            <div class="child-card__info">Grade Level: <?php echo $child['grade_level']; ?></div>
            <div class="child-card__info">Latest Time In:
                <?php echo $child['latest_time_in'] ? $child['latest_time_in'] : 'N/A'; ?></div>
            <div class="child-card__wrapper">
                <a href="view_attendance.php?student_id=<?php echo $child['student_id']; ?>" class="child-card__btn">
                    View Attendance
                </a>
            </div>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
        <p class="no-children">No children link to this account.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Styles for the Children Cards -->
<style>
/* Style for the date/time display */
.date-time {
    text-align: right;
    /* Align text to the right */
    margin-bottom: 20px;
    /* Space between the date/time and other elements */
    font-size: 1.2rem;
    /* Adjust the font size */
    color: #444;
    /* Set a color for the text */
}

.date-time span {
    margin-left: 10px;
    font-weight: bold;
}

/* Dashboard Layout */
.dashboard {
    display: flex;
    flex-direction: column;
    gap: 20px;
    /* Add consistent spacing between sections */
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.dashboard-card {
    background: #690B22;
    color: #fff;
    padding: 5px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    cursor: pointer;
    text-align: center;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    /* Enhanced shadow on hover */
}

.card-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

.children-container {
    text-align: center;
    font-family: Arial, sans-serif;
}

.children-container h2 {
    font-size: 2em;
    color: var(--main-bg-color);
    margin-bottom: 20px;
}

.children-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    /* 4 cards per row */
    gap: 20px;
    justify-content: center;
    align-items: start;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
}

.child-card {
    --main-color: #000;
    --submain-color: #78858F;
    --bg-color: #fff;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    position: relative;
    width: 280px;
    height: 380px;
    display: flex;
    flex-direction: column;
    align-items: center;
    border-radius: 20px;
    background: var(--bg-color);
    padding: 15px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.child-card__img {
    height: 120px;
    width: 100%;
    background: #f4f4f4;
    border-radius: 20px 20px 0 0;
}

.child-card__avatar {
    position: absolute;
    width: 90px;
    height: 90px;
    background: var(--bg-color);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    top: 50px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.child-card__title {
    margin-top: 150px;
    font-weight: 500;
    font-size: 18px;
    color: var(--main-color);
}

.child-card__subtitle,
.child-card__info {
    margin-top: 8px;
    font-size: 14px;
    color: var(--submain-color);
}

.child-card__wrapper {
    margin-top: 15px;
}

.child-card__btn {
    padding: 8px 15px;
    border: 2px solid #690B22;
    border-radius: 4px;
    font-weight: 700;
    font-size: 12px;
    color: #690B22;
    background: #F8FAFF;
    ;
    text-transform: uppercase;
    transition: all 0.3s;
    text-decoration: none;
}

.child-card__btn:hover {
    background: #690B22;
    color: #F8FAFF;
    text-decoration: none;
}

.no-children {
    font-size: 1.2em;
    color: #666;
}

@media (max-width: 700px) {

    .child-card {
        --main-color: #000;
        --submain-color: #78858F;
        --bg-color: #fff;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        position: relative;
        width: 280px;
        height: 380px;
        display: flex;
        flex-direction: column;
        align-items: center;
        left: -20px;
        border-radius: 20px;
        background: var(--bg-color);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .children-cards {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        /* Responsive columns */
        gap: 15px;
    }

    .date-time {
        display: none;
    }
}
</style>

<?php include_once('layouts/footer.php'); ?>