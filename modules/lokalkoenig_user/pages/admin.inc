<?php

function lokalkoenig_user_info($account){
    require_once 'sites/all/modules/lokalkoenig/functions/lokalkoenig_user/classes/UserManager.Maintanance.class.php';

    $user_account = \LK\get_user($account);
    $maintance = new \LK\UserManagerMaintanance($user_account);
    
    drupal_set_title("Usermanager");
    lk_set_icon('tasks');
    
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
    
  
    $return = '<div class="panel panel-info"><div class="panel-body">';
    
    if($links_rendered){
        $links = '<ul class="list-inline"><li>'. implode("</li><li>", $links_rendered) .'</li></ul>';
        $return .= '<div class="">'. $links .'</div><hr />'; 
    }
    else {
       $return .= '<p class="alert alert-success">Der Status dieses Benutzers kann nicht verändert werden.</p>'; 
        
    }
    
    
    $return .= '<div class="well well-white"><h3>User-Information</h3>' . $table . '</div>';
    
    
    
    $return .= '</div></div>';
    
    
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

