<?php

namespace LK\Admin\Tests;
use LK\Tests\TestCase;

/**
 * Description of TestSolr
 *
 * @author Maikito
 */
class UserCheckup extends TestCase {
    //put your code here
    
  function build() {
        //$this -> printLine('Kampagnen', $count);
    
    $users = [];
    
    $dbq = db_query("SELECT uid FROM users WHERE uid!='0'");
    while($all = $dbq -> fetchObject()){
      $users[] = $all -> uid;
    }
    
    $conflicts = 0;
    $x = 0;
    foreach ($users as $uid){
      $account = \LK\get_user($uid);
      $x++;
      
      if(!$account){
        $this -> printLine($uid, "Kein Account");
        continue;
      }
      
      //main
      if(!isset($account->user_data->profile['main'])){
          $this -> printLine($account, $account ->getRole() . " [". $account ->getUid() ."] hat keine Profildaten (main) ");
          $conflicts++;
      }
      
      if(isset($account->user_data->profile['verlag']) && !$account ->isVerlag()){
          $this -> printLine($account, $account ->getRole() . " [". $account ->getUid() ."] hat Verlagsprofil");
          $conflicts++;
      }
      
      if(isset($account->user_data->profile['mitarbeiter']) && !$account ->isMitarbeiter() && !$account ->isVerlagController() && !$account ->isVerlag()){
        $this -> printLine($account, $account ->getRole() . " [". $account ->getUid() ."] hat Mitarbeiterprofil");
        $conflicts++;
      }
      
      if($account ->isMitarbeiter() && $account ->isVerlagController() && $account -> verlag === 0){
        $this -> printLine($account, $account ->getRole() . " [". $account ->getUid() ."] hat keinen Verlag");
        $conflicts++;
      }
      
      if($account ->isMitarbeiter() && $account ->isVerlagController() && $account -> team === 0){
        $this -> printLine($account, $account ->getRole() . " [". $account ->getUid() ."] hat keine Team");
        $conflicts++;
      }
      
      if($conflicts === 50){
        $this -> printLine('___', 'to many conflicts');
        break;
      }
    }
    
    $this -> printLine('User', count($users) . "/" .  $x . " User tested");
  }   
}
