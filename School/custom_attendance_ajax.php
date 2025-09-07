<?php
  require_once('includes/load.php');
  if (!$session->isUserLoggedIn(true)) { redirect('index.php', false); }

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $response = [];

    if (!empty($start_date) && !empty($end_date)) {
        $records = find_all_attendances_with_date_range($start_date, $end_date);

        $html = '';
        if ($records) {
            foreach ($records as $result) {
                $html .= "<div class=\"table-content\" id=\"custom-attendance-table\">";
                $html .= "<div class=\"table-row\">";
                $html .= "<input type=\"hidden\" name=\"s_id\" value=\"{$result['id']}\">";
                $html .= "<div class=\"table-data  name-column\" name=\"s_name\">" . htmlspecialchars($result['student_name']) . "</div>";
                $html .= "<div class=\"table-data\" name=\"s_category_name\">" . htmlspecialchars($result['category_name']) . "</div>";
                $html .= "<div class=\"table-data\" name=\"s_grade_level\">" . htmlspecialchars($result['grade_level']) . "</div>";
                $html .= "<div class=\"table-data\">" . htmlspecialchars($result['timestamp_in']) . "</div>";
                $html .= "<div class=\"table-data\">" . htmlspecialchars($result['timestamp_out']) . "</div>";
                $html .= "<div class=\"table-data\">" . htmlspecialchars($result['late']) . "</div>";
                $html .= "<div class=\"table-data\">" . htmlspecialchars($result['date_in']) . "</div>";
                $html .= "</div>";
                $html .= "</div>";
            }
        } else {
            $html .= '<p>No records found.</p>';
        }
        
        // Set table content in the response
        $response['table'] = $html;

        // Set pagination controls in the response
        $response['pagination'] = '<div class="pagination" id="pagination-controls-custom"></div>';

        // Send the response back as JSON
        echo json_encode($response);
    }
}
?>