<?php

/** PLZ-Speichern */
function _lokalkoenig_editplz_submit(&$form, &$form_state){
  drupal_get_messages();
  drupal_set_message("Die Einstellungen wurden gespeichert.");
  
  lk_log_kampagne(arg(1), 'PLZ Sperre gespeichert');
  $form_state['redirect'] = 'node/' . arg(1) . "/plz";   

  $manager = new \LK\Kampagne\SperrenManager();
  $manager->rebuildAusgabenAccess(arg(1));


  //na_save_node_rule(arg(1));
}

/** PLZ löschen */
function _lokalkoenig_deletekampagne_plz_delete(&$form, &$form_state){
  drupal_get_messages();
  
  $entity_id = $form['entity']['#value']->id;

  $manager = new \LK\Kampagne\SperrenManager();
  $manager ->removeSperre($entity_id);

  drupal_set_message("Die PLZ-Einstellung wurde gelöscht.");
  $form_state['redirect'] = 'node/' . arg(1) . "/plz";
}



/** 
 *  Validiert die Eingabe der PLZ 
 * */
function lokalkoenig_addkampagne_plz_validate($form, &$form_state){
   // Check PLZ-Sperre
   $plz = array(); 
   
   foreach($form_state["values"]['field_plz_sperre']['und'] as $p){
      $plz[] = $p["name"];  
      
      if ( !preg_match('#^[0-9]{5}$#',  $p["name"]) ) {
        form_set_error('field_plz_sperre', "Die PlZ <u>". $p["name"] ."</u> ist nicht nicht valide.");
      } 
   }  
}

/** PLZ editieren */
function lokalkoenig_addkampagne_page_plz_edit($node, $entity){
  drupal_set_title("Postleitzahl-Sperre bearbeiten");

  module_load_include('inc', 'eck', 'eck.entity');
  $form = eck__entity__edit('plz', 'plz', $entity);
    
return $form;  
}

/** PLZ Löschen */
function lokalkoenig_addkampagne_page_plz_delete($node, $entityid){
 
  module_load_include('inc', 'eck', 'eck.entity');
  drupal_set_title("Postleitzahl-Sperre löschen");

  $manager = new \LK\Kampagne\SperrenManager();
  $sperre = $manager->getSperre($entityid);

  if(!$sperre){
    drupal_goto('node/' . $node -> nid . "/plz");
  }
  
  $form = eck__entity__delete('plz', 'plz', $sperre ->getId());

  return $form;
}

/** Gibt zurück wieviele Regeln auf eine Kampagne geschalten sind */
function _lokalkoenig_addkampagne_countplz($nid){
  
  $returns = array();
  
  $dbq = db_query("SELECT entity_id FROM field_data_field_medium_node WHERE entity_type='plz' AND field_medium_node_nid='". $nid ."'");
  foreach ($dbq as $record) {
     $returns[] = $record -> entity_id; 
   }
return $returns;   
}

/** PLZ hinzufügen */
function lokalkoenig_deletekampagne_page_plz_addextra($node){

  $entity = entity_create('plz', array('type' =>'plz'));
  $entity -> field_medium_node["und"][0]["nid"] = $node -> nid; 
  $entity->save();
  $edit_entity = $entity -> id;
  
  drupal_goto("node/" . $node -> nid . "/plz/" . $edit_entity . "/edit");

}

/** 
 *  PLZ in der Übersicht
 * */
function lokalkoenig_addkampagne_page_plz_view($node){

  pathtitle("node/x/plz");

  $view = views_embed_view('intern_plz_to_kampagne', 'attachment_1', $node -> nid) . '<div><a class="btn btn-success btn-sm" href="' . url('node/'. arg(1) .'/plz/addextra') . '"><span class="glyphicon glyphicon-plus"></span> PLZ Sperre anfügen</a></div>';

  $rows = [];
  $dbq = db_query("SELECT aid, until FROM na_node_access_ausgaben_time WHERE nid='". $node -> nid ."' ORDER by until ASC");
  foreach($dbq as $all){
    $ausgabe = \LK\get_ausgabe($all -> aid);
    $verlag = $ausgabe->getVerlagObject();

    $rows[] = [
      $ausgabe ->getTitleFormatted() .' ['. $all -> aid .']<br />'.  count($ausgabe ->getPlz()) .' PLZ /  '.  $ausgabe ->getPlzFormatted(),
      $verlag,
      date("d.m.Y", $all -> until)
    ];
  }

  if(!$rows){
    return $view;
  }
  
  $view .= '<hr /><div class="well well-white"><h4>Bestehende Sperren nach Ausgabe</h4>' . theme('table', [
    'header' => ['Ausgabe', 'Verlag', 'Bis'],
    'rows' => $rows,
   ]);
 
  return $view . "</div>";
}
