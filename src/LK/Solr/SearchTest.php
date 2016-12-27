<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace LK\Solr;

use LK\Alert\AlertManager;

/**
 * Description of SearchTest
 *
 * @author Maikito
 */
class SearchTest {
    //put your code here
    
    public static function run(){
            // Loads from an Alert
        
            $alert = AlertManager::load(67);
            $test = new Search();
            
            // the actual Query
            $query = $alert ->getQuery();
            
            dpm(SearchLabel::toLabel($query));
            $test -> addFromQuery($query);
            $num = $test ->getCount();
            
            dpm($num);
            $nodes = $test ->getNodes();
            dpm($nodes);
    }
}
