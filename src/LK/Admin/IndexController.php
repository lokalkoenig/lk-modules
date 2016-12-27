<?php

/*
 * This functions are orphaned and not 
 * presented to the frontend yet
 */

namespace LK\Admin;

/**
 * Description of IndexController
 *
 * @author Maikito
 */
class IndexController {
    //put your code here
    
    static function pageReset(){
        $return = '<p>Manuell:</p>';
        $return .= '<ul>
        <li>'. l("Suchindex neu aufbauen", "admin/structure/computed_field_recompute") .' </li>
        <li>'. l("Apache SOLR neu aufbauen", "admin/config/search/search_api/index/default_node_index") .'</li>
        </ul>
        ';

        $return .= '<p><b>Automatisch:</b></p>';
        $return .= '<ul>
        <li>'. l("Jetzt neu aufbauen", "lkadmin/lkreset", array('query' => array('process' => 1))) .' </li>
        </ul>
        ';



        if(isset($_GET["process"])){
          $entities_by_type = array();
          $entities_by_type['node'][0]  = 'kampagne';

          $batch = _computed_field_tools_setup_batch_args('field_kamp_suche', $entities_by_type, 100);

          batch_set($batch);
          batch_process('lkadmin/lkreset/reindex');
        }
        
    return $return;    
    }
    
    static function pageReindex(){
        $search_api_index = search_api_index_load('default_node_index');
        // Clear the index.
        $search_api_index->clear();
        // Run!
        search_api_index_items($search_api_index, -1);
        drupal_set_message('Suchindex erfolgrech neu aufgebaut');
        drupal_goto("lkadmin/lkreset");
    }    
}
