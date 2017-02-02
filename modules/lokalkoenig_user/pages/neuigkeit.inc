<?php

function _lokalkoenig_neuigkeit_show_redirect($id){
global $user;

    drupal_goto("user/" . $user -> uid . "/neuigkeiten/" . $id);
}

function quick_panel($string){
   return '<div class="well well-white">' . $string . '</div>';
}

function _lokalkoenig_neuigkeit_show($account, $id){
    module_load_include('inc', 'eck', 'eck.entity');
    $entity = entity_load('neuigkeit', array($id));
    
    if(!$entity){
        drupal_goto("user/" . $account -> uid . "/dashboard");
    }
    
    if(!lk_is_moderator()){
       $existent = array();
        
       foreach( $entity[$id]->field_recievers["und"] as $i){
            $existent[] = $i["uid"];
       } 
       
       if(!in_array($account -> uid, $existent)){
            drupal_goto("user/" . $account -> uid . "/dashboard");
        }
    }
    
    track_read_neuigkeit($account -> uid, $id);
    
    lk_set_icon('flag');
    drupal_set_title($entity[$id] -> title);
    $view = entity_view('neuigkeit', $entity);
    
    $entity[$id] -> edit_url = url("user/" . $account -> uid . "/neuigkeiten/" . $id . "/edit");
    $entity[$id] -> delete_url = url("user/" . $account -> uid . "/neuigkeiten/" . $id . "/edit", array("query" => array("delete" => 1)));
   
    if(lk_is_moderator() OR $entity[$id] -> uid == $account -> uid){
        $dbq = db_query("SELECT count(*) as count FROM lk_neuigkeiten_read WHERE neuigkeit_id='". $id ."'");
        $result = $dbq -> fetchObject();
        $entity[$id] -> read_count =  $result -> count;
        $entity[$id] -> read_count_details =  url("user/" . $account -> uid . "/neuigkeiten/" . $id, array("query" => array("action" => "readdeatails")));
        
        if(isset($_GET["action"]) AND $_GET["action"] == 'readdeatails'){
            $table = array();
            $dbq = db_query("SELECT * FROM lk_neuigkeiten_read WHERE neuigkeit_id='". $id ."' ORDER BY neuigkeit_read DESC");
            foreach($dbq as $all){
              $table[] = array(\LK\u($all -> uid), format_date($all -> neuigkeit_read, 'middle'));
            }
            
            return quick_panel(theme('table', array('header' => array("Benutzer", 'Gelesen am'), 'rows' => $table)));
        }
        
        
        $content = render($view);
        $content .= theme('neuigkeiten_status', array('entity' => $entity[$id]));   
        return $content;                                  
    };
    
return $view;
}





function _lokalkoenig_neuigkeit_add($account){
  lk_set_icon('flag');
  drupal_set_title("Neuigkeit erstellen");

  // Check if there is an unpublished one
  
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'neuigkeit')
  ->entityCondition('bundle', 'neuigkeit')
  ->propertyCondition('uid', $account -> uid)
  ->fieldCondition('field_message_status', 'value', 'draft', '=')
  ->range(0, 1);

  $result = $query->execute();
  $path = drupal_get_path('module', 'lokalkoenig_user');
   
  drupal_add_js($path . '/js/neuigkeiten.js');
  
  // Edit
  if($result){
     module_load_include('inc', 'eck', 'eck.entity');
     $id = key($result["neuigkeit"]);
     //dpm($id);
     if(isset($_GET["delete"])){
        entity_delete('neuigkeit', $id);
        drupal_set_message("Die Neuigkeit wurde gelöscht.");
        drupal_goto('user/' . $account -> uid . "/addnews");
     }
     
     if(isset($_GET["edit"])){
         return $form = eck__entity__edit('neuigkeit', 'neuigkeit', $id);  
     }
     
     
     
     $entity = entity_load('neuigkeit', array($id));
     $view = entity_view('neuigkeit', $entity);
  
     $entity[$id] -> send_info = neuigkeiten_get_to($entity[$id]);
     $entity[$id] -> edit_url = url('user/' . $account -> uid . "/addnews", array("query" => array('edit' => 1)));
     $entity[$id] -> delete_url = url('user/' . $account -> uid . "/addnews", array("query" =>  array('delete' => 1)));
     $entity[$id] -> publish_url = url('user/' . $account -> uid . "/addnews", array("query" =>  array('publish' => 1)));
     
     if(isset($_GET["publish"])){
          $users = array();
          $sendto = $entity[$id] -> send_info ;
          
          $entity[$id]->field_recievers["und"] = array();
          foreach($sendto["user"] as $u){
            $entity[$id]->field_recievers["und"][]['uid'] = $u;
          }
          
          $entity[$id]->created = time(); 
          $entity[$id]->field_message_status['und'][0]['value'] = 'published'; 
          $entity[$id]->save();
          
          // Send to PrivateMessage
          $subject = $entity[$id] -> title;
          $message = '';
          
          foreach($sendto["user"] as $u){
            if($account -> uid == $u) continue;
            privatemsg_new_thread(array(user_load($u)), $subject, $message, array("neuigkeit" => $id));
          }
          
          drupal_set_message("Die Neuigkeit wurde veröffentlicht.");
          drupal_goto("user/" . $account -> uid . '/dashboard');
          drupal_exit();   
     }
     
     return theme("neuigkeiten_admin", array('entity' => $entity[$id] , 'message' => $view));
  }
 
  module_load_include('inc', 'eck', 'eck.entity');
  $form = eck__entity__add('neuigkeit', 'neuigkeit');
 

  return $form;
}

