<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Tests;

use LK\Tests\TestCase;
use LK\Solr\Search;
/**
 * Description of TestSolr
 *
 * @author Maikito
 */
class TestSolr extends TestCase {
    //put your code here
    
    function build() {
        
        $term = "Sommer";
        if(isset($_GET["term"])){
            $term = $_GET["term"];
        }
        
        $search = new Search();
        $search -> setSearchTerm($term);
        $this -> printLine('Suche nach', $term . " GET param term");
        $count = $search ->getCount();
        $this -> printLine('Kampagnen', $count);
        
        $date = time() - 60 * 60 * 24 * 365;
        $this -> printLine('Suche nach', $term . " und Datum (". format_date($date) .")");
        $search ->addTimestamp($date);
        
        $count = $search -> getCount();
        $nodes = $search -> getNodes();
        $this -> printLine('Kampagnen', $count);
        $this -> printLine('Kampagnen', implode(', ', $nodes));
        
        
    }   
}
