<?php



function lokalkoenig_addkampagne_moderate($node){
 pathtitle('node/x/admin');
    
 $form = drupal_get_form('lk_kampagnen_administrate_form', $node);   
    
 return drupal_render($form);  
}



function lk_kampagnen_administrate_form_validate($form, &$form_state){
 $values = $form_state["values"];
 $action = $form_state['clicked_button']['#id'];


 if($action == "edit-submit-message"){
   if(empty($values["subject"])){
       form_set_error('subject', 'Bitte vergeben Sie einen Betreff');
   }
 
   if(empty($values["text"])){
       form_set_error('text', 'Bitte vergeben Sie eine Nachricht');
   }
 }
}


function lk_kampagnen_administrate_form_submit($form, &$form_state){
  
  $values = $form_state["values"];
  $newstatus = $values["status"];
  
  
  $action = $form_state['clicked_button']['#id'];
  $node = node_load($form["#nid"]);
  
  if(lk_is_admin() AND $newstatus == 'permanent' AND $node -> lkstatus == 'deleted'){
      $node = node_load($form["#nid"]);
      $msg = lk_note('node-delete', "Die Kampagne " . $node -> title . " [".$node -> nid  ."] wurde administrativ gelöscht");
      drupal_set_message($msg);
      
      node_delete($node -> nid);
      drupal_goto('kampagnen/deleted');
  }
  
  
  if($newstatus != (string)$node -> lkstatus){
     drupal_set_message("Der Kampagnenstatus wurde auf <strong>"  . $newstatus  ."</strong> verändert.");
     
     _lk_set_kampagnen_status($form["#nid"], $newstatus); 
     lk_log_kampagne($form["#nid"], "Kampagnenstatus auf " . $newstatus . " geändert.");
  }
  
  // Wenn keine Nachricht 
  if($action == "edit-submit-nomessage"){
      drupal_goto('node/' . $form["#nid"] . '/admin');
  }
 
  if($action == "edit-submit-message"){
      //drupal_goto('node/' . $form["#nid"] . '/delete');

    if($values["subject"] AND $values["text"]){
      privatemsg_new_thread(array(user_load($node -> uid)), $values["subject"], $values["text"]);
      drupal_set_message("Dem Benutzer wurde eine Nachricht gesendet.");
     }
  }
  
  drupal_goto('node/' . $form["#nid"] . '/admin'); 
}