function _lokalkoenig_neuigkeit_edit($account, $entity_id){
 
  $entity = entity_load('neuigkeit', array($entity_id));
  if(!isset($entity[$entity_id])){
     drupal_goto("user/" . $account -> uid . "/dashboard");
  }
 
  if(!lk_is_moderator()) {
    if($account -> uid != $entity[$entity_id] -> uid){
      drupal_goto("user/" . $account -> uid . "/dashboard");
    }
  }
  
  if(isset($_GET["delete"])){
      entity_delete('neuigkeit', $entity_id);
      $msg = lk_note("news", 'Die Neuigkeit wurde gelöscht.');
      drupal_set_message($msg);
      drupal_goto("user/" . $account -> uid . "/dashboard");
  }
  
 
  
   lk_set_icon('flag');
   drupal_set_title($entity[$entity_id] -> title ." editieren");
   $path = drupal_get_path('module', 'lokalkoenig_user');
   drupal_add_js($path . '/js/neuigkeiten.js'); 
    
  return $form = eck__entity__edit('neuigkeit', 'neuigkeit', $entity_id);   
}

function _neuigkeit_form_alter_submit(&$form, &$form_state){
global $user;
   
   drupal_get_messages();
   $entity = $form['entity']['#value'];
   
   if($form['entity']['#value']->field_message_status['und'][0]['value'] == 'draft'){
       $entity -> field_message_option["und"][0]["value"] = $form_state["values"]["option"]; 
       $entity -> save(); 
       drupal_goto('user/' . $user -> uid . '/addnews');  
   } 
   else {
       drupal_set_message('Die Neuigkeit wurde gespeichert.');
       drupal_goto('user/' . $user -> uid . '/neuigkeiten/' . $entity -> id);  
   }  
}


function _neuigkeit_form_alter_validate(&$form, &$form_state){
  
  //dpm($form_state["values"]);
  
  if($form_state["values"]['field_bild_predefined']['und'][0]['value'] == 'own'){
    if(!$form_state["values"]['field_bild_own']['und'][0]['fid']){
        form_set_error('field_bild_predefined', 'Bitte laden Sie ein Bild hoch.');
    }
  }
  
  if(in_array($form_state["values"]['option'], array('alle_ma_verlage', 'alle_vkl_verlage', 'alle_va_verlage'))){
      if(!$form_state["values"]['field_verlage']['und'][0]['uid']){
        if(!lk_is_verlag()){
            form_set_error('option', 'Bitte wählen Sie mindestens einen Verlag aus.');
        }
      }
  } 
  
  if($form_state["values"]['option'] == 'break'){
        form_set_error('option', 'Unzulässige Auswahl');
  }
}


/**
 * Returns basic information about a news post
 * 
 * @param Object $news
 * @return string
 */
function neuigkeiten_get_recivieres_stats($news){
    
    $recivers = count($news->field_recievers['und']);
  
    $an = '<strong>' . $recivers . '</strong> Benutzer';
    
    if(isset($news->field_message_option['und'][0]['value'])){
        $title = neuigkeiten_get_option_title($news->field_message_option['und'][0]['value']);
        
        if($title){
            $an .= ' / ' . $title; 
        }
    }
    
    $verlage = array();
    if(isset($news -> field_verlage["und"])){
        foreach($news -> field_verlage["und"] as $v){
            $object = \LK\get_user($v["uid"]);
            $verlage[] = (string)$object;
        }

        $an .= ' (<small>Verlage: ' . implode(", ", $verlage) . "</small>)";
    }
    
    $dbq = db_query("SELECT count(*) as count FROM lk_neuigkeiten_read WHERE neuigkeit_id='". $news  -> id ."'");
    $all = $dbq -> fetchObject();
    
    $an .= ' / <span class="label label-primary"><span class="glyphicon glyphicon-stats"></span>&nbsp;&nbsp;'. $all -> count .'</span>';
     
return $an;    
}

