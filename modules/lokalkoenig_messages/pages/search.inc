<?php

/**
 * Return autocomplete results for usernames.
 *
 * Prevents usernames from being used and/or suggested twice.
 */
function _lk_privatemsg_autocomplete($string) {
  
  $arr = array();
  $explode = explode(",", $string);
  for($x = 0; $x < count($explode); $x++){ 
     $explode[$x] = trim($explode[$x]);
     
     if($x == (count($explode) - 1)) $string = trim($explode[$x]);
     else $arr[] = $explode[$x]; 
  
  }
  
  $save = implode(", ", $arr);
  if($save) $save .= ', ';
  
  $matches = array();
  if ($string) {
    $result = db_select('users')->fields('users', array('name', "uid"))->condition('name', db_like($string) . '%', 'LIKE')->range(0, 10)->execute();
    foreach ($result as $user) {
      $matches[$save . $user->name . ", "] = (\LK\u($user->uid));
    }
  }

  drupal_json_output($matches);
}


?>