function lk_kampagnen_administrate_form($form, &$form_state, $node){
global $user;

 
  $status = $node -> lkstatus;

  
    $form["mark_sticky"]["#markup"] = ' <ul class="list-group">
  <li class="list-group-item">';
   
 
 $form["#nid"] = $node -> nid;
 $form["#nid_uid"] = $node -> uid;
 
 $form["#attributes"]["class"][] = 'panel panel-default panel-danger';
  
 $options = array(
   'new' => "[N] Editiermodus",
   'proof' => "[P] Überprüfung",
   'canceled' => "[C] Kampagne verwerfen",
   'published' => "[P] Kampagne freischalten",
   'deleted' => "[D] Kampagne verworfen"
  );
  
  
  $uid = $node -> uid;
  
  $possibilities = array();
  
  $possibilities["new"] = array(
    'title' => 'Editiermodus',
    'class' => 'primary',
    'desc' => 'Die Agentur kann die Kampagne editieren.'
  );
   
  $possibilities["canceled"] = array(
    'title' => 'Kampagne mangelhaft',
    'class' => 'warning',
    'desc' => 'Die Agentur kann die Editierung fortsetzen und wird auf Mängel hingewiesen. Die Agentur kann daraufhin die Kampagne wieder editieren.'
  );
  
  
  $possibilities["proof"] = array(
    'title' => 'Kampagne eingereicht',
    'class' => 'warning',
    'desc' => 'Die Kampagne soll überprüft werden und kann nicht von der Agentur verändert werden.'
  );
  
   $possibilities["deleted"] = array(
    'title' => 'Kampagne verwerfen',
    'class' => 'danger',
    'desc' => 'Die Kampagne wird abgelehnt (optional kann die Kampagne auch wieder zum Editieren freigeschalten werden mit einer Begründung).'
  );
 
   if(lk_is_admin() AND $status == 'deleted'){
        $possibilities["permanent"] = array(
            'title' => 'Kampagne permanent löschen',
            'class' => 'danger',
            'desc' => 'Kampagne wird permanant aus dem Portal entfernt.'
        );
   }
   
    $possibilities["published"] = array(
    'title' => 'Kampagne freischalten',
    'class' => 'success',
    'desc' => 'Die Kampagne abschließend für die Verlage freigegeben. Die Agentur kann die Kampagne dann nicht mehr bearbeiten.'
  );
   
  
   
   $possibilities[$node -> lkstatus]["active"] = true;
  
   $arg = '';
  
   if(arg(3)){
      $arg = arg(3);
      if(isset($possibilities[$arg])){
        drupal_set_title($possibilities[$arg]["title"]);
        
        $form['status'] = array('#type' => 'hidden', '#value' => $arg);
      }
      else $arg = '';
   
   }
  
  
    while(list($key, $val) = each($possibilities)){
      if($arg AND $key != $arg){
           continue;
      }
    
        $form["mark_" . $key]["#markup"] = ' <ul class="list-group">
  <li class="list-group-item">';
    
  
    if(isset($val["active"])){
        $form["mark_" . $key]["#markup"] .= '<div class="pull-right"><strong>(aktiv)</strong></div>';
    }
  
     $form["mark_" . $key]["#markup"] .= '
     <a href="'. url('node/' . arg(1) . "/admin/" . $key) .'" ><strong class="btn btn-'. $val["class"] .'">'. $val["title"] .' <span class="glyphicon glyphicon-chevron-right"></span></strong></a>
    <p style="margin-top:15px;">'. $val["desc"] .'</p></li></ul>';
   }
  
  if(!$arg) {  
    if(isset($possibilities['published']["active"])){
    
        $form["intmark_stats"]['#weight'] = -20;
        $form["intmark_stats"]['#markup'] = '<div class="well well-white"><h4>Hinweis:</h4>
        
        <p>Diese Kampagne ist online. Sofern die Kampagne offline genommen wird, werden auch bestimmte verknüften Daten gelöscht.
        Kampagnen mit Lizenzen sollten nicht verändert werden, die Lizenzen bestehen aber weiter.</p>
        
        '. _show_remove_stats($node) .'
        
        </div><hr />' ;
        
    
    
    
    }
  
    return $form;
  }
  
  
   $form["mark_" . $arg]["#markup"]  = '<div class="well"><h3 style="margin-top: 0;">' .$possibilities[$arg]["title"] . '</h3><p>' . $possibilities[$arg]["desc"] . '</p></div>';
  
  
   $form['submit_nomessage'] = array(
    '#type' => 'submit',
    '#value' => 'Status ändern ohne Nachricht zu senden'
  );

    $form['submit_nomessage']['#attributes']['class'] = array('btn btn-success form-submit');
 
      $form['mmark4'] = array(
      '#markup' => '<hr />');  
  
 
  $form['contact'] = array(

  '#type' => 'fieldset', 
  '#title' => ('Nachricht an die Agentur übermitteln'), 
  '#collapsible' => false, 
  '#collapsed' => true,

);

   $form['contact']["mark"] = array(
      '#markup' => '<div class="form-group"><p>Dem Benutzer wird optional eine persönliche Nachricht über das Portal zugestelt.</p></div>'
  ); 

  
  $form['contact']["mark4"] = array(
      '#markup' => '<div class="form-group"><strong>Von:</strong> ' . \LK\u($user -> uid) . '</div>'
  );  
  
  $account = user_load($uid);
  $form['contact']["mark3"] = array(
      '#markup' => '<div class="form-group"><strong>An:</strong> ' . \LK\u($account -> uid) . '</div>'
  );  
  
  
  $variables = array(
    '!kampa_title' => $node -> title,
    '!kampa_link' => url('node/' . $node -> nid, array('absolute' => true)),
    '!message_to' => $account -> name,
    '!message_from' => $user -> name, 
  );
  

  $form['contact']["subject"] = array(
      '#type' => 'textfield',
      '#title' => 'Betreff',
      '#default_value' => format_string(variable_get('lk_kampa_messaging_subj_' . $arg, ''), $variables)
  );


    $form['contact']["text"] = array(
      '#type' => 'textarea',
      '#title' => 'Nachricht an die Agentur',
      '#rows' => 20,
      '#default_value' => format_string(variable_get('lk_kampa_messaging_text_' . $arg, ''), $variables)
    );

    $form['contact']['submit_message'] = array(
    '#type' => 'submit',
    '#value' => 'Speichern und Nachricht senden'
  );

    $form['contact']['submit_message']['#attributes']['class'] = array('btn btn-success form-submit');
    
 
  
  
  return $form;
}


function _show_remove_stats($node){

  $manager = new \LK\Admin\Data\DataManager(\LK\current());
  $kampagne = new \LK\Kampagne\Kampagne($node);
  $stats = $manager -> getKampagneStats($kampagne);

  return \LK\UI\DataList::render($stats);

  /**
 $table = array();
 $dbq = db_query("SELECT count(*) as count FROM lk_lastviewed WHERE nid='". $nid ."'");
 $all = $dbq -> fetchObject();
 
 $table["Kampagnen History"] = $all -> count;
 
 $manager = new \LK\Merkliste\AdminMerkliste(0);
 $table["Merkliste"] = $manager->getGeneralKampagnenCount($nid);
  
 $vku_count = \LK\VKU\VKUManager::getNidInVKUCount($node -> nid);
 $table["VKU-Einträge"] = $vku_count . " (Teilweise betroffen)";
 
 $dbq = db_query("SELECT count(*) as count FROM lk_vku_lizenzen WHERE nid='". $nid ."'");
 $all = $dbq -> fetchObject();
 
 $table["Lizenzen"] = $all -> count . " (Nicht betroffen))";
 $return = '<ul>';
 
 while(list($key, $val) = each($table)){
    $return .= '<li>' . $key . ': '. $val .'</li>';
 }
 
 $return .= '</ul>';
return $return; */
}
