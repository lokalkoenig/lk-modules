<?php

use LK\Admin\UserManagerMaintanance;

/**
 * 
 * 
 * @param stdClass $account_user
 * @return string
 */
function lokalkoenig_user_info($account_user){

  drupal_set_title("Usermanager");
  lk_set_icon('tasks');

  $account = \LK\get_user($account_user -> uid);
  $manager = new \LK\Admin\Data\DataManager();
  $array = $manager ->getUserStats($account);
  $stats = \LK\UI\DataList::render($array);
  
  $links_rendered = [];
  
  if($manager -> canChangeUserState()){
    if($account ->getStatus()){
      $links[] = array("link" => url("user/" . $account ->getUid(). "/info", array("query" => array("action" => "deactivate"))),
            "class" => "btn btn-sm btn-warning",
            "title" => "Benutzer deaktivieren");
    }
    else {
      $links[] = array("link" => url("user/" . $account ->getUid(). "/info", array("query" => array("action" => "activate"))),
            "class" => "btn btn-sm btn-primary",
            "title" => "Benutzer aktivieren");
    }

    foreach($links as $link){
      $links_rendered[] = '<a href="'. $link["link"] .'" class="btn-sm '. $link["class"] .'">' . $link["title"] .'</a>';
    }
  }
  else {
    $links_rendered[] = '<p><small>Der Status des Users kann im Moment nicht verändert werden.</small></p>';
  }

  return '<div class="well well-white"><h4>Übersicht</h4>'. $stats .'<hr />'. implode(' ', $links_rendered) .'</div>';


  $user_account = \LK\get_user($account);
  $maintance = new UserManagerMaintanance($user_account);
    
 
    
  $stats = $maintance -> listStats();
  $table = '<table class="table">';
  foreach($stats as $stat):
    $table .= '<tr><td>'. $stat["title"] .'</td><td>'. $stat["count"] .'</td></tr>';           
    
    if(isset($stat["info"])){
      $table .= '<tr><td colspan="2"><div class="help-block">'.$stat["info"] .'</div></td></tr>';     
    }
  endforeach;
    
  $table .= '</table>';
   
  $links = array();
    
  if($maintance -> canDeactivate()){
      $links[] = array("link" => url("user/" . $account -> uid. "/info", array("query" => array("action" => "deactivate"))),
          "class" => "btn btn-warning",
          "title" => "Benutzer deaktivieren");
  }

    if($maintance ->canActivate()){
      $links[] = array("link" => url("user/" . $account -> uid. "/info", array("query" => array("action" => "activate"))),
          "class" => "btn btn-success",
          "title" => "Benutzer aktivieren");
    }

    if($maintance ->canDelete()){
      $links[] = array("link" => url("user/" . $account -> uid. "/info", array("query" => array("action" => "delete"))),
          "class" => "btn btn-danger",
          "title" => "Benutzer löschen");
    }
  
    
  if(isset($_GET["action"])){
      if($_GET["action"] == "delete"){

          if(!isset($_GET["confirm"])){
              drupal_set_title("Benutzer löschen");
              return lokalkoenig_user_info_confirm($user_account, 'delete', 'Account Löschen');
          }


         $result = $maintance -> userDelete(); 
         if($result){
              drupal_set_message($result);

              if($user_account -> isMitarbeiter()){
                 $verlag = $user_account -> getVerlag();
                 drupal_goto("user/" . $verlag . "/struktur");
              }

              drupal_goto("verlage/inaktiv");
         } 
      }

      if($_GET["action"] == "deactivate"){

          if(!isset($_GET["confirm"])){
              drupal_set_title("Benutzer deaktivieren");
              return lokalkoenig_user_info_confirm($user_account, 'deactivate', 'Deaktivieren');
          }

          $result = $maintance -> userDeactivate(); 
          if($result){
              drupal_set_message($result);
              drupal_goto("user/" . $account -> uid . "/info");
           } 
      }

      if($_GET["action"] == "activate"){

          if(!isset($_GET["confirm"])){
              drupal_set_title("Benutzer aktivieren");
              return lokalkoenig_user_info_confirm($user_account, 'activate', 'Aktivieren');
          }


         $result = $maintance ->userActivate(); 
         if($result){
              drupal_set_message($result);
              drupal_goto("user/" . $account -> uid . "/info");
         } 
      }

  }

  $links_rendered = array();
    
  foreach($links as $link){
    $links_rendered[] = '<a href="'. $link["link"] .'" class="btn-sm '. $link["class"] .'">' . $link["title"] .'</a>';
  }
    
  
  $return = '<div class="well well-white">';
  $return .= '<p><strong>User-Information</strong></p>' . $table . '<hr />';

  if($links_rendered){
    $links = '<ul class="list-inline"><li>'. implode("</li><li>", $links_rendered) .'</li></ul>';
    $return .= '<div class="">'. $links .'</div>';
  }
  else {
    $return .= '<p><strong>Der Status dieses Benutzers kann nicht verändert werden.</strong></p>';
  }
  $return .= '</div>';
  return $return;    
}


function lokalkoenig_user_info_confirm($account, $action, $title){
    
  $return = '<div class="panel panel-info"><div class="panel-body">'
         . '<p>Sind Sie sicher dass Sie diese Aktion ausführen wollen</p><hr />';

  $return .='<a href="'. url($account -> getInfoUrl()) .'" class="btn btn-default pull-right">Abbrechen und zurück</a>';
  $return .='<a href="'. url($account -> getInfoUrl(), array("query" => array("action" => $action, "confirm" => 1))) .'" class="btn btn-primary">'. $title .'</a>';
  $return .= '</div></div>';
    
  return $return;    
}
