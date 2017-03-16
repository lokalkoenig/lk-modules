<?php

namespace LK\Stats\Views;

/**
 * Description of StatsViewer
 *
 * @author Maikito
 */
class StatsViewer {
  
  /**
   * Can be verlag, user, team, lk
   * 
   * @var string 
   */
  var $stats_type;


  /**
   * Can be UID, Verlag-UID or Team-ID
   *
   * @var int
   */
  protected $stats_uid;

  protected $hide_form = FALSE;

  var $stats_values = [
    'activated_users' => 'Anzahl Mitarbeiter',
    'active_users' => 'Im Zeitraum aktive Benutzer',
    'created_vku' => 'Erstellte Verkaufsunterlagen',
    'generated_vku' => 'Abgeschlossene Verkaufsunterlagen',
    'purchased_vku' => 'Gekaufte Lizenzen',
    'merklisten' => 'Merklisten Einträge',
    'searches' => 'Key-Wort-Suchen',
    'accessed_kampagnen' => 'Angesehene Kampagnen',
  ];

  var $hide_stats_user = ['activated_users', 'active_users'];

  function __construct($type, $id = 0) {
    $this->stats_type = $type;
    $this->stats_uid = $id;
  }

  public function hideForm(){
    $this->hide_form = TRUE;
  }


  function getForm($values, $selected){

    $form['#method'] = 'get';

    $form["month"] = array(
      '#type' => 'select',
      '#name' => 'month',
      '#title' => 'Monat',
      '#options' => $values,
      '#value' => $selected,
      '#default_value' => $selected,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Anzeigen',
    );

    return '<form method="get">' .drupal_render($form)  . '</form>';
  }

  /**
   * Gets the LOG Month available
   *
   * @return array
   */
  private function getLogMonthes(){
    $where = array("stats_user_type='" .$this->stats_type ."'");
    $where[] = "stats_bundle_id='". $this->stats_uid ."'";

    $monthes = array();
    $dbq = db_query("SELECT DISTINCT stats_date FROM lk_verlag_stats WHERE " . implode(" AND ", $where) . " ORDER BY stats_date DESC");
    foreach($dbq as $all){
      $monthes[] = $all -> stats_date;
    }

    return $monthes;
  }


  /**
   * Renders the Stats
   *
   * @return string HTML
   */
  function render(){
    $monthes = $this->getLogMonthes();

    if(count($monthes) == 0) {
      return '<div class="well well-white">Keine Statistiken vorhanden.</div>';
    }

    if(isset($_GET['month']) && isset($monthes[$_GET['month']])) {
      $monat = $monthes[$_GET['month']];
      $test_next_month = $_GET['month'] + 1;
      $selected_month = $_GET['month'];
    }
    else {
      $monat = $monthes[0];
      $test_next_month = 1;
      $selected_month = 0;
    }

    $vormonat = [];
    $stats = (array)\LK\Stats::getLastEntry($this->stats_type, $this->stats_uid, $monat);

    if(isset($monthes[$test_next_month])) {
      $vormonat = (array)\LK\Stats::getLastEntry($this->stats_type, $this->stats_uid, $monthes[$test_next_month]);
    }
  
    $table = [];
    $x = 0;
    while(list($key, $title) = each($this->stats_values)) {

      if($this->stats_type === 'user' && in_array($key, $this->hide_stats_user)) {
        continue;
      }

      $table[$x] = [$title, $stats[$key], '', ''];

      if($vormonat) {
        $table[$x][2] = $vormonat[$key];
        $table[$x][3] = $this->calculate_diff($stats[$key], $vormonat[$key]);
      }
      
      $x++;
    }

    $form = $this->getForm($monthes, $selected_month);
    $subtitle = 'Die Statistiken geben einen Überblick der Aktivitäten der Mitarbeiter.';

    if($this->stats_type === 'user') {
      $subtitle = 'Die Statistiken geben einen Überblick Ihrer Aktivitäten.';
    }
   
    if($this->stats_type === 'user' && \LK\current()->getUid() == $this->stats_uid) {
      $table = $this->alterTableForCurrentUser($table);
    }

    $table_rendered = '<div class="well well-white">' . theme('table', array('header' => array("", $monat, "Vormonat", "%"), 'rows' => $table)) . '</div>';

    if($this->hide_form) {
      return $table_rendered;
    }

    return  '<div class="well well-white">
     <div class="row">
        <div class="col-xs-6">
           <h4>Statistiken</h4>
           <div class="help-block">'. $subtitle .'</div>
        </div>
        <div class="col-xs-6">'. $form . '</div>
     </div>
     <hr />
     ' . $table_rendered . '</div>';
  }

  /**
   * Alters the Table-Data for the current User
   *
   * @param array $rows
   * @return string
   */
  private function alterTableForCurrentUser($rows){

    $isuser = $this->stats_uid;

    $rows[1][0] .= ' ' .  l('<span class="glyphicon glyphicon-search"></span>', 'user/' . $isuser . "/vku", array("html" => true));
    $rows[2][0] .= ' ' .  l('<span class="glyphicon glyphicon-search"></span>', 'user/' . $isuser . "/vku", array("html" => true, 'query' => array('vku_status_2' => 2)));
    $rows[3][0] .= ' ' . l('<span class="glyphicon glyphicon-search"></span>', 'merkliste', array("html" => true));
    $rows[4][0] .= ' ' .  l('<span class="glyphicon glyphicon-search"></span>', 'user/' . $isuser . "/searches", array("html" => true));
    $rows[5][0] .= ' ' .  l('<span class="glyphicon glyphicon-search"></span>', 'history', array("html" => true));

    return $rows;
  }

  private function calculate_diff($value1, $value2){

    if($value1 > $value2){
      if($value1 == 0){
        $value = 100;
      }
      elseif($value2 == 0){
        return '-';
      }
      else {
        $value = round((($value1 - $value2) / $value2), 2)  * 100;
      }

      return '<span style="color:green">+ '. $value .'%</span>';
    }

    if($value1 < $value2){
      if($value2 == 0){
        $value = 100;
      }
      elseif($value1 == 0){
        return '-';
      }
      else {
        $value = abs(round((($value1 - $value2) / $value2), 2)  * 100);
      }

      return '<span style="color:red">- '. $value .'%</span>';
    }
        
    return '-';
  }

}
