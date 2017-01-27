<?php

$_lk_faq_node = NULL;
$_lk_subtitle = NULL;
$_lk_icon = NULL;



function lokalkoenig_admin_block_info(){

 $blocks['lokalkoenig_title'] = array(
    // info: The name of the block.
    'info' => 'Block: Title der Seite',
    // Block caching options (per role, per user, etc.)
  );

return $blocks;
}




function lokalkoenig_admin_block_view($delta = '') {
global $user, $_lk_subtitle, $_lk_icon, $_lk_faq_node;
  
  $block = array();

  if($delta == 'lokalkoenig_title'){
    $content = '<div class="page-header">';
    
    if((arg(0) == 'merkliste3' AND arg(1)) OR (arg(0) == "node" AND $node = node_load(arg(1)) AND $node -> type == 'kampagne' AND !arg(2))){
      $content .=  '<div class="pull-right" style="margin-top: 0px; font-size: 2em;"><a href="javascript:history.back()" style="font-size: 0.7em; color: White;">Zurück <span class="glyphicon glyphicon-chevron-right"></span></a></div>';
    }
    $content .= '<h2>';
    
   $title = drupal_get_title();
    
    if($user -> uid != 0){
      if(arg(0) == "user" AND !arg(2) AND $account = user_load(arg(1)) AND $account -> uid != 0){
        //$title = lk_get_username_and_role($account); 
      }
    }
    
    if(arg(0) == "node" AND $node = node_load(arg(1)) AND !arg(2)){
    
      
      if($node -> type == 'kampagne' AND $node -> status == 1){
        $title = 'Detailansicht'; 
        $content .= '<span class="glyphicon glyphicon-plus"></span> ';
      }
    }
    
    if($_lk_icon){
      $content .= '<span class="glyphicon glyphicon-'. $_lk_icon .'"></span> ';
    }  
      
    $content .= $title;
    
    if($_lk_subtitle){
      $content .= ' <small>' . $_lk_subtitle . '</small>';
    }
    
    $content .= '</h2></div>';
    
    return array('content' => $content);
  }
}  


function lk_get_subtitle(){
    return $GLOBALS["_lk_subtitle"];
}

function lk_set_subtitle($subtitle = NULL){
global $_lk_subtitle;
  $_lk_subtitle = $subtitle;
}

function lk_set_icon($icon = NULL){
global $_lk_icon;

  $_lk_icon = $icon;
}

function lk_set($pagetitle, $subtitle, $icon){
  
   drupal_set_title($pagetitle);
   lk_set_subtitle($subtitle);
   lk_set_icon($icon);
}

