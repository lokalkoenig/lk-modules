<?php
use LK\Alert\AlertManager;
use LK\Solr\SearchQueryParser;

/**
 * Alert Controller
 * 
 * @param type $account
 * @param type $page
 */
function _lk_alerts_page($uid, $page = true){
  
  $account = \LK\get_user($uid);
  
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
  
  $alertmanager = new \LK\Alert\AlertManager();
  
  if($action == 'add'){
      $query = SearchQueryParser::get();
      
      // Remove Sort by parameter
      if(isset($query["sort_by"])){
          unset($query['sort_by']);
      }
      // prevent double entries
      $title = SearchQueryParser::toLabel($query);
      $result = $alertmanager -> searchTitle($title, $account);
      
      if(!$result):
        $alert = $alertmanager -> create($query);
        drupal_set_message("Der Alert <em>" . $alert ->getTitle() . "</em> wurde erstellt. " . l("Alerts verwalten", 'user/' . $alert ->getAuthor() . "/alerts"));      
      
     else:
         drupal_set_message("Sie haben bereits einen Alert zu dieser Suche erstellt. " . l("Alerts verwalten", 'user/' . $account -> uid . "/alerts"));
     endif;
      
      
      if(isset($_GET["result"]) AND $_GET["result"] == 0){
          drupal_goto("suche");
      }
      
      drupal_goto("suche", array("query" => $query));     
  }
  
  if($action == 'remove' AND $id){
        $alert = $alertmanager ->loadAlert($id);
       
        if($alert && $alert -> getAuthor() == $account ->getUid()){
            drupal_set_message("Der Alert <em>" . $alert ->getTitle() . "</em> wurde gelöscht.");      
            $alert ->remove();
            drupal_goto('user/' . $alert -> getAuthor() . "/alerts");
        }
  }

  return $alertmanager ->listAlerts($account);
}

?>