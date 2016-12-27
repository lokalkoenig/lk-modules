<?php
/**
 * PLZ-Tool einer Kampagne
 * @Lastedit 2014-05-29
 *
 *
 */    

// OpenGeo DB
// http://www.lichtblau-it.de/downloads


/** Lädt das PLZ-Tool */
function _lokalkoenig_user_plz_load(){
  
   $bundeslaender = array();
  
  if(arg(2)){
    $uid = arg(2);
    $account = user_load($uid);
    
    if(lk_is_verlag($account)){
         $account -> profile = profile2_load_by_user($account);
         $plz_ids = $account -> profile['verlag']->field_plz_sperre['und'];
         
         $plz_auswahl = array('');
         foreach($plz_ids as $plz){
          $term = taxonomy_term_load($plz["tid"]);
          $plz_auswahl[] = $term -> name; 
         }
          
         $landkreise = array();
         
         $dbq = db_query("SELECT c.state_id, c.county_id 
            FROM 
              opengeodb_zipcode z, 
              opengeodb_city c
            WHERE 
              c.id= z.city_id AND
            z.zipcode IN ('". implode("','", $plz_auswahl) . "')"); 
         foreach($dbq as $all){
            $bundeslaender[$all -> state_id] = $all -> state_id;
            $landkreise[$all -> county_id] = $all -> county_id;
         
         }
    }
  }
  
  
  $states = array('<ul style="max-height: 400px; overflow: auto;" id="plzselect">');
  
  if($bundeslaender){
    $dbq = db_query("SELECT * FROM opengeodb_state WHERE id IN (". implode(",", $bundeslaender) . ") ORDER BY name");
  
  }
  else $dbq = db_query("SELECT * FROM opengeodb_state ORDER BY name");
  foreach($dbq as $state){
    $states[] = '<li><span class="state">' .  $state -> name . '</span>';
    
     $states[] = '<ul>';
    
    if($landkreise){
      $dbq2 = db_query("SELECT * FROM opengeodb_county WHERE state_id='". $state -> id . "' AND id IN (". implode(",", $landkreise) .") ORDER BY name");
    }
    else 
    $dbq2 = db_query("SELECT * FROM opengeodb_county WHERE state_id='". $state -> id . "' ORDER BY name");
    foreach($dbq2 as $county){
       $states[] = '<li><span class="county">' .  $county -> name . '</span>';
       
       
       $states[] = '<ul>';
        $dbq3 = db_query("SELECT c.name, z.zipcode FROM opengeodb_zipcode z, opengeodb_city c 
            WHERE c.id=z.city_id AND c.county_id='". $county -> id . "' ORDER BY z.zipcode ASC");
        foreach($dbq3 as $plz){
           $states[] = '<li><span class="plz" plz="'. $plz -> zipcode .'">' .  $plz -> name . ' ('. $plz -> zipcode .')</span></li>';  
        
        }  
        $states[] = '</ul>';
       
       $states[] = '</li>';
    } 
    $states[] = '</ul>';
     $states[] = '</li>';  
  }
   $states[] = '</ul>';
  $out = implode("", $states);
  
  print $out;
  drupal_exit();  

} 


/** PLZ-Speichern */
function _lokalkoenig_editplz_submit(&$form, &$form_state){
  drupal_get_messages();
  drupal_set_message("Die Einstellungen wurden gespeichert.");
  
  lk_log_kampagne(arg(1), 'PLZ Sperre gespeichert');
  $form_state['redirect'] = 'node/' . arg(1) . "/plz";   
  
  na_save_node_rule(arg(1)); 
}

/** PLZ löschen */
function _lokalkoenig_deletekampagne_plz_delete(&$form, &$form_state){
  drupal_get_messages();
  $entity_id = $form['entity']['#value']->id;
  lokalkoenig_nodeaccess_delete_rule($entity_id);  
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
 
  $entity = entity_load('plz', array($entityid));
  
  if(!isset($entity[$entityid])){
      drupal_goto('node/' . $node -> nid . "/plz");
  }
  
  $form = eck__entity__delete('plz', 'plz', $entityid);
  
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

  $edit_entity = 0;
  pathtitle("node/x/plz");
  
  $view = views_embed_view('intern_plz_to_kampagne', 'attachment_1', $node -> nid) . '<div><a class="btn btn-success btn-sm" href="' . url('node/'. arg(1) .'/plz/addextra') . '"><span class="glyphicon glyphicon-plus"></span> PLZ Sperre anfügen</a></div>';
 
  $table = array('<table class="table table-striped table-hover"><tr><th>Ausgabe</th><th>Verlag</th><th>Bis</th></tr>');
  $dbq = db_query("SELECT aid, until FROM na_node_access_ausgaben_time WHERE nid='". $node -> nid ."' ORDER by until ASC");
  $x = 0;
  foreach($dbq as $all){
       $verlag = _format_user(get_verlag_from_ausgabe($all -> aid));
       
       $b = lk_load_ausgabe($all -> aid);
         //dpm($b);
       
       $plz = array();
       foreach($b -> field_plz_sperre['und'] as $i){
        $tax = taxonomy_term_load($i["tid"]);
        $plz[] = $tax -> name;
       }
       
       
       
       
       $x++;
       $table[] = '<tr><td>'. lk_get_ausgaben_title($all -> aid) .' ['. $all -> aid .']<br />'.  count($plz) .' PLZ: <small>'. implode(", ", $plz) .'</small></td>
                      <td>'. $verlag .'</td>
                      <td>'. date("d.m.Y", $all -> until) .'</td>
              </tr>'; 
        
          
    
  }
  
  if($x == 0){
    return $view;
    
  }
  
  $table[] = '</table>';
   
  $view .= '<hr /><h4>Bestehende Sperren nach Ausgabe</h4>' . implode("", $table); 
 
return $view; 
}




?>