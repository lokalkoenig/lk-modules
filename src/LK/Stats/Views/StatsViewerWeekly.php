<?php

namespace LK\Stats\Views;

use LK\Stats\Views\StatsViewer;

/**
 * Description of StatsViewerWeekly
 *
 * @author Maikito
 */
class StatsViewerWeekly extends StatsViewer {

  function __construct($type, $id = 0) {
    $this->aggregator_synthax = '____-KW-%';
    $this->time_label = "Woche";
    $this->time_label_prev = "Vorwoche";
    
    parent::__construct($type . '-weekly', $id);
  }
}
