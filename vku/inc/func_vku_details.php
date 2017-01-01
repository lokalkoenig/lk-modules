<?php

define("VKU_TIME_MAX", 7);


/** VKU PDF Download
  * @account Account des Pfades
  * @vkuid VKU-ID
  */


function _vku_details($account, $vku_id){
global $user;
    
   $vku = new VKUCreator($vku_id);
   if(!$vku -> is()){
      drupal_goto("user");
      drupal_exit();
   }

   $vkuauthor = $vku -> getAuthor();

   if(!$vku -> hasAccess()){
      drupal_goto("user");
   }
  

   if($vkuauthor != $user -> uid){
       drupal_set_message("Die Verkaufsunterlage wurde nicht von Ihnen erstellt. Sie haben jedoch trotzdem Zugriff auf diesen Bereich.");
   }

  $vkustatus = $vku -> getStatus();
  $vku_company = $vku -> get('vku_company');
  $vku_ready_filename = $vku -> get('vku_ready_filename');
  $vku_generic = $vku -> get('vku_generic');
  $kampagnen = $vku -> getKampagnen();

  drupal_set_title('Verkaufsunterlage');  
  lk_set_icon('lock');

  if(lk_is_moderator()){
    drupal_set_title('VKU ('. $vkustatus .')');

  }

  // Checken wenn VKU-Author nicht mit dem Pfad übereinstimmt
  if($account -> uid != $vkuauthor){
       drupal_goto($vku -> url());
  }

  if($vkustatus == 'active' AND $user -> uid == $vkuauthor AND !lk_is_moderator()){
        drupal_goto($vku -> vku_url());
        drupal_exit();
   }       
   
  if($vku_company){
      lk_set_subtitle($vku_company);
   }      

   // PDF zu erstellen
   if($vkustatus == 'created'){
    drupal_set_title('Generieren der Verkaufsunterlage');  
    lk_set_icon('download');
    
    return theme('vkudetails_create', array('account' => $account, "vku" => $vku, "form" => ''));
   }  

  $admin = NULL;
  if(lk_is_moderator()){
       $admin = vku_administrate($vku);
  }
   
  if(in_array($vkustatus, array("deleted", "active", 'created'))){
    return theme('vkudetails', array('admin' => $admin, 'account' => $account, "vku" => $vku));
  }
  
  lk_set_icon('paperclip');
 
  if($vkustatus == 'purchased' OR $vkustatus == 'purchased_done'){
    $lizenzen = $vku -> getLizenzen();  
    
    drupal_set_title('Downloads');  
    lk_set_icon('download');
    return theme('vkudetails_lizenzen', 
            array('admin' => $admin, 
                  'lizenzen' => $lizenzen, 
                  'account' => $account, 
                  "vku" => $vku, 
                  "form" => ''
            ));
  }
  else {
    $form = drupal_get_form('lk_form_purchase_licences', $vku_id, $kampagnen);  
    $view = drupal_render($form);
    return theme('vkudetails', array('admin' => $admin, 'account' => $account, "vku" => $vku, "form" => $view));
  }
}





function lk_form_purchase_licences_submit($form, &$form_state){
global $user;
   
   $vku = new VKUCreator($form["#vku"]);
   if(!$vku -> is()){
      drupal_not_found();
      drupal_exit();
   }

   $author = $vku -> getAuthor();
   $id = $vku -> getAuthor();

   // Wenn Testverlag dann eine andere Nachricht anzeigen
   if(lk_is_in_testverlag($user)){
      drupal_get_messages(); 
      drupal_set_message("Im Testmodus dürfen Sie keine Kampagnen lizenzieren.");
      drupal_goto('user/'. $author .'/vku/'. $id .'/details'); 
      drupal_exit();   
   }
   
   if($user -> uid == $author){
         $show_plz_info = $vku ->hasPlzSperre();
         
        if($show_plz_info AND $show_plz_info["ausgaben_ids"]){
            // Set User to PLZ-Gebiet
            // Remove from VKU 
            $vku -> removePLZSperren();
            $account = \LK\get_user($user -> uid);
            // Set new Ausgaben
            $account -> setAusgaben($show_plz_info["ausgaben_ids"]);
        }
   }
  

   // Checken ob die Kampagne überhaupt lizensiert werden kann
   // Dbl Check
   
    $nodes = array();
    $values = $form_state["values"];
    while(list($key, $val) = each($values["kampagnen"])){
        if($key == $val){
          
          
        $access = \LK\Kampagne\AccessInfo::userHasAccessToKampagne($author, $key);
        if(!$access) {
             // Reload Page to Show Reasons
             drupal_get_messages(); 
             drupal_goto($vku -> url()); 
             drupal_exit();
          }

          $nodes[] = $key;  
        }
    }
   
   $manager = new \LK\Kampagne\LizenzManager(); 
    
   foreach($nodes as $node){
      $lizenz = $manager ->create($node, $vku);
      $lizenz ->generateZIP();
   }
   
   drupal_get_messages(); 
   $vku -> set('vku_status', 'purchased');
   $vku -> set('vku_purchased_date', time());

   drupal_set_message("Die Lizenzen stehen nun zum Download bereit.");
   drupal_goto($vku -> url());
   drupal_exit();    
}

