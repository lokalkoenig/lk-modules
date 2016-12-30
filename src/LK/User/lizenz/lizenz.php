<?php
namespace LK;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Lizenz {
    
   var $id = null;
   var $data;
   var $vku_id;
   var $ausgaben = array();
   
   function getId(){
       return $this -> id;
   }
   
   function is(){
       return $this -> id;
   }
   
   function getShortSummary(){
     return "Erworben am: " . format_date($this-> data -> lizenz_date) . " / " . $this -> data -> lizenz_downloads . " &times heruntergeladen";
   }
   
   
   
   
   
   function __construct($id) {
       
       $dbq2 = db_query("SELECT * FROM lk_vku_lizenzen WHERE id='". $id ."'");
       $lizenz = $dbq2 -> fetchObject();
       
       if(!$lizenz){
           return ;
       }
   
       $this -> data = $lizenz;
       $this -> id = $id;
       $this -> vku_id = $this -> data -> vku_id;
       
       $dbq = db_query("SELECT ausgabe_id FROM lk_vku_lizenzen_ausgabe WHERE lizenz_id='". $id ."'");
       foreach($dbq as $all){
            $this -> ausgaben[] = $all -> ausgabe_id;
       } 
   }
   
   function getDate(){
       return $this -> data -> lizenz_date;
   }
   
   function getAuthor(){
       return $this -> data -> lizenz_uid;
   }
   
   function getDownloads(){
       
       $downloads = array();
       $db2 = db_query("SELECT * FROM lk_vku_lizenzen_downloads WHERE lizenz_id='". $this -> id ."'");
       foreach($db2 as $all2){
          $downloads[] = u($all2 -> uid) . " (". format_date($all2 -> download_date) .")"; 
       } 
       
   return $downloads;    
   }
   
   function getAusgaben(){
      return $this -> ausgaben; 
   }
   
   function setAusgaben($ausgaben){
       
       $id = $this -> getId();
       // delete all them
       db_query("DELETE FROM lk_vku_lizenzen_ausgabe WHERE lizenz_id='". $id ."'");
       
       $plz_collection = array();
       $this -> ausgaben = array();
       foreach($ausgaben as $ausgabe){
           $object = new Ausgabe($ausgabe);
           $plz = $object ->getPlz();
           $this -> ausgaben[] = $ausgabe;
           db_query("INSERT INTO lk_vku_lizenzen_ausgabe SET lizenz_id='". $id ."', ausgabe_id='".  $ausgabe."'");
           
           // generate an PLZ-Area
           foreach($plz as $item){
               if(!in_array($item, $plz_collection)){
                   $plz_collection[] = $item;
               }
           }
       }
       
       
       if($this -> data -> plz_sperre_id){
           $sperre = new PlzSperre($this -> data -> plz_sperre_id);
           return $sperre ->setPlzTids($plz_collection);
       }
       
       
   return false;    
   }
   
   function extend($until_timestamp){
      $vku = $this -> getVku();
      $vku ->setStatus('purchased');
      $id = $this ->getId();
      
      $this -> data -> lizenz_until = $until_timestamp;
      db_query("UPDATE lk_vku_lizenzen SET lizenz_download_serverfilename='', lizenz_until='". $until_timestamp ."' WHERE id='". $id ."'");
      return \lk_note('lizenz-admin', "Erweitere Lizenz " . $id . " bis zum " . format_date($until_timestamp));
   }
   
   function getEditUrl(){
       return 'logbuch/editlizenz/' . $this -> id;
   }
   
   /**
    * Gibt die Lizenz zurück
    * 
    * @return \VKUCreator
    */
   function getVku(){
        return new \VKUCreator($this -> vku_id);
   }
           
   function remove(){
      // Remove PLZ-Sperre
      $id = $this -> id;
      $plz_sperre_id = $this -> data -> plz_sperre_id;
      $vku_id = $this -> vku_id;
      
      db_query("DELETE FROM lk_vku_lizenzen WHERE id='". $id ."'");
      db_query("DELETE FROM lk_vku_lizenzen_ausgabe WHERE lizenz_id='". $id ."'");
      db_query("DELETE FROM lk_verlag_log WHERE vku_id='". $vku_id ."' AND	nid='". $id ."'");
      
      // Lösche PLZ -Sperre
      if($plz_sperre_id){
          $manager = new \LK\Kampagne\SperrenManager();
          $manager ->removeSperre($plz_sperre_id);
       }
       
      // checken if VKU has a Lizenz
      $vku = $this -> getVku();
      $test = $vku -> getLizenzen();
      if(!$test){
         $vku -> setStatus('deleted'); 
         $vku -> logEvent('remove', 'Status geändert auf Deleted, da Lizenz gelöscht wurde.'); 
      }
      
      return \lk_note('lizenz-admin', "Lösche Lizenz " . $id);
   }
    
