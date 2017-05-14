<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * @path lkadmin/stats
 * @path lkadmin/pakete
 */

namespace LK\Admin;

/**
 * Description of StatsController
 *
 * @author Maikito
 */
class StatsController {
    //put your code here
    
    static public function pagePakete(){
        $inc = taxonomy_get_tree(7);
    
        $table = array();

        foreach($inc as $pak){
            $term = taxonomy_term_load($pak -> tid);
            $table[] = '<h2>'. $pak -> name . ' <small>'. self::count_kampagnen_paket($pak -> tid) .' Kampagnen</small><br /><small>'. $term -> description .'</small></h2><hr /><ul>';

            if(!isset($term->field_paket_medientypen['und'])){
                continue;
            }

            foreach($term->field_paket_medientypen['und'] as $type){
                $media = taxonomy_term_load($type["tid"]);
                $table[] = '<li style="margin-bottom: 15px;"><strong>' . $media -> name . '</strong>';

                if($media -> description) {
                    $table[] = ' &nbsp;&nbsp;&nbsp;('. $media -> description .')';
                }

                if(isset($media->field_medientyp_online_label['und'][0]['value'])){
                    $table[] = '<br /><u>Alternatives Online-Label:</u> ('. $media->field_medientyp_online_label['und'][0]['value'] .')';
                }

                $table[] = '<br /><u>Medien:</u> '. self::count_kampagnen_paket_media_size($pak -> tid, $media -> tid);
                $table[] = '</li>';
            }

            $table[] .= '</ul>';
        }
    
     return '<div class="well well-white"><div class="well well-white"><strong>Überblick über die aktuelle Verwendung von Paketen und Medien-Uploads</strong></div>'. implode("", $table) .'</div>';    
    }
    
    private static function count_kampagnen_paket_media_size($pak_id , $media_size_id){
          
        $dbq = db_query("SELECT count(*) as count "
                . "FROM field_data_field_kamp_preisnivau p, node n,  field_data_field_medium_node m, field_data_field_medium_typ t "
                . "WHERE n.nid=p.entity_id AND n.status='1' AND t.entity_id = m.entity_id "
                . "AND m.field_medium_node_nid=n.nid AND p.field_kamp_preisnivau_tid='". $pak_id ."' "
                . "AND t.field_medium_typ_tid='". $media_size_id ."'");
        $all = $dbq -> fetchObject(); 

    return $all -> count;       
    }
    
    
    private static function count_kampagnen_paket($tid){
         $dbq = db_query("SELECT count(*) as count "
           . "FROM field_data_field_kamp_preisnivau p, node n WHERE n.nid=p.entity_id AND n.status='1' AND p.field_kamp_preisnivau_tid='". $tid ."'");
        $all = $dbq -> fetchObject();

        return $all -> count;    
    }
    
    static public function pageStats(){
        $rows = array();
  
        // List also alle Pakete und deren möglichen Formate
        // Kampagnen online
        $dbq = db_query("SELECT count(*) as count FROM node WHERE type='kampagne' AND status='1'");
        $all = $dbq -> fetchObject();
        $rows[] = array('Kampagnen Online', $all -> count);

        $dbq = db_query("SELECT sum(filesize) as filesize2, count(*) as count FROM file_managed");
        $all = $dbq -> fetchObject();
        $rows[] = array('Kampagnendateien', $all -> count . " (". format_size($all -> filesize2) .")"); 
        $rows[] = array('', ''); 
        $rows[] = array('<strong>Große Dateien</strong>', ''); 


        $dbq = db_query("SELECT 
            s.entity_id, 
            f.filesize, 
            f.filename 
        FROM field_data_field_medium_source s, file_managed f 
            WHERE s.field_medium_source_fid=f.fid 
            ORDER BY f.filesize DESC LIMIT 10");
        foreach($dbq as $all) {

           $loading = entity_load('medium', array($all -> entity_id));
           $entity = $loading[$all -> entity_id]; 
           $nid = $entity->field_medium_node['und'][0]['nid']; 
           $node = node_load($nid);

           $rows[] = array($all -> filename . " - " . $node -> title . '<br />'. l($entity -> title, "node/" . $nid . "/media/" . $all -> entity_id . "/edit"), format_size($all -> filesize));  
        }   

        $size = 0;
        $deleted = 0;
        $rows[] = array('', ''); 
        $rows[] = array('<strong>Nicht benutzte Dateien</strong>', ''); 
        $dbq = db_query("SELECT fid FROM file_managed WHERE NOT EXISTS (SELECT * FROM file_usage WHERE file_managed.fid = file_usage.fid) ");
        foreach($dbq as $all) {
          $file = file_load($all->fid);
          if(!$file) continue; 

          $rows[] = array($file -> filename . " (". $all->fid .")", format_size($file -> filesize));
          $size += $file -> filesize;

          if(isset($_GET["clean"])){ 
            file_delete($file);  
            $deleted++; 
          }

        }

        $rows[] = array('<strong>Gesamt</strong>', format_size($size)); 
        if($size){
          $rows[] = array('<strong>&nbsp;</strong>', l("Löschen", 'lkadmin/stats', array("query" => array("clean" => 1)))); 
        }

        if(isset($_GET["clean"])){ 
           drupal_set_message($deleted . " Dateien wurden gelöscht.");
           drupal_goto("lkadmin/stats");
        }

        $rows[] = array('', ''); 
        $rows[] = array('<strong>Lizenzen</strong>', ''); 

        $files = 0;
        $filesize = 0;
        $dir = opendir("sites/default/private/downloads");
        while($all = readdir($dir)){
          if($all == '..' OR $all == '.') continue;

          $files++;
          $filesize += filesize("sites/default/private/downloads/" . $all);
         }

         $rows[] = array($files . ' Dateien', format_size($filesize));   


        $rows[] = array('', ''); 
        $rows[] = array('<strong>Aktive VKUs</strong>', ''); 

        $files = 0;
        $filesize = 0;
        $dir = opendir("sites/default/private/vku");
        while($all = readdir($dir)){
          if($all == '..' OR $all == '.') continue;

          $files++;
          $filesize += filesize("sites/default/private/vku/" . $all);
         }

         $rows[] = array($files . ' Dateien', format_size($filesize));  

        return  '<div class="well well-white"><h4>Gesamtstatistiken</h4><br />' . theme('table', array('header' => array(), 'rows' => $rows)) . '</div>';
    }    
}
