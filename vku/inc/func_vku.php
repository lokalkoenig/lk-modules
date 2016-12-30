<?php

function _vku_redirect_current(){
	
	$id = vku_get_active_id();        

	if($id){
		drupal_goto("vku/". $id);
		drupal_exit();
	}
	else {
		
		drupal_goto("user");
		drupal_exit();
	}
}


function _vku_create(){
global $user;

  $vku = new VKUCreator('new', array('uid' => $user -> uid));
  if(!$vku -> is()){
    drupal_set_message("Ein Fehler ist aufgetreten");
    drupal_goto("vku");
  }

  $id = $vku -> getId();
  $vku = new VKUCreator($id);
  $vku ->logEvent("new", "Eine neue VKU wurde erstellt");
  
  if(vku_is_update_user()){
    $vku ->setStatus('new');
    drupal_goto($vku -> url());
    exit;
  }
  else {
      $vku -> isCreated();
  }
  
  $title_id =  $vku -> getPageId('title');
  drupal_goto("vku/" . $id . "/edit/" . $title_id);
  //http://localhost/vku/503/edit/242?destination=vku/503
}

/**
 * Shows the VKU Generation form
 * @changes 2015-11-09 Supports also Templates
 * 
 * var $id ID of the VKU
 */
function _vku_show($id){
global $user;
    
    

    $vku = new VKUCreator($id);
    if(!$vku -> is()){  	
    	drupal_goto("user");
    }

    $status = $vku ->getStatus();
    
    
    $author = $vku -> getAuthor();
	if($user -> uid != $author){
		drupal_set_message("NO current one");	
		drupal_goto("user");
	}
    
    drupal_add_library('system', 'ui.sortable');
    drupal_add_library('system', 'ui.droppable');
        
    if(vku_is_update_user()){
        
        if(!in_array($status, array('active', 'template', 'new'))){
            drupal_goto($vku -> url());
        }

        
        
        include_once(__DIR__ . "/func_vku2_generate.php");
        return vku2_generate_form($vku);
    }    
    
    if(!in_array($status, array('active', 'template', 'new'))){
        drupal_goto($vku -> url());
    }

    
    
    drupal_set_title("Verkaufsunterlage");
    lk_set_icon('tint');
   
     $add = '';
     drupal_add_library('system', 'ui.sortable');
     drupal_add_js("sites/all/modules/lokalkoenig/vku/js/vku.js");
     drupal_add_css("sites/all/modules/lokalkoenig/vku/css/vku.css");
     
    if($status == 'active'){
        
        // Überprüfen ob zu viele Kampagnen vorliegen
        $count = count($vku -> getKampagnen());
        $max_nids = variable_get("lk_vku_add_max", 3);
        
        if($count > $max_nids){
            drupal_set_message("Sie haben mehr als <b>" . $max_nids . " Kampagnen</b> in der Verkaufsunterlage. Bitte reduzieren Sie die Anzahl indem Sie Kampagnen entfernen.", 'error');
        }
       
        $form = drupal_get_form('vku_form_vku_apply', $id, $user -> uid);
        $form = render($form);
        
         return theme('vku', array(
   		'vkunew' => $vku,
                'addons' => $add,
                'submitform' => $form,
                'ausgaben' => array(
                    //'days' => 5,
                    //'telefon' => $account -> isTelefonmitarbeiter(),
                    //'telefon_link' => url('user/' .  $account -> uid . "/setplz"),
                    //'ausgaben' => $ausgaben_formatted
                ),    
                'template_form' => '')
          );
    }    
    else {
       $form = drupal_get_form('vku_form_vorlage', $vku);
       $form = render($form); 
        
       drupal_set_title("Personalisierte Vorlage"); 
       lk_set_subtitle("VKU");
       
       return theme('vku', array(
   		'vkunew' => $vku,
                'addons' => $add,
                'submitform' => '',
                'template_form' => $form)
          );  
    }
}


  function vku_form_vku_apply($form, &$form_state, $vkuid, $userid){
    
    $form["#vku"] = $vkuid;
    $form["#userid"] = $userid;
    
     // Add Ausgaben-Support für Ausgaben
     // Show current Ausgaben, before VKU-Sumit
     $account = \LK\get_user((int)$userid);
     $ausgaben =  $account -> getCurrentAusgaben();
        
     $ausgaben_formatted = array();
     foreach($ausgaben as $ausgabe){
        $object = \LK\get_ausgabe($ausgabe);     
         if($object){
             $ausgaben_formatted[] = $object -> getTitleFormatted(); 
         }
     }
     
     
     $link = '';
     // Can adjust theese Settings
     if($account -> isTelefonmitarbeiter()){
         $link = '<a class="pull-right btn btn-sm btn-primary" href="'. url('user/' . $account -> uid . '/setplz', array("query" => drupal_get_destination())) .'"><span class="glyphicon glyphicon-cog"></span> Ausgaben anpassen</a>';
     }
    
    $days = 0; 
    $verlag = $account -> getVerlag();
    if($verlag){
        $verlag_user = \LK\get_user($verlag);
        $days = $verlag_user -> getVerlagSetting('sperrung_vku_pdf', 0);
    }
     
    if($ausgaben_formatted AND $days){
        $form["mark"] = array(
            '#weight' => 10,
            '#markup' => '<hr />'
            . '<div class="row"><div class="col-xs-6">'
            . '<p><span class="glyphicon glyphicon-exclamation-sign"></span> Bitte beachten Sie, dass die Kampagnen für '. $days .' Tage für '
            . 'Ihre ausgewählten Ausgaben vorgemerkt werden:</p></div><div class="col-xs-6">'
            . $link
            . '<ul class="list-inline"><li>' . implode("</li><li>", $ausgaben_formatted) . '</li></ul></div></div>'
        );
   }
    
    $form['submit'] = array(
      '#type' => 'submit',
      '#weight' => 9,  
      '#attributes' => array('class' => array('btn btn-yellow-arrow')),
      '#value' => 'Verkaufsunterlage herunterladen',
    );
    return $form;
  }


