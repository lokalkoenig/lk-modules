<?php

namespace LK\Admin;

class UserManagerMaintanance {
    
    use \LK\Log\LogTrait;
    var $LOG_CATEGORY = "User";
  
    var $account = NULL;
    var $uid = NULL;
    var $can_deactivate = false;
    var $can_activate = false;
    var $can_delete = false;
    
    
    
   function __construct($account) {
      $this -> account = $account;
      $this -> uid = $account -> getUid();
   }
   
   
   function canDeactivate(){
       return $this -> can_deactivate;
   }
   
   function canActivate(){
       return $this -> can_activate;
   }
   
   function canDelete(){
       return $this -> can_delete;
   }
   
   function userDelete(){
       
       if(!$this ->canDelete()){
           return false;
       }
       
       $role = $this -> account -> getRole();
       if($role == 'verlag'){
            $this -> _removeVerlagTeams();
            $this ->_removeVerlagAusgaben();
       }
       
       if($role == 'mitarbeiter'){
          // Konvertiere alle Lizenzen zum Verlags-Account und anonymisiere die Lizenzen
          $this -> _removeSimpleData(); 
          $this ->_removeUserVKU();
          $this -> _removeTeamMembership();
       }
       
       if($role == 'agentur'){
            $this -> _removeSimpleData(); 
            $this -> _removeKampagnen();
       }
       
       user_delete($this -> uid);
       return $this -> log("Account wurde gelöscht.");
   }
   
   
   private function _removeVerlagTeams(){
       
       $teams = $this -> account -> getTeams();
       while(list($id, $team) = each($teams)){
           $this -> _removeTeam($team);
       }       
   }
   
   private function _removeVerlagAusgaben(){
       
       $ausgaben = $this -> account -> getAusgaben();
       while(list($id, $ausgabe) = each($ausgaben)){
           $this ->_removeAusgabe($ausgabe);
       } 
   }
   
   
   private function _removeTeam($team){
       
       if($team){
           $id = $team -> getId();  
           $title = $team -> getTitle();
           entity_delete("team", $id);
           $this -> log("Lösche Team [". $id  ."/". $title ."]");
           
           // Deaktivierte User
           // Update alle die das Team noch haben
       }
   }
   
   
   private function _removeAusgabe($ausgabe){
       
       if($ausgabe){
           $id = $ausgabe -> getId();  
           $title = $ausgabe -> getTitle();
           entity_delete("ausgabe", $id);
           $this -> log("Lösche Ausgabe [". $id  ."/". $title ."]"); 
       }
   }

   private function _removeTeamMembership(){
     
       $teamleiter = $this -> account -> isTeamleiter();
        if($teamleiter){
              $team = $this -> account -> getTeam();
              $this ->_removeTeam($team);
        }
   }


   private function _removeKampagnen(){
       $uid = $this -> uid;
       
       // Only fetch allready disabled Nodes
       $dbq = db_query("SELECT nid FROM node WHERE type='kampagne' AND status='0' AND uid='". $uid ."'");
       foreach($dbq as $all){
           $node = node_load($all -> nid);
           if($node){
               $this -> log("Lösche deaktivierte Kampagne [". $node -> nid ."/". $node -> title ."]"); 
               node_delete($node -> nid);
           }
       }
   }
  
   
   function userActivate(){
       
       if($this ->canActivate()){
           $save = user_load($this -> uid);
           $save -> status = 1;
           user_save($save);  
           
           $return = $this -> log("Account wurde aktiviert.");
           return $return;
       } 
       
   return false;   
   }
   
   function userDeactivate(){
      if($this ->canDeactivate()){
           // Schaue wie es bei Verlagen aussieht 
          
           if($this -> account -> isVerlag()){
              $this -> _verlagDeactivateAccounts();
           }
          
          
           $save = user_load($this -> uid);
           $save -> status = 0;
           user_save($save);
           
           
           
           $return = $this -> log("Account wurde deaktiviert.");
           return $return;
      }      
      
   return false;   
   }
   
   
   private function _verlagDeactivateAccounts(){
       
       $accounts = $this -> account -> getActiveUsers();
       
       foreach($accounts as $account){
           $object = get_user((int)$account);
           
           if($object AND $object -> isMitarbeiter()){
              $save = user_load($object -> getUid());
              $save -> status = 0;
              user_save($save);
              $this -> log($save -> name . " deaktiviert, da Verlag deaktiviert wurde.");  
           }
       }
   }
   
   
   private function _removeUserVKU(){
       $uid = $this -> uid;
       
       // Wir nehmen an dass Vku keine Lizenz haben. Lizenzen wurden vorher
       // auf den Verlag konvertiert. 
       $dbq = db_query("SELECT vku_id FROM lk_vku WHERE uid='". $uid ."'");
       foreach($dbq as $all){
           $vku = new \VKUCreator($all -> id);
      
           $title = $vku ->getTitle();
           if(!$vku ->remove()){
               $this -> log("Löschen der VKU (". $all -> id ."/". $title .") fehlgeschlagen.");
           }
       }
   }
   
