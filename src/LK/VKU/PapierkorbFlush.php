<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\VKU;


/**
 * Description of PapierkorbFlush
 *
 * @author Maikito
 */
class PapierkorbFlush extends \LK\Component {
    //put your code here
    
    
    /**
     * 
     * @param \stdClass $account
     */
    static function vku_func_flush_deleted(\stdClass $account){
    
        $current = self::getCurrentUser();

        if(!$current ->isModerator() OR $current -> getUid() != $account -> uid){
            drupal_goto("user/" . $account -> uid . "/vku");
        }
        
        self::logNotice("User löscht alle Verkaufsunterlagen aus dem Papierkorb");
        
        // get all deleted
        $dbq = db_query("SELECT vku_id FROM lk_vku WHERE vku_status='deleted' AND uid='". $account -> uid ."'");
        foreach($dbq as $data){
            $vku = new \VKUCreator($data -> vku_id);
            if($vku -> is('deleted')){
                drupal_set_message("Die Verkaufsunterlage " . $vku ->get('vku_title') . " wurde gelöscht."); 
                $vku ->remove();  
            }
        }
   
        drupal_goto("user/" . $account -> uid . "/vku");
    }
    
}