function neuigkeiten_get_option_title($option){

    $array = array(
        'verlag_vkl' => 'Alle Verkaufsleiter ihres Verlages',
        'verlag_ma' => 'Alle Mitarbeiter ihres Verlages',
        'alle_admins' => 'Alle Admins',
        'alle_agenturen' => 'Alle Agenturen',
        'alle_verlage' => 'Alle Verlage (inkl. Verlagsübergreifende Mitarbeiter)',
        'alle_ma' => 'Alle Verlage und Mitarbeiter',
        'alle_vkl' => 'Alle Verkaufsleiter',
        'alle_verlage_vkl' => 'Alle Verkaufsleiter und Verlagsaccounts',
        'alle_va_verlage' => 'Alle Verlagsaccounts bestimmter Verlage',
        'alle_ma_verlage' => 'Alle MA bestimmter Verlage',
        'alle_vkl_verlage' => 'Alle Verkaufsleiter bestimmter Verlage'
    );
    
    if(isset($array[$option])){
      return $array[$option];  
    }
    
    return false;
}



function neuigkeiten_get_to($entity){
  
    $option = array();
    
    $option["count"] = 0;
    $option["user"] = array();
    
    $current = LK\current();
    $news_value = $entity->field_message_option['und'][0]['value'];
    $option["title"] = neuigkeiten_get_option_title($news_value);
    
    if(!$option["title"]){
        $option["title"] = 'Undefiniert';
    }
    
    
    switch($news_value) {
      case 'verlag_vkl':
         if($current ->isVerlag() OR $current ->isVerlagController()){
             $verlag = $current ->getVerlagObject();
             $option["user"] = $verlag -> getAllVerkaufsleiter();
         } 
          
        break;
        
      case 'verlag_ma':
          if($current ->isVerlag() OR $current ->isVerlagController()){
             $verlag = $current ->getVerlagObject();
             $option["user"] = $verlag -> getActiveUsers();
          } 
           
        break;
      // Ende Verlagsspezifische Auswahl
      
      case 'alle_admins':
        $option["user"] = \LK\manager()->getAdmins();
        $option["count"]  = count($option["user"]);
        break;
    
      case 'alle_agenturen':
        $option["user"] = \LK\manager()->getAgenturen();
        $option["count"]  = count($option["user"]);
        break;
      
      case 'alle_verlage':
        $option["user"] = \LK\manager() -> getVerlagsAccounts();
        $option["count"]  = count($option["user"]);
        break;
      
      case 'alle_ma':
        $option["user"] = \LK\manager() -> getVerlagsAccounts();
        $option["user"] += \LK\manager()->getMitarbeiter();
        
        break;
      
      case 'alle_vkl':
        $option["user"] = \LK\manager()->getVerkaufsleiter();
        break;
      
      case 'alle_verlage_vkl':  
         $option["user"] =  \LK\manager()->getMitarbeiter();
         $option["user"] +=  \LK\manager()->getVerlage();
      break;
     
      case 'alle_va_verlage':
         $user = array();
         $verlage = array(); 
         foreach($entity->field_verlage['und'] as $test){
              $verlag = \LK\get_user($test["uid"]);
              $verlage[] = (string)$verlag;
              $user[] = $verlag -> getUid();   
              $accounts = $verlag -> getVerlagscontroller();
              
              foreach($accounts as $acc){
                 $obj = \LK\get_user($acc); 
                 if($obj ->getStatus()){
                     $user[] = $acc;  
                  } 
               }
         }
         
        $option["title"] = 'Alle Verlagsaccounts bestimmter Verlage ('. implode(", ",$verlage) .')';
        $option["user"] = $user; 
          
      break;    
  
     case 'alle_ma_verlage':
       
       $user = array();
        $verlage = array(); 
       foreach($entity->field_verlage['und'] as $test){
            $verlag = \LK\get_user($test["uid"]);
            $user[] = $verlag -> getUid();
            $test[] = $verlag -> getUid();
            
            $verlage[] = (string)$verlag;
            
            $new = $verlag -> getActiveUsers();
            foreach ($new as $u) {
                $user[] = $u;
            }
        }
       
       $option["title"] = 'Alle MA bestimmter Verlage ('. implode(", ",$verlage) .')';
       $option["user"] = $user;
    
      break;
        
     case 'alle_vkl_verlage':
        $user  = array();
        $verlage = array(); 
        foreach($entity->field_verlage['und'] as $test){
            $verlag = \LK\get_user($test["uid"]);
            $verlage[] = (string)$verlag;
            $user += $verlag -> getAllVerkaufsleiter();
        }
        
        $option["title"] = 'Alle Verkaufsleiter bestimmter Verlage';
        $option["user"] = $user;
       break;                                                         
     
        
      
      
      default:
            if($current ->isTeamleiter()){
                $team = $current ->getTeamObject();
                $ma = $team ->getUser();

                $option["user"] = array();
                foreach($ma as $u){
                    if($u == $current ->getUid()){
                        continue;
                    }

                    $option["user"][] = $u;
                }

                $option["title"] = 'Mitarbeiter im Team ' . $team ->getTitle();
            }    
            else {
                $option["title"] = 'Not defined: ' . $entity->field_message_option['und'][0]['value'];
            }
        break;
    }
  
    $option["count"]  = count($option["user"]);
    return $option;
}




