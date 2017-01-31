<?php
/** Status Funktionen */

function lokalkoenig_addkampagne_stats($node){
  pathtitle('node/x/stats');
  
  $stats = array();
  
  $dbq = db_query("SELECT count(*) as count FROM  lk_vku_lizenzen WHERE nid='". $node -> nid ."'");
  $res = $dbq -> fetchObject();
  
  $stats["Anzahl von erworbenen Lizenzen"]  = $res -> count; 
  $count = \LK\VKU\VKUManager::getNidInVKUCount($node -> nid);
  
  $stats["Anzahl der Kampagne in allen Verkaufsdokumenten"]  = $count;
  
  $dbq = db_query("SELECT avg(field_kamp_beliebtheit_value) as count 
    FROM field_data_field_kamp_beliebtheit");
   $res = $dbq -> fetchObject();   
  
  
  $stats["Popularitätsindex"]  = $node->field_kamp_beliebtheit['und'][0]['value'];
  $stats["Durchschnitt aller Kampagnen"]  = round($res -> count, 2);
  
  
  return theme("lk_node_show_stats", array("stats" => $stats));
}


function lokalkoenig_addkampagne_contact(){
  pathtitle('node/x/contact');
  
  $node = node_load(arg(1)); 
  $form = drupal_get_form('lokalkoenig_addkampagne_contact_form', $node);
  
  return render($form);
}


function lokalkoenig_addkampagne_contact_form_submit($form, $form_state){
global $user;

    $nachricht = $form_state["values"]["nachricht"]; 
    $node = $form["#node"];
    
    $nachricht .= "\n\nLink zur Kampagne: ". url("node/" . $node -> nid, array("absolute" => true));
    privatemsg_new_thread(array(user_load(11)), 'Kampagne ' . $node -> title, $nachricht);
    
    drupal_set_message("Eine Nachricht wurde an das Admin-Team versendet.");
    drupal_goto("node/" . $node -> nid);
}

function lokalkoenig_addkampagne_contact_form($form, $form_state, $node){
  $form = array();

  $form["text"] = array('#markup' => '<div class="well">Haben Sie einen Fehler in der Kampagne entdeckt oder haben andere Anmerkungen dazu, dann schreiben Sie uns bitte eine persönliche Nachricht.</div>');

  $form["#node"] = $node;

  $form['nachricht']=array(
        '#type'=>'textarea',
        '#required' => true,
        '#title'=>('Ihre Nachricht')
      );


 $form['submit']=array(
        '#type'=>'submit',
        '#value'=>('Abschicken')
      );
      
      
 return $form;
}



?>