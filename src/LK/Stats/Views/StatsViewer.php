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
  private $stats_type;


  /**
   * Can be UID, Verlag-UID or Team-ID
   *
   * @var int
   */
  private $stats_uid;

  protected $aggregator_synthax = '____-__';
  protected $hide_form = FALSE;
  protected $time_label = 'Monat';
  protected $time_label_prev = 'Vormonat';


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

    if(in_array($type, ['user', 'user-weekly'])) {
      unset($this->stats_values['activated_users']);
      unset($this->stats_values['active_users']);
    }

    $current = \LK\current();
    if(lk_is_admin() && $current->isModerator()) {
      $this->stats_values += [
        'page_sessions' => 'Sessions',
        'page_hits' => "Seitenaufrufe",
        'page_time' => "Verbrachte Zeit",
      ];
    }
  }

  public function hideForm(){
    $this->hide_form = TRUE;
  }


  protected function getForm($values, $selected){

    $form['#method'] = 'get';

    $form["month"] = array(
      '#type' => 'select',
      '#name' => 'time',
      '#title' => $this->time_label,
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
    $where[] = "stats_date LIKE '". $this->aggregator_synthax ."'";

    $monthes = array();
    $dbq = db_query("SELECT DISTINCT stats_date FROM lk_verlag_stats WHERE " . implode(" AND ", $where) . " ORDER BY stats_date DESC");
    foreach($dbq as $all){
      $monthes[] = $all -> stats_date;
    }

    return $monthes;
  }

  /**
   * Gets a Stats DataSet
   *
   * @param string $type
   * @param int $id
   * @param string $time
   * @return Object
   */
  private function getLogData($time) {
    $where = array("stats_user_type='" .$this->stats_type ."'");
    $where[] = "stats_bundle_id='". $this->stats_uid ."'";
    $where[] = "stats_date='". $time ."'";

     $dbq = db_query("SELECT * FROM lk_verlag_stats "
            . "WHERE " . implode(" AND ", $where));



     return (array)$dbq -> fetchObject();
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

    if(isset($_GET['time']) && isset($monthes[$_GET['time']])) {
      $monat = $monthes[$_GET['time']];
      $test_next_month = $_GET['time'] + 1;
      $selected_month = $_GET['time'];
    }
    else {
      $monat = $monthes[0];
      $test_next_month = 1;
      $selected_month = 0;
    }

    // From a Link _time can have 2017-02
    if(isset($_GET['_time'])) {
      $key = array_search($_GET['_time'], $monthes);
      if($key) {
        $monat = $monthes[$key];
        $selected_month = $key;
        $test_next_month = $key + 1;
      }
    }

    $vormonat = [];
    $stats = $this->getLogData($monat);

    if(isset($monthes[$test_next_month])) {
      $vormonat = $this->getLogData($monthes[$test_next_month]);
    }
  
    $table = [];
    $x = 0;
    while(list($key, $title) = each($this->stats_values)) {

      if($this->stats_type === 'user' && in_array($key, $this->hide_stats_user)) {
        continue;
      }

      $table[$x] = [$title, $this->formatValue($key, $stats[$key]), '', ''];

      if($vormonat) {
        $table[$x][2] = $this->formatValue($key, $vormonat[$key]);
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

    $table_rendered = '<div class="well well-white">' . theme('table', array('header' => array("", $monat, $this->time_label_prev, "%"), 'rows' => $table)) . '</div>';

    if(lk_is_moderator()) {
      if(!in_array($this->stats_type, ['user', 'user-weekly'])) {
        $table_rendered .= $this->getUsersOnStatstype($stats);
      }
      else {
        $table_rendered .= $this->getUserSearches($stats);
      }
    }

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

  private function formatValue($key, $value) {

    if($key === 'page_time' && $value != 0) {
      return \format_interval($value);
    }

    return $value;
  }

  /**
   * Gets the Users Search-Keywords
   * for the given Time-Frame
   *
   * @param array $stats
   * @return string
   */
  private function getUserSearches($stats) {

    if(get_class($this) === 'LK\Stats\Views\StatsViewerWeekly') {
      //2017-KW-12
      $explode = explode('-', $stats['stats_date']);
      $from = strtotime(date('Y-m-d -01 00:00:01', strtotime($explode[0] ."-W". $explode[2] . "-1")));
      $to = strtotime(date('Y-m-d 23:59:59', strtotime($explode[0] ."-W". $explode[2] ."-7")));
    }
    else {
      $from = strtotime($stats['stats_date'] . '-01 00:00:01');
      $to = strtotime(date("Y-m-t", $from) . ' 23:59:59');
    }

    $words = [];
    $dbq = db_query('SELECT * FROM lk_search_history WHERE uid=:uid AND created BETWEEN '. $from .' AND '. $to .' ORDER BY created DESC',
            [':uid' => $stats['stats_bundle_id']]);
    foreach($dbq as $all) {
      $link = \LK\Solr\SearchQueryParser::buildLink(unserialize($all->search_text));
      $words[] = [date('d.m.Y', $all -> created), $all->search_string, $all->search_count, '<a href="'. $link .'"><span class="glyphicon glyphicon-link"></span></a>'];
    }
   
    if(!$words) {
      return ;
    }

    $user_table_rendered = theme('table', array('header' => array("Datum", "Suchwort", "Ergebnisse", 'Link'), 'rows' => $words));
    return '<div class="well well-white"><h4>Suchen</h4>'. $user_table_rendered .'</div>';
  }

  /**
   * Displays detailed Stats
   *
   * @param array $stats
   * @return string
   */
  private function getUsersOnStatstype($stats) {

    $where = ["stats_date='". $stats['stats_date'] ."'"];
    if(get_class($this) === 'LK\Stats\Views\StatsViewerWeekly') {
      $where[] = "stats_user_type='user-weekly'";
    }
    else {
      $where[] = "stats_user_type='user'";
    }

    if($stats['stats_user_type'] === 'verlag-weekly' || $stats['stats_user_type'] === 'verlag') {
      $where[] = "user_stats_verlag_uid='". $stats['stats_bundle_id'] ."'";
    }
    elseif($stats['stats_user_type'] === 'team' || $stats['stats_user_type'] === 'team-weekly') {
      $where[] = "user_stats_team_id='". $stats['stats_bundle_id'] ."'";
    }

    $base = 'stats';
    if(get_class($this) === 'LK\Stats\Views\StatsViewerWeekly') {
      $base = 'stats/weekly';
    }

    $user_table = [];

    $dbq = db_query('SELECT stats_bundle_id, page_hits, page_time, page_sessions, searches, accessed_kampagnen FROM lk_verlag_stats WHERE ' . implode(' AND ', $where) . ' ORDER BY page_time DESC');
    foreach($dbq as $all) {
      $user_name = \LK\u($all->stats_bundle_id);
      $link = url('user/' . $all->stats_bundle_id . '/' . $base, ['query' => ['_time' => $stats['stats_date']]]);
      $user_name .= ' <a class="small" href="'.  $link . '"><span class="glyphicon glyphicon-link"></span></a>';


      $searches = $kampagnen = '-';
      if($all->searches) {
        $searches = $all->searches;
      }

      if($all->accessed_kampagnen) {
        $kampagnen = $all->accessed_kampagnen;
      }

      $user_table[] = [$user_name, format_interval($all->page_time), $all->page_hits, $all->page_sessions, $searches, $kampagnen];
    }

    if(!$user_table) {
      return ;

    }

    $user_table_rendered = theme('table', array('header' => array("Benutzer", "Zeit", "Seitenaufrufe", "Sessions", "Suchen", "Kampagnen"), 'rows' => $user_table));

    if(in_array($this->stats_type, ['lk', 'lk-weekly'])) {
      return '<div class="well well-white"><h4>Aktive Benutzer *</h4>'. $user_table_rendered .'<div><hr /><small>* Einige Benutzer sind nicht in den generellen Statistiken inkludiert.</small></div></div>';
    }
 
    return '<div class="well well-white"><h4>Aktive Benutzer</h4>'. $user_table_rendered .'</div>';
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
