<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Kampagne;

/**
 * Description of SperreManager
 *
 * @author Maikito
 */
class SperrenManager {
    //put your code here
    use \LK\Log\LogTrait;
    var $LOG_CATEGORY = "PLZ";
  
    public function updateNodeAccess($nid){
        $this ->rebuildAusgabenAccess($nid);
    }
    
    
    function checkOutDatedSperren(){
        $nodes = array();
        $x = 0;
        $date = date("Y-m-d 00:00:00");
        $dbq = db_query("SELECT s.entity_id as plz_entity_id, m.field_medium_node_nid as nid FROM 
            field_data_field_plz_sperre_bis s,
            field_data_field_medium_node m,
            node node 
                WHERE 
              m.entity_id = s.entity_id
            AND m.field_medium_node_nid = node.nid 
            AND s.field_plz_sperre_bis_value <='". $date ."'");
        foreach($dbq as $all){
            $nodes[$all -> nid] = true; 
            $this ->removeSperre($all -> plz_entity_id);
            $x++;
        }
  
        if($x > 0){
            $this->logCron("Checke abgelaufene Regeln (". $x ." Regel)");
        } 
    }
    
    function updateAusgabe(\LK\Ausgabe $ausgabe){
        
        $id = $ausgabe ->getId();
        db_query("DELETE FROM na_node_access_ausgaben WHERE ausgaben_id='". $id ."'");
        db_query("DELETE FROM na_node_access_ausgaben_time WHERE aid='". $id ."'");
        
        // selects the Nodes who are affected
        $dbq = db_query("SELECT n.field_medium_node_nid as nid
            FROM 
            field_data_field_plz_sperre as p, 
            field_data_field_medium_node as n, 
            field_data_field_plz_sperre as ap,
            eck_ausgabe as aus
          WHERE 
            p.entity_id=n.entity_id AND n.entity_type = 'plz'  
            AND p.field_plz_sperre_tid=ap.field_plz_sperre_tid 
            AND ap.entity_type='ausgabe'
            AND aus.id=ap.entity_id 
            AND aus.id='". $id ."'
            GROUP BY n.field_medium_node_nid");
         
        while($all = $dbq -> fetchObject()){
            $this -> rebuildAusgabenAccess($all -> nid);
        } 
    }
    
    /**
     * Rebuilds a Sperre for a NID
     * 
     * @param Integer $nid
     * @return String Message
     */
    public function rebuildAusgabenAccess($nid){
        
        // first we remove the current Ausgaben
        db_query("DELETE FROM na_node_access_ausgaben WHERE nid='". $nid ."'");
        db_query("DELETE FROM na_node_access_ausgaben_time WHERE nid='". $nid ."'");
        
        // GET PLZ-Sperre from Nid
        $ausgaben_titles = array();
        $dbq = db_query("SELECT 
            aus.id as id, max(zeit.field_plz_sperre_bis_value) as until
            FROM 
                field_data_field_plz_sperre as p, 
                field_data_field_medium_node as n, 
                field_data_field_plz_sperre as ap,
                eck_ausgabe as aus,
                field_data_field_plz_sperre_bis zeit
              WHERE 
              p.entity_id=n.entity_id AND n.entity_type = 'plz' AND p.entity_type='plz'  
              AND n.field_medium_node_nid='". $nid ."'
              AND p.field_plz_sperre_tid=ap.field_plz_sperre_tid 
              AND ap.entity_type='ausgabe'
              AND aus.id=ap.entity_id 
              AND zeit.entity_id=p.entity_id
              GROUP BY aus.id");

        while($all = $dbq -> fetchObject()){
              $ausgabe = \LK\get_ausgabe($all -> id);
              if(!$ausgabe){
                  continue;
              }

              $ausgaben_titles[] = $ausgabe -> getTitle(); 
              $ausgabe_id = $ausgabe -> getId();
              $verlag  = $ausgabe -> getVerlag();

              $until = strtotime($all -> until);
              db_query("INSERT INTO na_node_access_ausgaben_time SET nid='". $nid ."', aid='". $ausgabe_id ."', until='". $until . "'");

              $plz = $ausgabe -> getPlz();
              $plz_alt = array();
              foreach($plz as $item){
                  $plz_alt[] = array('tid' => $item);
              }

              $aggr = plz_simplyfy($plz_alt);
              db_query("INSERT INTO na_node_access_ausgaben SET nid='". $nid ."', verlag_uid='". $verlag ."', ausgaben_id='". $ausgabe_id ."', plz_gebiet_aggregated = '". $aggr ."'");
        }
        
        $this ->logKampagne('Setze PLZ-Sperren für Ausgaben: ' . implode(", ", $ausgaben_titles), $nid);
    }
    
    /**
     * Creates a Sperre
     * 
     * @param Int $nid
     * @param Int $uid
     * @param array $ausgaben
     * @param type $duration
     * @return \LK\Kampagne\Sperre|boolean
     */
    function createSperre($nid, $uid, $ausgaben, $duration){
        
        // if we have no Ausgaben, then no Sperre
        if(!$ausgaben){
            return false;
        }
        
        $sperre = new Sperre($this);
        $sperre->setNid($nid);
        $sperre->setUser($uid);
        $sperre->setAusgaben($ausgaben);
        $sperre->setDuration($duration);
        $sperre->saveChanges();
        
    return $sperre;    
    }
    
    /**
     * Gets a Sperre object
     * 
     * @param type $id
     * @return boolean|\LK\Kampagne\Sperre
     */
    function getSperre($id){
        try {
            $sperre = new Sperre($this, $id);
            
        } catch (\Exception $ex) {
                return false;
        }
        
    return $sperre;    
    }
    
    /**
     * Removes a Sperre
     * 
     * @param type $id
     * @return boolean True or False
     */
    function removeSperre($id){
        $sperre = $this ->getSperre($id);
                
        if($sperre){
            $sperre ->remove();
            return true;
        }
    
    return $false;    
    }
    
}
