<?php

  require_once('includes/load.php');
  if (!$session->isUserLoggedIn(true)) { redirect('index.php', false);}
?>

<?php
 // Auto suggestion
    $html = '';
   if(isset($_POST['student_name']) && strlen($_POST['student_name']))
   {
     $students = find_student_by_title($_POST['student_name']);
     if($students){
        foreach ($students as $student):
           $html .= "<li class=\"list-group-item\">";
           $html .= $student['name'];
           $html .= "</li>";
         endforeach;
      } else {

        $html .= '<li onClick=\"fill(\''.addslashes().'\')\" class=\"list-group-item\">';
        $html .= 'Not found';
        $html .= "</li>";

      }

      echo json_encode($html);
}
?>
<?php
 // find all product
  if(isset($_POST['p_name']) && strlen($_POST['p_name']))
  {
    $student_title = remove_junk($db->escape($_POST['p_name']));
    if($results = find_all_student_info_by_title($student_title)){
        foreach ($results as $result) {
          $html .= "<div class=\"table-content\">";
          $html .= "<div class=\"table-row\">";
          $html .= "<input type=\"hidden\" name=\"s_id\" value=\"{$result['id']}\">";
          $html .= "<div class=\"table-data\" name=\"s_name\">" . htmlspecialchars($result['name']) . "</div>";
          $html .= "<div class=\"table-data\" name=\"s_category_name\">" . htmlspecialchars($result['category_name']) . "</div>";
          $html .= "<div class=\"table-data\" name=\"s_grade_level\">" . htmlspecialchars($result['grade_level']) . "</div>";
          $html .= "<div class=\"table-data\" name=\"s_grade_level\">" . htmlspecialchars($result['gender']) . "</div>";
          
          // Timestamp input field
          $html .= "<div class=\"table-data\">";
          $html .= "<input type=\"datetime-local\" class=\"form-control timestampPicker\" name=\"timestamp_in\">";
          $html .= "</div>";

          $html .= "<div class=\"table-data\">";
          $html .= "<input type=\"datetime-local\" class=\"form-control timestampPicker\" name=\"timestamp_out\">";
          $html .= "</div>";

          $html .= "<div class=\"table-data\">";
          $html .= "<input type=\"hidden\" class=\"form-control timestampPicker\" name=\"late\">";
          $html .= "</div>";
          
          $html .= "<div class=\"table-data\">";
          $html .= "<button type=\"submit\" name=\"add_attendance\" class=\"add_at\">Add attendance</button>";
          $html .= "</div>";
          $html .= "</div>";
          $html .= "</div>";




      }
  } else {
      $html = '<br><tr><td>Product name not registered in database</td></tr></br>';
  }

    echo json_encode($html);
  }
 ?>