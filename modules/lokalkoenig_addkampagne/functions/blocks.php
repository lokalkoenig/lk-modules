<?php

/**
 *  Definiert die Blöcke, die für die Kampagnen-Administration benötigt werden
 *  @Fehlt: Beschränkung auf bestimmte Gruppe
 *
 */   
function lokalkoenig_addkampagne_block_info(){
 
 $blocks['lk_addkampagne_edit_kampa'] = array(
    // info: The name of the block.
    'info' => 'Block: Kampagne bearbeiten',
    // Block caching options (per role, per user, etc.)
  ); #
  
  
  $blocks['lk_addkampagne_edit_kampa_act'] = array(
    // info: The name of the block.
    'info' => 'Block: Kampagne bearbeiten Aktionen',
    // Block caching options (per role, per user, etc.)
  );
  
  $blocks['lk_addkampagne_kampa_status'] = array(
    // info: The name of the block.
    'info' => 'Block: Kampagne Status',
    // Block caching options (per role, per user, etc.)
  );
  
  $blocks['lk_addkampagne_preview'] = array(
    // info: The name of the block.
    'info' => 'Block: Kampagne Vorschau',
    // Block caching options (per role, per user, etc.)
  );
  
  
return $blocks;
}


function _lokalkoenig_addkampagne_block_kampa_status($node){

  if($node -> status == 0){
      return render(node_view(node_load(6)));
  
      return 'Die Kampagne ist angelegt und wird administrativ geprüft.';
  }
}

function lokalkoenig_addkampagne_block_view($delta = '') {
   
  $block = array();
                                  
  if(arg(0) == "node" AND (arg(1))){
          $node = node_load(arg(1));
     
     if(arg(0) == "node" AND arg(1) == "add" AND arg(2) == "kampagne" AND $delta == 'lk_addkampagne_edit_kampa'){
          $node = new stdClass();
          $node -> nid = 0;
          $node -> type = 'kampagne';    
          $node -> lkstatus = 'new';
          $node -> status = 0;
      }
     
      if($node AND $node -> type == 'kampagne'){
            
  // The $delta parameter tells us which block is being requested.
  switch ($delta) {
       
      
    
  
    case 'lk_addkampagne_preview':
         
         //if($node -> lkstatus == 'canceled') return $block;
         
          
         if((($node -> lkstatus == 'new' 
            OR $node -> lkstatus == 'proof' 
            OR $node -> lkstatus == 'canceled') 
              AND !arg(2)) 
                OR arg(2) == 'status'){
            $block['subject'] = 'Vorschau Kampagnen-Seite';
            
            $nodec = clone $node;
            $medien =  @count($nodec -> medien);
            //$presi =  @count($nodec -> presentation);
            
            // Testen ob Medien hochgeladen wurden
            if($medien){
              $nodec -> vmode = 'full';
              
              $view = node_view($nodec, 'full');
              $block['content'] = 
              
              '<div class="width"><h3 class="list-group-item-heading">Vorschau Vollansicht</h3></div>' .
              drupal_render($view);  
            }
            else {
               return $block;
            
            }
         }
    break;
  
  
    case 'lk_addkampagne_edit_kampa_act':
      $block['subject'] = NULL;
      
      return array();
      
      $taxos = _lokalkeonig_get_missing_mediums($node);
      if(count($taxos["select"]) == 0 AND !$taxos["individuell"]){
        
      }
      
      $block['content'] = theme('lk_node_add_block_actions', array("node" => $node)); 
      break;
    
  
    case 'lk_addkampagne_edit_kampa':                           //$node -> lkstatus == 'new' OR $node -> lkstatus == 'canceled'
       
       // Wenn Agentur
       if(lk_is_agentur()){
          switch($node -> lkstatus){
            case 'new':
            case 'canceled':
              // kann bearbeiten  
              $block['content'] = theme('lk_node_add_block', array("node" => $node));  
              break;
            
            case 'proof':
            case 'deleted':
            case 'published':
              // Statistiken etc
              $block['content'] = theme('lk_node_show_agentur_block', array("node" => $node));
              break;
          }
       } 
       elseif(lk_is_moderator()){
          
           if($node -> lkstatus == 'published' AND !arg(2)){
              return array();
           } 
            
           $block['content'] = theme('lk_node_add_block', array("node" => $node));  
       
       
       } 
      break;
  }
  return $block;
  
    }
  }
  else {
    
  
  }
}

function lk_kampagnen_submit_form_submit($form, &$form_state){
global $user;

  $action = 'proof';
  
  lk_log_kampagne($form["#nid"], "Kampagnenstatus auf " . $action . " geändert.");

  $node = node_load($form["#nid"]);

  $url = url("node/" . $form["#nid"], array("absolute" => true));
  
  $msg = "Hallo LK-Admins,\n";
  $msg .= 'der Benutzer ' . $user -> name . " (". url("user/" . $user -> uid, array("absolute" => true)) .") hat soeben eine Kampagne eingestellt und benötigt eine Freigabe.\n\n";
  $msg .= 'Kampagne: ' . $node -> title . " (". url("node/" . $node -> nid, array("absolute" => true)) .")";
  $msg .= "---\nDas ist eine System-Nachricht, bitte nicht darauf antworten.".
  
  
  // Allen Admins eine Nachricht zukommen lassen
  
  
  
  
  privatemsg_new_thread(array(user_load(11)),  "Die Kampagne " . $node -> title . " benötigt Ihre Moderation", $msg);              

  _lk_set_kampagnen_status($form["#nid"], $action); 
  drupal_set_message("Ihre Kampagen wurde eingereicht und wird umgehend geprüft. Sie erhalten eine E-Mail, sobald die Kampagne freigeschalten wird."); 
  drupal_goto('node/' . $form["#nid"] . "/status");
}

function lk_kampagnen_submit_form_validate($form, &$form_state){
  
  // Redirect if is not Checked  
  if(!$form_state["values"]["check"]) drupal_goto("node/" . $form["#nid"]);

}


function lk_kampagnen_submit_form($form, &$form_state, $node){
 
  $form["#nid"] = $node -> nid;
  $form["#attributes"]["class"][] = 'panel panel-default panel-warning';


  $form["mark"] = array(
    '#markup' => '
    <h3><span class="glyphicon glyphicon-chevron-right"></span> Kampagne einreichen</h3>
    <p>Nach dem Sie die Kampagne eingereicht haben, ist diese nicht mehr für Sie editierbar und wird danach administrativ geprüft. Sie erhalten bei erfolgter Freischaltung eine E-Mail.</p>'
  );

  $form['check'] = array(
    '#type' => 'checkbox', 
    '#required' => true,
    '#title' => ('Hiermit bestätige ich, dass ich die Nutzungsbedingungen gelesen habe und diese akzeptiere. Die aktuellen Nutzungsbedingungen finden Sie ' . l("hier", "node/81", array("attributes" => array("target" => "_blank")))),
  );

 $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Kampagne einreichen'
  );
  
  $form['submit']['#attributes']['class'] = array('btn btn-success form-submit');
  
  
  return $form;
}

?>