<?php
 
function vku_vku_admin_edit($lizenz_id){

  $manager = new \LK\Kampagne\LizenzManager();
  $lizenz = $manager ->loadLizenz($lizenz_id);
  
  if(!$lizenz){
    drupal_set_message("Die Lizenz wurde nicht gefunden.");
    drupal_goto($manager -> getOverviewURL());
  }
  
  lk_set_icon('pencil');
  $form = (drupal_get_form("lk_edit_lizenz", $lizenz));
  return "<div class='well well-white'>" . render($form) . "</div>"; 
} 


function lk_edit_lizenz($form, $form_state, \LK\Lizenz $lizenz){

  $author = \LK\get_user($lizenz ->getAuthor());
  
  $form["#lizenz"] = $lizenz;
  $form["mark"] = array('#markup' => '
    <h3>Lizenz ändern:</h3>
    <p>
      Hier können Sie Lizenzen editieren. Bei Änderung der Lizenz erfolgt auch eine Änderung der PLZ-Sperre. Wird die Lizenz gelöscht, erlischt auch die PLZ Sperre.
    </p>
    <div class="well well-white">'. $lizenz ->getSummary() . "</div>");
  
   $form['lizenzerweiterung'] = array(
    '#type' => 'checkbox', 
    '#title' => ('Download erweitern um 30 Tage (von Heute aus)')
  );
  
   
  $verlag = $author -> getVerlagObject();
  
  if($verlag):
        // get Ausgaben von Verlag
        $bereiche = $verlag ->getAusgaben();

        $arr = array();
        while(list($key, $val) = each($bereiche)){
          $arr[$key] = $val ->getTitle();
        }

        // get Ausgaben der Lizenz
        $lizenz_ausgaben = $lizenz -> getAusgaben();

            $form['bereiche'] = array(
               '#type' => 'checkboxes',
               '#options' => $arr,
               '#title' => ('Ausgaben'),
               '#default_value' => $lizenz_ausgaben);
   endif;
        
    $form['actions'] = array(
      '#type' => 'actions',
      '#prefix' => '<div class="form-actions form-wrapper form-group">',
      '#suffix' => '</div>'
        
    );
  
    $form["actions"]['submit_one'] = array(
      '#type' => 'submit',
      '#value' => ('Speichern'),
      '#submit' => array('lk_edit_lizenz_edit'),
    );
  
    $form["actions"]['submit_two'] = array(
      '#type' => 'submit',
      '#value' => ('Lizenz löschen'),
      '#submit' => array('lk_edit_lizenz_delete'),
    );

return $form;
}

function lk_edit_lizenz_validate($form, &$form_state){

  if(!isset($form_state["values"]['bereiche'])){
      return ;
  }  
    
  $count = 0;
  while(list($key, $val) = each($form_state["values"]['bereiche'])) {
      if($val){
           $count++;
      }
  }     
  
  if($count == 0){
      form_set_error("", 'Bitte wählen Sie mindestens eine Ausgabe aus.');
  }
}

function lk_edit_lizenz_edit($form, $form_state){
  
   /**
    * \LK\Lizenz
    */ 
  $lizenz = $form["#lizenz"];
  
  // Lizenzerweiterung 30 days
  if($form_state["values"]['lizenzerweiterung']){
    $new = time() + (60*60*24*30);
    drupal_set_message($lizenz -> extend($new)); 
  }
 
  // when there are ausgaben  
  if(isset($form_state["values"]['bereiche'])):
    $bereiche = array();   
     // list the new Bereiche 
    while(list($key, $val) = each($form_state["values"]['bereiche'])) {
        if($val){
            $bereiche[] = $val;
        }
    }  
    
    $msg = $lizenz -> setAusgaben($bereiche);  
    drupal_set_message($msg);
    
  endif;
 
  drupal_set_message("Die Lizenz wurde administrativ editiert.");  
}


function lk_edit_lizenz_delete($form, $form_state){
    $msg = $form['#lizenz'] -> remove();
    drupal_set_message($msg);
} 
 
?>