    private function _removeSimpleData(){
       $uid = $this -> uid;
       
       $dbq = db_query("DELETE FROM lk_merklisten_terms WHERE uid='". $uid ."'");
       $count = $dbq -> rowCount();
       if($count){
           $this -> log("Lösche Merklisten Begriffe (". $count  .")");
       }
       
        
       $dbq = db_query("DELETE FROM lk_vku_plz_sperre WHERE uid='". $uid ."'");
       $count = $dbq -> rowCount();
       if($count){
           $this -> log("Lösche Kurzfirstige Sperren (". $count  .")");
       }
       
       $dbq = db_query("DELETE FROM lk_lastviewed WHERE uid='". $uid ."'");
       $count = $dbq -> rowCount();
       if($count){
           $this -> log("Kampagnenhistorie (". $count  .")");
       }
       
       $dbq = db_query("DELETE FROM lk_search_history WHERE uid='". $uid ."'");
       $count = $dbq -> rowCount();
       if($count){
           $this -> log("Suchen (". $count  .")");
       }
       
       $dbq = db_query("DELETE FROM lk_neuigkeiten_read WHERE uid='". $uid ."'");
       $count = $dbq -> rowCount();
       if($count){
           $this -> log("Neuigkeiten-Statistiken (". $count  .")");
       }
       
       
       // delete merklisten
       $dbq = db_query("SELECT id FROM eck_merkliste WHERE uid='". $uid ."'");
       foreach($dbq as $all){
            $entity = entity_load_single('merkliste', $entity_id);
            if($entity){
               $this -> log("Lösche Neuigkeit (". $entity -> title  .")");
               entity_delete("merkliste", $entity -> id);
            }
       }
   }
   
   /**
    * Loggs a Message to the Common log
    * 
    * @param String $message
    */
   function log($message){
     $log = $message;
     $title = $this -> account -> getTitle(); 
     $log .= ' ['. $title .' - UID: '. $this -> uid .']';
     
     return $this->logNotice($message);   
   }
   
