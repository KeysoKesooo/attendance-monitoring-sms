<?php
require_once('includes/load.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = isset($_POST['data']) ? $_POST['data'] : '';
    $lines = explode("\n", $data);
    $parsedData = [];
    foreach ($lines as $line) {
        list($key, $value) = explode(': ', $line, 2);
        $parsedData[$key] = $value;
    }

    if (isset($parsedData['Student ID'])) {
        $studentId = (int)$parsedData['Student ID'];
        $attendanceSql = "SELECT * FROM attendances 
                          WHERE student_id = '{$studentId}' 
                          AND DATE(timestamp_in) = CURDATE() 
                          LIMIT 1";
        $attendanceResult = $db->query($attendanceSql);

        if ($attendanceResult->num_rows > 0) {
            $attendance = $attendanceResult->fetch_assoc();

            if ($attendance['timestamp_in'] && !$attendance['timestamp_out']) {
                $timeIn = strtotime($attendance['timestamp_in']);
                $currentTime = time();
                $timeDifference = $currentTime - $timeIn;

                if ($timeDifference > 600) {
                    $updateSql = "UPDATE attendances 
                                  SET timestamp_out = NOW() 
                                  WHERE id = '{$attendance['id']}'";
                    if ($db->query($updateSql)) {
                        sendAttendanceMessage($studentId, 'exit');
                        echo json_encode(['success' => true, 'message' => 'Exit time recorded successfully.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to record exit time.']);
                    }
                } else {
                    $updateSql = "UPDATE attendances 
                                  SET timestamp_in = NOW() 
                                  WHERE id = '{$attendance['id']}'";
                    if ($db->query($updateSql)) {
                        sendAttendanceMessage($studentId, 'entry');
                        echo json_encode(['success' => true, 'message' => 'Entry time updated successfully.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update entry time.']);
                    }
                }
            } else {
                $insertSql = "INSERT INTO attendances (student_id, timestamp_in) 
                              VALUES ('{$studentId}', NOW())";
                if ($db->query($insertSql)) {
                    sendAttendanceMessage($studentId, 'entry');
                    echo json_encode(['success' => true, 'message' => 'Entry time recorded successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to record entry time.']);
                }
            }
        } else {
            $insertSql = "INSERT INTO attendances (student_id, timestamp_in) 
                          VALUES ('{$studentId}', NOW())";
            if ($db->query($insertSql)) {
                sendAttendanceMessage($studentId, 'entry');
                echo json_encode(['success' => true, 'message' => 'Entry time recorded successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to record entry time.']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid QR code data.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

function sendAttendanceMessage($studentId, $action) {
    global $db;

    $apiToken = 'e98e51fe42029f522041591db234bc993deb5c43';
    $apiUrl = 'https://sms.iprogtech.com/api/v1/sms_messages';

    $studentSql = "SELECT s.phone_id, s.gender, s.name, c.grade_level, c.strand, c.name AS section, 
                          a.timestamp_in, a.timestamp_out
                   FROM student AS s
                   JOIN categories AS c ON s.categorie_id = c.id
                   LEFT JOIN attendances AS a ON a.student_id = s.id 
                   WHERE s.id = '{$studentId}' AND DATE(a.timestamp_in) = CURDATE()
                   LIMIT 1";
    $studentResult = $db->query($studentSql);

    if ($studentResult && $studentResult->num_rows > 0) {
        $student = $studentResult->fetch_assoc();
        $phoneId = $student['phone_id'];
        $gender = $student['gender'];
        $strand = $student['strand'];
        $studentName = $student['name'];
        $gradeLevel = $student['grade_level'];
        $section = $student['section'];
        $timeIn = date('h:i A', strtotime($student['timestamp_in']));
        $timeOut = isset($student['timestamp_out']) ? date('h:i A', strtotime($student['timestamp_out'])) : null;

        $relation = ($gender === 'Male') ? 'son' : 'daughter';
        $pronouns = ($gender === 'Male') ? 'his' : 'her';

        $userQuery = "SELECT phone_number FROM users WHERE id = '{$phoneId}' AND user_level = 3 LIMIT 1";
        $userResult = $db->query($userQuery);

        if ($userResult && $userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();
            $phoneNumber = $user['phone_number'];

            // Construct the message
            if ($action === 'entry') {
                $messageBody = "TMCSHS Notification: Your {$relation}, {$studentName}, from {$strand}, Grade {$gradeLevel}, Section {$section}, has arrived at school at {$timeIn}. Thank you for entrusting us with your {$pronouns} care.";
            } else {
                $messageBody = "TMCSHS Notification: Your {$relation}, {$studentName}, from {$strand}, Grade {$gradeLevel}, Section {$section}, left the school at {$timeOut}. We hope they had a great day!";
            }

            // Prepare POST data
            $postData = [
                'api_token' => $apiToken,
                'phone_number' => $phoneNumber,
                'message' => $messageBody
            ];

            // Send SMS using IPROG
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            // Optional: Log or handle response
            error_log("IPROG SMS Response: " . $response);
        } else {
            error_log("No parent phone number found for student ID {$studentId}");
        }
    } else {
        error_log("No student attendance data found for ID {$studentId}");
    }
}