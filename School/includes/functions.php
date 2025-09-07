<?php
 $errors = array();

 /*--------------------------------------------------------------*/
 /* Function for Remove escapes special
 /* characters in a string for use in an SQL statement
 /*--------------------------------------------------------------*/
function real_escape($str){
  global $con;
  $escape = mysqli_real_escape_string($con,$str);
  return $escape;
}

function display_page_title($default = '') {
  // Use the global $page_title variable if it's set
  global $page_title;
  return isset($page_title) ? $page_title : $default;
}

/*--------------------------------------------------------------*/
/* Function for Remove html characters
/*--------------------------------------------------------------*/
function remove_junk($str){
  $str = nl2br($str);
  $str = htmlspecialchars(strip_tags($str, ENT_QUOTES));
  return $str;
}
/*--------------------------------------------------------------*/
/* Function for Uppercase first character
/*--------------------------------------------------------------*/
function first_character($str){
  $val = str_replace('-'," ",$str);
  $val = ucfirst($val);
  return $val;
}
/*--------------------------------------------------------------*/
/* Function for Checking input fields not empty
/*--------------------------------------------------------------*/
function validate_fields($var){
  global $errors;
  foreach ($var as $field) {
    $val = remove_junk($_POST[$field]);
    if(isset($val) && $val==''){
      $errors = $field ." can't be blank.";
      return $errors;
    }
  }
}
/*--------------------------------------------------------------*/
/* Function for Display Session Message
   Ex echo displayt_msg($message);
/*--------------------------------------------------------------*/
function display_msg($msg = ''){
  $output = '';
  if (is_array($msg) || is_object($msg)) {
     if (!empty($msg)) {
        foreach ($msg as $key => $value) {
           $output .= "<div class=\"alert alert-{$key} alert-dismissible\">";
           $output .= "<button type=\"button\" class=\"close\" onclick=\"this.parentElement.style.display='none'\">&times;</button>";
           $output .= remove_junk(first_character($value));
           $output .= "</div>";
        }
        return $output;
     } else {
        return "";
     }
  } else {
     return "";
  }
}

/*--------------------------------------------------------------*/
/* Function for redirect
/*--------------------------------------------------------------*/
function redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
      header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}



/*--------------------------------------------------------------*/
/* Function for Formatting a Readable Date
/*--------------------------------------------------------------*/
function read_date($str) {
    return $str ? date('F j, Y, g:i:s a', strtotime($str)) : null;
}

/*--------------------------------------------------------------*/
/* Function for Getting the Current Date and Time
/*--------------------------------------------------------------*/
date_default_timezone_set('Asia/Manila'); // or 'Asia/Manila' for the Philippines, etc.

function make_date() {
    return date("Y-m-d H:i:s"); // Outputs in the format: 2024-10-07 07:11:15
}
/*--------------------------------------------------------------*/
/* Function for  Readable date time
/*--------------------------------------------------------------*/
function count_id(){
  static $count = 1;
  return $count++;
}
/*--------------------------------------------------------------*/
/* Function for Creting random string
/*--------------------------------------------------------------*/
function randString($length = 5)
{
  $str='';
  $cha = "0123456789abcdefghijklmnopqrstuvwxyz";

  for($x=0; $x<$length; $x++)
   $str .= $cha[mt_rand(0,strlen($cha))];
  return $str;
}


?>