function vku_form_vku_apply_submit($form, $form_state){
global $user;

    $id = $form["#vku"];        
    
    $vku = new VKUCreator($id);
    if(!$vku -> is('active')){      
        drupal_goto("vku");
    }    

    $author = $vku -> getAuthor();
    if($user -> uid != $author){
        drupal_set_message("NO current one");   
        drupal_goto("vku");
    }

    
    $vku -> update();
    $error = 0;
    
    $max_nids = variable_get("lk_vku_add_max", 3);
    // Seiten 
    $count = count($vku ->getKampagnen()); 
    if($count > $max_nids){
      $error++; 
    }
    
    drupal_get_messages();
    
    $pages = $vku -> getPages();
    $active = 0;

    while(list($key, $val) = each($pages)){
        if($val["data_active"]){
            $active++;
        }

        if($val["data_class"] == 'kampagne'){
            $node = node_load($val["data_entity_id"]);
            $access = na_check_user_has_access($author, $node -> nid);
            if(!$node -> status OR $access["access"] == false){
              drupal_set_message("Die Kampagen <b>" . $node -> title . "</b> kann nicht mehr lizensiert werden. Bitte löschen Sie die Kampagne aus Ihrer Verkaufsunterlage.");    
              $error++;
            }
        }

    }

    if($active == 0){
      $error++;
      drupal_set_message("Bitte aktivieren Sie mindestens eine Seite in der Verkaufsunterlage");
    }

    // Checken ob die Nodes verwendet werden dürfen.
    if($error){
       drupal_goto($vku ->url());
       drupal_exit();
    }
    
    $vku -> setStatus('created');
    $vku -> update();
    
    $vku -> setShortPlzSperre();   
    $vku ->logVerlagEvent('Verkaufsunterlage fertig gemacht');
    
    drupal_goto('user/'. $author .'/vku/'. $id .'/details');
  }





?>