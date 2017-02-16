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

  $action = '';
  if(isset($_GET['action'])){
    $action = $_GET['action'];
  }

  $account = \LK\get_user($account_user -> uid);
  
  $manager = new \LK\Admin\Data\DataManager($account);
  $array = $manager ->getUserStats();

  $stats = \LK\UI\DataList::render($array);

  $links_rendered = [];
  
  if($manager -> canChangeUserState()){
    if($action === 'deactivate'){
      drupal_set_message($manager ->userDisable($account));
      drupal_goto('user/' . $account ->getUid() . "/info");
    }

    if($action === 'activate'){
      drupal_set_message($manager ->userEnable($account));
      drupal_goto('user/' . $account ->getUid() . "/info");
    }
   
    if($action === 'remove'){
      $v = $account ->getVerlag();
      drupal_set_message($manager ->userRemove($account));

      if($v){
        drupal_goto('user/'. $v .'/struktur');
      }
      else {
        drupal_goto('backoffice/users');
      }
    }
  }


  if($manager -> canChangeUserState()){
    if($account ->getStatus()){
      $links[] = array("link" => url("user/" . $account ->getUid(). "/info", array("query" => array("action" => "deactivate"))),
            "class" => "btn btn-sm btn-primary",
            "attributes" => [],
            "title" => "Benutzer deaktivieren");
    }
    else {
      $links[] = array("link" => url("user/" . $account ->getUid(). "/info", array("query" => array("action" => "activate"))),
            "class" => "btn btn-sm btn-primary",
            "attributes" => [],
            "title" => "Benutzer aktivieren");

      $links[] = array("link" => url("user/" . $account ->getUid(). "/info", array("query" => array("action" => "remove"))),
            "class" => "btn btn-sm btn-danger optindelete",
            "attributes" => ['optintitle' => 'Sind Sie sicher?', 'optin' => 'Der Account kann nicht wieder hergestellt werden.'],
            "title" => "Benutzer löschen");
    }

    foreach($links as $link){
      $links_rendered[] = '<a href="'. $link["link"] .'" class="btn-sm '. $link["class"] .'" '. drupal_attributes($link["attributes"]) .'>' . $link["title"] .'</a>';
    }
  }
  else {
    $links_rendered[] = '<p><small>Der Status des Users kann im Moment nicht verändert werden.</small></p>';
  }

  return '<div class="well well-white"><h4>Übersicht</h4>'. $stats .'<hr />'. implode(' ', $links_rendered) .'</div>';
}
