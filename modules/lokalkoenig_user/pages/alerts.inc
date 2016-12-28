<?php
use LK\Alert\AlertManager;
use LK\Solr\SearchQueryParser;
use LK\UI\Table;


namespace LK\Alert;

/**
 * Alert Controller
 * 
 * @param type $account
 * @param type $page
 */
function _lk_alerts_page($account, $page = true){
  
  // GET add=1&search-query
  
  $action = ''; 
  $id = 0;
  
  if(isset($_GET["action"])){
      $action = $_GET["action"];
  }

  if(isset($_GET["id"])){
      $id = (int)$_GET["id"];
  }

  if($page == true) :
      drupal_set_title("Alerts verwalten");
      lk_set_icon('flag');
  endif;
  
  if($action == 'add'){
      $query = SearchQueryParser::get();
      
      // Remove Sort by parameter
      if(isset($query["sort_by"])){
          unset($query['sort_by']);
      }
      // prevent double entries
      $title = SearchQueryParser::toLabel($query);
      $result = AlertManager::searchTitle($title, $account -> uid);
      
      if(!$result):
        $alert = AlertManager::create($query);
        drupal_set_message("Der Alert <em>" . $alert ->getTitle() . "</em> wurde erstellt. " . l("Alerts verwalten", 'user/' . $alert ->getAuthor() . "/alerts"));      
      
     else:
         drupal_set_message("Sie haben bereits einen Alert zu dieser Suche erstellt. " . l("Alerts verwalten", 'user/' . $account -> uid . "/alerts"));
     endif;
      
      
      if(isset($_GET["result"]) AND $_GET["result"] == 0){
          drupal_goto("suche");
      }
      
      drupal_goto("suche", array("query" => $query));     
  }
  
  if($action == 'delete' AND $id){
        $alert = AlertManager::load($id);   
        if($alert AND $alert -> getAuthor() == $account -> uid){
            lk_note("alert", "Loesche Alert " . $alert);
            drupal_set_message("Der Alert <em>" . $alert ->getTitle() . "</em> wurde gelöscht.");      
            $alert ->remove();
            drupal_goto('user/' . $alert -> getAuthor() . "/alerts");
        }
  }
  
  
  // Show existing Alters
  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'alert')
  ->entityCondition('bundle', 'alert')
  ->propertyCondition('uid', $account -> uid)->propertyOrderBy('changed', 'DESC');

  $result = $query->execute();
  
  $table = new Table();
  $table ->setHeader(array('Suche', 'Angelegt', 'Kampagnen', ''));
  $count = 0;
 if(isset($result["alert"])){
   foreach($result["alert"] as $alert_entity){
        $alert = AlertManager::load($alert_entity -> id);
        
        if(!$alert){
            continue ;
        }
        $table -> addRow(array($alert ->getTitle() . '<br /><small><a href="'. $alert -> getSearchLink() .'">Suchergebnisse öffnen</a></small>', 
            date("d.m.Y", $alert ->getCreated()), 
            $alert ->getCount(), '<a class="btn btn-sm btn-danger optindelete" href='. $alert ->getRemoveLink() .' optintitle="Alert löschen" optin="Sind Sie sicher?">Alert löschen</a>'));
        $count++;        
    }
 }  

 return theme('alerts_overview', array("account" => $account, 'count' => $count, "table" => (string)$table)); 
}

?>