   function __toString() {
      
       $vku = new \VKUCreator($this -> vku_id); 
       $node = node_load($this -> data -> nid); 
      
       $array = array();
       
       $array[] = 'Downloads gültig bis: ' . format_date($this -> data -> lizenz_until);
       $array[] = 'Kampagne: ' . l($node->title, 'node/'. $node->nid);
       $array[] = 'VKU: ' .  l($vku ->getTitle(), $vku -> url(), array("html" => true));
       
       if($this -> data -> lizenz_verlag_uid){
            $verlag = \LK\get_user($this -> data -> lizenz_verlag_uid);
            $array[] = 'Verlag: ' .  (string)$verlag;
       }
       
       $ausgaben = $this ->getAusgaben();
       $ausgaben_formatted = array();
       foreach($ausgaben as $au){
           $a = \LK\get_ausgabe($au);
           $ausgaben_formatted[] = $a ->getTitleFormatted();
       }
       
      if($ausgaben){
          $ausgaben_formatted[] = 'Ausgaben: ' . implode(" ", $ausgaben_formatted);
      }
      
      $downloads = $this ->getDownloads();
      
      if($downloads){
         $array[] = '<strong>Downloads: (' . count($downloads) . ')</strong> <ul><li>' . implode("</li><li>", $downloads) . '</li></ul>';  
       } 
       
      return '<ul><li>'. implode('</li><li>', $array) .'</li></ul>'; 
   }
}


class LizenzCreator {
    
    
}

/**
 * Class PlzSperre
 */
class PlzSperre {
    
    var $id = null;
    var $entity = null;
    var $nid = null;
    
    function __construct($id) {
        $this -> id = $id;
        $entity = entity_load_single('plz', $id);
        
        if(!$entity){
           new \Exception('Can not find a PLZ-Id.');
        }
        $this -> entity = $entity;
        $this -> nid = $this -> entity->field_medium_node['und'][0]['nid'];
    }
    
    function getNid(){
        return $this -> nid;
    }
    
    function getPlzTids(){
       
       $array = array();
       foreach($this -> entity -> field_plz_sperre['und'] as $id){
           $array[] = $id["tid"];
       }
        
     return $array;   
    }
    
    function setPlzTids($tids){
        $this -> entity  -> field_plz_sperre["und"] = array();
        
        foreach($tids as $tid){
            $this -> entity -> field_plz_sperre["und"][]["tid"] = $tid;
        }
        
        $this -> entity -> save();
        $nid = $this ->getNid();
        
        $manager = new \LK\Kampagne\SperrenManager();
        $manager ->updateNodeAccess($nid);        
    }
    
    function remove(){
         \entity_delete("plz", $this -> id); 
    }  
}

/**

  if($entry -> vku_id){
    $vku = new VKUCreator($entry -> vku_id);
    if($vku -> is()){
         $return .= '<br /><span class="glyphicon glyphicon-shopping-cart"></span> 
         <u>Verkaufsunterlage:</u> <em>' . $vku -> get("vku_title") . '</em> 
         vom ' . format_date($vku -> get("vku_created"));
    }  
    
    if($entry -> log_type == 'Lizenzen'){
        // Show also die Bereiche
        
        $dbq2 = db_query("SELECT * FROM lk_vku_lizenzen WHERE vku_id='". $entry -> vku_id ."'");
        $lizenz = $dbq2 -> fetchObject();
        
        if($lizenz){
          
           if(lk_is_moderator() AND (arg(0) == 'logbuch' OR (arg(0) == 'node' AND arg(2) == 'lizenzen'))){
                $admin = lk_verlag_log_entry_administrative($result, $lizenz);
            }
          
          
          $ausgaben = array();
          $dbq2 = db_query("SELECT a.ausgabe_id FROM lk_vku_lizenzen l, lk_vku_lizenzen_ausgabe a WHERE a.lizenz_id=l.id AND l.vku_id='". $entry -> vku_id ."'");
          foreach($dbq2 as $all){
              $ausgaben[] = format_ausgabe_kurz($all -> ausgabe_id);
          }
          
          $return .= '<br /><span class="glyphicon glyphicon-globe"></span> <u>Erworben für:</u> ' . implode(" ", $ausgaben);
          
          $downloads = array();
          $dbq2 = db_query("SELECT uid, download_date FROM lk_vku_lizenzen_downloads WHERE lizenz_id='". $lizenz -> id ."' ORDER BY download_date ASC");
          foreach($dbq2 as $all){
               $account = _lk_user($all -> uid);
               if($account -> uid == 0 OR lk_is_mitarbeiter($account) OR lk_is_verlag($account)){
                  $downloads[] = \LK\u($account -> uid) . " am " . format_date($all -> download_date);
               }
          }
          
          if(!$downloads){
           $return .= '<br /><span class="glyphicon glyphicon-download"></span> <u>Downloads:</u> <em>Keine</em>';
         }
          else
          $return .= '<br /><span class="glyphicon glyphicon-download"></span> <u>Downloads:</u> <ol style="margin-bottom: 20px;"><li>' . implode("</li><li>", $downloads) . '</li></ol>';
        }
        else {
           if(lk_is_moderator() AND (arg(0) == 'logbuch' OR (arg(0) == 'node' AND arg(2) == 'lizenzen'))){
                $admin = '<div class="pull-right"><em>Lizenz nicht mehr vorhanden</em></div>';
            }
        }
    }

 * 
 *  */