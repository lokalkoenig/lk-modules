<?php


/**
 * Set the VKU as the active (most recent VKU)
 * 
 * @param type $id
 */
function _vku_set_active($id){
global $user;

    $vku = new VKUCreator($id);
   if(!$vku -> is()){
      	drupal_json_output(array("error" => 1, "message" => "Unbekannte Verkaufsunterlage"));
        drupal_exit();  
    }
      
    $author = $vku -> getAuthor();
    if($author != $user -> uid ){
        drupal_json_output(array("error" => 1, "message" => "Unbekannte Verkaufsunterlage"));
        drupal_exit();    
     }
     
     // Vku updaten
     $vku ->update();
     $title = strip_tags($vku -> getTitleTrimmed());
     $vku_id = $vku ->getId();
     $url = url($vku -> url());
     
     $kampas = $vku ->getKampagnen();
     $kampagnen = count($kampas);
     $date = format_date($vku -> get("vku_changed"), "short");
     $kampagnen_implode = implode(",", $kampas);
     
     $vku ->logEvent('vku2_change_vku', 'Aktive VKU gewechselt');
     
     $array = array('kampagnen' => $kampagnen_implode, 'url' => $url, 'error' => 0, 'date' => $date, "title" => $title, "vku_id" => $vku_id, "total" => $kampagnen);
     drupal_json_output($array);
     drupal_exit();    
}

?>
