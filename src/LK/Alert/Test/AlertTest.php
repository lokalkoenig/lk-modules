<?php

namespace LK\Alert\Test;

use LK\Alert\AlertManager;
use LK\Tests\TestCase;
use LK\Alert\Cron\AlertCron;
use LK\Solr\Search;

class AlertTest extends TestCase {
    
    function build() {
        // create a Outdated 
        $manager = new AlertManager();
        $alerts = $manager->getUserAlerts(\LK\current());

        if(count($alerts) === 0){
          $this -> printLine('______', 'Keine Alerts erstellt');
          return ;
        }
        
        rsort($alerts);

        $alert = $manager ->loadAlert($alerts[0]);
        $this -> printLine('Use Last Alert', $alert);
        $newtime = time() - 60*60*24*356 * 2;
        $alert -> updateTimestamp($newtime);
        $this -> printLine('Kampagnen', $alert -> getCount());
        $query = $alert->getQuery();
        
        $search = new Search();
        $search ->addFromQuery($query);
        $search ->addTimestamp($newtime);
        $new_nodes = $search ->getNodes();

        if(!$new_nodes){
          $this -> printLine('Keine neuen Kampagnen seit ' . format_date($newtime), "Keine Email");
        }
        else {
          $this -> printLine('Neue Kampagnen seit ' . format_date($newtime), implode(", ", $new_nodes));
          $this -> printInfo('Du solltest jetzt eine Private Nachricht mit Kampagnen.');
        }

        $this -> printLine('______', '______');
        $this -> printLine('______', 'Run the cron');
        
        try {
          $alertcron = new AlertCron();
          $alertcron -> run();
        } catch (Exception $ex) {
          $this -> printLine('Cronrun', "Failed");
        }
        
        
    
    }
}