function pathtitle($path, $special = NULL){
  
  $node = new stdClass();
  $node -> title = 'Lokalkönig';
  $node -> type = 'dummy';
  
  if(arg(0) == "node"){
    $nodel = node_load(arg(1));
    if($nodel){
       //dpm($nodel);
       
       $node = $nodel;
    // Kampagnen-Typ
    if($node -> type == "kampagne" AND !$special){
        
        if(isset($node->field_kamp_preisnivau['und'][0]['tid'])){
            $tax = taxonomy_term_load($node->field_kamp_preisnivau['und'][0]['tid']);
            $special = '<span class="label label-success">'. $tax -> name .'</span>';
        }
        
        if(lk_is_moderator()){
          $special .= ' <span class="label label-info">'. strtoupper($node -> lkstatus) .'</span>'; 
        
        }
      }
    }
    
  }
  
  switch($path){
    
    case 'search':
       lk_set_icon('search');
       drupal_set_title("Suche");
      break;
    
    
    case 'dashboard':
      lk_set_icon('th-large');
      drupal_set_title("Startseite");
      break;
      
    // Medien verwalten
    case 'node/x/media':
      lk_set("Medien verwalten", $node -> title, 'ok');
     // lk_set_faq(7);
      
      
      lk_set_subtitle($node -> title);
      break;  
  
    case 'node/x/log':
      lk_set("Aktionen",$node -> title, 'list');
      //lk_set_faq(7);
    
      break;
      
      
    case 'node/x/contact':
      lk_set("Kontakt",$node -> title, 'user');
      //lk_set_faq(7);
    
      break;  
    
    case 'node/x/stats':
      lk_set("Statistiken",$node -> title, 'list');
      //lk_set_faq(7);
    
      break; 
    
    case 'node/x/addmedia':
      //lk_set_faq(7);
      lk_set_icon('cloud-upload');
      
      if($node)  lk_set_subtitle($node -> title);
      drupal_set_title("Upload hinzufügen");
    
      break;
  
    case 'node/add/kampagne':
     //lk_set_faq(7);
      drupal_set_title("Kampagne erstellen");
      //lk_set_subtitle('Aussagekräftiger Untertitel');
      lk_set_icon('plus');
      break;
    
    
    case 'node/x/delete':
      //lk_set_faq(7);
      //drupal_set_title("Kampagne erstellen");
      lk_set_subtitle('Löschen bestätigen');
      lk_set_icon('trash');
      break;
       
    case 'node/kampagne/edit':
       //lk_set_faq(7);
      drupal_set_title('Kampagne editieren');
      lk_set_subtitle($node -> title);
      lk_set_icon('edit');
      break;
   
    case 'node/x/plz/x/edit':
      //lk_set_faq(7);
      drupal_set_title('PLZ-Sperre editieren');
      lk_set_subtitle($node -> title);
      lk_set_icon('edit');
      break;
    
   
    
    case 'node/x/plz':
        //lk_set_faq(7);
        drupal_set_title("Postleitzahl-Sperre einrichten");
        lk_set_subtitle($node -> title);
        lk_set_icon('edit');
      break;
    
    case 'node/x/plz/x/delete':
      lk_set_icon('trash');
      lk_set_subtitle($node -> title);
      drupal_set_title("PLZ-Regel löschen");
      //lk_set_faq(7);
    
      break;  
      
    case 'node/x/media/x/edit':
      lk_set_icon('edit');
      lk_set_subtitle($node -> title);
      drupal_set_title("Upload bearbeiten");
      //lk_set_faq(7);
      break;  
  
    case 'node/x/media/x/delete':
      lk_set_icon('trash');
      lk_set_subtitle($node -> title);
      drupal_set_title("Upload löschen");
      //lk_set_faq(7);
      break;
  
    case 'node/x/presentation':
      lk_set_icon('align-justify');
      lk_set_subtitle($node -> title);
      drupal_set_title("Präsentation einrichten");
      //lk_set_faq(7);
      break;
      
    case 'node/x/admin':
      lk_set_icon('send');
      lk_set_subtitle($node -> title);
      drupal_set_title("Kampagne verwalten");
      //lk_set_faq(7);
      break;  
    
    
    /********************************** User-Seiten  **********************************/
      
    // Userseiten
    case 'user/pass':
      lk_set_icon('flag');
      //lk_set_subtitle('Passwort oder Kontakt-Email-Adresse ändern');
      drupal_set_title("Passwort zurücksetzen");
    
      break;
  
  
    case 'user/pass/reset':
      lk_set_icon('flag');
      //lk_set_subtitle('Einmalige Anmeldung');
      drupal_set_title("Passwort zurücksetzen");
    
      break;
    
    
  
    case 'user/x/edit':
      
      lk_set_icon('wrench');
      
      
      if(arg(3)){
      
      }
      else {
        //lk_set_subtitle('Passwort oder Kontakt-Email-Adresse ändern');
        drupal_set_title("Passwort ändern");
      }
      
      break;
  
    case 'user/x/edit/main':
      lk_set_icon('edit');
      //lk_set_subtitle('Persönliche Daten und Kontaktdaten');
      drupal_set_title("Profildaten bearbeiten");
      //lk_set_faq(7);
      break;
  
    case 'user/x/kampagnen':
      lk_set_icon('lock');
      //lk_set_subtitle('Behalten Sie hier Ihre Kampagnen im Überblick');
      drupal_set_title("Ihre Kampagnen");
      //lk_set_faq(7);
   
      break;
  
    case 'node/x/preview':
      lk_set_icon('lock');
      lk_set_subtitle($node -> title);
      drupal_set_title("Ihre Kampagne");
      //lk_set_faq(7);
      break;
    
     case 'node/x/status':
       lk_set_subtitle($node -> title);
       drupal_set_title("Ihre Kampagne");
       //lk_set_faq(7);
       lk_set_icon('lock');
      break;
    
    
    case 'messages':
      lk_set_subtitle("Lokalkönig Messaging System");
       //lk_set_faq(7);
       lk_set_icon('envelope');
      break;
    
    
    
    case 'merkliste':
        lk_set_icon('tag');
          drupal_set_title('Merklisten');
    break;
    
    
    case 'user/x/unteraccounts':
      lk_set_icon('user');
      drupal_set_title('Mitarbeiter-Accounts');
      break;
    
      
    default:
      //lk_set_faq(7);
      drupal_set_title('No Title');
      lk_set_subtitle('für ' . $path);
      lk_set_icon('edit');
      
    break;  
    
      
      
    
  }
  
  if($special){
    lk_set_subtitle(lk_get_subtitle() . " <small>". $special ."</small>");
  } 
}


function _formlk(&$form){
  $form["#title"] = drupal_get_title(); 
  $form["#attributes"]["class"][] = 'panel panel-default panel-info';    
}


function _formlkdelete(&$form, $pathcancel){
    $form["actions"]["cancel"]["#href"] = $pathcancel;
    $form["actions"]["cancel"]["#options"]["path"] = $pathcancel;
    $form['submit_redirect']["#value"] = $pathcancel;
    
    $form["description"]['#markup'] = '<div class="alert alert-warning">' . $form["description"]['#markup'] .'</div>';
    
    _formlk($form);   
}