   function listStats(){
       
       $created = $this -> account -> getCreated();
       $accessed = $this -> account -> getLastAccess();
       
       $array = array();
       
        $array[] = array(
            'title' => 'Account-typ',
            'count' => ucfirst($this -> account -> getRole())
         );
       
       
        $array[] = array(
            'title' => 'Account seit',
            'count' => format_date($created)
         );
       
        $array[] = array(
            'title' => 'Letzer Zugriff',
            'count' => format_date($accessed)
         );
       
        
       if($this -> account -> isMitarbeiter() OR $this -> account -> isVerlag() OR $this -> account -> isVerlagController()){
          
          $status = $this -> account -> getStatus(); 
          if($status){
              $this -> can_deactivate = true;
          }
          else {
              $this -> can_activate = true;
              $this -> can_delete = true;
          }
          
           
          if($this -> account -> isVerlag()){
            $active_people = $this -> account -> getPeopleCount(1);
            
            
            $array[] = array(
                'important' => false,
                'title' => '<strong>Verlagsaccount</strong>',
                'count' => ''
            );
            
            $count = $this -> account -> getPeopleCount(1);
            
            $array[] = array(
              'id' => 'members',
              'important' => !empty($active_people),
              'title' => "Anzahl Unteraccounts",
              'count' => $count . " - " . l("Ansehen", 'user/'. $this -> account -> getUid() .'/struktur'),
               'info' => 'Bitte löschen Sie zuvor alle Benutzer dieses Verlages um den Verlag zu löschen! Beim Deaktivieren werden alle Accounts deaktiviert.' 
            );  
            
           $deactive_people = $this -> account -> getPeopleCount(0);
           
            $array[] = array(
              'id' => 'members_notactive',
              'important' => false,
              'title' => "Anzahl Unteraccounts (Inaktiv)",
              'count' => $deactive_people
            );  
            
            if($active_people OR $deactive_people){
                $this -> can_delete = false;  
            }
            
            $count = $this ->getTableData('lk_vku_lizenzen', 'lizenz_verlag_uid');
            
            $array[] = array(
              'id' => 'lizenzen_verlag',
              'important' => !empty($count),
              'title' => "Im Verlag gebuchte Lizenzen",
              'count' => $count
            );
            
            if($count){
                $this -> can_delete = false;     
            }
            
            // Ausgaben
            $ausgaben = $this -> account -> getAusgaben();
            if($ausgaben){
                
                 $array[] = array(
                   
                    'title' => "<strong>Ausgaben</strong>",
                    'count' => count($ausgaben)
                 );
                
                while(list($key, $ausgabe) = each($ausgaben)){
                   $array[] = array(
                    'id' => 'lizenzen_verlag',
                    'title' => '- ' . $ausgabe -> getTitle(),
                    'count' => ''
                 ); 
                }
            }
            
            $teams = $this -> account -> getTeams();
              if($teams){
                
                 $array[] = array(
                    'title' => "<strong>Teams</strong>",
                    'count' => count($teams)
                 );
                
                while(list($key, $team) = each($teams)){
                   $array[] = array(
                    'title' => '- ' . $team -> getTitle(),
                    'count' => $team -> getUser_count() . " Benutzer"
                 ); 
                }
            }
            
            
            
            
            
          } 
          
          $verlag = $this -> account -> getVerlag();
          
          $array[] = array(
                'important' => false,
                'title' => '<strong>Mitarbeiter</strong>',
                'count' => ''
            );
           
          $array[] = array(
              'id' => 'lastviewed',
              'important' => false,
              'title' => "Zuletzt angeschaute Kampagnen",
              'db_simple' => 'lk_lastviewed',
              'count' => $this ->getTableData('lk_lastviewed')
          );
          
          $array[] = array(
              'id' => 'merklisten_terms',
              'important' => false,
              'db_simple' => 'lk_merklisten_terms',
              'title' => "Angelegte Merklisten-Kategorien",
              'count' => $this ->getTableData('lk_merklisten_terms')
          );
          
           $array[] = array(
              'id' => 'merklisten',
              'important' => false,
              'title' => "Angelegte Merklisten-Einträge",
              'count' => $this ->getTableData('eck_merkliste')
          );
          
          $array[] = array(
              'id' => 'search_history',
              'important' => false,
              'db_simple' => 'lk_lastviewed',
              'title' => "Suchhistorie",
              'count' => $this ->getTableData('lk_search_history')
          );
          
          
       
          
          
          $array[] = array(
              'id' => 'vku',
              'important' => false,
              'db_simple' => false,
              'title' => "Verkaufsunterlagen",
              'count' => $this ->getTableData('lk_vku')
          );
          
          $count = $this ->getTableData('lk_vku_lizenzen', 'lizenz_uid');
          
          if($count){
            $array[] = array(
              'id' => 'lizenz',
              'important' => !empty($count),
              'title' => "Lizenzen",
              'info' => "Dieser Benutzer hat Kampagnen lizenziert",  
              'count' => $count
            );
            
            $this -> can_delete = false;
          }
          
          
          $count = $this ->getTableData('eck_neuigkeit');
          if($count){
            $array[] = array(
              'title' => "Neuigkeiten",
              'count' => $count
            ); 
          }
          
          $count = $this ->getTableData('lk_neuigkeiten_read');
          if($count){
             $array[] = array(
              'title' => "Neuigkeiten Log",
              'count' => $count
            );
          }
          
          
        
           
          if($this -> account -> isMitarbeiter()){
              $teamleiter = $this -> account -> isTeamleiter();
              $team = $this -> account -> getTeamObject();
              
              if($teamleiter){
                  $important = true;
                  
                  // Checken wieviele MA das Team hat;
                  $count = $team -> getUser_count();
                  
                  if($count <= 1){
                      $important = false;
                      $msg = null;
                  }
                  else {
                     $this -> can_delete = false;
                     $this -> can_deactivate = false;
                     $msg = 'Aktive Teamleiter können nicht deaktiviert werden.';
                  }
                  
                  $array[] = array(
                     'id' => 'team',
                     'important' => $important,
                      'title' => "Team",
                      'count' => 'Teamleiter ' . l($team -> getTitle(), $team -> getUrl()),
                     'info' => $msg
                    );
                 
              }
              else {
                  if($team){
                     
                     
                   $array[] = array(
                      'title' => "Team",
                      'count' => l($team -> getTitle(), $team -> getUrl())
                    ); 
                  }
              }
          } 
       }
       
       
       if($this -> account -> isAgentur()){
          
           
           $uid = $this -> uid;
           $dbq = db_query("SELECT count(*) as count FROM node WHERE status='0' AND type='kampagne' AND uid='". $uid ."'");
           $res = $dbq -> fetchObject();
           $deactive_nodes = $res -> count;
           
           $dbq = db_query("SELECT count(*) as count FROM node WHERE status='1' AND type='kampagne' AND uid='". $uid ."'");
           $res = $dbq -> fetchObject();
           $active_nodes = $res -> count;
           
           
           $licences = $this ->getTableData('lk_vku_lizenzen', 'node_uid');
           
           $status = $this -> account -> getStatus(); 
           if($status){
              $this -> can_deactivate = true;
           }
           else {
              $this -> can_activate = true;
              $this -> can_delete = true;
           }
           
           if($licences){
               $this -> can_delete = false;
           }
           
           if($active_nodes){
             $this -> can_delete = false;
             $this -> can_deactivate = false;
           }
          
            $array[] = array(
                      'title' => "Lizenzierte Kampagnen",
                      'count' => $licences
            );   
          
            
            $array[] = array(
                      'title' => "Inaktive Kampagnen",
                      'count' => $deactive_nodes
            );   
          
            
            $array[] = array(
                      'title' => "Aktive Kampagnen",
                      'count' => $active_nodes 
            );   
           
       }
   
   return $array;    
   }
   
   function getLicences(){
       
       
   }
   
   function getTableData($table, $user_column = 'uid'){
     $uid  = $this -> uid;
     $dbq = db_query("SELECT count(*) as count FROM ". $table ." WHERE ". $user_column ."='". $uid ."'");
     $all = $dbq -> fetchObject();
     return $all -> count;
   }
   
   
   
   
}



?>