<?php

namespace LK\Tests;

use LK\Alert\AlertManager;
use LK\Tests\TestCase;
use LK\Alert\AltertCron;
use LK\Solr\Search;

class AlertTest extends TestCase {
    
    function build() {
        
        $query = array("search_api_views_fulltext" => "Sommer");
        
        // create a Outdated 
        $alert = AlertManager::create($query);
        $this -> printLine('Create new Alert', $alert);
        $newtime = time() - 60*60*24*356;
        $alert -> updateTimestamp($newtime);
        $this -> printLine('Kampagnen', $alert ->getCount());
        
        $search = new Search();
        $search ->addFromQuery($query);
        $search ->addTimestamp($newtime);
        $new_nodes = $search ->getNodes();
        
        $this -> printLine('Neue Kampagnen seit ' . format_date($newtime), implode(", ", $new_nodes));
        $this -> printLine('______', '______');
        $this -> printLine('______', 'Run the cron');
        
        try {
            AltertCron::run();
        } catch (Exception $ex) {
            $this -> printLine('Cronrun', "Failed");
        }
        
        $alert ->remove();
        $this -> printLine('Alert', "Remove");
        $this -> printInfo('Du solltest jetzt eine Private Nachricht mit Kampagnen zum Begriff Sommer erhalten haben.');
    
    }
}