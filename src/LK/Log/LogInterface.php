<?php

namespace LK\Log;

if(!isset($GLOBALS['LK_LOG'])){
  $GLOBALS['LK_LOG'] = true;
}

$GLOBALS['LK_LOG_RUN'] = [];

/**
 * Description of Log
 *
 * @author Maikito
 */
abstract class LogInterface {
    //put your code here
    const DB_TABLE = 'lk_log';
  
    var $table_rows = ['node_nid'];
    var $data = [];
    var $context = [];
    
    function init($category, $message, $context = []) {
    global $user;
     
        $this -> setCategory($category);
        $this -> set("message", $message);
        $this -> set("request_time", time());
        $this -> set("uid", $user -> uid);
        
        // add Context variables
        $context['REQUEST_URI'] = $_SERVER["REQUEST_URI"];
                
        while(list($key, $val) = each($context)){
             $this ->setContext($key, $val);
        }
    }
    
    final public function setCategory($category){
        $this -> set("category", $category);
    }
    
    final function setNid($nid){
        $this -> set("node_nid", $nid);
    }
    
    final function set($key, $val){
        $this -> data[$key] = $val;
    }
    
    final function setContext($key, $val){
        $this -> context[$key] = $val;
    }
    
    final function save(){

      // Do whatever save
      $data = $this -> data;
      $data["context"] = serialize($this -> context);

      if($GLOBALS['LK_LOG']){
        db_insert(LogInterface::DB_TABLE)->fields($data)->execute();
      }
      else {
        $GLOBALS['LK_LOG_RUN'][] = $data['message'];
      }

      return $data['message'];
    }
    
    final function setVku(\VKUCreator $vku){
          $this ->set("vku_id", $vku ->getId());
          $author = $vku ->getAuthor();
          $account = \LK\get_user($author);
          
          if($account){
              $this ->setUser($account);
          }
          
    return $this;      
    }
    
    final function setUser(\LK\User $account){
        $this ->set("uid", $account ->getUid());
        $this ->set("verlag_uid", $account ->getVerlag());
        $this ->set("team_id", $account ->getTeam());
    }   
    
    final function setLizenz(\LK\Lizenz $lizenz){
        $this ->set("lizenz_id", $lizenz ->getId());
    }    
}
