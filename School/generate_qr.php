<?php
require 'vendor/autoload.php';
require_once('includes/session.php'); // Ensure session is started
require_once('includes/sql.php'); // Ensure SQL functions are included

page_require_level(1);

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student = find_student_by_id($id);

if (!$student) {
    die('Product not found.');
}

$imagePath = "uploads/student/{$student['student_image']}";
$data = "<img src='{$imagePath}' alt='Student Photo' style='width: 100px; height: auto; padding: 10px 0px 0px 0px'><br>\n"; 
$data .= "Student ID: {$student['id']}<br>\n";
$data .= "Name: {$student['name']}<br>\n";
$data .= "Strand: {$student['strand']}<br>\n";
$data .= "Section: {$student['categorie']}<br>\n"; 
$data .= "Grade Level: {$student['grade_level']}<br>\n";

$qrCode = QrCode::create($data)
    ->setEncoding(new Encoding('UTF-8'))
    ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
    ->setSize(500)
    ->setMargin(40)
    ->setForegroundColor(new Color(0, 0, 0))
    ->setBackgroundColor(new Color(255, 255, 255));

$writer = new PngWriter();
$filename = "qr-code-{$student['id']}.png";
$path = __DIR__ . '/qr_codes/' . $filename;
$writer->write($qrCode)->saveToFile($path);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code</title>
    <link rel="stylesheet" type="text/css" href="libs/css/roles.css" />
</head>

<body>
    <div class="generator_container">
        <a href="management_student.php" class="qr-close">&times;</a>
        <div class="qr-container">
            <img src="qr_codes/<?php echo htmlspecialchars($filename); ?>" alt="QR Code" class="qr-image">
            <a href="qr_codes/<?php echo htmlspecialchars($filename); ?>" download class="qr-download">Download QR
                Code</a>
        </div>
    </div>
</body>

</html>