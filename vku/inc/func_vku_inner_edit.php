<?php

require_once 'func_vku_edit_node.php';

/**
 * Deletes an Line-Item from the VKU
 * 
 * @changed 2015-11-05 Added Ajax Support
 * @param type $vku_id
 * @param type $line_item
 * 
 */

function vku_delete_line_item($vku_id, $line_item){

  $vku = new VKUCreator($vku_id);
  if(!$vku ->isActiveStatus()){
    drupal_goto("vku");        
  }

  if($seite = $vku -> getPage($line_item)){
      $vku -> removePage($line_item);  
      
      $message = $vku ->logEvent("remove-page", 'Der Eintrag von der Verkaufsunterlage gelöscht.');
      
      if(isset($_GET["ajax"])){
          $msg = array(
              'error' => 0,
              'message' => $message,
              'id' => $line_item
          );
          
          drupal_json_output($msg);
          exit;
      }
      
      drupal_set_message($message);  
      drupal_goto($vku -> vku_url());   
  }
  else {
      $message = '"Unberechtigter Zugriff"';
      
      if(isset($_GET["ajax"])){
        $msg = array(
              'error' => 1,
              'id' => $line_item,
              'message' => $message
          );
          
          drupal_json_output($msg);
          exit;          
      }
  
     drupal_set_message($message);
     drupal_goto($vku -> vku_url());
  }
}

function vku_edit_line_item($vku_id, $line_item){
global $user;

	$vku = new VKUCreator($vku_id);

	if(!$vku -> isActiveStatus()){
		drupal_goto("vku");
	}

	$author = $vku -> getAuthor();

	if($user -> uid != $author){
            drupal_set_message("Unberechtigter Zugriff");
	    drupal_goto("vku");
	}

   lk_set_subtitle($vku -> get("vku_title"));
   lk_set_icon('edit');


	if($seite = $vku -> getPage($line_item)){
		if($seite["data_class"] == 'title'){
			return drupal_get_form('vku_form_vku_edit', $vku);				
		}


    $func = 'vku_edit_inner_' . $seite["data_module"];
    if(function_exists($func)){
      return $func($vku, $seite);        
    }
	}

	drupal_set_message("Unberechtigter Zugriff");
	drupal_goto("vku");
}







function vku_form_vku_edit_submit($form, &$form_state){
    
      $values = $form_state["values"];
      $vku = new VKUCreator($form['#vku']);
    
      $vku -> set("vku_title", $values["vku_title"]);
      $vku -> set("vku_company", $values["vku_company"]);
      $vku -> set("vku_untertitel", $values["vku_untertitel"]);
      $vku -> update();
      $vku ->logEvent("update-title", "Seite Titel wurde editiert");

     drupal_set_message('Die Verkaufsunterlagen wurden aktualisiert.');
     drupal_goto($vku -> vku_url());
  }
	



  function vku_form_vku_edit($form, $form_state, $vku){
   	
    drupal_set_title("VKU: Titelseite editieren");

    $form['#vku'] = $vku -> getId();
   
   $form['vku_title'] = array(
      '#type' => 'textfield', 
      '#title' => ('Titel des Angebots'), 
      '#default_value' => $vku -> get("vku_title"), 
      '#size' => 60, 
      '#description' => 'Maximallänge: 75 Zeichen',
      '#maxlength' => 75, 
      '#required' => TRUE);
   
   $form['vku_company'] = array(
      '#type' => 'textfield', 
      '#title' => ('Name des Unternehmens'), 
      '#default_value' => $vku -> get("vku_company"), 
      '#size' => 60, 
      '#description' => 'Maximallänge: 50 Zeichen',
      '#maxlength' => 50, 
      '#required' => false);
    
     $form['vku_untertitel'] = array(
      '#type' => 'textfield', 
      '#title' => ('Untertitel (optional)'), 
      '#default_value' => $vku -> get("vku_untertitel"), 
      '#size' => 60, 
      '#maxlength' => 50, 
      '#description' => 'Maximallänge: 50 Zeichen',
      '#required' => FALSE);
      
      $form['submit'] = array(
        '#type' => 'submit',
        '#value' => 'Speichern',
      );
      
  return $form;  
  }



?>