function neuigkeit_form_alter(&$form, &$form_state){
  
  $current = \LK\current();
    
  $form['field_recievers']['#access']  = false;
  $form['title']['#title']  = 'Überschrift';
  $form['title']['#weight']  = -5;
  $form['#submit'][]  = '_neuigkeit_form_alter_submit';
  $form['#validate'][]  = '_neuigkeit_form_alter_validate';
  
  $form['field_suchwort']['und'][0]['value']['#attributes']['class'][] = 'lk-autocomplete-text';
  //dpm($form);
  
  $form['field_message_option']['#access']  = false;
  $form['field_verlage']['#weight']  = -14;
  $form['submit']['#value'] = 'Vorschau';
  
  $options = array(
    'alle_admins' => "Alle Admins",
    'alle_agenturen' => "Alle Agenturen",
    'alle_verlage' => "Alle Verlagsaccounts (inkl. Verlagsübergreifende Mitarbeiter)",
    'alle_verlage_vkl' => "Alle Verlagsaccounts und Verkaufsleiter",
    'alle_vkl' => 'Alle Verkaufsleiter',
    'alle_ma' => 'Alle Benutzer (Verlage und deren Mitarbeiter)', 
    'break' => '---',
    'alle_vkl_verlage' => 'Alle Verkaufsleiter von bestimmten Verlagen',
    'alle_ma_verlage' => "Alle Mitarbeiter von bestimmten Verlagen",
    'alle_va_verlage' => "Alle Verlagsaccounts von bestimmten Verlagen",
    );
  
  $form["option"] = array(
    '#type' => 'select',
    '#options' => $options,
    '#title' => "Senden an",
    '#weight' => -15
  );
  
  
  if(isset($form['entity']['#value']->field_message_option['und'][0]['value'])){
     $form["option"]["#default_value"] = $form['entity']['#value']->field_message_option['und'][0]['value'];
  }
  
  $form["help"] = array(
    '#type' => 'markup',
    '#weight' => -20,
    '#markup' => '<div class="help-block">Neuigkeiten können an verschiedenste Nutzer gerichtet werden. Wird eine Neuigkeit erstellt, wird dieser per PM an den ausgewählten Nutzerkreis zugestellt.</div>'
  );
 
  if(!lk_is_moderator()){
    $form["option"]['#access'] = false;

    if(lk_is_mitarbeiter()){
        $form["help"]['#markup'] = '<div class="help-block">Die Neuigkeit wird an alle ihre Mitarbeiter gesendet.</div>';
    }
    
    $form['field_verlage']["#access"] = false;
    
    if($current -> isVerlag() OR $current ->isVerlagController()){
        $verlag = $current ->getVerlagObject();
        
        $count_vkl = count($verlag -> getAllVerkaufsleiter());
        $count = count($verlag -> getActiveUsers());
        
        $options = array(
          'verlag_vkl' => 'Alle Verkaufsleiter ihres Verlages ('. $count_vkl  .')',
          'verlag_ma' => "Alle Mitarbeiter ihres Verlages (". $count .")",
        );
  
        $form["option"] = array(
          '#type' => 'select',
          '#options' => $options,
          '#title' => "Senden an",
          '#description' => "Sie können auswählen an welchen Nutzerkreis die Neuigkeit gesendet wird.",  
          '#weight' => -15
        );
     }
  }
  
  if(isset($form['entity']['#value']->field_message_status['und'][0]['value'])){
    if($form['entity']['#value']->field_message_status['und'][0]['value'] != 'draft'){
      $form["help"]["#access"] = false; 
      $form['submit']['#value'] = 'Speichern';
      $form["option"]['#access'] = false;
      $form['field_verlage']["#access"] = false;
    }   
  }
}

?>