function vku_administrate(VKUCreator $vku){
   
$status = $vku->getStatus();
$lizenzen = $vku ->getLizenzen();
$link = $vku -> url();

if($status == 'purchased_done' AND !$lizenzen){
    
    if(isset($_GET["admin-remove"])){
        $vku ->remove();
        drupal_get_messages();
        drupal_set_message("Die Verkaufsunterlage wurde gelöscht.");
        drupal_goto('logbuch/vku');    
    }
    
    $msg = 'Diese VKU hat keine aktiven Lizenzen und sollte deshalb entfernt werden.';
    $link = l('<span class="glyphicon glyphicon-trash"></span> VKU entfernen!', $link, array('attributes' => array('class' => array('btn btn-danger')), 'html' => true,"query" => array("admin-remove" => 1)));
    return theme('vkudetails_admin', array("message" => $msg, 'link' => $link));
}
 
return '';    
}


function lk_form_purchase_licences_validate($form, &$form_state){
  
  $x = 0;
  $values = $form_state["values"];
  while(list($key, $val) = each($values["kampagnen"])){
      
      if($val == $key) $x++;
  }
  
  drupal_get_messages();
  
  if(!$values["hinweis"])  form_set_error('', "Bitte bestätigen Sie zuerst unsere Nutzungsbedingungen.");
  
  if($x == 0)
  form_set_error('kampas', "Sie haben keine Kampagne ausgewählt.");
}


function lk_form_purchase_licences($form, &$form_state, $vku_id,  $kampagnen){
global $user;   
   
    $form["#vku"] = $vku_id;
    $form["#kampagnen"] = $kampagnen;
    
    
    $vku = new VKUCreator($vku_id);
    $author = $vku -> getAuthor();
    $show_plz_info = $vku ->hasPlzSperre();
    
    $options = array();
    foreach($kampagnen as $nid){
        $node = node_load($nid);
        $node -> access = na_check_user_has_access($author, $nid);
        
        if($node -> access == false OR $node -> access["access"] == false) {
            // Checken ob Kurzzeitsperre vorliegt
            if($show_plz_info){
                 ; // nothing + grant
            }
            else {
                  drupal_set_message("Die Kampage <strong>\"" . $node -> title . "\"</strong> kann nicht lizensiert werden:<br />" . @$node -> access["reason"]);
                  continue;
            }
        }
        
        $node_view = node_view($node, 'purchase');
        $options[$nid] = render($node_view);
    }


   if(count($options) == 0){
      $form["info"] = array('#markup' => '<em>Es gibt keine Kampagnen zum lizensieren in diesem Verkaufsdokument.</em>');
      return $form;
   }
   
   if($show_plz_info){
      $form["info"] = array('#markup' => '<div class="well lead">' . $show_plz_info["message"] . '</div>');
   }
   

  $form['kampagnen'] = array(
  '#type' => 'checkboxes',
  '#options' => $options,
  '#title' => ('<h4>Lizenzen für folgende Kampagnen bestellen:</h4>'));
  
   if(lk_is_telefonmitarbeiter($user) AND !$show_plz_info){
      $account = _lk_user($user);  
   
       $ausgaben = array();
        
       if(isset($account->profile['mitarbeiter']->field_ausgabe['und'])){
          foreach($account->profile['mitarbeiter']->field_ausgabe['und'] as $a){
            $ausgaben[] = format_ausgabe_kurz($a["target_id"]);
          }
       }
      
      $link = '<a class="btn btn-sm btn-primary" href="'. url("user/" . arg(1) . "/setplz", array("query" => drupal_get_destination())) .'"><span class="glyphicon glyphicon-globe"></span> Ausgaben wechseln</a>';
      
       $form['hr2'] = array('#markup' => '<div class="well">
       <div class ="row clearfix">
       <div class="col-xs-9">Sie bestellen die ausgewählten Kampagnen für folgende Ausgaben: ' . implode(" ", $ausgaben) . '</div> 
       <div class="col-xs-3 text-right">'. $link .'</div></div></div>');
    }     
  
  
     $form['hinweis'] = array(

  '#type' => 'checkbox', 
  '#required' => true,
  '#title' => "Hiermit bestätige ich, dass ich die Nutzungsbedingungen gelesen habe und diese akzeptiere. Die aktuellen Nutzungsbedingungen finden Sie ". l("hier", "node/257", array("attributes" => array("target" => "_blank"))) .".");
  
   $form['hr'] = array('#markup' => '<hr />');
    
   $form['submit'] = array(
      '#type' => 'submit',
      '#value' => ('Zahlungspflichtig bestellen'),
    );
   
  $form['submit']["#attributes"]["class"] = array('btn btn-success btn-lg'); 
  
   if(lk_is_in_testverlag($user)){
      $form['submit']["#attributes"]["class"][] = ('disabled');  
      $form['submit_hinweis'] = array('#markup' => '<em style="line-height: 40px;" class="pull-right">(Nicht verfügbar im Testmodus)</em>', "#weight" => 20); 
   }
  
 return $form;
}




?>